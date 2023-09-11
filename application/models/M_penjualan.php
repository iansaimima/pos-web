<?php

defined('BASEPATH') or exit('No direct script access allowed');
class M_penjualan extends MY_Model
{

    private $transaksi_engine;
    private $pelanggan_engine;
    private $item_engine;
    private $kas_engine;
    private $gudang_engine;
    private $settings_engine;

    private $cabang_selected_uuid;
    private $cabang_selected_kode;
    private $actor_user_uuid;
    private $actor_user_name;

    private $allow;
    private $allow_create;
    private $allow_update;
    private $allow_delete;
    private $allow_pelunasan;
    private $allow_print_nota;
    private $allow_print_riwayat_pembayaran_piutang;
    private $allow_detail_create;
    private $allow_detail_update;
    private $allow_detail_delete;

    private $uri_1;
    function __construct()
    {
        parent::__construct();

        $this->uri_1 = $this->uri->segment(1);

        $this->settings_engine = new Settings_engine();
        $this->pelanggan_engine = new Pelanggan_engine();
        $this->transaksi_engine = new Transaksi_engine();
        $this->item_engine = new Item_engine();
        $this->kas_engine = new Kas_engine();
        $this->gudang_engine = new Gudang_engine();

        $cabang_selected = get_session("cabang_selected");
        $this->cabang_selected_uuid = isset($cabang_selected["uuid"]) ? $cabang_selected["uuid"] : "";
        $this->cabang_selected_kode = isset($cabang_selected["kode"]) ? $cabang_selected["kode"] : "";

        $user = get_session("user");
        $role = strtolower(get_session("role"));
        $this->actor_user_uuid = isset($user["uuid"]) ? $user["uuid"] : "";
        $this->actor_user_name = isset($user["name"]) ? $user["name"] : "";

        $privilege_list = isset($user["privilege_list"]) ? $user["privilege_list"] : array();

        $this->allow        = isset($privilege_list["allow_transaksi_penjualan"]) ? $privilege_list["allow_transaksi_penjualan"] : 0;
        $this->allow_create = isset($privilege_list["allow_transaksi_penjualan_create"]) ? $privilege_list["allow_transaksi_penjualan_create"] : 0;
        $this->allow_update = isset($privilege_list["allow_transaksi_penjualan_update"]) ? $privilege_list["allow_transaksi_penjualan_update"] : 0;
        $this->allow_delete = isset($privilege_list["allow_transaksi_penjualan_delete"]) ? $privilege_list["allow_transaksi_penjualan_delete"] : 0;
        $this->allow_pelunasan = isset($privilege_list["allow_transaksi_penjualan_pelunasan"]) ? $privilege_list["allow_transaksi_penjualan_pelunasan"] : 0;
        $this->allow_print_nota = isset($privilege_list["allow_transaksi_penjualan_print_nota"]) ? $privilege_list["allow_transaksi_penjualan_print_nota"] : 0;
        $this->allow_print_riwayat_pembayaran_piutang = isset($privilege_list["allow_transaksi_penjualan_print_riwayat_pembayaran_piutang"]) ? $privilege_list["allow_transaksi_penjualan_print_riwayat_pembayaran_piutang"] : 0;
    }

    function penjualan_get_list($filters = array(), $gudang_uuid = "")
    {
        if (!$this->allow) return array();

        if ($this->uri_1 == "kasir") {
            $filters["creator_user_uuid"] = $this->actor_user_uuid;
        }

        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        if (!empty($gudang_uuid)) $filters["gudang_uuid"] = $gudang_uuid;
        $res = $this->transaksi_engine->penjualan_get_list($filters, true, true);
        $no = isset($filters["start"]) ? (int) $filters["start"] : 0;

        $final_res = array();
        foreach ($res as $r) {
            $no++;

            $number_formatted = $r["number_formatted"];

            $row = array();
            $row["no"] = $no;
            $row["uuid"] = trim($r["uuid"]);
            $row["number_formatted"] = $number_formatted;
            $row["tanggal"] = date("d M Y H:i:s", strtotime($r["tanggal"]));
            $row["kas_akun_nama"] = $r["kas_akun_nama"];
            $row["pelanggan_number_formatted"] = $r["pelanggan_number_formatted"];
            $row["pelanggan_nama"] = $r["pelanggan_nama"];
            $row["total_akhir"] = number_format($r['total_akhir'], 0, ",", ".");
            $row["sisa"] = number_format($r['sisa'], 0, ",", ".");
            $row["metode_pembayaran"] = $r["metode_pembayaran"];
            $row["status"] = $r["cache_status"];

            $final_res[] = $row;
        }

        return $final_res;
    }

    function penjualan_get_filtered_total($filters = array(), $gudang_uuid = "")
    {
        if (!$this->allow) return 0;

        if ($this->uri_1 == "kasir") {
            $filters["creator_user_uuid"] = $this->actor_user_uuid;
        }

        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        if (!empty($gudang_uuid)) $filters["gudang_uuid"] = $gudang_uuid;
        $res = $this->transaksi_engine->penjualan_get_list($filters, false, true);
        return count($res);
    }

    function penjualan_get_total($filters = array(), $gudang_uuid = "")
    {
        if (!$this->allow) return 0;

        if ($this->uri_1 == "kasir") {
            $filters["creator_user_uuid"] = $this->actor_user_uuid;
        }

        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        if (!empty($gudang_uuid)) $filters["gudang_uuid"] = $gudang_uuid;
        $res = $this->transaksi_engine->penjualan_get_list($filters);
        return count($res);
    }

    function penjualan_get($uuid = "")
    {
        if (!$this->allow) return array();

        $filters = array();
        $filters["uuid"] = $uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->transaksi_engine->penjualan_get_list($filters);
        if (count($res) == 0) return array();
        $penjualan = $res[0];
        $penjualan_uuid = $penjualan["uuid"];

        // **
        // get penjualan detail list for penjualan id
        $filters = array();
        $filters["penjualan_uuid"] = $penjualan_uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $detail_list = $this->transaksi_engine->penjualan_detail_get_list($filters);
        $penjualan["detail"] = $detail_list;

        return $penjualan;
    }

    function penjualan_get_by_no_penjualan($no = "")
    {

        $filters = array();
        $filters["number_formatted"] = $no;
        $res = $this->transaksi_engine->penjualan_get_list($filters);
        if (count($res) == 0) return array();
        $penjualan = $res[0];
        $penjualan_uuid = $penjualan["uuid"];

        // **
        // get penjualan detail list for penjualan id
        $filters = array();
        $filters["penjualan_uuid"] = $penjualan_uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $detail_list = $this->transaksi_engine->penjualan_detail_get_list($filters);
        $penjualan["detail"] = $detail_list;

        return $penjualan;
    }

    function penjualan_delete($uuid = "")
    {
        if (!$this->allow_delete) return set_http_response_error(HTTP_FORBIDDEN, "Akses ditolak");

        $filters = array();
        $filters["uuid"] = $uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->transaksi_engine->penjualan_get_list($filters);
        if (count($res) == 0) return set_http_response_error(HTTP_BAD_REQUEST, "Penjualan tidak ditemukan");
        $res = $res[0];
        $uuid = $res["uuid"];
        $penjualan_uuid = $uuid;
        $metode_pembayaran = $res["metode_pembayaran"];

        if (strtolower($metode_pembayaran) == "non tunai") {
            // **
            // jika non tunai, check dulu jika ada ada peluasan, maka batalkan proses hapus
            // hahrus hapus pelunasan lebih dulu
            $filters = array();
            $filters["penjualan_uuid"] = $penjualan_uuid;
            $filters["cabang_uuid"] = $this->cabang_selected_uuid;
            $res = $this->transaksi_engine->pembayaran_piutang_detail_get_list($filters);
            if (count($res) > 0) {
                return set_http_response_error(HTTP_BAD_REQUEST, "Penjualan ini sudah memiliki riwayat pembayaran piutang / pelunasan. Hapus semua pembayaran piutang / pelunasan lebih dulu kemudian hapus penjualan ini");
            }
        }

        // **
        // check jika ada penjualan retur untuk penjualan id
        $filters = array();
        $filters["penjualan_uuid"] = $penjualan_uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->transaksi_engine->penjualan_retur_get_list($filters);
        if (count($res) > 0) return set_http_response_error(HTTP_BAD_REQUEST, "Ada retur penjualan untuk penjualan ini. Penjualan tidak dapat dihapus");

        // **
        // get item id list from penjualan detail by penjualan id
        $filters = array();
        $filters["penjualan_uuid"] = $penjualan_uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->transaksi_engine->penjualan_detail_get_list($filters);

        $item_uuid_list = array();
        foreach ($res as $r) {
            $item_uuid = $r['item_uuid'];
            $item_uuid_list[] = $item_uuid;
        }

        $this->db->trans_start();
        try {
            // **
            // hapus penjualan
            $res = $this->transaksi_engine->penjualan_delete($penjualan_uuid);
            if ($res == false) throw new Exception("Gagal menghapus penjualan #001");

            // **
            // hapus penjualan detail
            $res = $this->transaksi_engine->penjualan_detail_delete_by_penjualan_uuid($penjualan_uuid);
            if ($res == false) throw new Exception("Gagal menghapus penjualan #002");

            foreach ($item_uuid_list as $index => $item_uuid) {
                // **
                // get current total stock satuan terkecil
                $stock = $this->item_engine->item_get_total_stock_for_item_uuid($item_uuid);

                // update cache stock
                $res = $this->item_engine->item_update_cache_stock($item_uuid, $stock);
                if ($res == false) throw new Exception("Gagal menghapus penjualan #003");
            }

            // **
            // delete kas alur
            $res = $this->kas_engine->kas_alur_delete_for_transaksi_penjualan_uuid($penjualan_uuid);
            if ($res == false) throw new Exception("Gagal menghapus penjualan #004");
        } catch (Exception $e) {
            $this->db->trans_rollback();
            return set_http_response_error(501, "Error : " . $e->getMessage());
        }

        $this->db->trans_commit();
        return set_http_response_success("Penjualan telah dihapus");
    }

    function penjualan_save()
    {
        $uuid = $this->input->post("uuid");
        $tanggal = $this->input->post("tanggal");
        $pelanggan_uuid = $this->input->post("pelanggan_uuid");
        $gudang_uuid = $this->input->post("gudang_uuid");
        $item_detail_json = $this->input->post("item_detail");
        $potongan = to_number($this->input->post("potongan"));
        $kas_akun_uuid = $this->input->post("kas_akun_uuid");
        $bayar = to_number($this->input->post("bayar"));
        $keterangan = $this->input->post("keterangan");
        $metode_pembayaran = $this->input->post("metode_pembayaran");
        $jatuh_tempo = $this->input->post("jatuh_tempo");


        // **
        // validasi
        if (empty($metode_pembayaran)) return set_http_response_error(HTTP_BAD_REQUEST, "Metode pembayaran harus dipilih");
        if (empty($tanggal)) return set_http_response_error(HTTP_BAD_REQUEST, "Tanggal harus dipilih");
        if (date("Y-m-d", strtotime($tanggal)) == "1970-01-01") return set_http_response_error(HTTP_BAD_REQUEST, "Tanggal tidak valid");
        $item_detail_list = json_decode($item_detail_json, true);
        if (!is_array($item_detail_list)) $item_detail_list = array();
        if (count($item_detail_list) == 0) return set_http_response_error(HTTP_BAD_REQUEST, "Belum ada item yang dipilih pada penjualan");

        if (strtolower($metode_pembayaran) == "non tunai") {
            if (date("Y-m-d", strtotime($jatuh_tempo)) == "1970-01-01") {
                $jatuh_tempo = date("Y-m-d", strtotime($tanggal . " +" . DEFAULT_JUMLAH_HARI_JATUH_TEMPO . " " . DEFAULT_PERIODE_JATUH_TEMPO));
            } else {
                if (strtotime($jatuh_tempo) < strtotime($tanggal)) return set_http_response_error(HTTP_BAD_REQUEST, "Tanggal jatuh tempo harus lebih dari tanggal penjualan");
            }
        } else {
            $jatuh_tempo = $tanggal;
        }


        if (strtotime($tanggal) > time()) {
            return set_http_response_error(HTTP_BAD_REQUEST, "Tanggal Penjualan tidak boleh lebih dari tanggal hari ini");
        }

        // **
        // check pelanggan uuid
        $filters = array();
        $filters["uuid"] = $pelanggan_uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->pelanggan_engine->pelanggan_get_list($filters);
        if (count($res) == 0) return set_http_response_error(HTTP_BAD_REQUEST, "pelanggan tidak ditemukan");
        $res = $res[0];
        $pelanggan_uuid = $res["uuid"];
        $pelanggan_number_formatted = trim($res["number_formatted"]);
        $pelanggan_nama = trim($res["nama"]);
        $pelanggan_alamat = trim($res["alamat"]);
        $pelanggan_no_telepon = trim($res["no_telepon"]);

        // **
        // check gudang uuid
        $filters = array();
        $filters["uuid"] = $gudang_uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->gudang_engine->gudang_get_list($filters);
        if (count($res) == 0) return set_http_response_error(HTTP_BAD_REQUEST, "Gudang tidak ditemukan");
        $res = $res[0];
        $gudang_uuid = $res["uuid"];
        $gudang_kode = $res["kode"];
        $gudang_nama = $res["nama"];

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

        $penjualan_uuid = "";
        $kas_alur_uuid = "";
        $kas_alur_data = array();
        $created = date("Y-m-d H:i:s");
        $creator_user_uuid = $this->actor_user_uuid;
        $creator_user_name = $this->actor_user_name;
        $number = 0;
        $number_formatted = PREFIX_PENJUALAN . "/" . $this->cabang_selected_kode . "/" . $gudang_kode . "/" . microtime_();
        $tahun = date("Y", strtotime($tanggal . " 00:00:00"));
        $jam = date("H:i:s");

        // **
        // set gudang id
        // $settings = $this->settings_engine->get_settings('TRANSAKSI_PENJUALAN_DEFAULT_GUDANG_ID');
        // $gudang_uuid = $settings["_value"];

        $current_item_uuid_list = array();
        $old_tahun = $tahun;
        $old_gudang_kode = $gudang_kode;
        if (!empty($uuid)) {
            $filters = array();
            $filters["uuid"] = $uuid;
            $filters["cabang_uuid"] = $this->cabang_selected_uuid;
            $res = $this->transaksi_engine->penjualan_get_list($filters);
            if (count($res) == 0) return set_http_response_error(HTTP_BAD_REQUEST, "Penjualan tidak ditemukan");
            $res = $res[0];
            $uuid = $res["uuid"];
            $penjualan_uuid = $uuid;
            $created = $res["created"];
            $creator_user_uuid = $res["creator_user_uuid"];
            $creator_user_name = $res["creator_user_name"];
            $number = (int) $res["number"];
            $number_formatted = trim($res["number_formatted"]);
            $number_formatted = PREFIX_PENJUALAN . "/" . $this->cabang_selected_kode . "/" . $gudang_kode . "/" . microtime_();

            $old_gudang_kode = $res["gudang_kode"];
            $old_tahun = $res["tahun"];

            // **
            // get current penjualan detail list
            $filters = array();
            $filters["penjualan_uuid"] = $penjualan_uuid;
            $filters["cabang_uuid"] = $this->cabang_selected_uuid;
            $list = $this->transaksi_engine->penjualan_detail_get_list($filters);
            foreach ($list as $l) {
                $current_item_uuid = $l['item_uuid'];
                $current_item_uuid_list[] = $current_item_uuid;
            }

            $tahun = date("Y", $res["tahun"]);
            $jam = date("H:i:s", strtotime($res["tanggal"]));

            // **
            // dapatkan kas alur id berdasarkan transaksi_penjualan_uuid
            $filters = array();
            $filters['transaksi_penjualan_uuid'] = $uuid;
            $filters["cabang_uuid"] = $this->cabang_selected_uuid;
            $res2 = $this->kas_engine->kas_alur_get_list($filters);
            if (count($res2) > 0) {
                $res2 = $res2[0];
                $kas_alur_uuid = $res2["uuid"];

                $kas_alur_data = $res2;
            }

            // **
            // check, jika sudah ada pelunasan, maka tidak bisa melakukan perubahan penjualan
            $filters = array();
            $filters["penjualan_uuid"] = $uuid;
            $filters["cabang_uuid"] = $this->cabang_selected_uuid;
            $res = $this->transaksi_engine->pembayaran_piutang_detail_get_list($filters);
            if (count($res) > 0) return set_http_response_error(HTTP_BAD_REQUEST, "Penjualan non tunai ini sudah ada riwayat pembayaran piutang / pelunasan. Proses ubah tidak dapat dilakukan");
        }

        if (empty($uuid)) {
            if (!$this->allow_create) return set_http_response_error(HTTP_FORBIDDEN, "Akses ditolak");
        } else {
            if (!$this->allow_update) return set_http_response_error(HTTP_FORBIDDEN, "Akses ditolak");
        }

        $final_item_list = array();
        $sub_total = 0;
        $item_uuid_list = array();

        // **
        // -- generate penjualan detail
        // -- get harga beli satuan terkecil
        // -- get sub_total dari total harga beli
        // -- genereate item id list

        $jumlah_satuan_terkecil_item = array();
        $current_stock_item = array();
        $satuan_terkecil_item = array();
        $cek_stock_saat_penjualan_item = array();
        foreach ($item_detail_list as $i) {
            $kode = $i["item_code"];
            $selected_satuan = $i["satuan"];
            $jumlah = (int) $i["jumlah"];
            $potongan_persen = (float) $i['potongan'];
            if ((int) $jumlah == 0) return set_http_response_error(HTTP_BAD_REQUEST, "Item dengan kode $kode tidak memiliki jumlah");
            if (empty($selected_satuan)) return set_http_response_error(HTTP_BAD_REQUEST, "Satuan yang dipilih untuk item dengan kode $kode tidak dikenal");

            $filters = array();
            $filters["kode"] = $kode;
            $filters["arsip"] = 0;
            $filters["cabang_uuid"] = $this->cabang_selected_uuid;
            $res = $this->item_engine->item_get_list($filters);
            if (count($res) == 0) return set_http_response_error(HTTP_BAD_REQUEST, "Item dengan kode $kode tidak ditemukan atau tidak aktif");
            $res = $res[0];
            $item_uuid = $res["uuid"];
            $item_kode = $kode;
            $item_barcode = $res["barcode"];
            $item_nama = $res["nama"];
            $item_struktur_satuan_harga_json = $res["struktur_satuan_harga_json"];
            $item_tipe = $res["tipe"];
            $item_cek_stock_saat_penjualan = (int) $res["cek_stock_saat_penjualan"];
            $item_kategori_uuid = $res["item_kategori_uuid"];
            $item_kategori_nama = trim($res["item_kategori_nama"]);
            $current_stock = (int) $res['cache_stock'];
            $item_jenis_perhitungan_harga_jual = trim($res["jenis_perhitungan_harga_jual"]);
            $struktur_satuan_harga_list = json_decode($item_struktur_satuan_harga_json, true);
            if (!is_array($struktur_satuan_harga_list) || count($struktur_satuan_harga_list) == 0) return set_http_response_error(HTTP_BAD_REQUEST, "Item dengan kode $kode belum memiliki struktur satuan dan harga");

            // **
            // ambil jumlah satuan terkecil pada penjualan detail jika edit
            // untuk di tambahkan ke current stock
            if (!empty($uuid)) {
                $filters = array();
                $filters["penjualan_uuid"] = $uuid;
                $filters["item_uuid"] = $item_uuid;
                $filters["cabang_uuid"] = $this->cabang_selected_uuid;
                $penjualan_detail = $this->transaksi_engine->penjualan_detail_get_list($filters);
                if (count($penjualan_detail) > 0) {
                    $penjualan_detail = $penjualan_detail[0];

                    $current_stock += (int) $penjualan_detail["jumlah_satuan_terkecil"];
                }
            }

            $current_stock_item[$kode] = $current_stock;
            $cek_stock_saat_penjualan_item[$kode] = $item_cek_stock_saat_penjualan;

            // **
            // generate item id list
            $item_uuid_list[] = $item_uuid;

            // remove key from array
            $new_struktur_satuan_harga_list = array();
            $satuan_list = array();
            foreach ($struktur_satuan_harga_list as $satuan => $s) {
                $new_struktur_satuan_harga_list[] = $s;
                $satuan_list[] = $satuan;
            }

            krsort($new_struktur_satuan_harga_list);
            if (!in_array($selected_satuan, $satuan_list)) return set_http_response_error(HTTP_BAD_REQUEST, "Satuan yang dipilih untuk item dengan kode $kode tidak tersedia");

            // set back key to struktur list
            $struktur_satuan_harga_list = array();
            foreach ($new_struktur_satuan_harga_list as $n) {
                $struktur_satuan_harga_list[strtoupper($n["satuan"])] = $n;
            }

            $harga_jual = $struktur_satuan_harga_list[strtoupper($selected_satuan)]["harga_jual"];
            $harga_beli = $struktur_satuan_harga_list[strtoupper($selected_satuan)]["harga_pokok"];

            $margin_jual = $harga_jual - $harga_beli;
            $potongan_harga = 0;
            if ($harga_jual > 0 && $potongan_persen > 0) {
                $potongan_harga = $harga_jual * ($potongan_persen / 100);
            }


            // ** 
            // get total pcs untuk satuan terkecil
            $satuan_found = false;
            $selected_struktur_satuan_harga_list = array();
            $total_jumlah_satuan_terkecil = 1;
            foreach ($struktur_satuan_harga_list as $satuan => $s) {

                // **
                // proses untuk cek, apakah satuan dipilih ada, 
                // jika tidak ada, next ke struktur satuan selanjutnya, 
                // jika ada, maka ambil struktur satuan sampai satuan terkecil
                if (!$satuan_found) {
                    if ($selected_satuan != $satuan) {
                        $satuan_found = false;
                    } else {
                        $satuan_found = true;
                    }
                }
                if (!$satuan_found) continue;

                $selected_struktur_satuan_harga_list[$satuan] = $s;

                $total_jumlah_satuan_terkecil *= $s["konversi"];
            }
            $harga_jual_satuan_terkecil = $harga_jual / $total_jumlah_satuan_terkecil;
            $margin_jual_satuan_terkecil = $margin_jual / $total_jumlah_satuan_terkecil;

            $jumlah_satuan_terkecil = $total_jumlah_satuan_terkecil * $jumlah;
            if (!isset($jumlah_satuan_terkecil_item[$kode])) $jumlah_satuan_terkecil_item[$kode] = 0;
            $jumlah_satuan_terkecil_item[$kode] += $jumlah_satuan_terkecil;

            // **
            // hitung cache total
            $total = $jumlah * ($harga_jual - $potongan_harga);
            $sub_total += $total;

            $penjualan_detail_data_list = array(
                "uuid"     => "",
                "created" => date("Y-m-d H:i:s"),
                "creator_user_uuid" => $this->actor_user_uuid,
                "creator_user_name" => $this->actor_user_name,
                "last_updated" => date("Y-m-d H:i:s"),
                "last_updated_user_uuid" => $this->actor_user_uuid,
                "last_updated_user_name" => $this->actor_user_name,
                "Penjualan_uuid" => $uuid,
                "item_uuid" => $item_uuid,
                "item_kode" => $item_kode,
                "item_barcode" => $item_barcode,
                "item_nama" => $item_nama,
                "item_struktur_satuan_harga_json" => $item_struktur_satuan_harga_json,
                "item_tipe" => $item_tipe,
                "item_cek_stock_saat_penjualan" => $item_cek_stock_saat_penjualan,
                "item_kategori_uuid" => $item_kategori_uuid,
                "item_kategori_nama" => $item_kategori_nama,
                "jumlah" => $jumlah,
                "satuan" => $selected_satuan,
                "harga_jual_satuan" => $harga_jual,
                "margin_jual_satuan" => $margin_jual,
                "harga_jual_satuan_terkecil" => $harga_jual_satuan_terkecil,
                "margin_jual_satuan_terkecil" => $margin_jual_satuan_terkecil,
                "jumlah_satuan_terkecil" => $jumlah_satuan_terkecil,
                "potongan_persen" => $potongan_persen,
                "potongan_harga" => (int) $potongan_harga,

                "cabang_uuid" => $this->cabang_selected_uuid,
            );

            $final_item_list[] = $penjualan_detail_data_list;
        }

        foreach ($jumlah_satuan_terkecil_item as $kode => $jumlah) {
            $stock = $current_stock_item[$kode];
            $cek_stock_saat_penjualan = $cek_stock_saat_penjualan_item[$kode];

            if ($cek_stock_saat_penjualan == 1) {
                if ($jumlah > $stock) {
                    $satuan_terkecil = $satuan_list[0];
                    return set_http_response_error(
                        HTTP_BAD_REQUEST,
                        "Stock tidak mencukupi untuk item dengan kode $kode. <br/>
                        Total stock : " . number_format($stock) . " $satuan_terkecil <br/>
                        Total jumlah : " . number_format($jumlah) . " $satuan_terkecil"
                    );
                }
            }
        }

        // **
        // ambil deleted item id list
        $_temp = array_diff($current_item_uuid_list, $item_uuid_list);
        $deleted_item_data_list = array();
        foreach ($_temp as $index => $_item_uuid) {
            // **
            // get current total stock satuan terkecil
            $stock = $this->item_engine->item_get_total_stock_for_item_uuid($_item_uuid);
            $deleted_item_data_list[$_item_uuid]["stock"] = $stock;
        }

        // **
        // hitung total akhir, sisa dan sudah dibayar
        $total_akhir = $sub_total - $potongan;
        if ($potongan >= $sub_total) {
            $total_akhir = 0;
        }

        // $sisa = $total_akhir - $bayar;
        // $kembali = 0;
        // $lunas = 0;
        // if ($bayar >= $total_akhir) {
        //     $sisa = 0;
        //     $kembali = $bayar - $total_akhir;
        //     $lunas = 1;
        // }

        // **
        // =====================================================
        // bypass
        // =====================================================

        $cache_status = "Belum Lunas";
        $cache_sisa_piutang = 0;
        if ($bayar >= $total_akhir) {
            $cache_status = "Lunas";
            $metode_pembayaran = "Tunai";
        }
        if (strtolower($metode_pembayaran) == "non tunai") {
            $kembali = 0;

            $sisa = $total_akhir - $bayar;
            $lunas = 0;

            if (!empty($uuid)) {
                // ambil total pembayaran dari penjualan pelunasan
                // untuk set bayar, sisa dan lunas
                $total_pembayaran_list = $this->transaksi_engine->pembayaran_piutang_detail_get_total_pembayaran_for_penjualan_uuid_list(array($uuid));
                if (count($total_pembayaran_list) > 0 && isset($total_pembayaran_list[$uuid]) && (float) $total_pembayaran_list[$uuid] > 0) {
                    $cache_sisa_piutang = $total_akhir - $total_pembayaran_list[$uuid];

                    if ($cache_sisa_piutang <= 0) $lunas = 1;
                }
            }
        } else {
            if ($bayar < $total_akhir) {
                return set_http_response_error(HTTP_BAD_REQUEST, "Jumlah yang dibayar kurang dari total akhir");
            }
            $kembali = $bayar - $total_akhir;
            $sisa = 0;
            $lunas = 1;
        }
        if ($lunas == 1) {
            $cache_status = "Lunas";
        }

        $penjualan_data = array(
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
            "pelanggan_number_formatted" => $pelanggan_number_formatted,
            "pelanggan_nama" => $pelanggan_nama,
            "pelanggan_alamat" => $pelanggan_alamat,
            "pelanggan_no_telepon" => $pelanggan_no_telepon,
            "pelanggan_potongan_persen" => 0,

            "gudang_uuid" => $gudang_uuid,
            "gudang_kode" => $gudang_kode,
            "gudang_nama" => $gudang_nama,

            "kas_akun_uuid" => $kas_akun_uuid,
            "kas_alur_uuid" => $kas_alur_uuid,
            "sub_total" => $sub_total,
            "potongan" => $potongan,
            "total_akhir" => $total_akhir,
            "metode_pembayaran" => $metode_pembayaran,
            "jatuh_tempo" => $jatuh_tempo,
            "bayar" => $bayar,
            "kembali" => $kembali,
            "sisa" => $sisa,
            "lunas" => $lunas,
            "cache_status" => $cache_status,
            "cache_sisa_piutang" => $cache_sisa_piutang,
            "keterangan" => $keterangan,

            "old_tahun" => $old_tahun,
            "old_gudang_kode" => $old_gudang_kode,

            "cabang_uuid" => $this->cabang_selected_uuid,
            "cabang_kode" => $this->cabang_selected_kode,
        );

        // **
        // update kas alur data
        $kas_alur_tanggal_changed = false;
        if (count($kas_alur_data) == 0) {
            $kas_alur_data["uuid"] = $kas_alur_uuid;
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

            $kas_alur_data["kas_kategori_uuid"] = -20;
            $kas_alur_data["kas_kategori_nama"] = "Transaksi Penjualan";

            $kas_alur_data["alur_kas"] = "Masuk";

            $kas_alur_data["jumlah_masuk"] = $bayar;
            $kas_alur_data["jumlah_keluar"] = 0;

            $kas_alur_data["keterangan"] = "Transaksi Penjualan";

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

            // $kas_alur_data["tanggal"] = $tanggal;
            $kas_alur_data["jumlah_masuk"] = $bayar;
            $kas_alur_data["kas_akun_uuid"] = $kas_akun_uuid;
            $kas_alur_data["kas_akun_nama"] = $kas_akun_data["nama"];
        }

        // **
        // jika non tunai, maka simpan sesuai total akhir penjualan
        if (strtolower($metode_pembayaran) == "tunai") {
            $kas_alur_data["jumlah_masuk"] = $total_akhir;
        }

        $this->db->trans_start();
        try {
            // **
            // hapus semua penjualan detail untuk penjualan id jika penjualan id != 0
            if (!empty($uuid)) {
                $res = $this->transaksi_engine->penjualan_detail_delete_by_penjualan_uuid($uuid);
                if ($res == false) throw new Exception("Gagal menyimpan penjualan #001");
            }

            // **
            // simpan penjualan
            $res = $this->transaksi_engine->penjualan_save($penjualan_data);
            if ($res == false) throw new Exception("Gagal menyimpan penjualan #002");
            if (empty($uuid)) {
                $uuid = $res;
                $penjualan_uuid = $uuid;
            }

            // **
            // get detail penjualan untuk set ke kas alur data
            $filters = array();
            $filters["uuid"] = $uuid;
            $filters["cabang_uuid"] = $this->cabang_selected_uuid;
            $res = $this->transaksi_engine->penjualan_get_list($filters);
            $res = $res[0];
            $kas_alur_data['transaksi_penjualan_uuid'] = $uuid;
            $kas_alur_data['transaksi_penjualan_number_formatted'] = $res['number_formatted'];
            $kas_alur_data['keterangan'] = "Transaksi penjualan " . $res['number_formatted'];

            // **
            // simpan penjualan detail
            foreach ($final_item_list as $penjualan_detail) {
                $penjualan_detail["penjualan_uuid"] = $penjualan_uuid;
                $harga_jual_satuan_terkecil = (float) $penjualan_detail["harga_jual_satuan_terkecil"];
                $item_uuid = $penjualan_detail["item_uuid"];
                $item_struktur_satuan_harga_json = $penjualan_detail["item_struktur_satuan_harga_json"];
                $item_struktur_satuan_harga_list = json_decode($item_struktur_satuan_harga_json, true);

                $selected_satuan = $penjualan_detail["satuan"];
                $jumlah = (int) $penjualan_detail["jumlah"];

                // **
                // simpan penjualan detail
                $res = $this->transaksi_engine->penjualan_detail_save($penjualan_detail);
                if ($res == false) throw new Exception("Gagal menyimpan penjualan #003");

                // **
                // get current total stock satuan terkecil
                $stock = $this->item_engine->item_get_total_stock_for_item_uuid($item_uuid);

                // update cache stock
                $res = $this->item_engine->item_update_cache_stock($item_uuid, $stock);
                if ($res == false) throw new Exception("Gagal menyimpan penjualan #006");
            }


            // **
            // update stock  untuk item yang dihapus
            foreach ($deleted_item_data_list as $_item_uuid => $data) {

                // **
                // get current total stock satuan terkecil
                $stock = $this->item_engine->item_get_total_stock_for_item_uuid($item_uuid);

                // update cache stock
                $res = $this->item_engine->item_update_cache_stock($_item_uuid, $stock);
                if ($res == false) throw new Exception("Gagal menyimpan penjualan #008");
            }

            // **
            // simpan kas alur
            $res = $this->kas_engine->kas_alur_save($kas_alur_data);
            if ($res == false) throw new Exception("Gagal menyimpan penjualan #009");
            if (empty($kas_alur_uuid)) {
                $kas_alur_uuid = $res;
                // **
                // update kas alur id untuk transaksi penjualan
                $res = $this->transaksi_engine->penjualan_update_kas_alur_uuid($uuid, $kas_alur_uuid);
                if ($res == false) throw new Exception("Gagal menyimpan penjualan #010");
            }
        } catch (Exception $e) {
            $this->db->trans_rollback();
            return set_http_response_error(501, "Error : " . $e->getMessage());
        }

        $this->db->trans_commit();
        return set_http_response_success("Penjualan telah disimpan", array(), trim($uuid));
    }

    function item_get_detail_by_kode()
    {
        $kode = $this->input->get("kode");
        if (empty($kode)) return set_http_response_error(HTTP_BAD_REQUEST, "EMPTY_KODE");

        $filters = array();
        $filters["kode"] = $kode;
        $filters["arsip"] = 0;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->item_engine->item_get_list($filters);
        if (count($res) == 0) return set_http_response_error(HTTP_BAD_REQUEST, "NO_DATA");
        $res = $res[0];
        $uuid = $res["uuid"];
        $current_stock = $this->item_engine->item_get_total_stock_for_item_uuid($uuid);
        $cek_stock_saat_penjualan = (int) $res["cek_stock_saat_penjualan"];

        $struktur_satuan_harga_json = $res["struktur_satuan_harga_json"];
        $struktur_satuan_harga_list = json_decode($struktur_satuan_harga_json, true);
        if (json_last_error_msg() != JSON_ERROR_NONE || !is_array($struktur_satuan_harga_list)) return set_http_response_error(HTTP_BAD_REQUEST, "Struktur satuan bermasalah");

        $satuan_list = array();
        $harga_list = array();
        $stock_list = array();

        $stock = $current_stock;
        foreach ($struktur_satuan_harga_list as $satuan => $l) {
            $row = array(
                "name" => $satuan,
                "label" => $l["satuan"],
                "harga_jual" => $l["harga_jual"],
                "harga_pokok" => $l["harga_pokok"]
            );
            $satuan_list[] = $row;

            $stock = $stock / (int) $l['konversi'];

            $harga_list[strtoupper($satuan)] = $l['harga_jual'];
            $stock_list[strtoupper($satuan)] = $stock;
        }

        $data = array();
        $data["kode"] = $res["kode"];
        $data["nama"] = $res["nama"];
        $data["nama_kategori"] = $res["item_kategori_nama"];
        $data["satuan_list"] = $satuan_list;
        $data["harga_list"] = $harga_list;
        $data["stock_list"] = $stock_list;
        $data["cek_stock"] = $cek_stock_saat_penjualan;

        return set_http_response_success("OK", $data);
    }

    function pelanggan_get_detail_by_uuid($uuid = "")
    {
        if (empty($uuid)) return set_http_response_error(HTTP_BAD_REQUEST, "EMPTY_KODE");

        $filters = array();
        $filters["uuid"] = $uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->pelanggan_engine->pelanggan_get_list($filters);
        if (count($res) == 0) return set_http_response_error(HTTP_BAD_REQUEST, "NO_DATA");
        $res = $res[0];

        $data = array(
            'nama' => $res["nama"],
            'alamat' => $res['alamat'],
            'no_telepon' => $res['no_telepon'],
            'keterangan' => $res['keterangan'],
            'potongan_persen' => (float) $res['potongan_persen'],
        );

        return set_http_response_success('OK', $data);
    }


    function penjualan_cetak($uuid = "")
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

    function penjualan_cetak_riwayat_pembayaran_piutang($uuid = "")
    {
        $settings = get_session("settings");
        $header = array(
            "nama_toko" => $settings["TOKO_NAMA"]["_value"],
            "alamat_toko" => $settings["TOKO_ALAMAT"]["_value"],
            "no_telepon_toko" => $settings["TOKO_NO_TELEPON"]["_value"],
        );
        $body = array(
            "judul" => "Riwayat Pembayaran Piutang",
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
        if (!$this->allow_print_riwayat_pembayaran_piutang) return $result;

        $filters = array();
        $filters["uuid"] = $uuid;
        $filters["metode_pembayaran"] = "Non Tunai";
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
        $sisa = (float) $penjualan["sisa"];

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
        $sub_total = 0;
        $potongan = (float) $penjualan["potongan"];
        $filters = array();
        $filters["penjualan_uuid"] = $penjualan_uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $penjualan_detail_list = $this->transaksi_engine->penjualan_detail_get_list($filters);
        foreach ($penjualan_detail_list as $dl) {
            $jumlah = (int) $dl["jumlah"];
            $harga_jual = (float) $dl["harga_jual_satuan"];
            $potongan_harga = (float) $dl["potongan_harga"];

            $total = $jumlah * ($harga_jual - $potongan_harga);

            $sub_total += $total;
        }

        // **
        // get pembayaran piutang detail list for penjualan id
        $filters = array();
        $filters["penjualan_uuid"] = $penjualan_uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $pembayaran_piutang_list = $this->transaksi_engine->pembayaran_piutang_detail_get_list($filters);
        $penjualan["riwayat_pembayaran_piutang"] = $pembayaran_piutang_list;

        $body["tanggal"] = $tanggal;
        $body["no"] = $number_formatted;
        $body["pelanggan_number_formatted"] = $penjualan["pelanggan_number_formatted"];
        $body["pelanggan_nama"] = $penjualan["pelanggan_nama"];
        $body["pelanggan_detail"] = $penjualan["pelanggan_alamat"] . "\n" . $penjualan["pelanggan_no_telepon"];

        $contents = array();
        $no = 1;
        $piutang = $sisa;
        $sisa_piutang = $sisa;
        foreach ($pembayaran_piutang_list as $dl) {
            $jumlah_bayar = (float) $dl["jumlah"];

            $sisa_piutang = $sisa_piutang - $jumlah_bayar;


            $row = array(
                "no" => $no,
                "number_formatted" => $dl["pembayaran_piutang_number_formatted"],
                "tanggal" => date("d/m/Y", strtotime($dl["pembayaran_piutang_tanggal"])),
                "cara_bayar" => $dl["pembayaran_piutang_cara_bayar"],
                "keterangan" => $dl["pembayaran_piutang_keterangan"],
                "piutang" => number_format($piutang, 0, ",", "."),
                "jumlah_bayar" => number_format($jumlah_bayar, 0, ",", "."),
                "sisa_piutang" => number_format($sisa_piutang, 0, ",", ".")
            );
            $piutang = $piutang - $jumlah_bayar;

            $contents[] = $row;
            $no++;
        }

        $total_akhir = $sub_total - $potongan;
        $bayar = (float) $penjualan["bayar"];
        $kembali = (float) $penjualan["kembali"];

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

    // **
    // DASHBOARD
    // -- grafik penjualan
    function dashboard_grafik_penjualan_for_last_day_number($last_day_number = 90)
    {

        if ((int) $last_day_number <= 0) $last_day_number = 30;

        $end_date = date("Y-m-d 23:59:59");
        $start_date = date("Y-m-d 00:00:00", strtotime($end_date . " -$last_day_number DAYS"));

        // **
        // generate date range
        $date_range_list = generate_date_range_list($start_date, $end_date, true);

        // init default value list and total
        $temp = $date_range_list;
        $date_range_list = array();
        foreach ($temp as $tanggal => $t) {
            $date_range_list[$tanggal] = 0;
        }

        // **
        // get daftar penjualan berdasarkan date range
        $filters = array();
        $filters["start_date"] = $start_date;
        $filters["end_date"] = $end_date;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $list = $this->transaksi_engine->penjualan_get_list($filters);

        // **
        // generate grafik data list untuk setiap tanggal
        $tanggal_list = array();
        $total_list = array();
        foreach ($list as $l) {
            $tanggal = date("Y-m-d", strtotime($l['tanggal']));
            $total = (float) $l["total_akhir"];

            if (isset($date_range_list[$tanggal])) {
                $date_range_list[$tanggal] += $total;
            }
        }

        $grand_total = 0;
        foreach ($date_range_list as $tanggal => $total) {
            $tanggal_list[] = date("d/M", strtotime($tanggal));
            $total_list[] = $total;

            $grand_total += $total;
        }

        $result = array(
            "graph_data" => $date_range_list,
            "tanggal_list" => $tanggal_list,
            "total_list" => $total_list,
            "grand_total" => $grand_total,
            "judul" => $last_day_number . " hari terakhir"
        );

        return set_http_response_success("Sukses", $result);
    }

    // -- grafik laba jual tahunan
    function dashboard_grafik_laba_jual_for_year($tahun = 0)
    {
        if ((int) $tahun == 0) $tahun = date("Y");
        if ((int) $tahun < 2000) $tahun = date("Y");

        $bulan_list = array();
        $laba_jual_list = array();
        $tahun_ini = array();
        $tahun_lalu = array();
        for ($i = 1; $i <= 12; $i++) {
            $bulan = str_pad($i, 2, "0", STR_PAD_LEFT);
            $bulan_name = date("M", strtotime(date("Y-$bulan-01")));
            $bulan_list[] = $bulan_name;
            $laba_jual_list[$i] = 0;

            $tahun_ini[$i - 1] = 0;
            $tahun_lalu[$i - 1] = 0;
        }

        $final_data = array(
            "tahun_ini" => $tahun_ini,
            "tahun_lalu" => $tahun_lalu,
            "bulan_list" => $bulan_list,
        );

        // **
        // ambil data dari table penjualan untuk tahun ini
        $start_date = "$tahun-01-01";
        $end_date = "$tahun-12-31";
        $filters = array();
        $filters["start_date"] = $start_date . " 00:00:00";
        $filters["end_date"] = $end_date . " 23:59:59";
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $list = $this->transaksi_engine->penjualan_get_list($filters);
        if (count($list) > 0) {
            $tahun_ini = $this->generate_laba_jual_by_penjualan_list($list, $tahun_ini);
            $final_data["tahun_ini"] = $tahun_ini;
        }

        // **
        // ambil data dari table penjualan untuk tahun lalu
        $tahun = (int) date("Y") - 1;
        $start_date = "$tahun-01-01";
        $end_date = "$tahun-12-31";
        $filters = array();
        $filters["start_date"] = $start_date . " 00:00:00";
        $filters["end_date"] = $end_date . " 23:59:59";
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $list = $this->transaksi_engine->penjualan_get_list($filters);
        if (count($list) > 0) {
            $tahun_lalu = $this->generate_laba_jual_by_penjualan_list($list, $tahun_lalu);
            $final_data["tahun_lalu"] = $tahun_lalu;
        }
        return $final_data;
    }

    function generate_laba_jual_by_penjualan_list($list = array(), $month_list = array())
    {
        $penjualan_uuid_list = array();
        foreach ($list as $l) {
            $penjualan_uuid_list[] = (int) $l["uuid"];
        }
        // **
        // ambil penjualan detail
        $filters = array();
        $filters["penjualan_uuid_list"] = $penjualan_uuid_list;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $list2 = $this->transaksi_engine->penjualan_detail_get_list($filters);

        $detail_penjualan_list = array();
        foreach ($list2 as $l2) {
            $penjualan_uuid = $l2["penjualan_uuid"];
            $detail_penjualan_list[$penjualan_uuid][] = $l2;
        }

        // **
        // generate final data
        $total_laba_jual = 0;
        foreach ($list as $l) {
            $penjualan_uuid = $l["uuid"];
            $number_formatted = $l["number_formatted"];
            $tanggal = date("d/m/Y", strtotime($l["tanggal"]));
            $bulan = (int) date("m", strtotime($l["tanggal"]));
            $pelanggan_number_formatted = $l['pelanggan_number_formatted'];
            $pelanggan_nama = $l["pelanggan_nama"];
            $sub_total = (float) $l["sub_total"];
            $potongan = (float) $l["potongan"];

            $total_pokok = 0;
            $laba_kotor = 0;
            $total = 0;

            $detail_list = isset($detail_penjualan_list[$penjualan_uuid]) ? $detail_penjualan_list[$penjualan_uuid] : array();
            foreach ($detail_list as $d) {
                $potongan_persen = (float) $d["potongan_persen"];
                $potongan_harga = (float) $d["potongan_harga"];
                $harga_jual_satuan = (float) $d["harga_jual_satuan"];
                $harga_jual_satuan_terkecil = (float) $d["harga_jual_satuan_terkecil"];
                $margin_jual_satuan_terkecil = (float) $d["margin_jual_satuan_terkecil"];
                $jumlah_satuan_terkecil = (int) $d["jumlah_satuan_terkecil"];
                $jumlah_satuan = (int) $d["jumlah"];

                $potongan_harga = ($harga_jual_satuan * ($potongan_persen / 100)) * $jumlah_satuan;

                // **
                // hitungan potongan harga satuan terkecil
                $potongan_harga_satuan_terkecil = 0;
                if ($potongan_harga > 0) {
                    $potongan_harga_satuan_terkecil = $potongan_harga / $jumlah_satuan_terkecil;
                }

                $harga_beli_satuan_terkecil = $harga_jual_satuan_terkecil - $margin_jual_satuan_terkecil;

                // **
                // hitung total pokok
                $harga_pokok = $harga_beli_satuan_terkecil * $jumlah_satuan_terkecil;
                $total_pokok += $harga_pokok;

                // **
                // hitung laba kotor
                $margin_jual = ($margin_jual_satuan_terkecil - $potongan_harga_satuan_terkecil) * $jumlah_satuan_terkecil;
                $laba_kotor += $margin_jual;
            }

            $laba_jual = $laba_kotor - $potongan;
            $month_list[$bulan] += $laba_jual;

            $total_laba_jual += $laba_jual;
        }

        return $month_list;
    }

    // -- grafik laba jual tahunan
    function dashboard_grafik_penjualan_for_year($tahun = 0)
    {
        if ((int) $tahun == 0) $tahun = date("Y");
        if ((int) $tahun < 2000) $tahun = date("Y");

        $bulan_list = array();
        $laba_jual_list = array();
        $tahun_ini = array();
        $tahun_lalu = array();
        for ($i = 1; $i <= 12; $i++) {
            $bulan = str_pad($i, 2, "0", STR_PAD_LEFT);
            $bulan_name = date("M", strtotime(date("Y-$bulan-01")));
            $bulan_list[] = $bulan_name;
            $laba_jual_list[$i] = 0;

            $tahun_ini[$i - 1] = 0;
            $tahun_lalu[$i - 1] = 0;
        }

        $final_data = array(
            "tahun_ini" => $tahun_ini,
            "tahun_lalu" => $tahun_lalu,
            "bulan_list" => $bulan_list,
        );

        // **
        // ambil data dari table penjualan untuk tahun ini
        $start_date = "$tahun-01-01";
        $end_date = "$tahun-12-31";
        $filters = array();
        $filters["start_date"] = $start_date . " 00:00:00";
        $filters["end_date"] = $end_date . " 23:59:59";
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $list = $this->transaksi_engine->penjualan_get_list($filters);
        if (count($list) > 0) {
            $tahun_ini = $this->generate_total_penjualan_by_penjualan_list($list, $tahun_ini);
            $final_data["tahun_ini"] = $tahun_ini;
        }

        // **
        // ambil data dari table penjualan untuk tahun lalu
        $tahun = (int) date("Y") - 1;
        $start_date = "$tahun-01-01";
        $end_date = "$tahun-12-31";
        $filters = array();
        $filters["start_date"] = $start_date . " 00:00:00";
        $filters["end_date"] = $end_date . " 23:59:59";
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $list = $this->transaksi_engine->penjualan_get_list($filters);
        if (count($list) > 0) {
            $tahun_lalu = $this->generate_total_penjualan_by_penjualan_list($list, $tahun_lalu);
            $final_data["tahun_lalu"] = $tahun_lalu;
        }
        return $final_data;
    }

    function generate_total_penjualan_by_penjualan_list($list = array(), $month_list = array())
    {
        $penjualan_uuid_list = array();
        foreach ($list as $l) {
            $penjualan_uuid_list[] = (int) $l["uuid"];
        }

        // **
        // generate final data
        $total_laba_jual = 0;
        foreach ($list as $l) {
            $bulan = (int) date("m", strtotime($l["tanggal"]));
            $total_akhir = (float) $l["total_akhir"];

            $month_list[$bulan] += $total_akhir;
        }

        return $month_list;
    }

    //-- penjualan total data (hari ini, bulan ini, tahun ini) 
    function penjualan_total_data()
    {
        // **
        // dapatkan total penjualan hari ini
        $filters = array();
        $filters["start_date"] = date("Y-m-d 00:00:00");
        $filters["end_date"] = date("Y-m-d 23:59:59");
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $total_hari_ini_list = $this->transaksi_engine->penjualan_get_list($filters, false, false, true);
        $total_hari_ini = 0;
        if (count($total_hari_ini_list) > 0) {
            $res = $total_hari_ini_list[0];
            $total_hari_ini = (float) $res["total_akhir"];
        }

        // ** 
        // dapatkan total penjualan kemarin
        $filters = array();
        $filters["start_date"] = date("Y-m-d 00:00:00", strtotime(date("Y-m-d") . " -1 day"));
        $filters["end_date"] = date("Y-m-d 23:59:59", strtotime(date("Y-m-d") . " -1 day"));
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $total_kemarin_list = $this->transaksi_engine->penjualan_get_list($filters, false, false, true);
        $total_kemarin = 0;
        if (count($total_kemarin_list) > 0) {
            $res = $total_kemarin_list[0];
            $total_kemarin = (float) $res["total_akhir"];
        }

        // **
        // dapatkan total penjualan bulan ini        
        $filters = array();
        $filters["start_date"] = date("Y-m-01 00:00:00");
        $filters["end_date"] = date("Y-m-t 23:59:59");
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $total_bulan_ini_list = $this->transaksi_engine->penjualan_get_list($filters, false, false, true);
        $total_bulan_ini = 0;
        if (count($total_bulan_ini_list) > 0) {
            $res = $total_bulan_ini_list[0];
            $total_bulan_ini = (float) $res["total_akhir"];
        }

        // **
        // dapatkan total penjualan bulan lalu        
        $filters = array();
        $filters["start_date"] = date("Y-m-01 00:00:00", strtotime(date("Y-m-d") . " -1 month"));
        $filters["end_date"] = date("Y-m-t 23:59:59", strtotime(date("Y-m-d") . " -1 month"));
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $total_bulan_lalu_list = $this->transaksi_engine->penjualan_get_list($filters, false, false, true);
        $total_bulan_lalu = 0;
        if (count($total_bulan_lalu_list) > 0) {
            $res = $total_bulan_lalu_list[0];
            $total_bulan_lalu = (float) $res["total_akhir"];
        }

        // **
        // dapatkan total penjualan tahun ini        
        $filters = array();
        $filters["start_date"] = date("Y-01-01 00:00:00");
        $filters["end_date"] = date("Y-12-31 23:59:59");
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $total_tahun_ini_list = $this->transaksi_engine->penjualan_get_list($filters, false, false, true);
        $total_tahun_ini = 0;
        if (count($total_tahun_ini_list) > 0) {
            $res = $total_tahun_ini_list[0];
            $total_tahun_ini = (float) $res["total_akhir"];
        }

        // **
        // dapatkan total penjualan tahun lalu        
        $filters = array();
        $filters["start_date"] = date("Y-01-01 00:00:00", strtotime(date("Y-m-d") . " -1 year"));
        $filters["end_date"] = date("Y-12-31 23:59:59", strtotime(date("Y-m-d") . " -1 year"));
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $total_tahun_lalu_list = $this->transaksi_engine->penjualan_get_list($filters, false, false, true);
        $total_tahun_lalu = 0;
        if (count($total_tahun_lalu_list) > 0) {
            $res = $total_tahun_lalu_list[0];
            $total_tahun_lalu = (float) $res["total_akhir"];
        }

        // **
        // dapatkan laba jual tahun ini
        $filters = array();
        $filters["start_date"] = date("Y-01-01 00:00:00");
        $filters["end_date"] = date("Y-12-31 23:59:59");
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $list = $this->transaksi_engine->penjualan_get_list($filters, false, false);
        $laba_jual_tahun_ini = 0;
        if (count($list) > 0) {
            $month_list = array();
            for ($i = 1; $i <= 12; $i++) {
                $month_list[$i] = 0;
            }
            $tahun_ini_list = $this->generate_laba_jual_by_penjualan_list($list, $month_list);
            $laba_jual_tahun_ini = array_sum($tahun_ini_list);
        }

        // **
        // dapatkan laba jual tahun lalu
        $filters = array();
        $filters["start_date"] = date("Y-01-01 00:00:00", strtotime(date("Y-m-d") . " -1 year"));
        $filters["end_date"] = date("Y-12-31 23:59:59", strtotime(date("Y-m-d") . " -1 year"));
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $list = $this->transaksi_engine->penjualan_get_list($filters, false, false);
        $laba_jual_tahun_lalu = 0;
        if (count($list) > 0) {
            $month_list = array();
            for ($i = 1; $i <= 12; $i++) {
                $month_list[$i] = 0;
            }
            $tahun_lalu_list = $this->generate_laba_jual_by_penjualan_list($list, $month_list);
            $laba_jual_tahun_lalu = array_sum($tahun_lalu_list);
        }


        $selisih_kemarin = $total_hari_ini - $total_kemarin;
        $selisih_kemarin_persen = ($total_hari_ini > 0)
            ? (($selisih_kemarin / $total_hari_ini) * 100)
            : 0;
        $selisih_kemarin_stats = '';
        if ($selisih_kemarin > 0) $selisih_kemarin_stats = 'up';
        if ($selisih_kemarin < 0) $selisih_kemarin_stats = 'down';

        $selisih_bulan_lalu = $total_bulan_ini - $total_bulan_lalu;
        $selisih_bulan_lalu_persen = ($total_bulan_ini > 0)
            ? (($selisih_bulan_lalu / $total_bulan_ini) * 100)
            : 0;
        $selisih_bulan_lalu_stats = '';
        if ($selisih_bulan_lalu > 0) $selisih_bulan_lalu_stats = 'up';
        if ($selisih_bulan_lalu < 0) {
            $selisih_bulan_lalu_stats = 'down';
            $selisih_bulan_lalu_persen = $selisih_bulan_lalu_persen * -1;
        }

        $selisih_tahun_lalu = $total_tahun_ini - $total_tahun_lalu;
        $selisih_tahun_lalu_persen = ($total_tahun_ini > 0)
            ? (($selisih_tahun_lalu / $total_tahun_ini) * 100)
            : 0;
        $selisih_tahun_lalu_stats = '';
        if ($selisih_tahun_lalu > 0) $selisih_tahun_lalu_stats = 'up';
        if ($selisih_tahun_lalu < 0) {
            $selisih_tahun_lalu_stats = 'down';
            $selisih_tahun_lalu_persen = $selisih_tahun_lalu_persen * -1;
        }

        $selisih_laba_jual_tahun_lalu = $laba_jual_tahun_ini - $laba_jual_tahun_lalu;
        $selisih_laba_jual_tahun_lalu_persen = ($laba_jual_tahun_ini > 0)
            ? (($selisih_laba_jual_tahun_lalu / $laba_jual_tahun_ini) * 100)
            : 0;
        $selisih_laba_jual_tahun_lalu_stats = '';
        if ($selisih_laba_jual_tahun_lalu > 0) $selisih_laba_jual_tahun_lalu_stats = 'up';
        if ($selisih_laba_jual_tahun_lalu < 0) {
            $selisih_laba_jual_tahun_lalu_stats = 'down';
            $selisih_laba_jual_tahun_lalu_persen = $selisih_laba_jual_tahun_lalu_persen * -1;
        }

        $result = array(
            "total_hari_ini" => number_format($total_hari_ini, 0, ",", "."),
            "total_bulan_ini" => number_format($total_bulan_ini, 0, ",", "."),
            "total_tahun_ini" => number_format($total_tahun_ini, 0, ",", "."),
            "laba_jual_tahun_ini" => number_format($laba_jual_tahun_ini, 0, ",", "."),

            "total_kemarin" => $total_kemarin,
            "total_bulan_lalu" => number_format($total_bulan_lalu, 0, ",", "."),
            "total_tahun_lalu" => $total_tahun_lalu,
            "laba_jual_tahun_lalu" => $laba_jual_tahun_lalu,

            "selisih_kemarin" => number_format($selisih_kemarin_persen, 0, ",", ".") . "%",
            "selisih_bulan_lalu" => number_format($selisih_bulan_lalu_persen, 0, ",", ".") . "%",
            "selisih_tahun_lalu" => number_format($selisih_tahun_lalu_persen, 0, ",", ".") . "%",
            "selisih_laba_jual_tahun_lalu" => number_format($selisih_laba_jual_tahun_lalu_persen, 0, ",", ".") . "%",

            "selisih_kemarin_stats" => $selisih_kemarin_stats,
            "selisih_bulan_lalu_stats" => $selisih_bulan_lalu_stats,
            "selisih_tahun_lalu_stats" => $selisih_tahun_lalu_stats,
            "selisih_laba_jual_tahun_lalu_stats" => $selisih_laba_jual_tahun_lalu_stats,
        );

        return $result;
    }


    // **
    // laporan terkait penjualan
    function laporan_laba_jual_rekap()
    {

        $start_date = get("start_date");
        $end_date = get("end_date");
        $pelanggan_uuid = get("pelanggan_uuid");

        if (empty($start_date)) $start_date = date("Y-m-01");
        if (empty($end_date)) $end_date = date("Y-m-t");

        $filter_pelanggan_uuid = "";
        $filter_pelanggan_nama = "Semua Pelanggan";
        // **
        // check pelanggan uuid
        if (!empty($pelanggan_uuid)) {
            $filters = array();
            $filters["uuid"] = $pelanggan_uuid;
            $filters["cabang_uuid"] = $this->cabang_selected_uuid;
            $res = $this->pelanggan_engine->pelanggan_get_list($filters);
            if (count($res) > 0) {
                $res = $res[0];
                $filter_pelanggan_uuid = $res["uuid"];
                $filter_pelanggan_nama = $res["nama"];
            }
        }

        $list = $this->settings_engine->get_all_settings();
        $settings_list = array();
        foreach ($list as $l) {
            $_key = $l['_key'];
            $settings_list[$_key] = $l;
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
                "pelanggan" => $filter_pelanggan_nama,
            ),


            "body" => array(),

            "footer" => array(
                "sub_total" => 0,
                "total_pokok" => 0,
                "total_laba_kotor" => 0,
                "total_potongan" => 0,
                "total_laba_jual" => 0
            ),
        );

        // **
        // ambil data dari table penjualan
        $filters = array();
        $filters["start_date"] = $start_date . " 00:00:00";
        $filters["end_date"] = $end_date . " 23:59:59";
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        if (!empty($filter_pelanggan_uuid)) $filters["pelanggan_uuid"] = $filter_pelanggan_uuid;
        if (!empty($filter_gudang_uuid)) $filters["gudang_uuid"] = $filter_gudang_uuid;
        $list = $this->transaksi_engine->penjualan_get_list($filters);
        if (count($list) == 0) return $final_data;

        $penjualan_uuid_list = array();
        foreach ($list as $l) {
            $penjualan_uuid_list[] = $l["uuid"];
        }

        // **
        // ambil penjualan detail
        $filters = array();
        $filters["penjualan_uuid_list"] = $penjualan_uuid_list;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $list2 = $this->transaksi_engine->penjualan_detail_get_list($filters);

        $detail_penjualan_list = array();
        foreach ($list2 as $l2) {
            $penjualan_uuid = $l2["penjualan_uuid"];
            $detail_penjualan_list[$penjualan_uuid][] = $l2;
        }

        // **
        // generate final data
        $final_data = array();
        $final_list = array();

        $grand_sub_total = 0;
        $grand_total_pokok = 0;
        $total_laba_kotor = 0;
        $total_laba_jual = 0;
        $total_potongan = 0;
        $no = 1;
        foreach ($list as $l) {
            $penjualan_uuid = $l["uuid"];
            $number_formatted = $l["number_formatted"];
            $tanggal = date("d/m/Y", strtotime($l["tanggal"]));
            $pelanggan_number_formatted = $l['pelanggan_number_formatted'];
            $pelanggan_nama = $l["pelanggan_nama"];
            $sub_total = (float) $l["sub_total"];
            $potongan = (float) $l["potongan"];

            $total_pokok = 0;
            $laba_kotor = 0;
            $total = 0;

            $detail_list = $detail_penjualan_list[$penjualan_uuid];
            foreach ($detail_list as $d) {
                $potongan_persen = (float) $d["potongan_persen"];
                $potongan_harga = (float) $d["potongan_harga"];
                $harga_jual_satuan = (float) $d["harga_jual_satuan"];
                $harga_jual_satuan_terkecil = (float) $d["harga_jual_satuan_terkecil"];
                $margin_jual_satuan_terkecil = (float) $d["margin_jual_satuan_terkecil"];
                $jumlah_satuan_terkecil = (int) $d["jumlah_satuan_terkecil"];
                $jumlah_satuan = (int) $d["jumlah"];

                $potongan_harga = ($harga_jual_satuan * ($potongan_persen / 100)) * $jumlah_satuan;

                // **
                // hitungan potongan harga satuan terkecil
                $potongan_harga_satuan_terkecil = 0;
                if ($potongan_harga > 0) {
                    $potongan_harga_satuan_terkecil = $potongan_harga / $jumlah_satuan_terkecil;
                }

                $harga_beli_satuan_terkecil = $harga_jual_satuan_terkecil - $margin_jual_satuan_terkecil;

                // **
                // hitung total pokok
                $harga_pokok = $harga_beli_satuan_terkecil * $jumlah_satuan_terkecil;
                $total_pokok += $harga_pokok;

                // **
                // hitung laba kotor
                $margin_jual = ($margin_jual_satuan_terkecil - $potongan_harga_satuan_terkecil) * $jumlah_satuan_terkecil;
                $laba_kotor += $margin_jual;
            }

            $laba_jual = $laba_kotor - $potongan;

            $total_laba_jual += $laba_jual;
            $total_laba_kotor += $laba_kotor;
            $total_potongan += $potongan;
            $grand_sub_total += $sub_total;
            $grand_total_pokok += $total_pokok;

            $data = array(
                "no" => $no,
                "number_formatted" => $number_formatted,
                "tanggal" => $tanggal,
                "pelanggan_number_formatted" => $pelanggan_number_formatted,
                "pelanggan_nama" => $pelanggan_nama,
                "total" => number_format($total),
                "sub_total" => number_format($sub_total),
                "total_pokok" => number_format($total_pokok),
                "laba_kotor" => number_format($laba_kotor),
                "potongan" => number_format($potongan),
                "laba_jual" => number_format($laba_jual)
            );
            $final_list[] = $data;

            $no++;
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
                "pelanggan" => $filter_pelanggan_nama,
            ),


            "body" => $final_list,

            "footer" => array(
                "sub_total" => number_format($grand_sub_total),
                "total_pokok" => number_format($grand_total_pokok),
                "total_laba_kotor" => number_format($total_laba_kotor),
                "total_potongan" => number_format($total_potongan),
                "total_laba_jual" => number_format($total_laba_jual)
            ),
        );

        return $final_data;
    }

    function laporan_penjualan_harian()
    {
        $start_date = $this->input->get("start_date");
        $end_date = $this->input->get("end_date");
        $gudang_uuid = $this->input->get("gudang_uuid");

        if (empty($start_date)) $start_date = date("Y-m-d");
        if (empty($end_date)) $end_date = date("Y-m-d");

        $list = $this->settings_engine->get_all_settings();
        $settings_list = array();
        foreach ($list as $l) {
            $_key = $l['_key'];
            $settings_list[$_key] = $l;
        }

        $filter_gudang_uuid = "";
        $filter_gudang_nama = "Semua gudang";
        // **
        // check gudang uuid
        if (!empty($gudang_uuid)) {
            $filters = array();
            $filters["uuid"] = $gudang_uuid;
            $filters["cabang_uuid"] = $this->cabang_selected_uuid;
            $res = $this->gudang_engine->gudang_get_list($filters);
            if (count($res) > 0) {
                $res = $res[0];
                $filter_gudang_uuid = $res["uuid"];
                $filter_gudang_nama = $res["kode"] . " - " . $res["nama"];
            }
        }

        $filters = array();
        $filters["start_date"] = $start_date . " 00:00:00";
        $filters["end_date"] = $end_date . " 23:59:59";
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        if (!empty($filter_gudang_uuid)) $filters["gudang_uuid"] = $filter_gudang_uuid;
        $list = $this->transaksi_engine->penjualan_get_list($filters);

        $temp_list = array();
        $total_jumlah_transaksi = 0;
        $grand_total_transaksi = 0;
        $total_jumlah_bayar_tunai = 0;
        $total_jumlah_bayar_non_tunai = 0;
        foreach ($list as $l) {
            $tanggal = date("Y-m-d", strtotime($l["tanggal"]));
            $total = (float) $l["total_akhir"];

            $total_tunai = 0;
            $total_non_tunai = 0;
            if (strtolower($l["metode_pembayaran"]) == "tunai") {
                $total_tunai = $total;
            } else {
                $total_non_tunai = $total;
            }


            if (!isset($temp_list[$tanggal])) {
                $temp_list[$tanggal] = array(
                    "tanggal" => date("d-m-Y", strtotime($tanggal)),
                    "jumlah_transaksi" => 0,
                    "total_transaksi" => 0,
                    "jumlah_bayar_tunai" => 0,
                    "jumlah_bayar_non_tunai" => 0
                );
            }

            $temp_list[$tanggal]["jumlah_transaksi"]++;
            $temp_list[$tanggal]["total_transaksi"] += $total;
            $temp_list[$tanggal]["jumlah_bayar_tunai"] += $total_tunai;
            $temp_list[$tanggal]["jumlah_bayar_non_tunai"] += $total_non_tunai;

            $total_jumlah_transaksi++;
            $grand_total_transaksi += $total;
            $total_jumlah_bayar_tunai += $total_tunai;
            $total_jumlah_bayar_non_tunai += $total_non_tunai;
        }


        $final_list = array();
        $i = 0;
        foreach ($temp_list as $tanggal => $l) {
            $i++;

            $row = array(
                "tanggal" => trim($l["tanggal"]),
                "jumlah_transaksi" => number_format($l["jumlah_transaksi"]),
                "total_transaksi" => number_format($l["total_transaksi"]),
                "jumlah_bayar_tunai" => number_format($l["jumlah_bayar_tunai"]),
                "jumlah_bayar_non_tunai" => number_format($l["jumlah_bayar_non_tunai"]),
            );

            $final_list[] = $row;
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
                "gudang" => $filter_gudang_nama,
            ),


            "body" => $final_list,

            "footer" => array(
                "total_jumlah_transaksi" => number_format($total_jumlah_transaksi),
                "grand_total_transaksi" => number_format($grand_total_transaksi),
                "total_jumlah_bayar_tunai" => number_format($total_jumlah_bayar_tunai),
                "total_jumlah_bayar_non_tunai" => number_format($total_jumlah_bayar_non_tunai),
            ),
        );

        return $final_data;
    }

    function laporan_penjualan_rekap()
    {
        $start_date = $this->input->get("start_date");
        $end_date = $this->input->get("end_date");
        $pelanggan_uuid = $this->input->get("pelanggan_uuid");
        $gudang_uuid = $this->input->get("gudang_uuid");

        if (empty($start_date)) $start_date = date("Y-m-d");
        if (empty($end_date)) $end_date = date("Y-m-d");

        $list = $this->settings_engine->get_all_settings();
        $settings_list = array();
        foreach ($list as $l) {
            $_key = $l['_key'];
            $settings_list[$_key] = $l;
        }

        $filter_pelanggan_uuid = "";
        $filter_pelanggan_nama = "Semua Pelanggan";
        // **
        // check pelanggan uuid
        if (!empty($pelanggan_uuid)) {
            $filters = array();
            $filters["uuid"] = $pelanggan_uuid;
            $filters["cabang_uuid"] = $this->cabang_selected_uuid;
            $res = $this->pelanggan_engine->pelanggan_get_list($filters);
            if (count($res) > 0) {
                $res = $res[0];
                $filter_pelanggan_uuid = $res["uuid"];
                $filter_pelanggan_nama = $res["nama"];
            }
        }

        $filter_gudang_uuid = "";
        $filter_gudang_nama = "Semua gudang";
        // **
        // check gudang uuid
        if (!empty($gudang_uuid)) {
            $filters = array();
            $filters["uuid"] = $gudang_uuid;
            $filters["cabang_uuid"] = $this->cabang_selected_uuid;
            $res = $this->gudang_engine->gudang_get_list($filters);
            if (count($res) > 0) {
                $res = $res[0];
                $filter_gudang_uuid = $res["uuid"];
                $filter_gudang_nama = $res["kode"] . " - " . $res["nama"];
            }
        }

        $filters = array();
        $filters["start_date"] = $start_date . " 00:00:00";
        $filters["end_date"] = $end_date . " 23:59:59";
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        if (!empty($filter_pelanggan_uuid)) $filters["pelanggan_uuid"] = $filter_pelanggan_uuid;
        if (!empty($filter_gudang_uuid)) $filters["gudang_uuid"] = $filter_gudang_uuid;
        $list = $this->transaksi_engine->penjualan_get_list($filters);

        $final_list = array();
        $grand_sub_total = 0;
        $total_potongan = 0;
        $grand_total_akhir = 0;
        $total_bayar_tunai = 0;
        $total_bayar_non_tunai = 0;
        $no = 0;
        foreach ($list as $l) {
            $no++;

            $no_transaksi = $l["number_formatted"];
            $tanggal = date("Y-m-d", strtotime($l["tanggal"]));
            $pelanggan_nama = $l["pelanggan_nama"];

            $sub_total = (float) $l["sub_total"];
            $potongan = (float) $l["potongan"];
            $total_akhir = (float) $l["total_akhir"];
            $bayar_tunai = (float) $l["bayar"];
            $bayar_non_tunai = (float) $l["sisa"];

            if ($bayar_tunai > $total_akhir) $bayar_tunai = $total_akhir;

            $row = array(
                "no" => $no,
                "no_transaksi" => $no_transaksi,
                "tanggal" => date("d-m-Y", strtotime($tanggal)),
                "pelanggan_nama" => $pelanggan_nama,

                "sub_total" => number_format($sub_total),
                "potongan" => number_format($potongan),
                "total_akhir" => number_format($total_akhir),
                "bayar_tunai" => number_format($bayar_tunai),
                "bayar_non_tunai" => number_format($bayar_non_tunai),
            );
            $final_list[] = $row;

            $grand_sub_total += $sub_total;
            $total_potongan += $potongan;
            $grand_total_akhir += $total_akhir;
            $total_bayar_tunai += $bayar_tunai;
            $total_bayar_non_tunai += $bayar_non_tunai;
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
                "pelanggan" => $filter_pelanggan_nama,
                "gudang" => $filter_gudang_nama,
            ),


            "body" => $final_list,

            "footer" => array(
                "grand_sub_total" => number_format($grand_sub_total),
                "total_potongan" => number_format($total_potongan),
                "grand_total_akhir" => number_format($grand_total_akhir),
                "total_bayar_tunai" => number_format($total_bayar_tunai),
                "total_bayar_non_tunai" => number_format($total_bayar_non_tunai),
            ),
        );

        return $final_data;
    }

    function laporan_penjualan_detail()
    {
        $start_date = $this->input->get("start_date");
        $end_date = $this->input->get("end_date");
        $pelanggan_uuid = get("pelanggan_uuid");
        $gudang_uuid = get("gudang_uuid");

        if (empty($start_date)) $start_date = date("Y-m-d");
        if (empty($end_date)) $end_date = date("Y-m-d");

        $list = $this->settings_engine->get_all_settings();
        $settings_list = array();
        foreach ($list as $l) {
            $_key = $l['_key'];
            $settings_list[$_key] = $l;
        }

        $filter_pelanggan_uuid = "";
        $filter_pelanggan_nama = "Semua Pelanggan";
        // **
        // check pelanggan uuid
        if (!empty($pelanggan_uuid)) {
            $filters = array();
            $filters["uuid"] = $pelanggan_uuid;
            $filters["cabang_uuid"] = $this->cabang_selected_uuid;
            $res = $this->pelanggan_engine->pelanggan_get_list($filters);
            if (count($res) > 0) {
                $res = $res[0];
                $filter_pelanggan_uuid = $res["uuid"];
                $filter_pelanggan_nama = $res["nama"];
            }
        }

        $filter_gudang_uuid = "";
        $filter_gudang_nama = "Semua gudang";
        // **
        // check gudang uuid
        if (!empty($gudang_uuid)) {
            $filters = array();
            $filters["uuid"] = $gudang_uuid;
            $filters["cabang_uuid"] = $this->cabang_selected_uuid;
            $res = $this->gudang_engine->gudang_get_list($filters);
            if (count($res) > 0) {
                $res = $res[0];
                $filter_gudang_uuid = $res["uuid"];
                $filter_gudang_nama = $res["kode"] . " - " . $res["nama"];
            }
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
                "pelanggan" => $filter_pelanggan_nama,
                "gudang" => $filter_gudang_nama,
            ),


            "body" => array(),

            "footer" => array(
                "grand_sub_total" => 0,
                "total_potongan" => 0,
                "grand_total" => 0,
            ),
        );

        $filters = array();
        $filters["start_date"] = $start_date . " 00:00:00";
        $filters["end_date"] = $end_date . " 23:59:59";
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        if (!empty($filter_pelanggan_uuid)) $filters["pelanggan_uuid"] = $filter_pelanggan_uuid;
        if (!empty($filter_gudang_uuid)) $filters["gudang_uuid"] = $filter_gudang_uuid;
        $penjualan_list = $this->transaksi_engine->penjualan_get_list($filters);

        if (count($penjualan_list) == 0) return $final_data;

        // **
        // generate penjualan id list
        $grand_total = 0;
        $grand_sub_total = 0;
        $total_potongan = 0;
        $penjualan_data_list = array();
        $penjualan_uuid_list = array();
        foreach ($penjualan_list as $l) {
            $penjualan_uuid = $l["uuid"];
            $no_penjualan = trim($l["number_formatted"]);
            $tanggal = date("d-m-Y", strtotime($l["tanggal"]));
            $pelanggan_number_formatted = trim($l["pelanggan_number_formatted"]);
            $pelanggan_nama = trim($l["pelanggan_nama"]);
            $pelanggan_alamat = trim($l["pelanggan_alamat"]);

            $sub_total = (float) $l["sub_total"];
            $potongan = (float) $l["potongan"];
            $total_akhir = (float) $l["total_akhir"];

            $penjualan_uuid_list[] = $penjualan_uuid;
            $penjualan_data_list[$penjualan_uuid] = array(
                "no_penjualan" => $no_penjualan,
                "tanggal" => $tanggal,
                "pelanggan_number_formatted" => $pelanggan_number_formatted,
                "pelanggan_nama" => $pelanggan_nama,
                "pelanggan_alamat" => $pelanggan_alamat,
                "sub_total" => number_format($sub_total),
                "potongan" => number_format($potongan),
                "total_akhir" => number_format($total_akhir),
                "details" => array(),
                "footer" => array(
                    "total_jumlah" => 0,
                    "total_sub_total" => 0,
                ),
            );

            $grand_sub_total += $sub_total;
            $total_potongan += $potongan;
            $grand_total += $total_akhir;
        }

        // **
        // penjualan detail untuk penjualan id list
        $filters = array();
        $filters["penjualan_uuid_list"] = $penjualan_uuid_list;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $detail_list = $this->transaksi_engine->penjualan_detail_get_list($filters);
        foreach ($detail_list as $d) {
            $penjualan_uuid = $d["penjualan_uuid"];

            $item_kode = $d["item_kode"];
            $item_nama = $d["item_nama"];
            $jumlah = (int) $d["jumlah"];
            $satuan = trim($d["satuan"]);
            $harga_jual_satuan = (float) $d["harga_jual_satuan"];
            $potongan_persen = (float) $d["potongan_persen"];
            $potongan_harga = (float) $d["potongan_harga"];

            $total = ($jumlah * $harga_jual_satuan) - $potongan_harga;


            if (isset($penjualan_data_list[$penjualan_uuid])) {
                $penjualan_data_list[$penjualan_uuid]["details"][] = array(
                    "item_kode" => $item_kode,
                    "item_nama" => $item_nama,
                    "jumlah" => number_format($jumlah),
                    "satuan" => $satuan,
                    "harga_jual_satuan" => number_format($harga_jual_satuan),
                    "potongan_persen" => number_format($potongan_persen, 2),
                    "total" => number_format($total)
                );

                $penjualan_data_list[$penjualan_uuid]["footer"]["total_jumlah"] += $jumlah;
                $penjualan_data_list[$penjualan_uuid]["footer"]["total_sub_total"] += $total;
            }
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
                "pelanggan" => $filter_pelanggan_nama,
                "gudang" => $filter_gudang_nama,
            ),


            "body" => $penjualan_data_list,

            "footer" => array(
                "grand_sub_total" => number_format($grand_sub_total),
                "total_potongan" => number_format($total_potongan),
                "grand_total" => number_format($grand_total),
            ),
        );

        return $final_data;
    }
}
