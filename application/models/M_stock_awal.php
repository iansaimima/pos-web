<?php

use Mpdf\Tag\S;

defined('BASEPATH') or exit('No direct script access allowed');
class M_stock_awal extends MY_Model
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

        $this->item_engine = new Item_engine();
        $this->gudang_engine = new Gudang_engine();
        $this->settings_engine = new Settings_engine();

        $cabang_selected = get_session("cabang_selected");
        $this->cabang_selected_uuid = $cabang_selected["uuid"];
        $this->cabang_selected_kode = $cabang_selected["kode"];

        $user = get_session("user");
        $role = strtolower(get_session("role"));
        $this->actor_user_uuid = isset($user["uuid"]) ? $user["uuid"] : "";
        $this->actor_user_name = isset($user["name"]) ? $user["name"] : "";

        $privilege_list = isset($user["privilege_list"]) ? $user["privilege_list"] : array();

        $this->allow        = isset($privilege_list["allow_stock_awal"]) ? $privilege_list["allow_stock_awal"] : 0;
        $this->allow_create = isset($privilege_list["allow_stock_awal_create"]) ? $privilege_list["allow_stock_awal_create"] : 0;
        $this->allow_update = isset($privilege_list["allow_stock_awal_update"]) ? $privilege_list["allow_stock_awal_update"] : 0;
        $this->allow_delete = isset($privilege_list["allow_stock_awal_delete"]) ? $privilege_list["allow_stock_awal_delete"] : 0;
    }

    function get_tanggal_mulai_penggunaan_aplikasi(){

        // **
        // set tanggal berdasrakan bulan dan tahun mulai penggunaan aplikasi
        $cabang_selected = get_session("cabang_selected");        
        $bulan = $cabang_selected["bulan_mulai_penggunaan_aplikasi"];
        $tahun = $cabang_selected["tahun_mulai_penggunaan_aplikasi"];
        return "$tahun-$bulan-01";
    }


    function stock_awal_get_list($filters = array(), $gudang_uuid = "")
    {
        if (!$this->allow) return array();

        $filters["gudang_uuid"] = $gudang_uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->item_engine->stock_awal_get_list($filters, true, true);
        $no = isset($filters["start"]) ? (int) $filters["start"] : 0;

        $final_res = array();
        foreach ($res as $r) {
            $no++;

            $jumlah = (int) $r["jumlah"];
            $harga_beli_satuan = (float) $r["harga_beli_satuan"];
            $total = $jumlah * $harga_beli_satuan;

            $row = array();
            $row["no"] = $no;
            $row["uuid"] = trim($r["uuid"]);
            $row["item_kode"] = $r["item_kode"];
            $row["item_nama"] = $r["item_nama"];
            $row["item_kategori_nama"] = $r["item_kategori_nama"];
            $row["jumlah"] = $jumlah;
            $row["satuan"] = $r["satuan"];
            $row["harga_beli_satuan"] = number_format($harga_beli_satuan);
            $row["total"] = number_format($total);

            $final_res[] = $row;
        }

        return $final_res;
    }

    function stock_awal_get_filtered_total($filters = array(), $gudang_uuid = "")
    {
        if (!$this->allow) return 0;
        
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $filters["gudang_uuid"] = $gudang_uuid;
        $res = $this->item_engine->stock_awal_get_list($filters, false, true);
        return count($res);
    }

    function stock_awal_get_total($filters = array(), $gudang_uuid = "")
    {
        if (!$this->allow) return 0;

        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $filters["gudang_uuid"] = $gudang_uuid;
        $res = $this->item_engine->stock_awal_get_list($filters);
        return count($res);
    }

    function stock_awal_get($uuid = '')
    {
        if (!$this->allow) return array();

        // check uuid
        $filters = array();
        $filters["uuid"] = $uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->item_engine->stock_awal_get_list($filters);
        if (count($res) == 0) return array();
        $res = $res[0];

        return $res;
    }

    function stock_awal_delete($uuid = "")
    {
        if (!$this->allow_delete) return set_http_response_error(HTTP_FORBIDDEN, "Akses ditolak");

        // **
        // check uuid
        $filters = array();
        $filters["uuid"] = $uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->item_engine->stock_awal_get_list($filters);
        if (count($res) == 0) return set_http_response_error(HTTP_BAD_REQUEST, "Stock awal tidak ditemukan");
        $res = $res[0];
        $uuid = $res["uuid"];

        $item_uuid = $res["item_uuid"];
        $item_struktur_satuan_harga_json = $res["item_struktur_satuan_harga_json"];

        // **
        // get item data
        $filters = array();
        $filters["uuid"] = $item_uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $item_list = $this->item_engine->item_get_list($filters);
        if (count($item_list) > 0) {
            $item = $item_list[0];
            $item_struktur_satuan_harga_json = $item["struktur_satuan_harga_json"];
        }
        $item_struktur_satuan_harga_list = json_decode($item_struktur_satuan_harga_json, true);

        // **
        // proses yang dilakukan : 
        // 1. hapus stock awal
        // 2. ambil rata-rata harga beli satuan terkecil setelah hapus stock awal
        // 3. update cache harga beli pada table item
        // 4. ambil stock satuan terkecil setelah hapus stock awal
        // 5. update cache stock pada table item
        // 6. set ulang struktur satuan harga list
        // 7. update stuktur satuan harga list
        $this->db->trans_start();
        try {
            // **
            // 1. hapus stock awal
            $res = $this->item_engine->stock_awal_delete($uuid);
            if ($res == false ) throw new Exception("Gagal menghapus stock awal #001");

            // **
            // 2. ambil daftar harga beli satuan terkecil berdasarkan item id
            $rata_rata_harga_beli_satuan_terkecil = $this->item_engine->item_get_rata_rata_harga_beli_satuan_terkecil_for_item_uuid($item_uuid);

            // **
            // 3. update item cache harga pokok = rata_rata_harga_beli_satuan_terkecil
            $res = $this->item_engine->item_update_cache_harga_pokok($item_uuid, $rata_rata_harga_beli_satuan_terkecil);
            if ($res == false ) throw new Exception("Gagal menghapus stock awal #002");

            // **
            // 4. get current total stock satuan terkecil
            $stock = $this->item_engine->item_get_total_stock_for_item_uuid($item_uuid);

            // 5. update cache stock
            $res = $this->item_engine->item_update_cache_stock($item_uuid, $stock);
            if ($res == false ) throw new Exception("Gagal menghapus stock awal #003");

            // **
            // 6. set ulang struktur satuan dan harga
            $total_pcs_satuan_terkecil = 1;
            $new_struktur_satuan_harga_list = array();
            foreach ($item_struktur_satuan_harga_list as $key => $l) {
                $satuan = trim($l["satuan"]);
                $konversi = (int) $l["konversi"];
                $konversi_satuan = $l["konversi_satuan"];
                $harga_pokok = (float) $l["harga_pokok"];
                $harga_jual = (float) $l["harga_jual"];
                $margin = (float) $l["margin"];
                $stock = (int) $l["stock"];

                $total_pcs_satuan_terkecil *= $konversi;
                $harga_pokok = $total_pcs_satuan_terkecil * $rata_rata_harga_beli_satuan_terkecil;

                $new_struktur_satuan_harga_list[$key] = array(
                    "satuan" => $satuan,
                    "konversi" => $konversi,
                    "konversi_satuan" => $konversi_satuan,
                    "harga_pokok" => $harga_pokok,
                    "harga_jual" => $harga_jual,
                    "margin" => $margin,
                    "stock" => $stock
                );
            }

            // **
            // 7. update item struktur satuan harga
            $res = $this->item_engine->item_update_struktur_satuan_harga_json($item_uuid, json_encode($new_struktur_satuan_harga_list));
            if ($res == false ) throw new Exception("Gagal menghapus stock awal #004");
        } catch (Exception $e) {
            $this->db->trans_rollback();
            return set_http_response_error(501, "Error : " . $e->getMessage());
        }

        $this->db->trans_commit();
        return set_http_response_success("Stock awal telah dihapus");
    }

    function stock_awal_save()
    {
        $uuid = $this->input->post("uuid");
        $item_kode = $this->input->post("item_kode");
        $gudang_uuid = $this->input->post("gudang_uuid");
        $jumlah = to_number($this->input->post("jumlah"));
        $selected_satuan = $this->input->post("satuan");
        $harga_beli = to_number($this->input->post("harga_beli"));

        if ((int) $jumlah < 1) return set_http_response_error(HTTP_BAD_REQUEST, "Jumlah harus lebih dari 0");
        if (empty($selected_satuan)) return set_http_response_error(HTTP_BAD_REQUEST, "Satuan harus dipilih");
        
        $created = date("Y-m-d H:i:s");
        $creator_user_uuid = $this->actor_user_uuid;
        $creator_user_name = $this->actor_user_name;
        $number = 0;
        $number_formatted = "";
        
        $jam = date("H:i:s");

        // **
        // set gudang id
        // $settings = $this->settings_engine->get_settings('PERSEDIAAN_STOCK_AWAL_DEFAULT_GUDANG_ID');
        // $gudang_uuid = $settings["_value"];
        
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
        // set tanggal berdasrakan bulan dan tahun mulai penggunaan aplikasi
        $cabang_selected = get_session("cabang_selected");        
        $bulan = $cabang_selected["bulan_mulai_penggunaan_aplikasi"];
        $tahun = $cabang_selected["tahun_mulai_penggunaan_aplikasi"];

        if(empty($bulan)) return set_http_response_error(HTTP_BAD_REQUEST, "Bulan mulai penggunaan aplikasi belum di set pada menu Pengaturan -> Cabang");
        if(empty($tahun)) return set_http_response_error(HTTP_BAD_REQUEST, "Tahun mulai penggunaan aplikasi belum di set pada menu Pengaturan -> Cabang");

        $tanggal = "$tahun-$bulan-01";        
        
        $old_item_uuid = "";
        if (!empty($uuid)) {
            $filters = array();
            $filters["uuid"] = $uuid;
            $filters["cabang_uuid"] = $this->cabang_selected_uuid;
            $res = $this->item_engine->stock_awal_get_list($filters);
            if (count($res) == 0) return set_http_response_error(HTTP_BAD_REQUEST, "Stock awal tidak ditemukan");
            $res = $res[0];

            $uuid = $res["uuid"];
            $created = $res["created"];
            $creator_user_uuid = $res["creator_user_uuid"];
            $creator_user_name = $res["creator_user_name"];
            $number = (int) $res["number"];
            $number_formatted = trim($res["number_formatted"]);

            $old_item_uuid = $res["item_uuid"];

            $jam = date("H:i:s", strtotime($res["tanggal"]));
        }

        if (empty($uuid)) {
            if($tanggal != date("Y-m-01")) return set_http_response_error(HTTP_BAD_REQUEST, "Hanya bisa melakukan penginputan stock awal pada periode " . get_nama_bulan((int) $bulan) . " $tahun");

            if (!$this->allow_create) return set_http_response_error(HTTP_FORBIDDEN, "Akses ditolak");
        } else {
            if (!$this->allow_update) return set_http_response_error(HTTP_FORBIDDEN, "Akses ditolak");
        }

        // **
        // check item uuid
        $filters = array();
        $filters["arsip"] = 0;
        $filters["kode"] = $item_kode;
        $filters["tipe"] = "Barang";
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->item_engine->item_get_list($filters);
        if (count($res) == 0) return set_http_response_error(HTTP_BAD_REQUEST, "Item tidak ditemukan atau sudah diarsip");
        $res = $res[0];
        $item_uuid = $res["uuid"];
        $item_nama = $res["nama"];
        $item_kode = $res["kode"];
        $item_barcode = $res["barcode"];
        $item_struktur_satuan_harga_json = $res["struktur_satuan_harga_json"];
        $item_tipe = $res["tipe"];
        $margin_persen = (float) $res['margin_persen'];
        $item_kategori_uuid = $res["item_kategori_uuid"];
        $item_kategori_nama = $res["item_kategori_nama"];
        $struktur_satuan_harga_list = json_decode($item_struktur_satuan_harga_json, true);
        if (!is_array($struktur_satuan_harga_list) || count($struktur_satuan_harga_list) == 0) return set_http_response_error(HTTP_BAD_REQUEST, "Item belum memiliki struktur satuan dan harga");

        if(!empty($old_item_uuid) && $old_item_uuid != $item_uuid) {
            return set_http_response_error(HTTP_BAD_REQUEST, "Mengubah item pada detail stock awal tidak dibolehkan");
        }

        // remove key from array
        $new_struktur_satuan_harga_list = array();
        $satuan_list = array();
        $used_satuan_list = array();

        foreach ($struktur_satuan_harga_list as $satuan => $s) {
            $new_struktur_satuan_harga_list[] = $s;
            $satuan_list[] = $satuan;
        }
        krsort($new_struktur_satuan_harga_list);
        if (!in_array($selected_satuan, $satuan_list)) return set_http_response_error(HTTP_BAD_REQUEST, "Satuan yang dipilih untuk item tidak tersedia : $selected_satuan");



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

            $selected_struktur_satuan_harga_list[$satuan] = $s;

            $total_pcs_satuan_terkecil *= $s["konversi"];
            if ($satuan_found) break;
        }

        $harga_beli_satuan_terkecil = $harga_beli / $total_pcs_satuan_terkecil;
        $jumlah_satuan_terkecil = $total_pcs_satuan_terkecil * $jumlah;

        $total = $jumlah * $harga_beli;

        $data = array(
            "uuid"     => $uuid,
            "created" => $created,
            "creator_user_uuid" => $creator_user_uuid,
            "creator_user_name" => $creator_user_name,
            "last_updated" => date("Y-m-d H:i:s"),
            "last_updated_user_uuid" => $this->actor_user_uuid,
            "last_updated_user_name" => $this->actor_user_name,
            "tanggal" => $tanggal . " $jam",
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
            
            "gudang_uuid" => $gudang_uuid,
            "gudang_kode" => $gudang_kode,
            "gudang_nama" => $gudang_nama,

            "total" => $total,
            "cabang_uuid" => $this->cabang_selected_uuid,
        );

        $this->db->trans_start();
        try {
            // **
            // simpan stock awal
            $res = $this->item_engine->stock_awal_save($data);
            if ($res == false ) throw new Exception("Gagal menyimpan stotck awal #001");
            if (empty($uuid)) $uuid = $res;

            // **
            // ambil daftar harga beli satuan terkecil berdasarkan item id
            $rata_rata_harga_beli_satuan_terkecil = $this->item_engine->item_get_rata_rata_harga_beli_satuan_terkecil_for_item_uuid($item_uuid);
            if ($rata_rata_harga_beli_satuan_terkecil == 0) $rata_rata_harga_beli_satuan_terkecil = $harga_beli_satuan_terkecil;

            // **
            // update item cache harga pokok = rata_rata_harga_beli_satuan_terkecil
            $res = $this->item_engine->item_update_cache_harga_pokok($item_uuid, $rata_rata_harga_beli_satuan_terkecil);
            if ($res == false ) throw new Exception("Gagal menyimpan stock awal #002");

            // **
            // hitung ulang struktur satuan dan harga
            $new_struktur_satuan_harga_list = array();
            $satuan_terkecil = "";
            foreach ($struktur_satuan_harga_list as $satuan => $l) {
                $satuan_terkecil = $satuan;
                break;
            }

            // set harga untuk satuan terkecil
            $struktur_satuan_harga_list[strtoupper($satuan_terkecil)]["harga_pokok"] = $rata_rata_harga_beli_satuan_terkecil;

            // **
            // set ulang struktur satuan dan harga
            $total_pcs_satuan_terkecil = 1;
            foreach ($struktur_satuan_harga_list as $key => $l) {
                $satuan = trim($l["satuan"]);
                $konversi = (int) $l["konversi"];
                $konversi_satuan = $l["konversi_satuan"];
                $harga_pokok = (float) $l["harga_pokok"];
                $harga_jual = (float) $l["harga_jual"];
                $margin = (float) $l["margin"];
                $stock = (int) $l["stock"];

                $total_pcs_satuan_terkecil *= $konversi;
                $harga_pokok = $total_pcs_satuan_terkecil * $rata_rata_harga_beli_satuan_terkecil;

                $margin_nilai = 0;
                if($margin_persen > 0 && $harga_pokok  >0) {
                    $margin_nilai = ($harga_pokok * $margin_persen) / 100;
                }
                $harga_jual = $harga_pokok + $margin_nilai;

                $new_struktur_satuan_harga_list[$key] = array(
                    "satuan" => $satuan,
                    "konversi" => $konversi,
                    "konversi_satuan" => $konversi_satuan,
                    "harga_pokok" => $harga_pokok,
                    "harga_jual" => $harga_jual,
                    "margin" => $margin,
                    "stock" => $stock
                );
            }

            // **
            // update item struktur satuan harga
            $res = $this->item_engine->item_update_struktur_satuan_harga_json($item_uuid, json_encode($new_struktur_satuan_harga_list));
            if ($res == false ) throw new Exception("Gagal menyimpan stock awal #003");

            // **
            // get current total stock satuan terkecil
            $stock = $this->item_engine->item_get_total_stock_for_item_uuid($item_uuid);

            // update cache stock
            $res = $this->item_engine->item_update_cache_stock($item_uuid, $stock);
            if ($res == false ) throw new Exception("Gagal menyimpan stock awal #004");
        } catch (Exception $e) {
            $this->db->trans_rollback();
            return set_http_response_error(501, "Error : " . $e->getMessage());
        }

        $this->db->trans_commit();
        return set_http_response_success("Stock awal telah disimpan", array(), trim($uuid));
    }
}
