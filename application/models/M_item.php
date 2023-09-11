<?php

defined('BASEPATH') or exit('No direct script access allowed');

class M_item extends MY_Model
{

    private $item_engine;
    private $gudang_engine;
    private $transaksi_engine;

    private $cabang_selected_uuid;
    private $actor_user_uuid;
    private $actor_user_name;

    private $allow;
    private $allow_create;
    private $allow_update;
    private $allow_set_arsip;
    private $allow_set_aktif;
    private $allow_ubah_struktur_satuan_harga;
    function __construct()
    {
        parent::__construct();

        $this->item_engine = new Item_engine();
        $this->gudang_engine = new Gudang_engine();
        $this->transaksi_engine = new Transaksi_engine();

        $cabang_selected = get_session("cabang_selected");
        $this->cabang_selected_uuid = isset($cabang_selected["uuid"]) ? $cabang_selected["uuid"] : "";
        $this->cabang_selected_kode = isset($cabang_selected["kode"]) ? $cabang_selected["kode"] : "";

        $user = get_session("user");
        $role = strtolower(get_session("role"));
        $this->actor_user_uuid = isset($user["uuid"]) ? $user["uuid"] : "";
        $this->actor_user_name = isset($user["name"]) ? $user["name"] : "";

        $privilege_list = isset($user["privilege_list"]) ? $user["privilege_list"] : array();

        $this->allow                            = isset($privilege_list["allow_item"]) ? $privilege_list["allow_item"] : 0;
        $this->allow_create                     = isset($privilege_list["allow_item_create"]) ? $privilege_list["allow_item_create"] : 0;
        $this->allow_update                     = isset($privilege_list["allow_item_update"]) ? $privilege_list["allow_item_update"] : 0;
        $this->allow_set_arsip                  = isset($privilege_list["allow_item_set_arsip"]) ? $privilege_list["allow_item_set_arsip"] : 0;
        $this->allow_set_aktif                  = isset($privilege_list["allow_item_set_aktif"]) ? $privilege_list["allow_item_set_aktif"] : 0;
        $this->allow_ubah_struktur_satuan_harga = isset($privilege_list["allow_item_ubah_struktur_satuan_harga"]) ? $privilege_list["allow_item_ubah_struktur_satuan_harga"] : 0;

        if ($this->uri->segment(1) == 'kasir' || $this->uri->segment(1) == "api") {
            $this->allow = 1;
        }
    }

    function item_get_list($filters = array(), $arsip = "-1", $tipe = "")
    {
        if (!$this->allow) return array();

        if ($arsip != "-1") {
            $filters["arsip"] = $arsip;
        }

        if (!empty($tipe)) {
            $filters["tipe"] = $tipe;
        }
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->item_engine->item_get_list($filters, true, true);
        $no = isset($filters["start"]) ? (int) $filters["start"] : 0;

        $final_res = array();
        foreach ($res as $r) {
            $no++;

            $row = array();
            $row["no"] = $no;
            $row["uuid"] = trim($r["uuid"]);
            $row["kode"] = $r["kode"];
            $row["barcode"] = $r["barcode"];
            $row["nama"] = $r["nama"];
            $row["kategori"] = $r["item_kategori_nama"];
            $row["keterangan"] = $r["keterangan"];
            $row["tipe"] = $r["tipe"];
            $row["last_updated"] = $r["last_updated"];
            $row["last_updated_user_name"] = $r["last_updated_user_name"];
            $row["arsip"] = (int) $r["arsip"];
            $row["arsip_date"] = $r["arsip_date"];
            $row["arsip_user_name"] = $r["arsip_user_name"];

            $final_res[] = $row;
        }

        return $final_res;
    }

    function item_get_list_with_stock($filters = array(), $arsip = "-1", $tipe = "", $gudang_uuid = "", $item_kategori_uuid = "")
    {
        if (!$this->allow) return array();

        if ($arsip != "-1") {
            $filters["arsip"] = $arsip;
        }

        if (!empty($tipe)) {
            $filters["tipe"] = $tipe;
        }

        if (!empty($item_kategori_uuid)) $filters["item_kategori_uuid"] = $item_kategori_uuid;

        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->item_engine->item_get_list($filters, true, true);
        $no = isset($filters["start"]) ? (int) $filters["start"] : 0;

        $final_res = array();
        foreach ($res as $r) {
            $no++;

            $row = array();
            $row["uuid"] = $r["uuid"];
            $row["kode"] = $r["kode"];
            $row["barcode"] = $r["barcode"];
            $row["nama"] = $r["nama"];
            $row["item_kategori_nama"] = $r["item_kategori_nama"];
            $row["stock"] = 0;
            $row["satuan"] = $r["satuan_tipe_jasa"];
            $row["tipe"] = $r["tipe"];
            $row["harga_pokok"] = 0;
            $row["harga_jual"] = 0;
            $row["arsip"] = $r["arsip"];

            $struktur_satuan_harga_json = trim($r["struktur_satuan_harga_json"]);
            $struktur_satuan_harga_list = json_decode($struktur_satuan_harga_json, true);
            if (!is_array($struktur_satuan_harga_list) || count($struktur_satuan_harga_list) == 0) {
                $final_res[] = $row;
                continue;
            }
            if (strtolower($r["tipe"]) == "jasa") {
                $row["harga_jual"] = number_format($r["harga_jual_tipe_jasa"], 0, ",", ".");
                $final_res[] = $row;
                continue;
            }

            $stock = $this->item_engine->item_get_total_stock_for_item_uuid($r['uuid'], $gudang_uuid);
            $prev_satuan = "";
            foreach ($struktur_satuan_harga_list as $key => $l2) {
                $satuan = $l2["satuan"];
                $konversi = (int) $l2["konversi"];
                $stock = $stock / $konversi;
                if($stock > 0) $stock = round($stock, 2, PHP_ROUND_HALF_DOWN);

                $stock_str = $stock;
                $exp = explode(".", $stock_str);
                $stock = (int) $exp[0];

                $harga_jual = $l2["harga_jual"];
                $harga_pokok = $l2["harga_pokok"];

                $row2 = $row;
                $row["satuan"] = "$satuan";
                $row["stock"] = number_format($stock, 0, ",", ".");
                $row["harga_pokok"] = number_format($harga_pokok, 0, ',', '.');
                $row["harga_jual"] = number_format($harga_jual, 0, ',', '.');

                $final_res[] = $row;
                $prev_satuan = $satuan;
            }

            // $final_res[] = $row;
        }

        return $final_res;
    }

    function item_get_filtered_total($filters = array(), $arsip = -1, $tipe = "Semua")
    {
        if (!$this->allow) return 0;

        if ($arsip >= 0) {
            $filters["arsip"] = $arsip;
        }

        if (strtolower($tipe) != "semua") {
            $filters["tipe"] = $tipe;
        }

        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->item_engine->item_get_list($filters, false, true);
        return count($res);
    }

    function item_get_total($filters = array(), $arsip = -1, $tipe = "Semua")
    {
        if (!$this->allow) return 0;

        if ($arsip >= 0) {
            $filters["arsip"] = $arsip;
        }

        if (strtolower($tipe) != "semua") {
            $filters["tipe"] = $tipe;
        }

        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->item_engine->item_get_list($filters);
        return count($res);
    }

    function item_get_list_for_stock_opname($filters = array())
    {
        $filters["arsip"] = 0;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->item_engine->item_get_list($filters, true, true);
        $no = isset($filters["start"]) ? (int) $filters["start"] : 0;

        $final_res = array();
        foreach ($res as $r) {
            $no++;

            $row = array();
            $row["no"] = $no;
            $row["uuid"] = trim($r["uuid"]);
            $row["kode"] = $r["kode"];
            $row["barcode"] = $r["barcode"];
            $row["nama"] = $r["nama"];
            $row["kategori"] = $r["item_kategori_nama"];
            $row["keterangan"] = $r["keterangan"];
            $row["tipe"] = $r["tipe"];
            $row["last_updated"] = $r["last_updated"];
            $row["last_updated_user_name"] = $r["last_updated_user_name"];
            $row["arsip"] = (int) $r["arsip"];
            $row["arsip_date"] = $r["arsip_date"];
            $row["arsip_user_name"] = $r["arsip_user_name"];

            $final_res[] = $row;
        }

        return $final_res;
    }

    function item_get_filtered_total_for_stock_opname($filters = array())
    {
        $filters["arsip"] = 0;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->item_engine->item_get_list($filters, false, true);
        return count($res);
    }

    function item_get_total_for_stock_opname($filters = array())
    {
        $filters["arsip"] = 0;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->item_engine->item_get_list($filters);
        return count($res);
    }

    function item_get_list_for_pembelian_uuid($filters = array(), $pembelian_uuid = "")
    {
        $filters = array();
        $filters["pembelian_uuid"] = $pembelian_uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->transaksi_engine->pembelian_detail_get_list($filters, true, true);
        $no = isset($filters["start"]) ? (int) $filters["start"] : 0;

        $final_res = array();
        foreach ($res as $r) {
            $no++;

            $jumlah = (int) $r["jumlah"];
            $harga_beli_satuan = (float) $r["harga_beli_satuan"];
            $potongan_persen = (float) $r["potongan_persen"];
            $potongan_harga = (float) $r["potongan_harga"];

            $sub_total = $jumlah * ($harga_beli_satuan - $potongan_harga);

            $row = array();
            $row["no"] = $no;
            $row["uuid"] = trim($r["uuid"]);
            $row["kode"] = $r["item_kode"];
            $row["barcode"] = $r["item_barcode"];
            $row["nama"] = $r["item_nama"];
            $row["kategori"] = $r["item_kategori_nama"];
            $row["jumlah"] = number_format($r["jumlah"]);
            $row["satuan"] = $r["satuan"];
            $row["harga_beli_satuan"] = number_format($harga_beli_satuan, 0, ",", ".");
            $row["potongan"] = number_format($potongan_persen, 2, ",", ".");
            $row["total"] = number_format($sub_total, 0, ",", ".");

            $final_res[] = $row;
        }

        return $final_res;
    }

    function item_get_filtered_total_for_pembelian_uuid($filters = array(), $pembelian_uuid = "")
    {
        $filters = array();
        $filters["pembelian_uuid"] = $pembelian_uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->item_engine->item_get_list($filters, false, true);
        return count($res);
    }

    function item_get_total_for_pembelian_uuid($filters = array(), $pembelian_uuid = "")
    {
        $filters = array();
        $filters["pembelian_uuid"] = $pembelian_uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->item_engine->item_get_list($filters);
        return count($res);
    }

    function item_get_list_for_penjualan_uuid($filters = array(), $penjualan_uuid = "")
    {

        $filters = array();
        $filters["penjualan_uuid"] = $penjualan_uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->transaksi_engine->penjualan_detail_get_list($filters, true, true);
        $no = isset($filters["start"]) ? (int) $filters["start"] : 0;

        $final_res = array();
        foreach ($res as $r) {
            $no++;

            $jumlah = (int) $r["jumlah"];
            $harga_jual_satuan = (float) $r["harga_jual_satuan"];
            $potongan_persen = (float) $r["potongan_persen"];
            $potongan_harga = (float) $r["potongan_harga"];

            $sub_total = $jumlah * ($harga_jual_satuan - $potongan_harga);

            $row = array();
            $row["no"] = $no;
            $row["uuid"] = trim($r["uuid"]);
            $row["kode"] = $r["item_kode"];
            $row["barcode"] = $r["item_barcode"];
            $row["nama"] = $r["item_nama"];
            $row["kategori"] = $r["item_kategori_nama"];
            $row["jumlah"] = number_format($r["jumlah"]);
            $row["satuan"] = $r["satuan"];
            $row["harga_jual_satuan"] = number_format($harga_jual_satuan, 2);
            $row["potongan"] = number_format($potongan_persen, 2);
            $row["total"] = number_format($sub_total);

            $final_res[] = $row;
        }

        return $final_res;
    }

    function item_get_filtered_total_for_penjualan_uuid($filters = array(), $penjualan_uuid = "")
    {
        $filters = array();
        $filters["penjualan_uuid"] = $penjualan_uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->item_engine->item_get_list($filters, false, true);
        return count($res);
    }

    function item_get_total_for_penjualan_uuid($filters = array(), $penjualan_uuid = "")
    {
        $filters = array();
        $filters["penjualan_uuid"] = $penjualan_uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->item_engine->item_get_list($filters);
        return count($res);
    }

    function item_get($uuid = "")
    {
        if (!$this->allow) return array();

        // check uuid
        $filters = array();
        $filters["uuid"] = $uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->item_engine->item_get_list($filters);
        if (count($res) == 0) return array();
        $res = $res[0];

        $res["cache_stock"] = $this->item_engine->item_get_total_stock_for_item_uuid($res["uuid"]);

        return $res;
    }

    function item_get_nama_by_kode()
    {
        $item_kode = $this->input->get("kode");
        // check uuid
        $filters = array();
        $filters["kode"] = $item_kode;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->item_engine->item_get_list($filters);
        if (count($res) == 0) return "Tidak ditemukan";
        $res = $res[0];
        return $res["nama"];
    }

    function item_set_arsip($uuid = "")
    {
        if (!$this->allow_set_arsip) return set_http_response_error(HTTP_FORBIDDEN, "Akses ditolak");

        // check uuid
        $filters = array();
        $filters["uuid"] = $uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->item_engine->item_get_list($filters);
        if (count($res) == 0) return set_http_response_error(HTTP_BAD_REQUEST, "Item tidak ditemukan");
        $res = $res[0];
        $uuid = $res["uuid"];
        $arsip = (int) $res["arsip"];
        $arsip_date = date("d M Y H:i:s", strtotime($res["arsip_date"]));
        $arsip_user_name = trim($res["arsip_user_name"]);

        if ($arsip == 1) return set_http_response_success("Item telah diarsip oleh $arsip_user_name pada $arsip_date");

        // mulai proses arsip
        $this->db->trans_start();
        try {
            $res = $this->item_engine->item_set_arsip($uuid, 1, date("Y-m-d H:i:s"), $this->actor_user_uuid, $this->actor_user_name);
            if ($res == false) {
                throw new Exception("Gagal set arsip item yang dipilih");
            }
        } catch (Exception $e) {
            $this->db->trans_rollback();
            return set_http_response_error(501, "Error : " . $e->getMessage());
        }

        $this->db->trans_commit();
        return set_http_response_success("Item telah diarsip");
    }

    function item_set_aktif($uuid = "")
    {
        if (!$this->allow_set_aktif) return set_http_response_error(HTTP_FORBIDDEN, "Akses ditolak");

        // check uuid
        $filters = array();
        $filters["uuid"] = $uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->item_engine->item_get_list($filters);
        if (count($res) == 0) return set_http_response_error(HTTP_BAD_REQUEST, "Item tidak ditemukan");
        $res = $res[0];
        $uuid = $res["uuid"];
        $arsip = (int) $res["arsip"];

        if ($arsip == 0) return set_http_response_success("Item belum diarsip");

        // mulai proses aktif
        $this->db->trans_start();
        try {
            $res = $this->item_engine->item_set_arsip($uuid, 0);
            if ($res == false) {
                throw new Exception("Gagal set aktif item yang dipilih");
            }
        } catch (Exception $e) {
            $this->db->trans_rollback();
            return set_http_response_error(501, "Error : " . $e->getMessage());
        }

        $this->db->trans_commit();
        return set_http_response_success("Item telah di aktifkan kembali");
    }

    function item_save()
    {
        $uuid = $this->input->post("uuid");
        $kode = $this->input->post("kode");
        $barcode = $this->input->post("barcode");
        $nama = $this->input->post("nama");
        $keterangan = $this->input->post("keterangan");
        $item_kategori_uuid = $this->input->post("item_kategori_uuid");
        $tipe = $this->input->post("tipe");
        $cek_stock_saat_penjualan = (int) $this->input->post("cek_stock_saat_penjualan");
        $minimum_stock = $this->input->post("minimum_stock");
        $minimum_stock = to_number($minimum_stock);

        $harga_pokok = (float) to_number($this->input->post("harga_pokok"));
        $harga_jual_tipe_jasa = (float) to_number($this->input->post("harga_jual_tipe_jasa"));
        $satuan_tipe_jasa = $this->input->post("satuan_tipe_jasa");

        $margin_persen = (float) $this->input->post("margin_persen");
        $margin_nilai = (float) $this->input->post("margin_nilai");

        $allowed_tipe = array("Barang", "Jasa");

        // validasi
        if (empty($kode)) return set_http_response_error(HTTP_BAD_REQUEST, "Kode tidak boleh kosong");
        if (empty($barcode)) $barcode = $kode;
        if (empty($nama)) return set_http_response_error(HTTP_BAD_REQUEST, "Nama tidak boleh kosong");
        if (!in_array($tipe, $allowed_tipe)) return set_http_response_error(HTTP_BAD_REQUEST, "Tipe tidak valid");
        if ($cek_stock_saat_penjualan < 0 || $cek_stock_saat_penjualan > 1) return set_http_response_error(HTTP_BAD_REQUEST, "Nilai cek stock saat penjualan tidak valid");
        if ($minimum_stock < 0) return set_http_response_error(HTTP_BAD_REQUEST, "Nilai minimum stock tidak valid");

        if ($margin_persen < 0) return set_http_response_error(HTTP_BAD_REQUEST, "Margin persen tidak boleh dibawah 0");
        if ($margin_nilai < 0) return set_http_response_error(HTTP_BAD_REQUEST, "Marign nilai tidak boleh dibawah 0");

        if (strtolower($tipe) == "jasa") {
            if ($harga_jual_tipe_jasa <= 0) return set_http_response_error(HTTP_BAD_REQUEST, "Harga jual harus diisi");
            if (empty($satuan_tipe_jasa)) return set_http_response_error(HTTP_BAD_REQUEST, "Satuan harus diisi");
        } else {
            if ($cek_stock_saat_penjualan == 0) {
                // **
                // karena cek stock saat jual = tidak, 
                // maka harga jual tipe jasa dipakai untuk menampung harga jual barang
                if ($harga_jual_tipe_jasa <= 0) return set_http_response_error(HTTP_BAD_REQUEST, "Harga jual harus diisi");

                if ($harga_pokok < 0) $harga_pokok = 0;
                if (empty($satuan_tipe_jasa)) return set_http_response_error(HTTP_BAD_REQUEST, "Satuan harus diisi");
            }
        }

        $created = date("Y-m-d H:i:s");
        $creator_user_uuid = $this->actor_user_uuid;
        $creator_user_name = $this->actor_user_name;
        $last_updated = date("Y-m-d H:i:s");
        $last_updated_user_uuid = $this->actor_user_uuid;
        $last_updated_user_name = $this->actor_user_name;
        $arsip = 0;
        $arsip_date = null;
        $arsip_user_uuid = "";
        $arsip_user_name = "";
        $cache_stock = 0;
        $cache_harga_pokok = 0;

        $struktur_satuan_harga_list[strtoupper("PCS")] = array(
            "satuan" => "Pcs",
            "konversi" => 1,
            "konversi_satuan" => "Pcs",
            "harga_pokok" => 0,
            "harga_jual" => 0,
            "stock" => 0,
        );

        $struktur_satuan_harga_json = json_encode($struktur_satuan_harga_list);

        // check uuid
        if (!empty($uuid)) {
            $filters = array();
            $filters["uuid"] = $uuid;
            $filters["cabang_uuid"] = $this->cabang_selected_uuid;
            $res = $this->item_engine->item_get_list($filters);
            if (count($res) > 0) {
                $res = $res[0];

                $uuid = $res["uuid"];
                $created = trim($res["created"]);
                $creator_user_uuid = $res["creator_user_uuid"];
                $creator_user_name = trim($res["creator_user_name"]);
                $arsip = trim($res["arsip"]);
                $arsip_date = $res["arsip_date"];
                $arsip_user_uuid = $res["arsip_user_uuid"];
                $arsip_user_name = trim($res["arsip_user_name"]);
                $struktur_satuan_harga_json = $res["struktur_satuan_harga_json"];
                $cache_stock = (int) $res["cache_stock"];
                $cache_harga_pokok = (float) $res["cache_harga_pokok"];

                $tipe = $res["tipe"];
            } else {
                return set_http_response_error(HTTP_BAD_REQUEST, "Invalid item");
            }
        }

        // **
        // check jika boleh create / update
        if (empty($uuid)) {
            if (!$this->allow_create) return set_http_response_error(HTTP_FORBIDDEN, "Akses ditolak");
        } else {
            if (!$this->allow_update) return set_http_response_error(HTTP_FORBIDDEN, "Akses ditolak");
        }

        // check duplikat kode
        $filters = array();
        $filters["kode"] = $kode;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->item_engine->item_get_list($filters);
        if (count($res) > 0) {
            $res = $res[0];

            $curr_uuid = $res["uuid"];
            $curr_nama = trim($res["nama"]);

            if ($curr_uuid != $uuid) {
                return set_http_response_error(HTTP_BAD_REQUEST, "Kode $kode sudah terdaftar untuk item $curr_nama");
            }
        }

        // check duplikat barcode
        $filters = array();
        $filters["barcode"] = $barcode;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->item_engine->item_get_list($filters);
        if (count($res) > 0) {
            $res = $res[0];

            $curr_uuid = $res["uuid"];
            $curr_nama = trim($res["nama"]);

            if ($curr_uuid != $uuid) {
                return set_http_response_error(HTTP_BAD_REQUEST, "Barcode $barcode sudah terdaftar untuk item $curr_nama");
            }
        }

        // check item kategori uuid
        $filters = array();
        $filters["uuid"] = $item_kategori_uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->item_engine->item_kategori_get_list($filters);
        if (count($res) == 0) return set_http_response_error(HTTP_BAD_REQUEST, "Kategori item yang dipilih tidak ditemukan");
        $res = $res[0];
        $item_kategori_uuid = $res["uuid"];


        if (strtolower($tipe) == "jasa") {
            $struktur_satuan_harga_list = array();
            $struktur_satuan_harga_list[strtoupper($satuan_tipe_jasa)] = array(
                "satuan" => $satuan_tipe_jasa,
                "konversi" => 1,
                "konversi_satuan" => $satuan_tipe_jasa,
                "harga_pokok" => 0,
                "harga_jual" => $harga_jual_tipe_jasa,
                "stock" => 0,
            );

            $struktur_satuan_harga_json = json_encode($struktur_satuan_harga_list);
            $cek_stock_saat_penjualan = 0;
        } else {
            if ($cek_stock_saat_penjualan == 0) {
                $struktur_satuan_harga_list = array();
                $struktur_satuan_harga_list[strtoupper($satuan_tipe_jasa)] = array(
                    "satuan" => $satuan_tipe_jasa,
                    "konversi" => 1,
                    "konversi_satuan" => $satuan_tipe_jasa,
                    "harga_pokok" => $harga_pokok,
                    "harga_jual" => $harga_jual_tipe_jasa,
                    "stock" => 0,
                );

                $struktur_satuan_harga_json = json_encode($struktur_satuan_harga_list);

                // **
                // karena cek stock saat jual = tidak, 
                // maka harga jual tipe jasa dipakai untuk menampung harga jual barang
                $margin_nilai = $harga_jual_tipe_jasa - $harga_pokok;
                $margin_persen = $harga_pokok == 0 ? 100 : ($margin_nilai / $harga_pokok) * 100;
            }
        }

        $data = array();
        $data["uuid"] = $uuid;
        $data["created"] = $created;
        $data["creator_user_uuid"] = $creator_user_uuid;
        $data["creator_user_name"] = $creator_user_name;
        $data["last_updated"] = $last_updated;
        $data["last_updated_user_uuid"] = $last_updated_user_uuid;
        $data["last_updated_user_name"] = $last_updated_user_name;

        $data["kode"] = $kode;
        $data["barcode"] = $barcode;
        $data["nama"] = $nama;
        $data["keterangan"] = $keterangan;
        $data["struktur_satuan_harga_json"] = $struktur_satuan_harga_json;
        $data["item_kategori_uuid"] = $item_kategori_uuid;
        $data["tipe"] = $tipe;
        $data["cek_stock_saat_penjualan"] = $cek_stock_saat_penjualan;
        $data["minimum_stock"] = $minimum_stock;

        $data["margin_persen"] = $margin_persen;
        $data["margin_nilai"] = $margin_nilai;

        $data["harga_jual_tipe_jasa"] = $harga_jual_tipe_jasa;
        $data["satuan_tipe_jasa"] = $satuan_tipe_jasa;

        $data["cache_harga_pokok"] = $cache_harga_pokok;
        $data["cache_stock"] = $cache_stock;

        $data["arsip"] = $arsip;
        $data["arsip_date"] = $arsip_date;
        $data["arsip_user_uuid"] = $arsip_user_uuid;
        $data["arsip_user_name"] = $arsip_user_name;
        $data["cabang_uuid"] = $this->cabang_selected_uuid;

        $this->db->trans_start();
        try {
            $res = $this->item_engine->item_save($data);
            if ($res == false) {
                throw new Exception("Item gagal disimpan");
            }

            $uuid = trim($res);
        } catch (Exception $e) {
            $this->db->trans_rollback();
            return set_http_response_error(501, "Error : " . $e->getMessage());
        }

        $this->db->trans_commit();
        return set_http_response_success("Item telah berhasil disimpan", array(), $uuid);
    }

    function item_update_struktur_satuan_harga_json()
    {
        if (!$this->allow_ubah_struktur_satuan_harga) return set_http_response_error(HTTP_FORBIDDEN, "Akses ditolak");

        $uuid = $this->input->post("uuid");
        $key = $this->input->post("key");
        $action = $this->input->post("action");
        $struktur_satuan_harga_json = $this->input->post("struktur_satuan_harga_json");

        $struktur_satuan_harga_list = json_decode($struktur_satuan_harga_json, true);
        if (json_last_error() !== JSON_ERROR_NONE) return set_http_response_error(HTTP_BAD_REQUEST, "Struktur Harga dan Satuan tidak valid");

        $margin_persen = 0;
        $current_struktur_satuan_harga_list = array();

        $filters = array();
        $filters["uuid"] = $uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->item_engine->item_get_list($filters);
        $rata_rata_harga_beli_satuan_terkecil = 0;
        if (count($res) > 0) {
            $res = $res[0];

            $uuid = $res["uuid"];
            $current_struktur_satuan_harga_list = json_decode($res["struktur_satuan_harga_json"], true);
            $margin_persen = (float) $res['margin_persen'];

            $rata_rata_harga_beli_satuan_terkecil = $this->item_engine->item_get_rata_rata_harga_beli_satuan_terkecil_for_item_uuid($uuid);
        }

        if (empty($key)) {
            return set_http_response_error(HTTP_BAD_REQUEST, "Kunci Struktur satuan dan harga tidak valid");
        } else {
            $key = strtoupper($key);

            if (isset($current_struktur_satuan_harga_list[$key])) {
                if (strtolower($action) == "add") {
                    return set_http_response_error(HTTP_BAD_REQUEST, "Struktur satuan dan harga untuk satuan '$key' sudah ada");
                }
            } else {
                if (strtolower($action) == "edit") {
                    return set_http_response_error(HTTP_BAD_REQUEST, "Struktur satuan dan harga untuk satuan '$key' tidak ditemukan");
                }
            }
            $satuan = $struktur_satuan_harga_list["satuan"];
            $struktur_satuan_harga_list["satuan"] = ucwords($satuan);
            $current_struktur_satuan_harga_list[$key] = $struktur_satuan_harga_list;
        }

        $new_struktur_satuan_harga_list = array();

        $total_pcs_satuan_terkecil = 1;
        $prev_satuan = "";
        foreach ($current_struktur_satuan_harga_list as $key => $l) {
            $satuan = trim($l["satuan"]);
            $konversi = to_number($l["konversi"]);
            $harga_pokok = to_number($l["harga_pokok"]);
            $harga_jual = to_number($l["harga_jual"]);
            $stock = to_number($l["stock"]);

            $total_pcs_satuan_terkecil *= $konversi;
            $harga_pokok = $total_pcs_satuan_terkecil * $rata_rata_harga_beli_satuan_terkecil;

            $step_down_satuan = "";
            if (empty($prev_satuan)) {
                $step_down_satuan = $satuan;
            } else {
                $step_down_satuan = $prev_satuan;
            }

            $margin_nilai = 0;
            if ($margin_persen > 0 && $harga_pokok  > 0) {
                $margin_nilai = ($harga_pokok * $margin_persen) / 100;
            }
            $harga_jual = $harga_pokok + $margin_nilai;
            
            if ($harga_jual > 0) $harga_jual = (int) round($harga_jual, 0, PHP_ROUND_HALF_UP);
            if ($harga_pokok > 0) $harga_pokok = (int) round($harga_pokok, 0, PHP_ROUND_HALF_DOWN);

            if($stock > 0) {
                $stock = round($stock,0);
            }

            $new_struktur_satuan_harga_list[$key] = array(
                "satuan" => $satuan,
                "konversi" => $konversi,
                "konversi_satuan" => $step_down_satuan,
                "harga_pokok" => $harga_pokok,
                "harga_jual" => $harga_jual,
                "stock" => $stock,
            );

            $prev_harga_pokok = $harga_pokok;
            $prev_satuan = $satuan;
        }

        $new_struktur_satuan_harga_json = json_encode($new_struktur_satuan_harga_list);
        $this->db->trans_start();
        try {
            $res = $this->item_engine->item_update_struktur_satuan_harga_json($uuid, $new_struktur_satuan_harga_json);
            if ($res == false) {
                throw new Exception("Gagal update struktur satuan dan harga");
            }
        } catch (Exception $e) {
            $this->db->trans_rollback();
            return set_http_response_error(501, "Error : " . $e->getMessage());
        }

        $this->db->trans_commit();
        return set_http_response_success("Sukses update struktur satuan dan harga");
    }

    function item_delete_struktur_satuan_harga_json_row()
    {
        if (!$this->allow_ubah_struktur_satuan_harga) return set_http_response_error(HTTP_FORBIDDEN, "Akses ditolak");

        $uuid = $this->input->get("uuid");
        $key = $this->input->get("key");


        $current_struktur_satuan_harga_list = array();

        $filters = array();
        $filters["uuid"] = $uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->item_engine->item_get_list($filters);
        if (count($res) > 0) {
            $res = $res[0];

            $uuid = $res["uuid"];
            $current_struktur_satuan_harga_list = json_decode($res["struktur_satuan_harga_json"], true);
        }

        if (empty($key)) {
            return set_http_response_error(HTTP_BAD_REQUEST, "Kunci Struktur satuan dan harga tidak valid");
        } else {
            $key = strtoupper($key);

            if (!isset($current_struktur_satuan_harga_list[$key])) {
                return set_http_response_error(HTTP_BAD_REQUEST, "Struktur satuan dan harga untuk satuan '$key' tidak ada");
            }

            $total_items = count($current_struktur_satuan_harga_list);
            if ($total_items == 0) return set_http_response_error(HTTP_BAD_REQUEST, "No data");
            if ($total_items == 1) {
                unset($current_struktur_satuan_harga_list[$key]);
            } else {

                $position = 1;
                $flag = 0;
                foreach ($current_struktur_satuan_harga_list as $_key => $l) {
                    if (strtoupper($key) == strtoupper($_key)) {
                        $flag = 1;
                        break;
                    }

                    $position++;
                }

                if ($flag) {
                    if ($position != $total_items) {
                        return set_http_response_error(HTTP_BAD_REQUEST, "Silahkan hapus mulai dari stukrur paling terakhir");
                    }
                }

                unset($current_struktur_satuan_harga_list[$key]);
            }
        }

        $new_struktur_satuan_harga_json = json_encode($current_struktur_satuan_harga_list);
        $this->db->trans_start();
        try {
            $res = $this->item_engine->item_update_struktur_satuan_harga_json($uuid, $new_struktur_satuan_harga_json);
            if ($res == false) {
                throw new Exception("Gagal hapus struktur satuan dan harga baris '$key'");
            }
        } catch (Exception $e) {
            $this->db->trans_rollback();
            return set_http_response_error(501, "Error : " . $e->getMessage());
        }

        $this->db->trans_commit();
        return set_http_response_success("Sukses hapus struktur satuan dan harga '$key'");
    }

    function item_get_total_aktif()
    {
        $filters = array();
        $filters["arsip"] = 0;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->item_engine->item_get_list($filters);
        return count($res);
    }

    function item_stock_minimum()
    {
        $filters = array();
        $filters["arsip"] = 0;
        $filters["minimum"] = 1;
        $filters["tipe"] = "barang";
        $filters["cek_stock_saat_penjualan"] = 1;
        $item_list = $this->item_engine->item_get_list($filters);

        $minimum_list = array();
        foreach ($item_list as $l) {
            $minimum_stock = (int) $l["minimum_stock"];
            $cache_stock = (int) $l["cache_stock"];

            $struktur_satuan_harga_json = $l["struktur_satuan_harga_json"];
            $struktur_satuan_harga_list = json_decode($l["struktur_satuan_harga_json"], true);
            $satuan_list = array();
            if (is_array($struktur_satuan_harga_list)) {
                foreach ($struktur_satuan_harga_list as $key => $data) {
                    $satuan_list[] = $data["satuan"];
                }
            }
            $satuan_terkecil = "";
            if (count($satuan_list) > 0) $satuan_terkecil = $satuan_list[0];

            $row = array(
                "kode" => $l["kode"],
                "nama" => $l["nama"],
                "kategori_nama" => $l["item_kategori_nama"],
                "stock" => number_format($cache_stock, 2, ",", "."),
                "satuan" => $satuan_terkecil,
                "minimum_stock" => number_format($minimum_stock, 2, ",", ".")
            );

            $minimum_list[] = $row;
        }

        return $minimum_list;
    }

    function laporan_item()
    {
        $gudang_uuid = $this->input->get("gudang_uuid");
        $item_kategori_uuid = $this->input->get("item_kategori_uuid");
        $arsip = $this->input->get("arsip");
        $tipe = $this->input->get("tipe");

        $list = $this->settings_engine->get_all_settings();
        $settings_list = array();
        foreach ($list as $l) {
            $_key = $l['_key'];
            $settings_list[$_key] = $l;
        }

        $filter_item_kategori_uuid = "";
        $filter_item_kategori_nama = "Semua Kategori";

        $filter_gudang_uuid = "";
        $filter_gudang_nama = "Semua Gudang";

        $filter_status = "Semua status";
        if ((int) $arsip == 1) $filter_status = "Arsip";
        if ((int) $arsip == 0) $filter_status = "Aktif";

        $filter_tipe = "Semua Tipe";
        if (!empty($tipe)) $filter_tipe = $tipe;

        $final_data = array(
            "header" => array(
                "nama_toko" => $settings_list["TOKO_NAMA"]["_value"],
                "alamat_toko" => $settings_list["TOKO_ALAMAT"]["_value"],
                "no_telepon_toko" => $settings_list["TOKO_NO_TELEPON"]["_value"],
            ),

            "filters" => array(
                "tipe" => $filter_tipe,
                "status" => $filter_status,
                "gudang" => $filter_gudang_nama,
                "item_kategori" => $filter_item_kategori_nama,
            ),

            "body" => array(),

            "footer" => array(),
        );

        if (!empty($item_kategori_uuid)) {
            // **
            // check item kategori uuid
            $filters = array();
            $filters["uuid"] = $item_kategori_uuid;
            $filters["cabang_uuid"] = $this->cabang_selected_uuid;
            $res = $this->item_engine->item_kategori_get_list($filters);
            if (count($res) == 0) return $final_data;
            $res = $res[0];
            $filter_item_kategori_uuid = $res["uuid"];
            $filter_item_kategori_nama = trim($res["nama"]);
        }

        if (!empty($gudang_uuid)) {
            // **
            // check item kategori uuid
            $filters = array();
            $filters["uuid"] = $gudang_uuid;
            $filters["cabang_uuid"] = $this->cabang_selected_uuid;
            $res = $this->gudang_engine->gudang_get_list($filters);
            if (count($res) == 0) return $final_data;
            $res = $res[0];
            $filter_gudang_uuid = $res["uuid"];
            $filter_gudang_nama = $res["kode"] . " - " . trim($res["nama"]);
        }


        $filters = array();
        if ($arsip == "0" || $arsip == "1") $filters["arsip"] = $arsip;
        if (!empty($filter_item_kategori_uuid)) $filters["item_kategori_uuid"] = $filter_item_kategori_uuid;
        if (!empty($tipe)) $filters["tipe"] = $tipe;

        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->item_engine->item_get_list($filters);
        if (count($res) == 0) return $final_data;
        $item_list = array();

        foreach ($res as $l) {
            $row = array();
            $row["kode"] = $l["kode"];
            $row["barcode"] = $l["barcode"];
            $row["nama"] = $l["nama"];
            $row["item_kategori_nama"] = $l["item_kategori_nama"];
            $row["stock"] = 0;
            $row["satuan"] = $l["satuan_tipe_jasa"];
            $row["tipe"] = $l["tipe"];
            $row["harga_pokok"] = 0;
            $row["harga_jual"] = 0;

            $struktur_satuan_harga_json = trim($l["struktur_satuan_harga_json"]);
            $struktur_satuan_harga_list = json_decode($struktur_satuan_harga_json, true);
            if (!is_array($struktur_satuan_harga_list) || count($struktur_satuan_harga_list) == 0) {
                $item_list[] = $row;
                continue;
            }
            if (strtolower($l["tipe"]) == "jasa") {
                $row["harga_jual"] = number_format($l["harga_jual_tipe_jasa"], 0, ",", ".");
                $item_list[] = $row;
                continue;
            }

            $stock = $this->item_engine->item_get_total_stock_for_item_uuid($l["uuid"], $filter_gudang_uuid);
            foreach ($struktur_satuan_harga_list as $key => $l2) {
                $satuan = $l2["satuan"];
                $stock = $stock / (int) $l2['konversi'];

                if($stock > 0) $stock = round($stock, 2, PHP_ROUND_HALF_DOWN);

                $stock_str = $stock;
                $exp = explode(".", $stock_str);
                $stock = (int) $exp[0];

                $row2 = $row;
                $row["satuan"] = $satuan;
                $row["stock"] = $stock;
                $row["harga_pokok"] = number_format($l2["harga_pokok"], 0, ',', '.');
                $row["harga_jual"] = number_format($l2["harga_jual"], 0, ',', '.');

                $item_list[] = $row;
            }
        }

        $final_data["filters"]["item_kategori"] = $filter_item_kategori_nama;
        $final_data["filters"]["gudang"] = $filter_gudang_nama;
        $final_data["body"] = $item_list;
        return $final_data;
    }


    //**
    // laporan kartu stock
    function laporan_kartu_stock()
    {
        $bulan = get("bulan");
        $tahun = get("tahun");
        $item_kode = get("item_kode");
        $gudang_uuid = get("gudang_uuid");

        if (empty($bulan)) $bulan = date("m");
        if (empty($tahun)) $tahun = date("Y");

        $start_date = "$tahun-$bulan-01 00:00:00";
        $end_date = date("Y-m-t 23:59:59", strtotime($start_date));
        $end_date_last_month = date("Y-m-d 23:59:59", strtotime($start_date . " -1 day"));
        $start_date_this_month = $start_date;

        $filter_gudang_uuid = "";
        $filter_gudang_nama = "Semua Gudang";

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
                "bulan" => get_nama_bulan((int) date("m", strtotime($start_date))),
                "tahun" => date("Y", strtotime($start_date)),
                "kode_item" => "",
                "nama" => "",
                "gudang" => $filter_gudang_nama
            ),

            "body" => array(
                "stock_bulan_lalu_data" => array(),
                "stock_bulan_ini_data_list" => array()
            ),

            "footer" => array(
                "total_masuk" => 0,
                "total_keluar" => 0,
                "saldo_awal" => 0,
                "saldo_akhir" => 0
            ),
        );

        if (empty($item_kode)) return $final_data;

        // **
        // check item berdasarkan code;
        $filters = array();
        $filters["kode"] = $item_kode;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->item_engine->item_get_list($filters);
        if (count($res) == 0) return array();
        $res = $res[0];
        $item_uuid = $res["uuid"];
        $item_kode = $res["kode"];
        $item_nama = $res["nama"];

        $final_data["filters"]["kode_item"] = $item_kode;
        $final_data["filters"]["nama"] = $item_nama;

        $item_uuid_list = array($item_uuid);

        // **
        // get saldo stock sampai akhir bulan lalu        
        $total_stock_bulan_lalu_list = $this->item_engine->get_total_stock_for_date_range_and_item_uuid_list($item_uuid_list, false, $end_date_last_month, $filter_gudang_uuid);
        if (count($total_stock_bulan_lalu_list) == 0) return array();

        $total_stock_bulan_lalu = $total_stock_bulan_lalu_list[$item_uuid];

        // **
        // get stock awal untuk tanggal awal bulan
        $stock_awal_bulan = 0;
        $filters = array();
        $filters["start_date"] = $start_date;
        $filters["end_date"] = date("Y-m-d 23:59:59", strtotime($start_date));
        $filters["item_uuid"] = $item_uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        if (!empty($filter_gudang_uuid)) $filters["gudang_uuid"] = $filter_gudang_uuid;
        $stock_awal_list = $this->item_engine->stock_awal_get_list($filters);
        if (count($stock_awal_list) > 0) {
            $res = $stock_awal_list[0];
            $stock_awal_bulan = (float) $res["jumlah_satuan_terkecil"];
            $stock_awal_bulan = $stock_awal_bulan - $total_stock_bulan_lalu;
        } else {
            $stock_awal_bulan = $total_stock_bulan_lalu;
        }

        $stock_bulan_lalu_data_list = array();
        foreach ($item_uuid_list as $index => $item_uuid) {
            $stock_bulan_lalu_data_list[$item_uuid] = array(
                "no_transaksi" => "",
                "tanggal" => "",
                "tipe" => "SA",
                "keterangan" => "Saldo awal",
                "masuk" => 0,
                "keluar" => 0,
                "saldo" => number_format($stock_awal_bulan, 2),
            );
        }

        $final_data["body"]["stock_bulan_lalu_data"] = $stock_bulan_lalu_data_list[$item_uuid];

        $list_1 = array();

        // **
        // ambil item masuk dari item_transfer berdasarkan ke_gudang_id
        $filters = array();
        $filters["start_date"] = $start_date;
        $filters["end_date"] = $end_date;
        $filters["item_uuid"] = $item_uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        if (!empty($filter_gudang_uuid)) $filters["ke_gudang_uuid"] = $filter_gudang_uuid;
        $item_transfer_detail_list = $this->item_engine->item_transfer_detail_get_total_jumlah_satuan_terkecil_list_group_by_item_uuid_and_item_transfer_uuid($filters);

        $item_transfer_masuk_data_list = array();
        if (count($item_transfer_detail_list) > 0) {
            foreach ($item_transfer_detail_list as $l) {
                $no_transaksi = $l["item_transfer_number_formatted"];
                $tanggal = $l["tanggal"];
                $tipe = "IT/M";
                $keterangan = "item Transfer";
                $masuk = (float) $l["total_jumlah_satuan_terkecil"];
                $timestamp = strtotime($l["tanggal"]);

                $row = array(
                    "no_transaksi" => $no_transaksi,
                    "tanggal" => date("d-m-Y", strtotime($tanggal)),
                    "tipe" => $tipe,
                    "keterangan" => $keterangan,
                    "masuk" => $masuk,
                    "keluar" => 0,
                    "saldo" => 0
                );

                $item_transfer_masuk_data_list[$timestamp][] = $row;

                $list_1[$timestamp][] = $row;
            }
            ksort($item_transfer_masuk_data_list);
        }

        // **
        // ambil item keluar dari item_transfer berdasarkan dari_gudang_id
        $filters = array();
        $filters["start_date"] = $start_date;
        $filters["end_date"] = $end_date;
        $filters["item_uuid"] = $item_uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        if (!empty($filter_gudang_uuid)) $filters["dari_gudang_uuid"] = $filter_gudang_uuid;
        $item_transfer_detail_list = $this->item_engine->item_transfer_detail_get_total_jumlah_satuan_terkecil_list_group_by_item_uuid_and_item_transfer_uuid($filters);

        $item_transfer_keluar_data_list = array();
        if (count($item_transfer_detail_list) > 0) {
            foreach ($item_transfer_detail_list as $l) {
                $no_transaksi = $l["item_transfer_number_formatted"];
                $tanggal = $l["tanggal"];
                $tipe = "IT/K";
                $keterangan = "item Transfer";
                $keluar = (float) $l["total_jumlah_satuan_terkecil"];
                $timestamp = strtotime($l["tanggal"]);

                $row = array(
                    "no_transaksi" => $no_transaksi,
                    "tanggal" => date("d-m-Y", strtotime($tanggal)),
                    "tipe" => $tipe,
                    "keterangan" => $keterangan,
                    "masuk" => 0,
                    "keluar" => $keluar,
                    "saldo" => 0
                );

                $item_transfer_keluar_data_list[$timestamp][] = $row;

                $list_1[$timestamp][] = $row;
            }
            ksort($item_transfer_keluar_data_list);
        }

        // **
        // ambil item masuk dari pembelian
        $filters = array();
        $filters["start_date"] = $start_date;
        $filters["end_date"] = $end_date;
        $filters["item_uuid"] = $item_uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        if (!empty($filter_gudang_uuid)) $filters["gudang_uuid"] = $filter_gudang_uuid;
        $pembelian_detail_list = $this->transaksi_engine->pembelian_detail_get_total_jumlah_satuan_terkecil_list_group_by_item_uuid_and_pembelian_uuid($filters);

        $pembelian_data_list = array();
        if (count($pembelian_detail_list) > 0) {
            foreach ($pembelian_detail_list as $l) {
                $no_transaksi = $l["pembelian_number_formatted"];
                $tanggal = $l["tanggal"];
                $tipe = "BL";
                $keterangan = "Pembelian";
                $masuk = (float) $l["total_jumlah_satuan_terkecil"];
                $timestamp = strtotime($l["tanggal"]);

                $row = array(
                    "no_transaksi" => $no_transaksi,
                    "tanggal" => date("d-m-Y", strtotime($tanggal)),
                    "tipe" => $tipe,
                    "keterangan" => $keterangan,
                    "masuk" => $masuk,
                    "keluar" => 0,
                    "saldo" => 0
                );

                $pembelian_data_list[$timestamp][] = $row;

                $list_1[$timestamp][] = $row;
            }
            ksort($pembelian_data_list);
        }

        // **
        // ambil item keluar dari pembelian return
        $filters = array();
        $filters["start_date"] = $start_date;
        $filters["end_date"] = $end_date;
        $filters["item_uuid"] = $item_uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        if (!empty($filter_gudang_uuid)) $filters["gudang_uuid"] = $filter_gudang_uuid;
        $pembelian_retur_detail_list = $this->transaksi_engine->pembelian_retur_detail_get_total_jumlah_satuan_terkecil_list_group_by_item_uuid_and_pembelian_uuid($filters);

        $pembelian_retur_data_list = array();
        if (count($pembelian_retur_detail_list) > 0) {
            foreach ($pembelian_retur_detail_list as $l) {
                $no_transaksi = $l["pembelian_retur_number_formatted"];
                $tanggal = $l["tanggal"];
                $tipe = "RBL";
                $keterangan = "Retur Pembelian";
                $keluar = (float) $l["total_jumlah_satuan_terkecil"];
                $timestamp = strtotime($l["tanggal"]);

                $row = array(
                    "no_transaksi" => $no_transaksi,
                    "tanggal" => date("d-m-Y", strtotime($tanggal)),
                    "tipe" => $tipe,
                    "keterangan" => $keterangan,
                    "masuk" => 0,
                    "keluar" => $keluar,
                    "saldo" => 0
                );

                $pembelian_retur_data_list[$timestamp][] = $row;

                $list_1[$timestamp][] = $row;
            }
            ksort($pembelian_retur_data_list);
        }


        // **
        // ambil item masuk dari penjualan
        $filters = array();
        $filters["start_date"] = $start_date;
        $filters["end_date"] = $end_date;
        $filters["item_uuid"] = $item_uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        if (!empty($filter_gudang_uuid)) $filters["gudang_uuid"] = $filter_gudang_uuid;
        $penjualan_detail_list = $this->transaksi_engine->penjualan_detail_get_total_jumlah_satuan_terkecil_list_group_by_item_uuid_and_penjualan_uuid($filters);

        $penjualan_data_list = array();
        if (count($penjualan_detail_list) > 0) {
            foreach ($penjualan_detail_list as $l) {
                $no_transaksi = $l["penjualan_number_formatted"];
                $tanggal = $l["tanggal"];
                $tipe = "JL";
                $keterangan = "Penjualan";
                $keluar = (float) $l["total_jumlah_satuan_terkecil"];
                $timestamp = strtotime($l["tanggal"]);

                $row = array(
                    "no_transaksi" => $no_transaksi,
                    "tanggal" => date("d-m-Y", strtotime($tanggal)),
                    "tipe" => $tipe,
                    "keterangan" => $keterangan,
                    "masuk" => 0,
                    "keluar" => $keluar,
                    "saldo" => 0
                );

                $penjualan_data_list[$timestamp][] = $row;

                $list_1[$timestamp][] = $row;
            }
            ksort($penjualan_data_list);
        }


        // **
        // ambil item masuk dari stock_opname
        $filters = array();
        $filters["start_date"] = $start_date;
        $filters["end_date"] = $end_date;
        $filters["item_uuid"] = $item_uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        if (!empty($filter_gudang_uuid)) $filters["gudang_uuid"] = $filter_gudang_uuid;
        $stock_opname_detail_list = $this->item_engine->stock_opname_detail_get_total_jumlah_satuan_terkecil_list_group_by_item_uuid_and_stock_opname_uuid($filters);

        $stock_opname_data_list = array();
        if (count($stock_opname_detail_list) > 0) {
            foreach ($stock_opname_detail_list as $l) {
                $no_transaksi = $l["stock_opname_number_formatted"];
                $tanggal = $l["tanggal"];
                $tipe = "SO";
                $keterangan = "Stock Opname";
                $stock_selisih = (float) $l["total_stock_selisih_satuan_terkecil"];
                $timestamp = strtotime($l["tanggal"]);

                $masuk = 0;
                $keluar = 0;
                if ($stock_selisih < 0) $keluar = $stock_selisih * -1;
                if ($stock_selisih > 0) $masuk = $stock_selisih;

                $row = array(
                    "no_transaksi" => $no_transaksi,
                    "tanggal" => date("d-m-Y", strtotime($tanggal)),
                    "tipe" => $tipe,
                    "keterangan" => $keterangan,
                    "masuk" => $masuk,
                    "keluar" => $keluar,
                    "saldo" => 0
                );

                $stock_opname_data_list[$timestamp][] = $row;

                $list_1[$timestamp][] = $row;
            }
            ksort($stock_opname_data_list);
        }
        ksort($list_1);

        // **
        // kelompokkan semua list ke dalam 1 array
        $list_2 = array();
        $saldo = $stock_awal_bulan;

        $total_masuk = 0;
        $total_keluar = 0;

        foreach ($list_1 as $timstmap => $list) {
            foreach ($list as $l) {

                $masuk = (float) $l["masuk"];
                $keluar = (float) $l["keluar"];

                if ($masuk > 0) {
                    $saldo += $masuk;
                }
                if ($keluar > 0) {
                    $saldo -= $keluar;
                }

                $total_masuk += $masuk;
                $total_keluar += $keluar;

                $row = $l;
                $row["masuk"] = number_format($masuk, 2);
                $row["keluar"] = number_format($keluar, 2);
                $row["saldo"] = number_format($saldo, 2);

                $list_2[] = $row;
            }
        }

        $final_data["body"]["stock_bulan_ini_data_list"] = $list_2;

        $final_data["footer"]["total_masuk"] = number_format($total_masuk, 2);
        $final_data["footer"]["total_keluar"] = number_format($total_keluar, 2);
        $final_data["footer"]["saldo_awal"] = number_format($total_stock_bulan_lalu, 2);
        $final_data["footer"]["saldo_akhir"] = number_format($saldo, 2);

        return $final_data;
    }


    // **
    // search
    // ========================================================================
    function item_search_flow_stock_in($jenis = "")
    {
        $kode = $this->input->get("kode");
        if (empty($kode) && (int) $kode != 0) return set_http_response_error(HTTP_BAD_REQUEST, "EMPTY_KODE");

        $filters = array();
        $filters["kode"] = $kode;
        $filters["arsip"] = 0;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $filters["tipe"] = "barang";
        $res = $this->item_engine->item_get_list($filters);
        if (count($res) == 0) return set_http_response_success("OK");

        $data_list = array();
        foreach ($res as $r) {
            $struktur_satuan_harga_json = $r["struktur_satuan_harga_json"];
            $struktur_satuan_harga_list = json_decode($struktur_satuan_harga_json, true);
            if (json_last_error_msg() != JSON_ERROR_NONE || !is_array($struktur_satuan_harga_list)) return set_http_response_error(HTTP_BAD_REQUEST, "Struktur satuan bermasalah");

            $satuan_list = array();
            $harga_list = array();
            foreach ($struktur_satuan_harga_list as $satuan => $l) {
                $row = array(
                    "name" => $satuan,
                    "label" => $l["satuan"],
                    "harga_jual" => $l["harga_jual"],
                    "harga_beli" => $l["harga_pokok"]
                );
                $satuan_list[] = $row;

                $harga_list[strtoupper($satuan)] = $l['harga_pokok'];
            }

            $data = array();
            $data["kode"] = $r["kode"];
            $data["nama"] = $r["nama"];
            $data["nama_kategori"] = $r["item_kategori_nama"];
            $data["satuan_list"] = $satuan_list;
            $data["harga_list"] = $harga_list;

            $data_list[] = $data;
        }

        return set_http_response_success("OK", $data_list);
    }

    function item_search_flow_stock_out()
    {
        $kode = $this->input->get("kode");
        if (empty($kode) && (int) $kode != 0) return set_http_response_error(HTTP_BAD_REQUEST, "EMPTY_KODE");

        $filters = array();
        $filters["kode"] = $kode;
        $filters["arsip"] = 0;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->item_engine->item_get_list($filters);
        if (count($res) == 0) return set_http_response_success("OK");

        $data_list = array();
        foreach ($res as $r) {
            $uuid = $r["uuid"];
            $current_stock = $this->item_engine->item_get_total_stock_for_item_uuid($uuid);
            $cek_stock_saat_penjualan = (int) $r["cek_stock_saat_penjualan"];

            $struktur_satuan_harga_json = $r["struktur_satuan_harga_json"];
            $struktur_satuan_harga_list = json_decode($struktur_satuan_harga_json, true);
            if (json_last_error_msg() != JSON_ERROR_NONE || !is_array($struktur_satuan_harga_list)) return set_http_response_error(HTTP_BAD_REQUEST, "Struktur satuan bermasalah");

            $satuan_list = array();
            $harga_list = array();
            $stock_list = array();

            $stock = $current_stock;
            foreach ($struktur_satuan_harga_list as $satuan => $l) {
                $harga_jual = $l["harga_jual"];
                $harga_pokok = $l["harga_pokok"];

                $row = array(
                    "name" => $satuan,
                    "label" => $l["satuan"],
                    "harga_jual" => $harga_jual,
                    "harga_pokok" => $harga_pokok
                );
                $satuan_list[] = $row;

                $stock = $stock / (int) $l['konversi'];
                if ($cek_stock_saat_penjualan == 0) $stock = 999999;

                $harga_list[strtoupper($satuan)] = $harga_jual;
                $stock_list[strtoupper($satuan)] = $stock;
            }

            $data = array();
            $data["kode"] = $r["kode"];
            $data["nama"] = $r["nama"];
            $data["nama_kategori"] = $r["item_kategori_nama"];
            $data["satuan_list"] = $satuan_list;
            $data["harga_list"] = $harga_list;
            $data["stock_list"] = $stock_list;
            $data["cek_stock"] = $cek_stock_saat_penjualan;

            $data_list[] = $data;
        }

        return set_http_response_success("OK", $data_list);
    }


    function item_get_detail_by_kode_and_tanggal()
    {
        $kode = get("kode");
        $tanggal = get("tanggal");
        $stock_opname_uuid = get("stock_opname_uuid");

        if (empty($kode) && (int) $kode != 0) return set_http_response_error(HTTP_BAD_REQUEST, "EMPTY_KODE");
        if (empty($tanggal)) return set_http_response_error(HTTP_BAD_REQUEST, "EMPTY_TANGGAL");

        $today_datetime = date("Y-m-d 23:59:59", strtotime($tanggal . " 00:00:00"));
        $filters = array();
        $filters["kode"] = $kode;
        $filters["arsip"] = 0;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->item_engine->item_get_list($filters);
        if (count($res) == 0) return set_http_response_success("OK");

        $data_list = array();
        foreach ($res as $r) {

            $uuid = $r["uuid"];
            $item_uuid = $uuid;
            $current_stock = $this->item_engine->item_get_total_stock_for_item_uuid($uuid);
            $cek_stock_saat_penjualan = (int) $r["cek_stock_saat_penjualan"];
            $struktur_satuan_harga_json = $r["struktur_satuan_harga_json"];
            $struktur_satuan_harga_list = json_decode($struktur_satuan_harga_json, true);
            if (json_last_error_msg() != JSON_ERROR_NONE || !is_array($struktur_satuan_harga_list)) return set_http_response_error(HTTP_BAD_REQUEST, "Struktur satuan bermasalah");


            // **
            // get current stock system satuan terkecil untuk item id
            $total_stock_list = $this->item_engine->get_total_stock_for_date_range_and_item_uuid_list(array($item_uuid), false, $today_datetime);

            $stock_system_satuan_terkecil = 0;
            if (isset($total_stock_list[$item_uuid])) {
                $stock_system_satuan_terkecil = (int) $total_stock_list[$item_uuid];
            }
            $stock_fisik_satuan_terkecil = $stock_system_satuan_terkecil;
            $stock = $current_stock;
            $satuan_terkecil = "";
            foreach ($struktur_satuan_harga_list as $satuan => $l) {
                if (empty($satuan_terkecil)) $satuan_terkecil = $satuan;
            }

            $data = array();
            $data["kode"] = $r["kode"];
            $data["nama"] = $r["nama"];
            $data["nama_kategori"] = $r["item_kategori_nama"];
            $data["satuan"] = $satuan_terkecil;
            $data["stock_system"] = $stock_system_satuan_terkecil;
            $data["stock_fisik"] = $stock_fisik_satuan_terkecil;
            $data["stock_selisih"] = $stock_fisik_satuan_terkecil - $stock_system_satuan_terkecil;

            if (!empty($stock_opname_uuid)) {
                $filters = array();
                $filters["stock_opname_uuid"] = $stock_opname_uuid;
                $filters["item_uuid"] = $item_uuid;
                $res = $this->item_engine->stock_opname_detail_get_list($filters);
                if (count($res) > 0) {
                    $res = $res[0];

                    $data["stock_system"] = (int) $res["stock_system_satuan_terkecil"];
                    $data["stock_fisik"] = (int) $res["stock_fisik_satuan_terkecil"];
                    $data["stock_selisih"] = (int) $res["stock_selisih_satuan_terkecil"];
                }
            }

            $data_list[] = $data;
        }

        return set_http_response_success("OK", $data_list);
    }
}
