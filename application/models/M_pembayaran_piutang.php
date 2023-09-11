<?php

defined('BASEPATH') or exit('No direct script access allowed');
class M_pembayaran_piutang extends MY_Model
{

    private $transaksi_engine;
    private $pelanggan_engine;
    private $item_engine;
    private $kas_engine;
    private $settings_engine;

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

        $this->settings_engine = new Settings_engine();
        $this->pelanggan_engine = new Pelanggan_engine();
        $this->transaksi_engine = new Transaksi_engine();
        $this->item_engine = new Item_engine();
        $this->kas_engine = new Kas_engine();

        $cabang_selected = get_session("cabang_selected");
        $this->cabang_selected_uuid = $cabang_selected["uuid"];
        $this->cabang_selected_kode = $cabang_selected["kode"];

        $user = get_session("user");
        $role = strtolower(get_session("role"));
        $this->actor_user_uuid = isset($user["uuid"]) ? $user["uuid"] : "";
        $this->actor_user_name = isset($user["name"]) ? $user["name"] : "";

        $privilege_list = isset($user["privilege_list"]) ? $user["privilege_list"] : array();

        $this->allow        = isset($privilege_list["allow_transaksi_pembayaran_piutang"]) ? $privilege_list["allow_transaksi_pembayaran_piutang"] : 0;
        $this->allow_create = isset($privilege_list["allow_transaksi_pembayaran_piutang_create"]) ? $privilege_list["allow_transaksi_pembayaran_piutang_create"] : 0;
        $this->allow_update = isset($privilege_list["allow_transaksi_pembayaran_piutang_update"]) ? $privilege_list["allow_transaksi_pembayaran_piutang_update"] : 0;
        $this->allow_delete = isset($privilege_list["allow_transaksi_pembayaran_piutang_delete"]) ? $privilege_list["allow_transaksi_pembayaran_piutang_delete"] : 0;
    }

    function pembayaran_piutang_get_list($filters = array())
    {
        if (!$this->allow) return array();

        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->transaksi_engine->pembayaran_piutang_get_list($filters, true, true);
        $no = isset($filters["start"]) ? (int) $filters["start"] : 0;

        $final_res = array();
        foreach ($res as $r) {
            $no++;

            $row = array();
            $row["no"] = $no;
            $row["uuid"] = trim($r["uuid"]);
            $row["number_formatted"] = $r["number_formatted"];
            $row["tanggal"] = date("d M Y", strtotime($r["tanggal"]));
            $row["cara_bayar"] = $r["cara_bayar"];
            $row["pelanggan_number_formatted"] = $r["pelanggan_number_formatted"];
            $row["pelanggan_nama"] = $r["pelanggan_nama"];
            $row["jumlah"] = number_format($r['jumlah'], 0, ",", ".");
            $row["keterangan"] = $r["keterangan"];

            $final_res[] = $row;
        }

        return $final_res;
    }

    function pembayaran_piutang_get_filtered_total($filters = array())
    {
        if (!$this->allow) return 0;

        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->transaksi_engine->pembayaran_piutang_get_list($filters, false, true);
        return count($res);
    }

    function pembayaran_piutang_get_total($filters = array())
    {
        if (!$this->allow) return 0;

        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->transaksi_engine->pembayaran_piutang_get_list($filters);
        return count($res);
    }

    function pembayaran_piutang_get($uuid = "")
    {
        if (!$this->allow) return array();

        $filters = array();
        $filters["uuid"] = $uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->transaksi_engine->pembayaran_piutang_get_list($filters);
        if (count($res) == 0) return array();
        $pembayaran_piutang = $res[0];
        $pembayaran_piutang_uuid = $pembayaran_piutang["uuid"];
        $pelanggan_uuid = $pembayaran_piutang["pelanggan_uuid"];

        // **
        // get pembayaran_piutang detail list for pembayaran_piutang id
        $filters = array();
        $filters["pembayaran_piutang_uuid"] = $pembayaran_piutang_uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $detail_list = $this->transaksi_engine->pembayaran_piutang_detail_get_list($filters);
        $pembayaran_piutang["detail"] = $detail_list;

        $next_pembayaran_piutang_uuid = $this->get_next_pembayaran_piutang_uuid_for_pelanggan_uuid_and_current_pembayaran_piutang_uuid($pelanggan_uuid, $pembayaran_piutang_uuid);
        $boleh_hapus_pembayaran = $next_pembayaran_piutang_uuid == 0 ? 1 : 0;
        $pembayaran_piutang["boleh_hapus_pembayaran"] = $boleh_hapus_pembayaran;

        return $pembayaran_piutang;
    }

    function pembayaran_piutang_delete($uuid = "")
    {
        if (!$this->allow_delete) return set_http_response_error(HTTP_FORBIDDEN, "Akses ditolak");

        $filters = array();
        $filters["uuid"] = $uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->transaksi_engine->pembayaran_piutang_get_list($filters);
        if (count($res) == 0) return set_http_response_error(HTTP_BAD_REQUEST, "Pembayaran piutang tidak ditemukan");
        $res = $res[0];
        $uuid = $res["uuid"];
        $pembayaran_piutang_uuid = $uuid;
        $pelanggan_uuid = $res["pelanggan_uuid"];
        $pelanggan_nama = $res["pelanggan_nama"];

        // **
        // get daftar penjualan yang telah lunas
        $filters = array();
        $filters["pembayaran_piutang_uuid"] = $pembayaran_piutang_uuid;
        $res = $this->transaksi_engine->pembayaran_piutang_detail_get_list($filters);
        $penjualan_uuid_list = array();
        foreach($res as $r) {
            $penjualan_uuid_list[$r["penjualan_uuid"]] = $r["penjualan_uuid"];
        }

        $next_pembayaran_piutang_uuid = $this->get_next_pembayaran_piutang_uuid_for_pelanggan_uuid_and_current_pembayaran_piutang_uuid($pelanggan_uuid, $pembayaran_piutang_uuid);
        $boleh_hapus_pembayaran = $next_pembayaran_piutang_uuid == 0 ? 1 : 0;

        // **
        // get next pembayaran number formatted
        $filters = array();
        $filters["uuid"] = $next_pembayaran_piutang_uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->transaksi_engine->pembayaran_piutang_get_list($filters);
        if (count($res) > 0) {
            $res = $res[0];
            $next_pembayaran_piutang_number_formatted = $res["number_formatted"];
            if ((int) $boleh_hapus_pembayaran == 0) return set_http_response_error(HTTP_BAD_REQUEST, "Pembayaran untuk pelanggan <b>$pelanggan_nama</b> sudah memiliki pembayaran setelahnya dengan nomor pembayaran <b>$next_pembayaran_piutang_number_formatted</b>. Silahkan hapus pembayaran <b>$next_pembayaran_piutang_number_formatted</b> lebih dulu, kemudian hapus pembayaran ini");
        }

        $this->db->trans_start();
        try {
            // **
            // hapus pembayaran_piutang
            $res = $this->transaksi_engine->pembayaran_piutang_delete($uuid);
            if ($res == false) throw new Exception("Gagal menghapus pembayaran piutang #001");

            // **
            // hapus pembayaran_piutang detail
            $res = $this->transaksi_engine->pembayaran_piutang_detail_delete_for_pembayaran_piutang_uuid($pembayaran_piutang_uuid);
            if ($res == false) throw new Exception("Gagal menghapus pembayaran piutang #002");

            // **
            // delete kas alur
            $res = $this->kas_engine->kas_alur_delete_for_transaksi_pembayaran_piutang_uuid($pembayaran_piutang_uuid);
            if ($res == false) throw new Exception("Gagal menghapus pembayaran piutang #003");

            foreach($penjualan_uuid_list as $penjualan_uuid => $p_uuid){
                $res = $this->transaksi_engine->penjualan_update_status_lunas($p_uuid, 0, "Belum Lunas");
            }
            
        } catch (Exception $e) {
            $this->db->trans_rollback();
            return set_http_response_error(501, "Error : " . $e->getMessage());
        }

        $this->db->trans_commit();
        return set_http_response_success("Pembayaran piutang telah dihapus");
    }

    function pembayaran_piutang_save()
    {
        $uuid = $this->input->post("uuid");
        $tanggal = $this->input->post("tanggal");
        $pelanggan_uuid = $this->input->post("pelanggan_uuid");
        $jumlah_bayar = to_number(post("jumlah_bayar"));
        $kas_akun_uuid = $this->input->post("kas_akun_uuid");
        $cara_bayar = $this->input->post("cara_bayar");
        $keterangan = $this->input->post("keterangan");

        // **
        // validasi
        if ($jumlah_bayar <= 0 && empty($uuid)) return set_http_response_error(HTTP_BAD_REQUEST, "Jumlah bayar harus diisi");
        if (empty($cara_bayar)) return set_http_response_error(HTTP_BAD_REQUEST, "Cara bayar harus dipilih");


        if (strtotime($tanggal) > time()) {
            return set_http_response_error(HTTP_BAD_REQUEST, "Tanggal Pembayaran Piutang tidak boleh lebih dari tanggal hari ini");
        }

        $input_jumlah_bayar = $jumlah_bayar;

        // **
        // check pelanggan uuid
        if (empty($uuid)) {
            $filters = array();
            $filters["uuid"] = $pelanggan_uuid;
            $filters["cabang_uuid"] = $this->cabang_selected_uuid;
            $res = $this->pelanggan_engine->pelanggan_get_list($filters);
            if (count($res) == 0) return set_http_response_error(HTTP_BAD_REQUEST, "Pelanggan tidak ditemukan");
            $res = $res[0];
            $pelanggan_uuid = $res["uuid"];
        }

        // **
        // check kas akun uuid
        $filters = array();
        $filters['uuid'] = $kas_akun_uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->kas_engine->kas_akun_get_list($filters);
        if (count($res) == 0) return set_http_response_error(HTTP_BAD_REQUEST, "Kas akun tidak ditemukan");
        $res = $res[0];
        $kas_akun_uuid = $res["uuid"];
        $kas_akun_data = $res;

        $pembayaran_piutang_uuid = "";
        $kas_alur_uuid = "";
        $kas_alur_data = array();
        $created = date("Y-m-d H:i:s");
        $creator_user_uuid = $this->actor_user_uuid;
        $creator_user_name = $this->actor_user_name;
        $number = 0;
        $number_formatted = PREFIX_PEMBAYARAN_PIUTANG . "/" . $this->cabang_selected_kode . "/" . microtime_();
        $tahun = date("Y", strtotime($tanggal . " 00:00:00"));
        $jam = date("H:i:s");
        $sisa_jumlah_bayar = 0;

        $edit = false;
        if (!empty($uuid)) {
            $filters = array();
            $filters["uuid"] = $uuid;
            $filters["cabang_uuid"] = $this->cabang_selected_uuid;
            $res = $this->transaksi_engine->pembayaran_piutang_get_list($filters);
            if (count($res) == 0) return set_http_response_error(HTTP_BAD_REQUEST, "Invalid pembayaran piutang");
            $res = $res[0];

            $uuid = $res["uuid"];
            $pembayaran_piutang_uuid = $uuid;
            $kas_alur_uuid = $res["kas_alur_uuid"];
            $pelanggan_uuid = $res["pelanggan_uuid"];
            $jumlah_bayar = (float) $res["jumlah"];
            $sisa_jumlah_bayar = (float) $res["sisa_jumlah_bayar"];

            $created = $res["created"];
            $creator_user_uuid = $res["creator_user_uuid"];
            $creator_user_name = $res["creator_user_name"];
            $number = (int) $res["number"];
            $number_formatted = trim($res["number_formatted"]);

            $tahun = date("Y", $res["tahun"]);
            $jam = date("H:i:s", strtotime($res["tanggal"]));

            // **
            // dapatkan kas alur id berdasarkan transaksi_penjualan_uuid
            $filters = array();
            $filters['transaksi_pembayaran_piutang_uuid'] = $uuid;
            $filters["cabang_uuid"] = $this->cabang_selected_uuid;
            $res2 = $this->kas_engine->kas_alur_get_list($filters);
            if (count($res2) > 0) {
                $res2 = $res2[0];
                $kas_alur_uuid = $res2["uuid"];

                $kas_alur_data = $res2;
            }

            $edit = true;
        }

        if (empty($uuid)) {
            if (!$this->allow_create) return set_http_response_error(HTTP_FORBIDDEN, "Akses ditolak");
        } else {
            if (!$this->allow_update) return set_http_response_error(HTTP_FORBIDDEN, "Akses ditolak");
        }

        $pembayaran_piutang_detail_list = array();
        $penjualan_status_lunas_list = array();

        if (!$edit) {
            $result = $this->generate_penjualan_belum_lunas_get_list_for_pelanggan_uuid_and_jumlah_bayar($pelanggan_uuid, $jumlah_bayar);
            if (count($result) == 0) return set_http_response_error(HTTP_BAD_REQUEST, "Tidak ada penjualan yang belum lunas");
            $penjualan_belum_lunas_list = $result["daftar_penjualan_belum_lunas"];
            $sisa_jumlah_bayar = $result["sisa_jumlah_bayar"];

            if ($sisa_jumlah_bayar > 0) {
                return set_http_response_error(HTTP_BAD_REQUEST, "Jumlah bayar melebihi piutang");
            }
            foreach ($penjualan_belum_lunas_list as $d) {
                if ((float) $d["jumlah_bayar"] == 0) continue;

                $row = array();
                $row["uuid"] = "";
                $row["created"] = date("Y-m-d H:i:s");
                $row["creator_user_uuid"] = $this->actor_user_uuid;
                $row["creator_user_name"] = $this->actor_user_name;
                $row["last_updated"] = date("Y-m-d H:i:s");
                $row["last_updated_user_uuid"] = $this->actor_user_uuid;
                $row["last_updated_user_name"] = $this->actor_user_name;
                $row["pembayaran_piutang_uuid"] = 0;
                $row["penjualan_uuid"] = $d["uuid"];
                $row["sisa_piutang"] = $d["sisa_piutang"];
                $row["potongan"] = $d["potongan"];
                $row["jumlah"] = $d["jumlah_bayar"];
                $row["cabang_uuid"] = $this->cabang_selected_uuid;
                $pembayaran_piutang_detail_list[] = $row;

                // **
                // cache pelunasan penjualan
                $row = array();
                $row["uuid"] = $d["uuid"];
                $row["lunas"] = $d["cache_lunas"];
                $row["cache_status"] = (int) $d["cache_lunas"] == 1 ? "Lunas" : "Belum Lunas";
                $penjualan_status_lunas_list[] = $row;
            }
        }

        $save_data = array(
            "uuid" => $uuid,
            "created" => $created,
            "creator_user_uuid" => $creator_user_uuid,
            "creator_user_name" => $creator_user_name,
            "last_updated" => date("Y-m-d H:i:s"),
            "last_updated_user_uuid" => $this->actor_user_uuid,
            "last_updated_user_name" => $this->actor_user_name,
            "number" => $number,
            "number_formatted" => $number_formatted,
            "tanggal" => $tanggal . " $jam",
            "tahun" => $tahun,
            "pelanggan_uuid" => $pelanggan_uuid,
            "jumlah" => $jumlah_bayar,
            "sisa_jumlah_bayar" => $sisa_jumlah_bayar,
            "cara_bayar" => $cara_bayar,
            "kas_akun_uuid" => $kas_akun_uuid,
            "kas_alur_uuid" => $kas_alur_uuid,
            "keterangan" => $keterangan,
            "cabang_uuid" => $this->cabang_selected_uuid,
            "cabang_kode" => $this->cabang_selected_kode,
        );

        $kas_alur_tanggal_changed = false;
        if (count($kas_alur_data) == 0) {
            $kas_alur_data["uuid"] = "";
            $kas_alur_data["created"] = $created;
            $kas_alur_data["creator_user_uuid"] = $creator_user_uuid;
            $kas_alur_data["creator_user_name"] = $creator_user_name;
            $kas_alur_data["last_updated"] = date("Y-m-d H:i:s");
            $kas_alur_data["last_updated_user_uuid"] = $this->actor_user_uuid;
            $kas_alur_data["last_updated_user_name"] = $this->actor_user_name;

            $kas_alur_data["number"] = 0;
            $kas_alur_data["number_formatted"] = "KM/" . $this->cabang_selected_kode . "/" . microtime_();

            $kas_alur_data["tanggal"] = $tanggal;

            $kas_alur_data["kas_akun_uuid"] = $kas_akun_uuid;
            $kas_alur_data["kas_akun_nama"] = $kas_akun_data["nama"];

            $kas_alur_data["kas_kategori_uuid"] = -22;
            $kas_alur_data["kas_kategori_nama"] = "Transaksi Pembayaran Piutang";

            $kas_alur_data["alur_kas"] = "Masuk";

            $kas_alur_data["jumlah_masuk"] = $jumlah_bayar;
            $kas_alur_data["jumlah_keluar"] = 0;

            $kas_alur_data["keterangan"] = "Transaksi Pembayaran Piutang";

            $kas_alur_data["transaksi_pembelian_uuid"] = 0;
            $kas_alur_data["transaksi_pembelian_number_formatted"] = "";
            $kas_alur_data["transaksi_pembelian_retur_uuid"] = 0;
            $kas_alur_data["transaksi_pembelian_retur_number_formatted"] = "";

            $kas_alur_data["transaksi_penjualan_uuid"] = 0;
            $kas_alur_data["transaksi_penjualan_number_formatted"] = "";
            $kas_alur_data["transaksi_penjualan_retur_uuid"] = 0;
            $kas_alur_data["transaksi_penjualan_retur_number_formatted"] = "";
            $kas_alur_data["transaksi_pembayaran_piutang_uuid"] = 0;
            $kas_alur_data["transaksi_pembayaran_piutang_number_formatted"] = "";

            $kas_alur_data["kas_transfer_uuid"] = 0;
            $kas_alur_data["kas_transfer_number_formatted"] = "";
            $kas_alur_data["cabang_uuid"] = $this->cabang_selected_uuid;
            $kas_alur_data["cabang_kode"] = $this->cabang_selected_kode;
        } else {
            $kas_alur_tanggal = $kas_alur_data["tanggal"];

            if ($kas_alur_tanggal != $tanggal) $kas_alur_tanggal_changed = true;
            $kas_alur_data["kas_akun_uuid"] = $kas_akun_uuid;
            $kas_alur_data["kas_akun_nama"] = $kas_akun_data["nama"];
            $kas_akun_data["cabang_uuid"] = $this->cabang_selected_uuid;
        }

        try {

            // **
            // simpan pembayaran piutang
            $res = $this->transaksi_engine->pembayaran_piutang_save($save_data);
            if ($res == false) throw new Exception("Gagal menyimpan pembayaran piutang #001");
            $uuid = $res;
            $pembayaran_piutang_uuid = $uuid;

            // **
            // get detail pembayaran piutang untuk set ke kas alur data
            $filters = array();
            $filters["uuid"] = $uuid;
            $filters["cabang_uuid"] = $this->cabang_selected_uuid;
            $res = $this->transaksi_engine->pembayaran_piutang_get_list($filters);
            $res = $res[0];
            $kas_alur_data['transaksi_pembayaran_piutang_uuid'] = $uuid;
            $kas_alur_data['transaksi_pembayaran_piutang_number_formatted'] = $res['number_formatted'];
            $kas_alur_data['keterangan'] = "Transaksi pembayaran piutang " . $res['number_formatted'];

            if (!$edit) {
                // **
                // simpan pembayaran piutang detail
                foreach ($pembayaran_piutang_detail_list as $data) {
                    $data["pembayaran_piutang_uuid"] = $pembayaran_piutang_uuid;
                    $data["cabang_uuid"] = $this->cabang_selected_uuid;
                    $res = $this->transaksi_engine->pembayaran_piutang_detail_save($data);
                    if ($res == false) throw new Exception("Gagal menyimpan pembayaran piutang #002");
                }
            }

            // **
            // simpan kas alur
            $res = $this->kas_engine->kas_alur_save($kas_alur_data);
            if ($res == false) throw new Exception("Gagal menyimpan pembayaran piutang #003");
            if (empty($kas_alur_uuid) || $kas_alur_tanggal_changed) {
                $kas_alur_uuid = $res;
                // **
                // update kas alur id untuk transaksi pembayaran piutang
                $res = $this->transaksi_engine->pembayaran_piutang_update_kas_alur_uuid($uuid, $kas_alur_uuid);
                if ($res == false) throw new Exception("Gagal menyimpan pembayaran piutang #004");
            }

            if (!$edit) {
                // **
                // simpan cache penjualan pelunasan
                foreach ($penjualan_status_lunas_list as $data) {
                    $uuid = $data["uuid"];
                    $lunas = (int) $data["lunas"];
                    $cache_status = $data["cache_status"];

                    $res = $this->transaksi_engine->penjualan_update_status_lunas($uuid, $lunas, $cache_status);
                    if ($res == false) throw new Exception("Gagal menyimpan pembayaran piutang #005");
                }
            }
        } catch (Exception $e) {
            $this->db->trans_rollback();
            return set_http_response_error(501, $e->getMessage());
        }

        $this->db->trans_commit();
        return set_http_response_success("Pembayaran piutang telah disimpan", array(), trim($pembayaran_piutang_uuid));
    }

    function get_next_pembayaran_piutang_uuid_for_pelanggan_uuid_and_current_pembayaran_piutang_uuid($pelanggan_uuid = "", $pembayaran_piutang_uuid = "")
    {
        if (empty($pelanggan_uuid) || empty($pembayaran_piutang_uuid)) return 0;

        // **
        // check jika boleh hapus pembayaran untuk pelanggan id dan pembayaran piutang id
        $next_pembayaran_piutang_uuid = "";
        $filters = array();
        $filters["pelanggan_uuid"] = $pelanggan_uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->transaksi_engine->pembayaran_piutang_get_list($filters);

        if (count($res) <= 1) {
            return $next_pembayaran_piutang_uuid;
        }

        $total_rows = count($res);
        $row = 1;
        foreach ($res as $r) {
            $current_pembayaran_piutang_uuid = $r["uuid"];

            if ($current_pembayaran_piutang_uuid == $pembayaran_piutang_uuid) {
                if ($row < $total_rows) {
                    break;
                }
            }
            $row++;
        }

        if ($row <= $total_rows) {
            $next_row_data = $res[$row];
            $next_pembayaran_piutang_uuid = $next_row_data["uuid"];
        }
        return $next_pembayaran_piutang_uuid;
    }

    // **
    // bagian pelunasan
    function generate_penjualan_belum_lunas_get_list_for_pelanggan_uuid_and_jumlah_bayar($pelanggan_uuid = "", $jumlah_bayar = 0, $lunas = 0)
    {

        $input_jumlah_bayar = $jumlah_bayar;

        // **
        // check pelanggan uuid
        $filters = array();
        $filters["uuid"] = $pelanggan_uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->pelanggan_engine->pelanggan_get_list($filters);
        if (count($res) == 0) return array();
        $res = $res[0];
        $pelanggan_uuid = $res["uuid"];

        // **
        // get penjualan untuk pelanggan id
        $filters = array();
        $filters["pelanggan_uuid"] = $pelanggan_uuid;
        $filters["lunas"] = $lunas;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $penjualan_list = $this->transaksi_engine->penjualan_get_list($filters);
        if (count($penjualan_list) == 0) return array();

        // **
        // generate penjualan id list
        $penjualan_uuid_list = array();
        foreach ($penjualan_list as $r) {
            $penjualan_uuid_list[] = $r["uuid"];
        }

        // **
        // ambil total penjualan pelunasan untuk penjualan id list
        $penjualan_pelunasan_pembayaran_total_list = $this->transaksi_engine->pembayaran_piutang_detail_get_total_pembayaran_for_penjualan_uuid_list($penjualan_uuid_list);

        $sisa_jumlah_bayar = 0;
        $total_sisa_piutang = 0;
        foreach ($penjualan_list as $index => $r) {
            $penjualan_uuid = $r["uuid"];
            $penjualan_number_formatted = $r["number_formatted"];
            $sisa = (float) $r["sisa"];
            $total_piutang_terbayar = $penjualan_pelunasan_pembayaran_total_list[$penjualan_uuid];

            $sisa_piutang = $sisa - $total_piutang_terbayar;
            $total_sisa_piutang += $sisa_piutang;

            $sisa = 0;
            $row = array(
                "uuid" => $penjualan_uuid,
                "number_formatted" => $r["number_formatted"],
                "tanggal" => $r["tanggal"],
                "jatuh_tempo" => $r["jatuh_tempo"],
                "sisa_piutang" => $sisa_piutang,
                "potongan" => 0,
                "jumlah_bayar" => $jumlah_bayar,
                "sisa" => $sisa,
                "sisa_jumlah_bayar" => $sisa_jumlah_bayar
            );

            if ($jumlah_bayar > $sisa_piutang) {
                $sisa_jumlah_bayar = $jumlah_bayar - $sisa_piutang;
                $jumlah_bayar = $sisa_piutang;
                $sisa = 0;
            } else if ($jumlah_bayar <= $sisa_piutang) {
                $sisa = $sisa_piutang - $jumlah_bayar;
                $sisa_jumlah_bayar = 0;
            }

            $row["jumlah_bayar"] = $jumlah_bayar;
            $row["sisa"] = $sisa;
            $row["sisa_jumlah_bayar"] = $sisa_jumlah_bayar;

            $row["cache_total_terbayar"] = 0;
            $row["cache_lunas"] = $sisa == 0 ? 1 : 0;

            $jumlah_bayar = $sisa_jumlah_bayar;

            $final_res[] = $row;
        }

        $result = array();
        $result["pelanggan_uuid"] = $pelanggan_uuid;
        $result["total_piutang"] = $total_sisa_piutang;
        $result["jumlah_bayar"] = $input_jumlah_bayar;
        $result["sisa_jumlah_bayar"] = $sisa_jumlah_bayar;
        $result["daftar_penjualan_belum_lunas"] = $final_res;

        return $result;
    }

    function penjualan_pelunasan_bulk_save()
    {
        if (!$this->allow_pelunasan) return set_http_response_error(HTTP_FORBIDDEN, "Akses ditolak");

        $tanggal_bayar = $this->input->post("tanggal");
        $pelanggan_uuid = $this->input->post("pelanggan_uuid");
        $jumlah_bayar = to_number(post("jumlah_bayar"));
        $cara_bayar = $this->input->post("cara_bayar");
        $keterangan = $this->input->post("keterangan");
        $kas_akun_uuid = $this->input->post("kas_akun_uuid");

        if ((int) $jumlah_bayar <= 0) return set_http_response_error(HTTP_BAD_REQUEST, "Jumlah bayar harus diisi");

        $result = $this->generate_penjualan_belum_lunas_get_list_for_pelanggan_uuid_and_jumlah_bayar($pelanggan_uuid, $jumlah_bayar);
        if (count($result) == 0) return set_http_response_error(HTTP_BAD_REQUEST, "Gagal menyimpan pembayaran piutang");

        $pelanggan_uuid = isset($result["pelanggan_uuid"]) ? $result["pelanggan_uuid"] : 0;
        $total_piutang = isset($result["total_piutang"]) ? $result["total_piutang"] : 0;
        $jumlah_bayar = isset($result["jumlah_bayar"]) ? $result["jumlah_bayar"] : 0;
        $saldo_pelanggan_setelah_pelunasan = isset($result["sisa_jumlah_bayar"]) ? $result["sisa_jumlah_bayar"] : 0;
        $daftar_penjualan_belum_lunas = isset($result["daftar_penjualan_belum_lunas"]) ? $result["daftar_penjualan_belum_lunas"] : array();

        if (count($daftar_penjualan_belum_lunas) == 0) return set_http_response_error(HTTP_BAD_REQUEST, "Tidak ada penjualan yang belum lunas");


        // **
        // check kas akun uuid
        $filters = array();
        $filters['uuid'] = $kas_akun_uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->kas_engine->kas_akun_get_list($filters);
        if (count($res) == 0) return set_http_response_error(HTTP_BAD_REQUEST, "Kas akun tidak ditemukan");
        $res = $res[0];
        $kas_akun_uuid = $res["uuid"];
        $kas_akun_data = $res;

        // **
        // check pelanggan uuid
        $filters = array();
        $filters["uuid"] = $pelanggan_uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->pelanggan_engine->pelanggan_get_list($filters);
        if (count($res) == 0) return set_http_response_error(HTTP_BAD_REQUEST, "Pelanggan tidak ditemukan");
        $res = $res[0];
        $pelanggan_uuid = $res[0];

        $penjualan_pelunasan_data_list = array();
        $penjualan_status_lunas_list = array();
        foreach ($daftar_penjualan_belum_lunas as $d) {
            if ((float) $d["jumlah_bayar"] == 0) continue;

            $row = array();
            $row["uuid"] = "";
            $row["created"] = date("Y-m-d H:i:s");
            $row["creator_user_uuid"] = $this->actor_user_uuid;
            $row["creator_user_name"] = $this->actor_user_name;
            $row["last_updated"] = date("Y-m-d H:i:s");
            $row["last_updated_user_uuid"] = $this->actor_user_uuid;
            $row["last_updated_user_name"] = $this->actor_user_name;
            $row["penjualan_uuid"] = $d["uuid"];
            $row["number"] = 0;
            $row["number_formatted"] = "";
            $row["tanggal"] = $tanggal_bayar;
            $row["tahun"] = date("Y", strtotime($tanggal_bayar . " 00:00:00"));
            $row["jumlah"] = $d["jumlah_bayar"];
            $row["cara_bayar"] = "";
            $row["kas_akun_uuid"] = $kas_akun_uuid;
            $row["keterangan"] = "Pelunasan otomatis";
            $row["cabang_uuid"] = $this->cabang_selected_uuid;
            $penjualan_pelunasan_data_list[] = $row;

            // **
            // cache pelunasan penjualan
            $row = array();
            $row["uuid"] = $d["uuid"];
            $row["lunas"] = $d["cache_lunas"];
            $row["cache_status"] = (int) $d["cache_lunas"] == 1 ? "Lunas" : "Belum Lunas";
            $penjualan_status_lunas_list[] = $row;
        }

        try {
            // **
            // simpan penjualan pelunasan
            foreach ($penjualan_pelunasan_data_list as $data) {
                $res = $this->transaksi_engine->pembayaran_piutang_save($data);
                if ($res == false) throw new Exception("Gagal menyimpan pelunasan #001");
            }

            // **
            // simpan cache penjualan pelunasan
            foreach ($penjualan_status_lunas_list as $data) {
                $uuid = $data["uuid"];
                $lunas = (int) $data["lunas"];
                $cache_status = $data["cache_status"];

                $res = $this->transaksi_engine->penjualan_update_status_lunas($uuid, $lunas, $cache_status);
                if ($res == false) throw new Exception("Gagal menyimpan pelunasan #002");
            }
        } catch (Exception $e) {
            $this->db->trans_rollback();
            return set_http_response_error(501, $e->getMessage());
        }

        $this->db->trans_commit();
        return set_http_response_success("Pelunasan telah disimpan");
    }

    function pembayaran_piutang_cetak($uuid = "")
    {

        $settings = get_session("settings");
        $header = array(
            "nama_toko" => $settings["TOKO_NAMA"]["_value"],
            "alamat_toko" => $settings["TOKO_ALAMAT"]["_value"],
            "no_telepon_toko" => $settings["TOKO_NO_TELEPON"]["_value"],
        );
        $body = array(
            "judul" => "Nota Penjualan",
            "no" => "",
            "tanggal" => "",
            "keterangan" => "",
            "content" => array()
        );
        $footer = array();
        $result = array(
            "header" => $header,
            "body" => $body,
            "footer" => $footer
        );

        // **
        // get detail
        if (!$this->allow_print_nota) return $result;

        $filters = array();
        $filters["uuid"] = $uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->transaksi_engine->penjualan_get_list($filters);
        if (count($res) == 0) return $result;
        $penjualan = $res[0];
        $penjualan_uuid = $penjualan["uuid"];
        $number_formatted = $penjualan["number_formatted"];
        $tanggal = date("d/m/Y", strtotime($penjualan["tanggal"]));
        $keterangan = $penjualan["keterangan"];
        $created = date("d/m/Y H:i:s", strtotime($penjualan["created"]));
        $creator_user_name = $penjualan["creator_user_name"];
        $last_updated = date("d/m/Y H:i:s", strtotime($penjualan["last_updated"]));
        $last_updated_user_name = $penjualan["last_updated_user_name"];
        $printed = date("d/m/Y H:i:s");
        $printed_user_name = $this->actor_user_name;
        $metode_pembayaran = $penjualan["metode_pembayaran"];

        $footer = array(
            "created" => $created,
            "creator_user_name" => $creator_user_name,
            "last_updated" => $last_updated,
            "last_updated_user_name" => $last_updated_user_name,
            "printed" => $printed,
            "printed_user_name" => $printed_user_name
        );

        // **
        // get penjualan detail list for penjualan id
        $filters = array();
        $filters["penjualan_uuid"] = $penjualan_uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $detail_list = $this->transaksi_engine->penjualan_detail_get_list($filters);
        $penjualan["detail"] = $detail_list;

        $body["judul"] = $metode_pembayaran == "Tunai" ? "Nota Penjualan " : "Invoice ";
        $body["tanggal"] = $tanggal;
        $body["no"] = $number_formatted;
        $body["pelanggan_number_formatted"] = $penjualan["pelanggan_number_formatted"];
        $body["pelanggan_nama"] = $penjualan["pelanggan_nama"];
        $body["pelanggan_detail"] = $penjualan["pelanggan_alamat"] . "\n" . $penjualan["pelanggan_no_telepon"];

        $contents = array();
        $no = 1;

        $sub_total = 0;
        $potongan = (float) $penjualan["potongan"];

        foreach ($detail_list as $dl) {
            $jumlah = (int) $dl["jumlah"];
            $harga_jual = (float) $dl["harga_jual_satuan"];
            $potongan_harga = (float) $dl["potongan_harga"];

            $total = $jumlah * ($harga_jual - $potongan_harga);
            $row = array(
                "no" => $no,
                "kode" => $dl["item_kode"],
                "nama" => $dl["item_nama"],
                "kategori" => $dl["item_kategori_nama"],
                "jumlah" => number_format($jumlah, 0, ",", "."),
                "satuan" => ucwords($dl["satuan"]),
                "harga_jual" => number_format($harga_jual, 0, ",", "."),
                "potongan" => number_format($dl["potongan_persen"], 2),
                "total" => number_format($total, 0, ",", "."),
            );

            $contents[] = $row;
            $no++;

            $sub_total += $total;
        }

        $total_akhir = $sub_total - $potongan;
        $bayar = (float) $penjualan["bayar"];
        $kembali = (float) $penjualan["kembali"];
        $sisa = (float) $penjualan["sisa"];

        $body["content"] = $contents;
        $body["sub_total"] = number_format($sub_total, 0, ",", ".");
        $body["potongan"] = number_format($potongan, 0, ",", ".");
        $body["total_akhir"] = number_format($total_akhir, 0, ",", ".");
        $body["bayar"] = number_format($bayar, 0, ",", ".");
        $body["kembali"] = number_format($kembali, 0, ",", ".");
        $body["sisa"] = number_format($sisa, 0, ",", ".");
        $body["terbilang"] = ucwords(terbilang($total_akhir));
        $body["keterangan"] = ucwords($penjualan['keterangan']);
        $body["metode_pembayaran"] = ucwords($penjualan['metode_pembayaran']);
        $body["tanggal_jatuh_tempo"] = date("d/m/Y", strtotime($penjualan["jatuh_tempo"]));

        $result = array(
            "header" => $header,
            "body" => $body,
            "footer" => $footer
        );

        return $result;
    }

    function dashboard_total_piutang_aktif($start_date = "", $end_date = "")
    {

        $filters = array();
        if (!empty($start_date)) $filters["start_date_jatuh_tempo"] = $start_date . " 00:00:00";
        $filters["end_date_jatuh_tempo"] = $end_date . " 23:59:59";
        $filters["metode_pembayaran"] = "non tunai";
        $filters["lunas"] = 0;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $penjualan_list = $this->transaksi_engine->penjualan_get_list($filters, false, false, false);
        if (count($penjualan_list) == 0) return 0;

        // **
        // get penjualan id list
        $penjualan_uuid_list = array();
        $total_piutang = 0;
        foreach ($penjualan_list as $r) {
            $total_piutang += (float) $r["sisa"];
        }

        // **
        // get total piutang telah dibayar dari table pembayaran piutang detail berdasarkan pennjualan id
        $filters = array();
        $filters["penjualan_uuid_list"] = $penjualan_uuid_list;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $piutang_total_list = $this->transaksi_engine->pembayaran_piutang_detail_get_total_pembayaran_for_penjualan_uuid_list($penjualan_uuid_list);
        $total_piutang_terbayar = array_sum($piutang_total_list);

        $total_sisa_piutang = $total_piutang - $total_piutang_terbayar;
        return $total_sisa_piutang;
    }

    function dashboard_daftar_piutang_jatuh_tempo($start_date = "", $end_date = "", $limit = false)
    {
        $filters = array();
        if (!empty($start_date)) $filters["start_date_jatuh_tempo"] = $start_date . " 00:00:00";
        $filters["end_date_jatuh_tempo"] = $end_date . " 23:59:59";
        $filters["metode_pembayaran"] = "non tunai";
        $filters["lunas"] = 0;
        $filters["order_by"] = "penjualan.jatuh_tempo";
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $penjualan_list = $this->transaksi_engine->penjualan_get_list($filters, false, false, false, $limit);
        if (count($penjualan_list) == 0) return array();

        // **
        // get penjualan id list
        $penjualan_uuid_list = array();
        $piutang_report_data_list = array();
        foreach ($penjualan_list as $r) {
            $no_transaksi = $r["number_formatted"];
            $tanggal = date("Y-m-d", strtotime($r["tanggal"]));
            $jatuh_tempo = date("Y-m-d", strtotime($r["jatuh_tempo"]));
            $jatuh_tempo_timestamp = strtotime($jatuh_tempo);
            $umur_dari_jatuh_tempo_timestamp = strtotime($end_date) - $jatuh_tempo_timestamp;
            $umur_dari_jatuh_tempo = $umur_dari_jatuh_tempo_timestamp / (60 * 60 * 24);

            $penjualan_uuid_list[] = $r["uuid"];


            $piutang_report_data_list[$r["uuid"]]["no_transaksi"] = $no_transaksi;
            $piutang_report_data_list[$r["uuid"]]["tanggal"] = $tanggal;
            $piutang_report_data_list[$r["uuid"]]["tanggal_jatuh_tempo"] = $jatuh_tempo;
            $piutang_report_data_list[$r["uuid"]]["piutang"] = (float) $r["sisa"];
            $piutang_report_data_list[$r["uuid"]]["sisa_piutang"] = 0;
            $piutang_report_data_list[$r["uuid"]]["umur_dari_jatuh_tempo"] = $umur_dari_jatuh_tempo;
        }

        // **
        // get total piutang telah dibayar dari table pembayaran piutang detail berdasarkan pennjualan id
        $filters = array();
        $filters["penjualan_uuid_list"] = $penjualan_uuid_list;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $piutang_total_list = $this->transaksi_engine->pembayaran_piutang_detail_get_total_pembayaran_for_penjualan_uuid_list($penjualan_uuid_list);

        foreach ($piutang_total_list as $penjualan_uuid => $total_piutang_terbayar) {
            if (isset($piutang_report_data_list[$penjualan_uuid])) {
                $piutang = (float) $piutang_report_data_list[$penjualan_uuid]["piutang"];
                $piutang_report_data_list[$penjualan_uuid]["sisa_piutang"] = $piutang - $total_piutang_terbayar;
            }
        }

        // printr($piutang_report_data_list);
        return $piutang_report_data_list;
    }

    function laporan_pembayaran_piutang_aktif($sudah_jatuh_tempo = false)
    {
        $pelanggan_uuid = get("pelanggan_uuid");
        $end_date = $this->input->get("end_date");
        $start_date = date("Y-01-01", strtotime($end_date . " 00:00:00"));
        // periode akuntansi = 1 januari tahun berjalan - 31 desember tahun berjalan.
        // jika end date juli 2021, maka start date adalah 1 januari 2021   

        $filter_pelanggan_uuid = "";
        $filter_pelanggan_nama = "Semua Pelanggan";

        // **
        // check pelanggan uuid
        if (!empty($pelanggan_uuid)) {
            $filters = array();
            $filters["uuid"] = $pelanggan_uuid;
            $res = $this->pelanggan_engine->pelanggan_get_list($filters);
            if (count($res) > 0) {
                $res = $res[0];
                $filter_pelanggan_uuid = $res["uuid"];
                $filter_pelanggan_nama = $res["nama"];
            }
        }

        $settings = get_session("settings");
        $header = array(
            "nama_toko" => $settings["TOKO_NAMA"]["_value"],
            "alamat_toko" => $settings["TOKO_ALAMAT"]["_value"],
            "no_telepon_toko" => $settings["TOKO_NO_TELEPON"]["_value"],
        );
        $body = array(
            "judul" => "Laporan Piutang",
            "periode" => date("d-m-Y", strtotime($end_date)),
            "periode_akuntansi" => date("01-01-Y", strtotime($end_date)) . " s/d " . date("31-12-Y", strtotime($end_date)),
            "pelanggan_nama" => $filter_pelanggan_nama,
            "content" => array()
        );
        $footer = array();
        $result = array(
            "header" => $header,
            "body" => $body,
            "footer" => $footer
        );

        $filters = array();
        $filters["start_date_jatuh_tempo"] = $start_date . " 00:00:00";
        $filters["end_date_jatuh_tempo"] = $end_date . " 23:59:59";
        $filters["metode_pembayaran"] = "non tunai";
        $filters["lunas"] = 0;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        if (!empty($filter_pelanggan_uuid)) $filters["pelanggan_uuid"] = $filter_pelanggan_uuid;
        $penjualan_list = $this->transaksi_engine->penjualan_get_list($filters);
        if (count($penjualan_list) == 0) return $result;

        // **
        // get penjualan id list
        $penjualan_uuid_list = array();
        $piutang_report_data_list = array();
        foreach ($penjualan_list as $r) {
            $pelanggan_uuid = $r["pelanggan_uuid"];
            $pelanggan_number_formatted = trim($r["pelanggan_number_formatted"]);
            $pelanggan_nama = trim($r["pelanggan_nama"]);
            $no_transaksi = $r["number_formatted"];
            $tanggal = date("Y-m-d", strtotime($r["tanggal"]));
            $jatuh_tempo = date("Y-m-d", strtotime($r["jatuh_tempo"]));
            $jatuh_tempo_timestamp = strtotime($jatuh_tempo);
            $umur_dari_jatuh_tempo_timestamp = strtotime($end_date) - $jatuh_tempo_timestamp;
            $umur_dari_jatuh_tempo = $umur_dari_jatuh_tempo_timestamp / (60 * 60 * 24);

            $penjualan_uuid_list[] = $r["uuid"];


            $piutang_report_data_list[$r["uuid"]]["no_transaksi"] = $no_transaksi;
            $piutang_report_data_list[$r["uuid"]]["pelanggan_uuid"] = $pelanggan_uuid;
            $piutang_report_data_list[$r["uuid"]]["pelanggan_number_formatted"] = $pelanggan_number_formatted;
            $piutang_report_data_list[$r["uuid"]]["pelanggan_nama"] = $pelanggan_nama;
            $piutang_report_data_list[$r["uuid"]]["tanggal"] = $tanggal;
            $piutang_report_data_list[$r["uuid"]]["tanggal_jatuh_tempo"] = $jatuh_tempo;
            $piutang_report_data_list[$r["uuid"]]["piutang"] = (float) $r["sisa"];
            $piutang_report_data_list[$r["uuid"]]["sisa_piutang"] = 0;
            $piutang_report_data_list[$r["uuid"]]["umur_dari_jatuh_tempo"] = $umur_dari_jatuh_tempo;
        }

        // **
        // get total piutang telah dibayar dari table pembayaran piutang detail berdasarkan pennjualan id
        $filters = array();
        $filters["penjualan_uuid_list"] = $penjualan_uuid_list;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $piutang_total_list = $this->transaksi_engine->pembayaran_piutang_detail_get_total_pembayaran_for_penjualan_uuid_list($penjualan_uuid_list);

        foreach ($piutang_total_list as $penjualan_uuid => $total_piutang_terbayar) {
            if (isset($piutang_report_data_list[$penjualan_uuid])) {
                $piutang = (float) $piutang_report_data_list[$penjualan_uuid]["piutang"];
                $piutang_report_data_list[$penjualan_uuid]["sisa_piutang"] = $piutang - $total_piutang_terbayar;
            }
        }

        // ** 
        // order berdasarkan umur jauth tempo
        $temp = $piutang_report_data_list;
        $piutang_report_data_list = array();
        foreach ($temp as $t) {
            $umur_dari_jatuh_tempo = $t["umur_dari_jatuh_tempo"];

            $piutang_report_data_list[$umur_dari_jatuh_tempo][] = $t;
        }
        ksort($piutang_report_data_list);

        // **
        // order menjadi object list
        $temp = $piutang_report_data_list;
        $piutang_report_data_list = array();
        foreach ($temp as $umur => $t_list) {
            foreach ($t_list as $t) {
                $piutang_report_data_list[] = $t;
            }
        }

        // **
        // group berdasarkan pelanggan
        $temp = $piutang_report_data_list;
        $piutang_report_data_list = array();
        $piutang_pelanggan_data = array();
        foreach ($temp as $t) {
            $pelanggan_uuid = $t["pelanggan_uuid"];

            $piutang_report_data_list[$pelanggan_uuid][] = $t;

            if (!isset($piutang_pelanggan_data[$pelanggan_uuid]["total_piutang"])) $piutang_pelanggan_data[$pelanggan_uuid]["total_piutang"] = 0;
            if (!isset($piutang_pelanggan_data[$pelanggan_uuid]["total_sisa_piutang"])) $piutang_pelanggan_data[$pelanggan_uuid]["total_sisa_piutang"] = 0;

            $piutang_pelanggan_data[$pelanggan_uuid]["uuid"] = $t["pelanggan_uuid"];
            $piutang_pelanggan_data[$pelanggan_uuid]["number_formatted"] = $t["pelanggan_number_formatted"];
            $piutang_pelanggan_data[$pelanggan_uuid]["nama"] = ucwords($t["pelanggan_nama"]);
            $piutang_pelanggan_data[$pelanggan_uuid]["total_piutang"] += (float) $t["piutang"];
            $piutang_pelanggan_data[$pelanggan_uuid]["total_sisa_piutang"] += (float) $t["sisa_piutang"];
            $piutang_pelanggan_data[$pelanggan_uuid]["piutang_data"] = array();
        }

        // **
        // update piutang pelanggan data list        
        $temp = $piutang_report_data_list;
        foreach ($temp as $pelanggan_uuid => $t) {
            $piutang_pelanggan_data[$pelanggan_uuid]["piutang_data"] = $t;
        }

        // **
        // oder menjadi object list
        $piutang_report_data_list = array();
        foreach ($piutang_pelanggan_data as $p) {
            $piutang_report_data_list[] = $p;
        }
        $result["body"]["content"] = $piutang_report_data_list;
        return $result;
    }
}
