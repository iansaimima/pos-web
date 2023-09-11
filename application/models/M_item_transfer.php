<?php

defined('BASEPATH') or exit('No direct script access allowed');
class M_item_transfer extends MY_Model
{

    private $item_engine;
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
    function __construct()
    {
        parent::__construct();

        $this->settings_engine = new Settings_engine();
        $this->item_engine = new Item_engine();
        $this->gudang_engine = new Gudang_engine();

        $cabang_selected = get_session("cabang_selected");
        $this->cabang_selected_uuid = $cabang_selected["uuid"];
        $this->cabang_selected_kode = $cabang_selected["kode"];

        $user = get_session("user");
        $role = strtolower(get_session("role"));
        $this->actor_user_uuid = isset($user["uuid"]) ? $user["uuid"] : "";
        $this->actor_user_name = isset($user["name"]) ? $user["name"] : "";

        $privilege_list = isset($user["privilege_list"]) ? $user["privilege_list"] : array();

        $this->allow        = isset($privilege_list["allow_item_transfer"]) ? $privilege_list["allow_item_transfer"] : 0;
        $this->allow_create = isset($privilege_list["allow_item_transfer_create"]) ? $privilege_list["allow_item_transfer_create"] : 0;
        $this->allow_update = isset($privilege_list["allow_item_transfer_update"]) ? $privilege_list["allow_item_transfer_update"] : 0;
        $this->allow_delete = isset($privilege_list["allow_item_transfer_delete"]) ? $privilege_list["allow_item_transfer_delete"] : 0;
        $this->allow_print  = isset($privilege_list["allow_item_transfer_print"]) ? $privilege_list["allow_item_transfer_print"] : 0;
    }

    function item_transfer_get_list($filters = array(), $dari_gudang_uuid = "")
    {
        if (!$this->allow) return array();

        if(!empty($dari_gudang_uuid)) $filters["dari_gudang_uuid"] = $dari_gudang_uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->item_engine->item_transfer_get_list($filters, true, true);
        $no = isset($filters["start"]) ? (int) $filters["start"] : 0;

        $final_res = array();
        foreach ($res as $r) {
            $no++;

            $row = array();
            $row["no"] = $no;
            $row["uuid"] = trim($r["uuid"]);
            $row["number_formatted"]    = $r["number_formatted"];
            $row["tanggal"]             = date("d M Y", strtotime($r["tanggal"]));
            $row["dari_gudang_nama"]    = $r["dari_gudang_nama"];
            $row["ke_gudang_nama"]      = $r["ke_gudang_nama"];
            $row["keterangan"]          = $r["keterangan"];

            $final_res[] = $row;
        }

        return $final_res;
    }

    function item_transfer_get_filtered_total($filters = array(), $dari_gudang_uuid = "")
    {
        if (!$this->allow) return 0;

        if(!empty($dari_gudang_uuid)) $filters["dari_gudang_uuid"] = $dari_gudang_uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->item_engine->item_transfer_get_list($filters, false, true);
        return count($res);
    }

    function item_transfer_get_total($filters = array(), $dari_gudang_uuid = "")
    {
        if (!$this->allow) return 0;

        if(!empty($dari_gudang_uuid)) $filters["dari_gudang_uuid"] = $dari_gudang_uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->item_engine->item_transfer_get_list($filters);
        return count($res);
    }

    function item_transfer_get($uuid = "")
    {
        if (!$this->allow) return array();

        $filters = array();
        $filters["uuid"] = $uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->item_engine->item_transfer_get_list($filters);
        if (count($res) == 0) return array();
        $item_transfer = $res[0];
        $item_transfer_uuid = $item_transfer["uuid"];

        // **
        // get item_transfer detail list for item_transfer id
        $filters = array();
        $filters["item_transfer_uuid"] = $item_transfer_uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $detail_list = $this->item_engine->item_transfer_detail_get_list($filters);
        $item_transfer["detail"] = $detail_list;

        return $item_transfer;
    }

    function item_transfer_delete($uuid = "")
    {
        if (!$this->allow_delete) return set_http_response_error(HTTP_FORBIDDEN, "Akses ditolak");

        $filters = array();
        $filters["uuid"] = $uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->item_engine->item_transfer_get_list($filters);
        if (count($res) == 0) return set_http_response_error(HTTP_BAD_REQUEST, "Item transfer tidak ditemukan");
        $res = $res[0];
        $uuid = $res["uuid"];
        $item_transfer_uuid = $uuid;

        // **
        // get item id list from penjualan detail by penjualan id
        $filters = array();
        $filters["item_transfer_uuid"] = $item_transfer_uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->item_engine->item_transfer_detail_get_list($filters);

        $item_uuid_list = array();
        foreach ($res as $r) {
            $item_uuid = $r['item_uuid'];
            $item_uuid_list[] = $item_uuid;
        }

        $this->db->trans_start();
        try {
            // **
            // hapus Item transfer
            $res = $this->item_engine->item_transfer_delete($item_transfer_uuid);
            if ($res == false ) throw new Exception("Gagal menghapus Item transfer #001");

            // **
            // hapus Item transfer detail
            $res = $this->item_engine->item_transfer_detail_delete_by_item_transfer_uuid($item_transfer_uuid);
            if ($res == false ) throw new Exception("Gagal menghapus Item transfer #002");

            foreach ($item_uuid_list as $index => $item_uuid) {
                // **
                // get current total stock satuan terkecil
                $stock = $this->item_engine->item_get_total_stock_for_item_uuid($item_uuid);

                // update cache stock
                $res = $this->item_engine->item_update_cache_stock($item_uuid, $stock);
                if ($res == false ) throw new Exception("Gagal menghapus Item transfer #003");
            }
        } catch (Exception $e) {
            $this->db->trans_rollback();
            return set_http_response_error(501, "Error : " . $e->getMessage());
        }

        $this->db->trans_commit();
        return set_http_response_success("Item transfer telah dihapus");
    }

    function item_transfer_save()
    {
        $uuid               = $this->input->post("uuid");
        $dari_gudang_uuid   = $this->input->post("dari_gudang_uuid");
        $ke_gudang_uuid     = $this->input->post("ke_gudang_uuid");
        $tanggal            = $this->input->post("tanggal");
        $item_detail_json   = $this->input->post("item_detail");
        $keterangan         = $this->input->post("keterangan");

        // **
        // validasi
        if (empty($tanggal)) return set_http_response_error(HTTP_BAD_REQUEST, "Tanggal tidak valid");
        $item_detail_list = json_decode($item_detail_json, true);
        if (!is_array($item_detail_list)) $item_detail_list = array();
        if (count($item_detail_list) == 0) return set_http_response_error(HTTP_BAD_REQUEST, "Belum ada item yang dipilih pada Item transfer");

        if($dari_gudang_uuid == $ke_gudang_uuid) {
            return set_http_response_error(HTTP_BAD_REQUEST, "Gudang sumber dan gudang tujuan tidak boleh sama");
        }

        
        if(strtotime($tanggal) > time()) {
            return set_http_response_error(HTTP_BAD_REQUEST, "Tanggal Item Transfer tidak boleh lebih dari tanggal hari ini");
        }

        $tahun = date("Y", strtotime($tanggal . " 00:00:00"));
        $jam = date("H:i:s");

        $today_datetime = date("Y-m-d 23:59:59", strtotime($tanggal . " 00:00:00"));
        
        // **
        // check dari gudang uuid
        $filters = array();
        $filters["uuid"] = $dari_gudang_uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->gudang_engine->gudang_get_list($filters);
        if (count($res) == 0) return set_http_response_error(HTTP_BAD_REQUEST, "Gudang sumber tidak ditemukan");
        $res = $res[0];
        $dari_gudang_uuid = $res["uuid"];
        $dari_gudang_kode = $res["kode"];
        $dari_gudang_nama = $res["nama"];
        
        // **
        // check ke gudang uuid
        $filters = array();
        $filters["uuid"] = $ke_gudang_uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->gudang_engine->gudang_get_list($filters);
        if (count($res) == 0) return set_http_response_error(HTTP_BAD_REQUEST, "Gudang tujuan tidak ditemukan");
        $res = $res[0];
        $ke_gudang_uuid = $res["uuid"];
        $ke_gudang_kode = $res["kode"];
        $ke_gudang_nama = $res["nama"];

                
        $created = date("Y-m-d H:i:s");
        $creator_user_uuid = $this->actor_user_uuid;
        $creator_user_name = $this->actor_user_name;
        $number = 0;
        $number_formatted = PREFIX_ITEM_TRANSFER . "/" . $this->cabang_selected_kode . "/" . $dari_gudang_kode . "/" . microtime_();
        $current_item_uuid_list = array();
        $old_tahun = $tahun;
        $old_dari_gudang_kode = $dari_gudang_kode;
        if (!empty($uuid)) {
            $filters = array();
            $filters["uuid"] = $uuid;
            $filters["cabang_uuid"] = $this->cabang_selected_uuid;
            $res = $this->item_engine->item_transfer_get_list($filters);
            if (count($res) == 0) return set_http_response_error(HTTP_BAD_REQUEST, "Item transfer tidak ditemukan");
            $res = $res[0];
            $uuid = $res["uuid"];
            $item_transfer_uuid = $uuid;
            $created = $res["created"];
            $creator_user_uuid = $res["creator_user_uuid"];
            $creator_user_name = $res["creator_user_name"];
            $number = (int) $res["number"];
            $number_formatted = trim($res["number_formatted"]);
            $number_formatted = PREFIX_ITEM_TRANSFER . "/" . $this->cabang_selected_kode . "/" . $dari_gudang_kode . "/" . microtime_();

            $old_dari_gudang_kode = $res["dari_gudang_kode"];
            $old_tahun = $res["tahun"];

            // **
            // get current Item transfer detail list
            $filters = array();
            $filters["item_transfer_uuid"] = $item_transfer_uuid;
            $filters["cabang_uuid"] = $this->cabang_selected_uuid;
            $list = $this->item_engine->item_transfer_detail_get_list($filters);
            foreach ($list as $l) {
                $current_item_uuid = $l['item_uuid'];
                $current_item_uuid_list[] = $current_item_uuid;
            }

            $jam = date("H:i:s", strtotime($res["tanggal"]));
        }

        if (empty($uuid)) {
            if (!$this->allow_create) return set_http_response_error(HTTP_FORBIDDEN, "Akses ditolak");
        } else {
            if (!$this->allow_update) return set_http_response_error(HTTP_FORBIDDEN, "Akses ditolak");
        }


        $final_item_list = array();
        $sub_total = 0;
        $item_uuid_list = array();

        $jumlah_satuan_terkecil_item = array();
        $current_stock_item = array();

        // **
        // -- generate item_transfer detail
        // -- genereate item id list
        foreach ($item_detail_list as $i) {
            $kode = $i["item_code"];
            $selected_satuan = $i["satuan"];
            $jumlah = to_number($i["jumlah"]);
            $harga_beli = to_number($i["harga_beli"]);
            
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
            $item_kategori_uuid = $res["item_kategori_uuid"];
            $item_kategori_nama = trim($res["item_kategori_nama"]);
            $item_jenis_perhitungan_harga_jual = trim($res["jenis_perhitungan_harga_jual"]);
            $struktur_satuan_harga_list = json_decode($item_struktur_satuan_harga_json, true);
            if (!is_array($struktur_satuan_harga_list) || count($struktur_satuan_harga_list) == 0) return set_http_response_error(HTTP_BAD_REQUEST, "Item dengan kode $kode belum memiliki struktur satuan dan harga");

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

            // ** 
            // get total pcs untuk satuan terkecil
            $satuan_found = false;
            $selected_struktur_satuan_harga_list = array();
            $total_pcs_satuan_terkecil = 1;
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
                if(!$satuan_found) continue;

                $selected_struktur_satuan_harga_list[$satuan] = $s;
                $total_pcs_satuan_terkecil *= $s["konversi"];

                // if ($satuan_found) break;
            }
            $harga_beli_satuan_terkecil = $i["harga_beli"] / $total_pcs_satuan_terkecil;

            $jumlah_satuan_terkecil = $total_pcs_satuan_terkecil * $jumlah;
            if (!isset($jumlah_satuan_terkecil_item[$kode])) $jumlah_satuan_terkecil_item[$kode] = 0;
            $jumlah_satuan_terkecil_item[$kode] += $jumlah_satuan_terkecil;

            // **
            // get total stock satuan terkecil dari gudang sumber
            $currennt_item_stock_satuan_terkecil = $this->item_engine->item_get_total_stock_for_item_uuid($item_uuid, $dari_gudang_uuid);
            if($currennt_item_stock_satuan_terkecil < $jumlah_satuan_terkecil) {
                $satuan_terkecil = $satuan_list[0];
                return set_http_response_error(
                    HTTP_BAD_REQUEST, 
                    "Stock item $item_kode pada Gudang $dari_gudang_nama tidak cukup <br/>
                    Total Stock : " . number_format($currennt_item_stock_satuan_terkecil) . " $satuan_terkecil <br/>
                    Total Jumlah : " . number_format($jumlah_satuan_terkecil) . " $satuan_terkecil"
                );
            }

            // **
            // hitung cache total
            $total = $jumlah * ($harga_beli);
            $sub_total += $total;

            $item_transfer_detail_data_list = array(
                "uuid"     => "",
                
                "created" => date("Y-m-d H:i:s"),
                "creator_user_uuid" => $this->actor_user_uuid,
                "creator_user_name" => $this->actor_user_name,
                
                "last_updated" => date("Y-m-d H:i:s"),
                "last_updated_user_uuid" => $this->actor_user_uuid,
                "last_updated_user_name" => $this->actor_user_name,
                
                "item_transfer_uuid" => $uuid,
                "item_uuid" => $item_uuid,
                "item_kode" => $item_kode,
                "item_barcode" => $item_barcode,
                "item_nama" => $item_nama,
                "item_struktur_satuan_harga_json" => $item_struktur_satuan_harga_json,
                "item_tipe" => $item_tipe,
                
                "item_kategori_uuid" => $item_kategori_uuid,
                "item_kategori_nama" => $item_kategori_nama,

                "jumlah" => $jumlah,
                "satuan" => $selected_satuan,
                
                "harga_beli_satuan" => $harga_beli,
                "harga_beli_satuan_terkecil" => $harga_beli_satuan_terkecil,
                "jumlah_satuan_terkecil" => $jumlah_satuan_terkecil,
                
                "cabang_uuid" => $this->cabang_selected_uuid,
            );

            $final_item_list[] = $item_transfer_detail_data_list;
        }

        // **
        // ambil deleted item id list
        $_temp = array_diff($current_item_uuid_list, $item_uuid_list);
        $deleted_item_data_list = array();
        foreach ($_temp as $index => $_item_uuid) {

            // **
            // ambil daftar harga beli satuan terkecil berdasarkan item id
            $rata_rata_harga_beli_satuan_terkecil = $this->item_engine->item_get_rata_rata_harga_beli_satuan_terkecil_for_item_uuid($_item_uuid);

            // **
            // get current total stock satuan terkecil
            $stock = $this->item_engine->item_get_total_stock_for_item_uuid($_item_uuid);

            $deleted_item_data_list[$_item_uuid]["rata_rata_harga_beli_satuan_terkecil"] = $rata_rata_harga_beli_satuan_terkecil;
            $deleted_item_data_list[$_item_uuid]["stock"] = $stock;
        }

        $item_transfer_data = array(
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
            
            "dari_gudang_uuid" => $dari_gudang_uuid,
            "dari_gudang_kode" => $dari_gudang_kode,
            "dari_gudang_nama" => $dari_gudang_nama,
            
            "ke_gudang_uuid" => $ke_gudang_uuid,
            "ke_gudang_kode" => $ke_gudang_kode,
            "ke_gudang_nama" => $ke_gudang_nama,

            "keterangan" => $keterangan,

            "old_tahun" => $old_tahun, 
            "old_dari_gudang_kode" => $old_dari_gudang_kode,

            "cabang_uuid" => $this->cabang_selected_uuid,
            "cabang_kode" => $this->cabang_selected_kode,
        );

        $this->db->trans_start();
        try {
            // **
            // hapus semua item_transfer detail untuk item_transfer id jika item_transfer id != 0
            if (!empty($uuid)) {
                $res = $this->item_engine->item_transfer_detail_delete_by_item_transfer_uuid($uuid);
                if ($res == false ) throw new Exception("Gagal menyimpan Item transfer #001");
            }

            // **
            // simpan item_transfer
            $res = $this->item_engine->item_transfer_save($item_transfer_data);
            if ($res == false ) throw new Exception("Gagal menyimpan Item transfer #002");
            if (empty($uuid)) {
                $uuid = $res;
                $item_transfer_uuid = $uuid;
            }

            // **
            // simpan Item transfer detail
            foreach ($final_item_list as $item_transfer_detail) {
                $item_transfer_detail["item_transfer_uuid"] = $item_transfer_uuid;
                $item_uuid = $item_transfer_detail["item_uuid"];

                // **
                // simpan Item transfer detail
                $res = $this->item_engine->item_transfer_detail_save($item_transfer_detail);
                if ($res == false ) throw new Exception("Gagal menyimpan Item transfer #003");

                // **
                // get current total stock satuan terkecil
                $stock = $this->item_engine->item_get_total_stock_for_item_uuid($item_uuid);

                // update cache stock
                $res = $this->item_engine->item_update_cache_stock($item_uuid, $stock);
                if ($res == false ) throw new Exception("Gagal menyimpan Item transfer #004");
            }


            // **
            // update stock  untuk item yang dihapus
            foreach ($deleted_item_data_list as $_item_uuid => $data) {

                // **
                // get current total stock satuan terkecil
                $stock = $this->item_engine->item_get_total_stock_for_item_uuid($item_uuid);

                // update cache stock
                $res = $this->item_engine->item_update_cache_stock($_item_uuid, $stock);
                if ($res == false ) throw new Exception("Gagal menyimpan Item transfer #005");
            }
        } catch (Exception $e) {
            $this->db->trans_rollback();
            return set_http_response_error(501, "Error : " . $e->getMessage());
        }

        $this->db->trans_commit();
        return set_http_response_success("Item transfer telah disimpan", array(), trim($uuid));
    }

    function item_get_detail_by_kode()
    {
        $kode = $this->input->get("kode");
        if (empty($kode)) return set_http_response_error(HTTP_BAD_REQUEST, "EMPTY_KODE");

        $filters = array();
        $filters["kode"] = $kode;
        $filters["arsip"] = 0;
        $filters["tipe"] = "Barang";
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->item_engine->item_get_list($filters);
        if (count($res) == 0) return set_http_response_error(HTTP_BAD_REQUEST, "NO_DATA");
        $res = $res[0];
        if (strtolower($res['tipe']) == "jasa") return set_http_response_error(HTTP_BAD_REQUEST, "Item dengan tipe jasa tidak bolehkan");

        $struktur_satuan_harga_json = $res["struktur_satuan_harga_json"];
        $struktur_satuan_harga_list = json_decode($struktur_satuan_harga_json, true);
        if (json_last_error_msg() != JSON_ERROR_NONE || !is_array($struktur_satuan_harga_list)) return set_http_response_error(HTTP_BAD_REQUEST, "Struktur satuan bermasalah");
        
        $satuan_list = array();
        $harga_list = array();
        foreach ($struktur_satuan_harga_list as $satuan => $l) {
            $row = array(
                "name" => $satuan,
                "label" => $l["satuan"],
                "harga_beli" => (double)$l["harga_pokok"] > 0  ? round($l["harga_pokok"]) : 0
            );
            $satuan_list[] = $row;

            $harga_list[strtoupper($satuan)] = (double) $l['harga_pokok'] > 0 ? round($l['harga_pokok']) : 0;
        }

        $data = array();
        $data["kode"] = $res["kode"];
        $data["nama"] = $res["nama"];
        $data["nama_kategori"] = $res["item_kategori_nama"];
        $data["satuan_list"] = $satuan_list;
        $data["harga_list"] = $harga_list;

        return set_http_response_success("OK", $data);
    }

    function item_transfer_cetak($uuid = ""){
        
        $settings = get_session("settings");
        $header = array(
            "nama_toko" => $settings["TOKO_NAMA"]["_value"],
            "alamat_toko" => $settings["TOKO_ALAMAT"]["_value"],
            "no_telepon_toko" => $settings["TOKO_NO_TELEPON"]["_value"],
        );
        $body = array(
            "judul" => "Item transfer",
            "no" => "",
            "tanggal" => "",
            "keterangan" => "",
            "dari_gudang" => "", 
            "ke_gudang" => "",
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
        if (!$this->allow) return $result;

        $filters = array();
        $filters["uuid"] = $uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->item_engine->item_transfer_get_list($filters);
        if (count($res) == 0) return $result;
        $item_transfer = $res[0];
        $item_transfer_uuid = $item_transfer["uuid"];
        $number_formatted = $item_transfer["number_formatted"];
        
        $dari_gudang_uuid = $item_transfer["dari_gudang_uuid"];
        $dari_gudang_kode = $item_transfer["dari_gudang_kode"];
        $dari_gudang_nama = $item_transfer["dari_gudang_nama"];

        $ke_gudang_uuid = $item_transfer["ke_gudang_uuid"];
        $ke_gudang_kode = $item_transfer["ke_gudang_kode"];
        $ke_gudang_nama = $item_transfer["ke_gudang_nama"];
        
        $tanggal = date("d/m/Y", strtotime($item_transfer["tanggal"]));
        $keterangan = $item_transfer["keterangan"];
        $created = date("d/m/Y H:i:s", strtotime($item_transfer["created"]));
        $creator_user_name = $item_transfer["creator_user_name"];
        $last_updated = date("d/m/Y H:i:s", strtotime($item_transfer["last_updated"]));
        $last_updated_user_name = $item_transfer["last_updated_user_name"];
        $printed = date("d/m/Y H:i:s");
        $printed_user_name = $this->actor_user_name;

        $footer = array(
            "created" => $created, 
            "creator_user_name" => $creator_user_name, 
            "last_updated" => $last_updated, 
            "last_updated_user_name" => $last_updated_user_name, 
            "printed" => $printed, 
            "printed_user_name" => $printed_user_name
        );

        // **
        // get item_transfer detail list for item_transfer id
        $filters = array();
        $filters["item_transfer_uuid"] = $item_transfer_uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $detail_list = $this->item_engine->item_transfer_detail_get_list($filters);
        $item_transfer["detail"] = $detail_list;
        
        $body["judul"]          = "Item transfer";
        $body["tanggal"]        = $tanggal;
        $body["no"]             = $number_formatted;
        $body["keterangan"]     = $keterangan;
        $body["dari_gudang"]    = $dari_gudang_kode . " - " . $dari_gudang_nama;
        $body["ke_gudang"]      = $ke_gudang_kode . " - " . $ke_gudang_nama;

        $contents = array();
        $no = 1;
        foreach($detail_list as $dl){
            $jumlah = (int) $dl["jumlah"];
            $row = array(
                "no" => $no, 
                "kode" => $dl["item_kode"], 
                "nama" => $dl["item_nama"], 
                "kategori" => $dl["item_kategori_nama"], 
                "jumlah" => number_format($jumlah, 0, ",", "."), 
                "satuan" => ucwords($dl["satuan"]), 
            );

            $contents[] = $row;
            $no++;
        }

        $body["content"] = $contents;
        $result = array(
            "header" => $header, 
            "body" => $body, 
            "footer" => $footer
        );

        return $result;
    }

    function laporan_item_transfer(){
        $start_date = $this->input->get("start_date");
        $end_date = $this->input->get("end_date");
        $dari_gudang_uuid = $this->input->get("dari_gudang_uuid");
        $ke_gudang_uuid = $this->input->get("ke_gudang_uuid");

        
        $list = $this->settings_engine->get_all_settings();
        $settings_list = array();
        foreach($list as $l){
            $_key = $l['_key'];
            $settings_list[$_key] = $l;
        }

        $filter_dari_gudang_uuid = "";
        $filter_dari_gudang_nama = "";
        $filter_ke_gudang_uuid = "";
        $filter_ke_gudang_nama = "";

        // **
        // check dari_gudang_uuid
        if (!empty($dari_gudang_uuid)) {
            $filters = array();
            $filters["uuid"] = $dari_gudang_uuid;
            $filters["cabang_uuid"] = $this->cabang_selected_uuid;
            $res = $this->gudang_engine->gudang_get_list($filters);
            if (count($res) > 0) {
                $res = $res[0];
                $filter_dari_gudang_uuid = $res["uuid"];
                $filter_dari_gudang_nama = $res["kode"] . " - " . $res["nama"];
            }
        }

        // **
        // check ke_gudang_uuid
        if (!empty($ke_gudang_uuid)) {
            $filters = array();
            $filters["uuid"] = $ke_gudang_uuid;
            $filters["cabang_uuid"] = $this->cabang_selected_uuid;
            $res = $this->gudang_engine->gudang_get_list($filters);
            if (count($res) > 0) {
                $res = $res[0];
                $filter_ke_gudang_uuid = $res["uuid"];
                $filter_ke_gudang_nama = $res["kode"] . " - " . $res["nama"];
            }
        }

        $filters = array();
        $filters["cabang_uuid"]         = $this->cabang_selected_uuid;
        $filters["start_date"]          = $start_date . " 00:00:00";
        $filters["end_date"]            = $end_date . " 23:59:59";  
        $filters["dari_gudang_uuid"]    = $dari_gudang_uuid;
        $filters["ke_gudang_uuid"]      = $ke_gudang_uuid;
        $detail_list = $this->item_engine->item_transfer_detail_get_list($filters);

        $contents = array();
        $no = 1;

        foreach($detail_list as $dl){
            $jumlah = (double) $dl["jumlah"];

            $row = array(
                "no" => $no, 
                "number_formatted" => $dl["item_transfer_number_formatted"], 
                "kode" => $dl["item_kode"], 
                "nama" => $dl["item_nama"], 
                "dari_gudang_kode" => $dl["dari_gudang_kode"], 
                "dari_gudang_nama" => $dl["dari_gudang_nama"], 
                "ke_gudang_kode" => $dl["ke_gudang_kode"], 
                "ke_gudang_nama" => $dl["ke_gudang_nama"], 
                "tanggal" => date("d-m-Y", strtotime($dl["tanggal"])), 
                "jumlah" => number_format($jumlah),
                "satuan" => $dl["satuan"],
            );

            $contents[] = $row;
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
                "dari_gudang_nama" => $filter_dari_gudang_nama,
                "ke_gudang_nama" => $filter_ke_gudang_nama,
            ),

            "body" => $contents,

            "footer" => array(),
        );

        return $final_data;
    }
}
