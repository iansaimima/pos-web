<?php

defined('BASEPATH') or exit('No direct script access allowed');
class M_kas_alur extends MY_Model
{

    private $kas_engine;

    private $cabang_selected_uuid;
    private $cabang_selected_kode;
    private $actor_user_uuid;
    private $actor_user_name;

    private $allow;
    private $allow_create;
    private $allow_update;
    private $allow_delete;
    function __construct()
    {
        parent::__construct();

        $this->kas_engine = new Kas_engine();

        $cabang_selected = get_session("cabang_selected");
        $this->cabang_selected_uuid = $cabang_selected["uuid"];
        $this->cabang_selected_kode = $cabang_selected["kode"];

        $user = get_session("user");
        $role = strtolower(get_session("role"));
        $this->actor_user_uuid = isset($user["uuid"]) ? $user["uuid"] : "";
        $this->actor_user_name = isset($user["name"]) ? $user["name"] : "";

        $privilege_list = isset($user["privilege_list"]) ? $user["privilege_list"] : array();

        $this->allow        = isset($privilege_list["allow_kas_alur"]) ? $privilege_list["allow_kas_alur"] : 0;
        $this->allow_create = isset($privilege_list["allow_kas_alur_create"]) ? $privilege_list["allow_kas_alur_create"] : 0;
        $this->allow_update = isset($privilege_list["allow_kas_alur_update"]) ? $privilege_list["allow_kas_alur_update"] : 0;
        $this->allow_delete = isset($privilege_list["allow_kas_alur_delete"]) ? $privilege_list["allow_kas_alur_delete"] : 0;
    }

    function kas_alur_get_list($filters = array())
    {
        if (!$this->allow) return array();

        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->kas_engine->kas_alur_get_list($filters, true, true);

        $no = isset($filters["start"]) ? (int) $filters["start"] : 0;
        $final_res = array();

        foreach ($res as $r) {
            $no++;

            $row = array();
            $row["no"] = $no;
            $row["uuid"] = trim($r["uuid"]);
            $row["number_formatted"] = trim($r["number_formatted"]);
            $row["tanggal"] = $r["tanggal"];
            $row["kas_akun_nama"] = $r["kas_akun_nama"];
            $row["kas_kategori_nama"] = $r["kas_kategori_nama"];
            $row["alur_kas"] = ucwords($r["alur_kas"]);
            $row["keterangan"] = $r["keterangan"];
            $row["jumlah_masuk"] = number_format($r["jumlah_masuk"], 0, ",", ".");
            $row["jumlah_keluar"] = number_format($r["jumlah_keluar"], 0, ",", ".");

            $allow_edit = 1;
            if ($r["transaksi_pembelian_uuid"] > 0 || $r["transaksi_penjualan_uuid"] > 0) $allow_edit = 0;
            $row["allow_edit"] = $allow_edit;

            $final_res[] = $row;
        }

        return $final_res;
    }

    function kas_alur_get_filtered_total($filters = array())
    {
        if (!$this->allow) return 0;

        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->kas_engine->kas_alur_get_list($filters, false, true);
        return count($res);
    }

    function kas_alur_get_total($filters = array())
    {
        if (!$this->allow) return 0;

        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->kas_engine->kas_alur_get_list($filters);
        return count($res);
    }

    function kas_alur_get($uuid = "")
    {
        if (!$this->allow) return array();

        $filters = array();
        $filters["uuid"] = $uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->kas_engine->kas_alur_get_list($filters);
        if (count($res) == 0) return array();
        $res = $res[0];

        return $res;
    }

    function kas_alur_delete($uuid = "")
    {
        if (!$this->allow_delete) return set_http_response_error(HTTP_FORBIDDEN, "Akses ditolak");

        $filters = array();
        $filters["uuid"] = $uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->kas_engine->kas_alur_get_list($filters);
        if (count($res) == 0) return set_http_response_error(HTTP_BAD_REQUEST, "Invalid Kas");
        $res = $res[0];
        $uuid = $res["uuid"];
        $transaksi_pembelian_uuid = $res["transaksi_pembelian_uuid"];
        if (!empty($transaksi_pembelian_uuid)) {
            return set_http_response_error(HTTP_BAD_REQUEST, "Alur kas ini dibuat oleh modul pembayaran dan hanya bisa dihapus melalui modul pembayaran");
        }

        $this->db->trans_start();
        try {
            $res = $this->kas_engine->kas_alur_delete($uuid);
        } catch (Exception $e) {
            $this->db->trans_rollback();
            return set_http_response_error(501, "Error: " . $e->getMessage());
        }

        $this->db->trans_commit();
        return set_http_response_success("Kas telah dihapus");
    }

    function kas_alur_save($post = array())
    {
        $uuid = $this->input->post("uuid");
        $tanggal = $this->input->post("tanggal");
        $kas_akun_uuid = $this->input->post("kas_akun_uuid");
        $kas_kategori_masuk_uuid = $this->input->post("kas_kategori_masuk_uuid");
        $kas_kategori_keluar_uuid = $this->input->post("kas_kategori_keluar_uuid");
        $alur_kas = $this->input->post("alur_kas");
        $jumlah = to_number($this->input->post("jumlah"));
        $keterangan = $this->input->post("keterangan");

        $jumlah_keluar = 0;
        $jumlah_masuk = 0;
        $kas_kategori_uuid = "";
        if ($jumlah <= 0) return set_http_response_error(HTTP_BAD_REQUEST, "Jumlah harus diisi");
        $kas_alur_prefix = "";
        if (strtolower($alur_kas) == "masuk") {
            $jumlah_masuk = $jumlah;
            $kas_kategori_uuid = $kas_kategori_masuk_uuid;
            $kas_alur_prefix = "KM";
        }
        if (strtolower($alur_kas) == "keluar") {
            $jumlah_keluar = $jumlah;
            $kas_kategori_uuid = $kas_kategori_keluar_uuid;
            $kas_alur_prefix = "KK";
        }

        if (empty($tanggal)) return set_http_response_error(HTTP_BAD_REQUEST, "Tanggal harus dipilih");
        if (empty($keterangan)) return set_http_response_error(HTTP_BAD_REQUEST, "Keterangan harus diisi");

        
        if(strtotime($tanggal) > time()) {
            return set_http_response_error(HTTP_BAD_REQUEST, "Tanggal Alur Kas tidak boleh lebih dari tanggal hari ini");
        }

        // **
        // cek uuid
        $kas_transfer_uuid = "";
        $kas_transfer_number_formatted = "";
        $number = 0;
        $number_formatted = $kas_alur_prefix . "/" . $this->cabang_selected_kode . "/" . microtime_();
        $created = date("Y-m-d H:i:s");
        $creator_user_uuid = $this->actor_user_uuid;
        $creator_user_name = $this->actor_user_name;
        $last_updated = date("Y-m-d H:i:s");
        $last_updated_user_uuid = $this->actor_user_uuid;
        $last_updated_user_name = $this->actor_user_name;

        $transaksi_pembelian_uuid = "";
        $transaksi_pembelian_number_formatted = "";
        $transaksi_pembelian_retur_uuid = "";
        $transaksi_pembelian_retur_number_formatted = "";

        $transaksi_penjualan_uuid = "";
        $transaksi_penjualan_number_formatted = "";
        $transaksi_penjualan_retur_uuid = "";
        $transaksi_penjualan_retur_number_formatted = "";
        $transaksi_penjualan_pelunasan_uuid = "";
        $transaksi_penjualan_pelunasan_number_formatted = "";

        $year_month_date = date("ymd", strtotime($tanggal));
        $current_year_month_date = date("ymd", strtotime($tanggal));
        if (!empty($uuid)) {
            $filters = array();
            $filters["uuid"] = $uuid;
            $filters["cabang_uuid"] = $this->cabang_selected_uuid;
            $res = $this->kas_engine->kas_alur_get_list($filters);
            if (count($res) == 0) return set_http_response_error(HTTP_BAD_REQUEST, "Invalid Alur Kas");

            $res = $res[0];
            $uuid = $res["uuid"];
            $created = date("Y-m-d H:i:s", strtotime($res['created']));
            $creator_user_uuid = $res["creator_user_uuid"];
            $creator_user_name = $res["creator_user_name"];
            $kas_transfer_uuid = $res["kas_transfer_uuid"];
            $kas_transfer_number_formatted = trim($res["kas_transfer_number_formatted"]);

            $number = (int) $res["number"];
            $number_formatted = trim($res["number_formatted"]);

            $transaksi_pembelian_uuid = $res["transaksi_pembelian_uuid"];
            $transaksi_pembelian_number_formatted = trim($res["transaksi_pembelian_number_formatted"]);
            $transaksi_pembelian_retur_uuid = $res["transaksi_pembelian_retur_uuid"];
            $transaksi_pembelian_retur_number_formatted = trim($res["transaksi_pembelian_retur_number_formatted"]);

            $transaksi_penjualan_uuid = $res["transaksi_penjualan_uuid"];
            $transaksi_penjualan_number_formatted = trim($res["transaksi_penjualan_number_formatted"]);
            $transaksi_penjualan_retur_uuid = $res["transaksi_penjualan_retur_uuid"];
            $transaksi_penjualan_retur_number_formatted = trim($res["transaksi_penjualan_retur_number_formatted"]);
            $transaksi_penjualan_pelunasan_uuid = $res["transaksi_penjualan_pelunasan_uuid"];
            $transaksi_penjualan_pelunasan_number_formatted = trim($res["transaksi_penjualan_pelunasan_number_formatted"]);

            if (!empty($transaksi_pembelian_uuid)) {
                return set_http_response_error(HTTP_BAD_REQUEST, "Alur kas ini dibuat dari modul transaksi pembelian dan tidak dapat diubah di modul ini");
            }
            if (!empty($transaksi_pembelian_retur_uuid)) {
                return set_http_response_error(HTTP_BAD_REQUEST, "Alur kas ini dibuat dari modul transaksi retur pembelian dan tidak dapat diubah di modul ini");
            }
            if (!empty($transaksi_penjualan_uuid)) {
                return set_http_response_error(HTTP_BAD_REQUEST, "Alur kas ini dibuat dari modul transaksi penjualan dan tidak dapat diubah di modul ini");
            }
            if (!empty($transaksi_penjualan_retur_uuid)) {
                return set_http_response_error(HTTP_BAD_REQUEST, "Alur kas ini dibuat dari modul transaksi retur penjualan dan tidak dapat diubah di modul ini");
            }
            if (!empty($transaksi_penjualan_pelunasan_uuid)) {
                return set_http_response_error(HTTP_BAD_REQUEST, "Alur kas ini dibuat dari modul transaksi penjualan pembayaran piutang dan tidak dapat diubah di modul ini");
            }

            $current_year_month_date = date("ymd", strtotime($res["tanggal"]));
            $curr_alur_kas = strtolower(trim($res["alur_kas"]));
            if ($current_year_month_date != $year_month_date || strtolower($alur_kas) != $curr_alur_kas) {

                $prefix = "";
                if (strtolower($alur_kas) == "masuk") $prefix = "KM/" . $this->cabang_selected_kode . "/";
                if (strtolower($alur_kas) == "keluar") $prefix = "KK/" . $this->cabang_selected_kode . "/";

                $tanggal_format = date("ymd", strtotime($tanggal));

                $number = $this->kas_engine->kas_alur_get_next_number(strtolower($alur_kas), $tanggal, $this->cabang_selected_uuid);
                $number_formatted = $this->kas_engine->kas_alur_generate_number_formatted($prefix, $tanggal_format, $number, 3);
            }
        }

        if (!empty($uuid)) {
            if (!$this->allow_create) return set_http_response_error(HTTP_FORBIDDEN, "Akses ditolak");
        } else {
            if (!$this->allow_update) return set_http_response_error(HTTP_FORBIDDEN, "Akses ditolak");
        }

        // **
        // cek akun uuid
        $filters = array();
        $filters["uuid"] = $kas_akun_uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->kas_engine->kas_akun_get_list($filters);
        if (count($res) == 0) return set_http_response_error(HTTP_BAD_REQUEST, "Invalid Akun kas");
        $res = $res[0];
        $kas_akun_uuid = $res["uuid"];
        $kas_akun_nama = trim($res["nama"]);

        // ** 
        // get saldo untuk kas_akun_uuid
        $saldo_kas_akun = $this->kas_engine->kas_alur_get_saldo_for_kas_akun_uuid($kas_akun_uuid);
        if (strtolower($alur_kas) == "keluar" && $jumlah > $saldo_kas_akun) {
            // return set_http_response_error(HTTP_BAD_REQUEST, "Sisa saldo pada akun $kas_akun_nama tidak mencukupi. Saldo saat ini " . number_format($saldo_kas_akun));
        }

        // **
        // cek kategori uuid
        $filters = array();
        $filters["uuid"] = $kas_kategori_uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->kas_engine->kas_kategori_get_list($filters);
        if (count($res) == 0) return set_http_response_error(HTTP_BAD_REQUEST, "Invalid kategori kas");
        $res = $res[0];
        $kas_kategori_uuid = $res["uuid"];
        $kas_kategori_nama = trim($res["nama"]);

        $save_data = array();
        $save_data["uuid"] = $uuid;
        $save_data["created"] = $created;
        $save_data["creator_user_uuid"] = $creator_user_uuid;
        $save_data["creator_user_name"] = $creator_user_name;
        $save_data["last_updated"] = $last_updated;
        $save_data["last_updated_user_uuid"] = $last_updated_user_uuid;
        $save_data["last_updated_user_name"] = $last_updated_user_name;

        $save_data["number"] = $number;
        $save_data["number_formatted"] = $number_formatted;

        $save_data["tanggal"] = $tanggal;

        $save_data["kas_akun_uuid"] = $kas_akun_uuid;
        $save_data["kas_akun_nama"] = $kas_akun_nama;

        $save_data["kas_kategori_uuid"] = $kas_kategori_uuid;
        $save_data["kas_kategori_nama"] = $kas_kategori_nama;

        $save_data["alur_kas"] = $alur_kas;

        $save_data["jumlah_masuk"] = $jumlah_masuk;
        $save_data["jumlah_keluar"] = $jumlah_keluar;

        $save_data["keterangan"] = $keterangan;

        $save_data["transaksi_pembelian_uuid"] = $transaksi_pembelian_uuid;
        $save_data["transaksi_pembelian_number_formatted"] = $transaksi_pembelian_number_formatted;
        $save_data["transaksi_pembelian_retur_uuid"] = $transaksi_pembelian_retur_uuid;
        $save_data["transaksi_pembelian_retur_number_formatted"] = $transaksi_pembelian_retur_number_formatted;

        $save_data["transaksi_penjualan_uuid"] = $transaksi_penjualan_uuid;
        $save_data["transaksi_penjualan_number_formatted"] = $transaksi_penjualan_number_formatted;
        $save_data["transaksi_penjualan_retur_uuid"] = $transaksi_penjualan_retur_uuid;
        $save_data["transaksi_penjualan_retur_number_formatted"] = $transaksi_penjualan_retur_number_formatted;
        $save_data["transaksi_penjualan_pelunasan_uuid"] = $transaksi_penjualan_pelunasan_uuid;
        $save_data["transaksi_penjualan_pelunasan_number_formatted"] = $transaksi_penjualan_pelunasan_number_formatted;

        $save_data["kas_transfer_uuid"] = $kas_transfer_uuid;
        $save_data["kas_transfer_number_formatted"] = $kas_transfer_number_formatted;
        $save_data["cabang_uuid"] = $this->cabang_selected_uuid;

        $this->db->trans_start();
        try {
            $res = $this->kas_engine->kas_alur_save($save_data);
            if ($res == false ) {
                throw new Exception("Gagal menyimpan alur kas #001");
            }

        } catch (Exception $e) {
            $this->db->trans_rollback();
            return set_http_response_error(501, "Error : " . $e->getMessage());
        }

        $this->db->trans_commit();
        return set_http_response_success("Alur Kas telah disimpan");
    }

    function kas_alur_get_saldo_saat_ini()
    {        
        $saldo = $this->kas_engine->kas_alur_get_saldo_saat_ini($this->cabang_selected_uuid);
        return $saldo;
    }

    function kas_alur_get_summary()
    {
        $saldo = $this->kas_engine->kas_alur_get_saldo_saat_ini($this->cabang_selected_uuid);

        $data = array();
        $data["saldo_saat_ini"] = number_format($saldo);

        return set_http_response_success("success", $data);
    }


    function laporan_alur_kas()
    {
        $start_date = $this->input->get("start_date");
        $end_date = $this->input->get("end_date");

        $list = $this->settings_engine->get_all_settings();
        $settings_list = array();
        foreach ($list as $l) {
            $_key = $l['_key'];
            $settings_list[$_key] = $l;
        }

        $filters = array();
        $filters["start_date"] = $start_date . " 00:00:00";
        $filters["end_date"] = $end_date . " 23:59:59";
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $kas_alur_list = $this->kas_engine->kas_alur_get_list_for_print($filters);

        $tanggal_saldo_sebelumnya = date("Y-m-d", strtotime($start_date) - (60 * 60 * 24));
        $saldo_sebelumnya = $this->kas_engine->kas_alur_get_saldo_sebelumnya_for_date($tanggal_saldo_sebelumnya);


        $final_list = array();
        $saldo = $saldo_sebelumnya;
        $row_counter = 1;
        foreach ($kas_alur_list as $l) {
            $masuk = (float) $l["jumlah_masuk"];
            $keluar = (float) $l["jumlah_keluar"];

            $alur_kas = "";
            if ($l["alur_kas"] == "masuk") {
                $saldo += $masuk;
                $alur_kas = "Masuk";
            } else {
                $alur_kas = "Keluar";
                $saldo -= $keluar;
            }

            $row = array(
                "no" => $row_counter,
                "no_kas" => $l["number_formatted"],
                "tanggal" => date("d-m-Y", strtotime($l["tanggal"])),
                "kategori" => $l["kas_kategori_nama"],
                "akun" => $l["kas_akun_nama"],
                "keterangan" => $l["keterangan"],
                "masuk" => number_format($masuk),
                "keluar" => number_format($keluar),
                "saldo" => number_format($saldo)
            );
            $final_list[] = $row;

            $row_counter++;
        }

        $final_data = array(
            "header" => array(
                "nama_toko" => $settings_list["TOKO_NAMA"]["_value"],
                "alamat_toko" => $settings_list["TOKO_ALAMAT"]["_value"],
                "no_telepon_toko" => $settings_list["TOKO_NO_TELEPON"]["_value"],
            ),

            "filters" => array(
                "start_date" => date("d-m-Y", strtotime($start_date)),
                "end_date" => date("d-m-Y", strtotime($end_date)),
            ),

            "body" => array(
                "saldo_sebelumnya" => number_format($saldo_sebelumnya),
                "tanggal_saldo_sebelumnya" => date("d-m-Y", strtotime($tanggal_saldo_sebelumnya)),
                "data" => $final_list
            ),

            "footer" => array(),
        );
        return $final_data;
    }
}
