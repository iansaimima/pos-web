<?php

defined('BASEPATH') or exit('No direct script access allowed');
class M_penjualan_retur extends MY_Model
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
    private $allow_detail_create;
    private $allow_detail_update;
    private $allow_detail_delete;
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

        $this->allow        = isset($privilege_list["allow_transaksi_penjualan_retur"]) ? $privilege_list["allow_transaksi_penjualan_retur"] : 0;
        $this->allow_create = isset($privilege_list["allow_transaksi_penjualan_retur_create"]) ? $privilege_list["allow_transaksi_penjualan_retur_create"] : 0;
        $this->allow_update = isset($privilege_list["allow_transaksi_penjualan_retur_update"]) ? $privilege_list["allow_transaksi_penjualan_retur_update"] : 0;
        $this->allow_delete = isset($privilege_list["allow_transaksi_penjualan_retur_delete"]) ? $privilege_list["allow_transaksi_penjualan_retur_delete"] : 0;
    }

    function penjualan_retur_get_list($filters = array(), $gudang_uuid = "")
    {
        if (!$this->allow) return array();

        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        if(!empty($gudang_uuid)) $filters["gudang_uuid"] = $gudang_uuid;
        $res = $this->transaksi_engine->penjualan_retur_get_list($filters, true, true);
        $no = isset($filters["start"]) ? (int) $filters["start"] : 0;

        $final_res = array();
        foreach ($res as $r) {
            $no++;

            $row = array();
            $row["no"] = $no;
            $row["uuid"] = trim($r["uuid"]);
            $row["number_formatted"] = $r["number_formatted"];
            $row["tanggal"] = date("d M Y", strtotime($r["tanggal"]));
            $row["kas_akun_nama"] = $r["kas_akun_nama"];
            $row["pelanggan_number_formatted"] = $r["pelanggan_number_formatted"];
            $row["pelanggan_nama"] = $r["pelanggan_nama"];
            $row["total_akhir"] = number_format($r['total_akhir'], 0);
            $row["sisa"] = number_format($r['sisa'], 0);
            $row["status"] = (int) $r["lunas"] == 1 ? "Lunas" : "Belum Lunas";

            $final_res[] = $row;
        }

        return $final_res;
    }

    function penjualan_retur_get_filtered_total($filters = array(), $gudang_uuid = "")
    {
        if (!$this->allow) return 0;

        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        if(!empty($gudang_uuid)) $filters["gudang_uuid"] = $gudang_uuid;
        $res = $this->transaksi_engine->penjualan_retur_get_list($filters, false, true);
        return count($res);
    }

    function penjualan_retur_get_total($filters = array(), $gudang_uuid = "")
    {
        if (!$this->allow) return 0;

        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        if(!empty($gudang_uuid)) $filters["gudang_uuid"] = $gudang_uuid;
        $res = $this->transaksi_engine->penjualan_retur_get_list($filters);
        return count($res);
    }

    function penjualan_retur_get($uuid = "")
    {
        if (!$this->allow) return array();

        $filters = array();
        $filters["uuid"] = $uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->transaksi_engine->penjualan_retur_get_list($filters);
        if (count($res) == 0) return array();
        $penjualan_retur = $res[0];
        $penjualan_retur_uuid = $penjualan_retur["uuid"];

        // **
        // get penjualan_retur detail list for penjualan_retur id
        $filters = array();
        $filters["penjualan_retur_uuid"] = $penjualan_retur_uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $detail_list = $this->transaksi_engine->penjualan_retur_detail_get_list($filters);
        $penjualan_retur["detail"] = $detail_list;

        return $penjualan_retur;
    }

    function penjualan_retur_delete($uuid = "")
    {
        if (!$this->allow_delete) return set_http_response_error(HTTP_FORBIDDEN, "Akses ditolak");

        $filters = array();
        $filters["uuid"] = $uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->transaksi_engine->penjualan_retur_get_list($filters);
        if (count($res) == 0) return set_http_response_error(HTTP_BAD_REQUEST, "retur penjualan tidak ditemukan");
        $res = $res[0];
        $uuid = $res["uuid"];
        $penjualan_retur_uuid = $uuid;

        // **
        // get item id list from penjualan detail by penjualan id
        $filters = array();
        $filters["penjualan_uuid"] = $penjualan_retur_uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->transaksi_engine->penjualan_retur_detail_get_list($filters);

        $item_uuid_list = array();
        foreach($res as $r){
            $item_uuid = $r['item_uuid'];
            $item_uuid_list[] = $item_uuid;
        }

        $this->db->trans_start();
        try {
            // **
            // hapus retur penjualan
            $res = $this->transaksi_engine->penjualan_retur_delete($penjualan_retur_uuid);
            if ($res == false ) throw new Exception("Gagal menghapus retur penjualan #001");

            // **
            // hapus retur penjualan detail
            $res = $this->transaksi_engine->penjualan_retur_detail_delete_by_penjualan_retur_uuid($penjualan_retur_uuid);
            if ($res == false ) throw new Exception("Gagal menghapus retur penjualan #002");

            foreach ($item_uuid_list as $index => $item_uuid) {
                // **
                // get current total stock satuan terkecil
                $stock = $this->item_engine->item_get_total_stock_for_item_uuid($item_uuid);

                // update cache stock
                $res = $this->item_engine->item_update_cache_stock($item_uuid, $stock);
                if ($res == false ) throw new Exception("Gagal menghapus retur penjualan #003");
            }

            // **
            // delete kas alur
            $res = $this->kas_engine->kas_alur_delete_for_transaksi_penjualan_retur_uuid($penjualan_retur_uuid);
            if ($res == false ) throw new Exception("Gagal menghapus retur penjualan #004");
        } catch (Exception $e) {
            $this->db->trans_rollback();
            return set_http_response_error(501, "Error : " . $e->getMessage());
        }

        $this->db->trans_commit();
        return set_http_response_success("retur penjualan telah dihapus");
    }

    function penjualan_retur_save()
    {
        $uuid = $this->input->post("uuid");
        $tanggal = $this->input->post("tanggal");
        $penjualan_uuid = $this->input->post("penjualan_uuid");
        $item_detail_json = $this->input->post("item_detail");
        $potongan = to_number($this->input->post("potongan"));
        $kas_akun_uuid = $this->input->post("kas_akun_uuid");
        $keterangan = $this->input->post("keterangan");
        $bayar = to_number($this->input->post("bayar"));

        // **
        // validasi
        if (empty($tanggal)) return set_http_response_error(HTTP_BAD_REQUEST, "Tanggal tidak valid");
        $item_detail_list = json_decode($item_detail_json, true);
        if (!is_array($item_detail_list)) $item_detail_list = array();
        if (count($item_detail_list) == 0) return set_http_response_error(HTTP_BAD_REQUEST, "Belum ada item yang dipilih pada penjualan");

        
        if(strtotime($tanggal) > time()) {
            return set_http_response_error(HTTP_BAD_REQUEST, "Tanggal Penjualan Retur tidak boleh lebih dari tanggal hari ini");
        }

        // **
        // check penjualan uuid
        $filters = array();
        $filters["uuid"] = $penjualan_uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->transaksi_engine->penjualan_get_list($filters);
        if (count($res) == 0) return set_http_response_error(HTTP_BAD_REQUEST, "Penjualan tidak ditemukan");
        $res = $res[0];
        $penjualan_uuid = $res["uuid"];
        $penjualan_number_formatted = trim($res["number_formatted"]);
        $pelanggan_uuid = $res["pelanggan_uuid"];
        $gudang_uuid = $res["gudang_uuid"];
        $gudang_kode = $res["gudang_kode"];
        $gudang_nama = $res["gudang_nama"];

        // **
        // ambil total jumlah satuan terkecil dari penjualan detail group by item id
        $filters = array();
        $filters["penjualan_uuid"] = $penjualan_uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $current_total_jumlah_satuan_terkecil_list = array();
        $penjualan_detail_list = $this->transaksi_engine->penjualan_detail_get_list($filters);
        foreach ($penjualan_detail_list as $dl) {
            $current_item_uuid = $dl["item_uuid"];
            if (!isset($current_total_jumlah_satuan_terkecil_list[$current_item_uuid])) {
                $current_total_jumlah_satuan_terkecil_list[$current_item_uuid] = 0;
            }
            $current_total_jumlah_satuan_terkecil_list[$current_item_uuid] += (int) $dl["jumlah_satuan_terkecil"];
        }

        // **
        // check kas akun uuid
        $kas_akun_data = array();
        $kas_akun_uuid = 0;
        if (!empty($kas_akun_uuid)) {
            $filters = array();
            $filters['uuid'] = $kas_akun_uuid;
            $filters["cabang_uuid"] = $this->cabang_selected_uuid;
            $res = $this->kas_engine->kas_akun_get_list($filters);
            if (count($res) == 0) return set_http_response_error(HTTP_BAD_REQUEST, "Kas akun tidak ditemukan");
            $res = $res[0];
            $kas_akun_uuid = $res["uuid"];
            $kas_akun_data = $res;
        }

        
        $penjualan_retur_uuid = "";
        $kas_alur_uuid = "";
        $kas_alur_data = array();
        $created = date("Y-m-d H:i:s");
        $creator_user_uuid = $this->actor_user_uuid;
        $creator_user_name = $this->actor_user_name;
        $number = 0;
        $number_formatted = PREFIX_PENJUALAN_RETUR . "/" . $this->cabang_selected_kode . "/" . $gudang_kode . "/" . microtime_();
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
            $res = $this->transaksi_engine->penjualan_retur_get_list($filters);
            if (count($res) == 0) return set_http_response_error(HTTP_BAD_REQUEST, "retur penjualan tidak ditemukan");
            $res = $res[0];
            $uuid = $res["uuid"];
            $penjualan_retur_uuid = $uuid;
            $created = $res["created"];
            $creator_user_uuid = $res["creator_user_uuid"];
            $creator_user_name = $res["creator_user_name"];
            $number = (int) $res["number"];
            $number_formatted = trim($res["number_formatted"]);
            $number_formatted = PREFIX_PENJUALAN_RETUR . "/" . $this->cabang_selected_kode . "/" . $gudang_kode . "/" . microtime_();

            $old_gudang_kode = $res["gudang_kode"];
            $old_tahun = $res["tahun"];     

            // **
            // get current retur penjualan detail list
            $filters = array();
            $filters["penjualan_retur_uuid"] = $penjualan_retur_uuid;
            $filters["cabang_uuid"] = $this->cabang_selected_uuid;
            $list = $this->transaksi_engine->penjualan_retur_detail_get_list($filters);
            foreach ($list as $l) {
                $current_item_uuid = $l['item_uuid'];
                $current_item_uuid_list[] = $current_item_uuid;
            }

            $jam = date("H:i:s", strtotime($res["tanggal"]));

            // **
            // dapatkan kas alur id berdasarkan transaksi_penjualan_retur_uuid
            $filters = array();
            $filters['transaksi_penjualan_retur_uuid'] = $uuid;
            $filters["cabang_uuid"] = $this->cabang_selected_uuid;
            $res2 = $this->kas_engine->kas_alur_get_list($filters);
            if (count($res2) > 0) {
                $res2 = $res2[0];
                $kas_alur_uuid = $res2["uuid"];

                $kas_alur_data = $res2;
            }
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
        // -- generate retur penjualan detail
        // -- get harga beli satuan terkecil
        // -- get sub_total dari total harga beli
        // -- genereate item id list

        $jumlah_satuan_terkecil_item = array();
        $current_stock_item = array();
        $satuan_terkecil_item = array();
        $cek_stock_saat_penjualan_item = array();

        $total_jumlah_satuan_terkecil_list = array();
        $data_item_jumlah_satuan_terkecil_list = array();
        foreach ($item_detail_list as $i) {
            $kode = $i["item_code"];
            $selected_satuan = $i["satuan"];
            $jumlah = (int) $i["jumlah"];
            $potongan_persen = (float) $i['potongan'];
            if ((int) $jumlah == 0) return set_http_response_error(HTTP_BAD_REQUEST, "Item dengan kode $kode tidak memiliki jumlah");
            if (empty($selected_satuan)) return set_http_response_error(HTTP_BAD_REQUEST, "Satuan yang dipilih untuk item dengan kode $kode tidak dikenal");

            $filters = array();
            $filters["item_kode"] = $kode;
            $filters["penjualan_uuid"] = $penjualan_uuid;
            $filters["cabang_uuid"] = $this->cabang_selected_uuid;
            $res = $this->transaksi_engine->penjualan_detail_get_list($filters);
            if (count($res) == 0) return set_http_response_error(HTTP_BAD_REQUEST, "Item dengan kode $kode tidak ditemukan pada No. Penjualan $penjualan_number_formatted");
            $res = $res[0];
            $item_uuid = $res["item_uuid"];
            $item_kode = $kode;
            $item_barcode = $res["item_barcode"];
            $item_nama = $res["item_nama"];
            $item_struktur_satuan_harga_json = $res["item_struktur_satuan_harga_json"];
            $item_tipe = $res["item_tipe"];
            $item_kategori_uuid = $res["item_kategori_uuid"];
            $item_kategori_nama = trim($res["item_kategori_nama"]);
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

            $penjualan_retur_detail_data_list = array(
                "uuid"     => "",
                "created" => date("Y-m-d H:i:s"),
                "creator_user_uuid" => $this->actor_user_uuid,
                "creator_user_name" => $this->actor_user_name,
                "last_updated" => date("Y-m-d H:i:s"),
                "last_updated_user_uuid" => $this->actor_user_uuid,
                "last_updated_user_name" => $this->actor_user_name,
                "penjualan_retur_uuid" => $uuid,
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
                "harga_jual_satuan" => $harga_jual,
                "margin_jual_satuan" => $margin_jual,
                "harga_jual_satuan_terkecil" => $harga_jual_satuan_terkecil,
                "margin_jual_satuan_terkecil" => $margin_jual_satuan_terkecil,
                "jumlah_satuan_terkecil" => $jumlah_satuan_terkecil,
                "potongan_persen" => $potongan_persen,
                "potongan_harga" => (int) $potongan_harga,
                "cabang_uuid" => $this->cabang_selected_uuid,
            );

            if (!isset($total_jumlah_satuan_terkecil_list[$item_uuid])) $total_jumlah_satuan_terkecil_list[$item_uuid] = 0;
            $total_jumlah_satuan_terkecil_list[$item_uuid] += $jumlah_satuan_terkecil;
            $data_item_jumlah_satuan_terkecil_list[$item_uuid]["kode"] = $item_kode;
            $data_item_jumlah_satuan_terkecil_list[$item_uuid]["nama"] = $item_nama;

            $final_item_list[] = $penjualan_retur_detail_data_list;
        }

        // **
        // validasi
        // pastikan jumlah retur tidak melebihi jumlah penjualan
        foreach ($total_jumlah_satuan_terkecil_list as $item_uuid => $_total) {
            $item_kode = $data_item_jumlah_satuan_terkecil_list[$item_uuid]["kode"];
            $item_nama = $data_item_jumlah_satuan_terkecil_list[$item_uuid]["nama"];

            if (!isset($current_total_jumlah_satuan_terkecil_list[$item_uuid])) {
                return set_http_response_error(HTTP_BAD_REQUEST, "Item dengan kode $item_kode tidak ada pada No. Penjualan $penjualan_number_formatted");
            }

            $current_total = $current_total_jumlah_satuan_terkecil_list[$item_uuid];

            if ($current_total < $_total) {
                return set_http_response_error(HTTP_BAD_REQUEST, "Jumlah yang akan diretur untuk item dengan kode $item_kode melebihi jumlah pada No. Penjualan $penjualan_number_formatted");;
            }
        }

        // foreach ($jumlah_satuan_terkecil_item as $kode => $jumlah) {
        //     $stock = $current_stock_item[$kode];
        //     $cek_stock_saat_penjualan = $cek_stock_saat_penjualan_item[$kode];

        //     if ($cek_stock_saat_penjualan == 1) {
        //         if ($jumlah > $stock) {
        //             $satuan_terkecil = $satuan_list[0];
        //             return set_http_response_error(
        //                 201,
        //                 "Stock tidak mencukupi untuk item dengan kode $kode. <br/>
        //                 Total stock : " . number_format($stock) . " $satuan_terkecil <br/>
        //                 Total jumlah : " . number_format($jumlah) . " $satuan_terkecil"
        //             );
        //         }
        //     }
        // }

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

        $sisa = $total_akhir - $bayar;
        if ($bayar >= $total_akhir) {
            $sisa = 0;
            $bayar = $total_akhir;
        }
        $lunas = 0;
        if ($sisa <= 0) $lunas = 1;

        // **
        // =====================================================
        // bypass
        // =====================================================
        $bayar = $total_akhir;
        $sisa = 0;
        $lunas = 1;

        $penjualan_retur_data = array(
            "uuid" => $uuid,
            "created" => $created,
            "creator_user_uuid" => $creator_user_uuid,
            "creator_user_name" => $creator_user_name,
            "last_updated" => date("Y-m-d H:i:s"),
            "last_updated_user_uuid" => $this->actor_user_uuid,
            "last_updated_user_name" => $this->actor_user_name,
            "penjualan_uuid" => $penjualan_uuid,
            "penjualan_number_formatted" => $penjualan_number_formatted,
            "number" => $number,
            "number_formatted" => $number_formatted,
            "tanggal" => $tanggal . " $jam",
            "tahun" => $tahun,
            
            "pelanggan_uuid" => $pelanggan_uuid,
            "pelanggan_potongan_persen" => 0,
            
            "gudang_uuid" => $gudang_uuid,
            "gudang_kode" => $gudang_kode,
            "gudang_nama" => $gudang_nama,

            "kas_akun_uuid" => $kas_akun_uuid,
            "kas_alur_uuid" => $kas_alur_uuid,
            
            "sub_total" => $sub_total,
            "potongan" => $potongan,
            "total_akhir" => $total_akhir,
            "bayar" => $bayar,
            "sisa" => $sisa,
            "lunas" => $lunas,
            "keterangan" => $keterangan,

            "old_tahun" => $old_tahun, 
            "old_gudang_kode" => $old_gudang_kode,

            "cabang_uuid" => $this->cabang_selected_uuid,
            "cabang_kode" => $this->cabang_selected_kode,
        );

        // **
        // update kas alur data
        if (!empty($kas_akun_uuid)) {
            if (count($kas_alur_data) == 0) {
                $kas_alur_data["uuid"] = $kas_alur_uuid;
                $kas_alur_data["created"] = $created;
                $kas_alur_data["creator_user_uuid"] = $creator_user_uuid;
                $kas_alur_data["creator_user_name"] = $creator_user_name;
                $kas_alur_data["last_updated"] = date("Y-m-d H:i:s");
                $kas_alur_data["last_updated_user_uuid"] = $this->actor_user_uuid;
                $kas_alur_data["last_updated_user_name"] = $this->actor_user_name;

                $kas_alur_data["number"] = 0;
                $kas_alur_data["number_formatted"] = "KK/" . $this->cabang_selected_kode . "/" . microtime_();

                $kas_alur_data["tanggal"] = $tanggal;

                $kas_alur_data["kas_akun_uuid"] = $kas_akun_uuid;
                $kas_alur_data["kas_akun_nama"] = $kas_akun_data["nama"];

                $kas_alur_data["kas_kategori_uuid"] = -21;
                $kas_alur_data["kas_kategori_nama"] = "Transaksi Retur Penjualan";

                $kas_alur_data["alur_kas"] = "Keluar";

                $kas_alur_data["jumlah_masuk"] = 0;
                $kas_alur_data["jumlah_keluar"] = $bayar;

                $kas_alur_data["keterangan"] = "Transaksi Retur Penjualan";

                $kas_alur_data["transaksi_pembelian_uuid"] = 0;
                $kas_alur_data["transaksi_pembelian_number_formatted"] = "";
                $kas_alur_data["transaksi_pembelian_retur_uuid"] = 0;
                $kas_alur_data["transaksi_pembelian_retur_number_formatted"] = "";

                $kas_alur_data["transaksi_penjualan_uuid"] = 0;
                $kas_alur_data["transaksi_penjualan_number_formatted"] = "";
                $kas_alur_data["transaksi_penjualan_retur_uuid"] = 0;
                $kas_alur_data["transaksi_penjualan_retur_number_formatted"] = "";
                $kas_alur_data["transaksi_penjualan_pelunasan_uuid"] = 0;
                $kas_alur_data["transaksi_penjualan_pelunasan_number_formatted"] = "";

                $kas_alur_data["kas_transfer_uuid"] = 0;
                $kas_alur_data["kas_transfer_number_formatted"] = "";

                $kas_akun_data["cabang_uuid"] = $this->cabang_selected_uuid;
                $kas_akun_data["cabang_kode"] = $this->cabang_selected_kode;
            } else {
                $kas_alur_data["jumlah_keluar"] = $bayar;
                $kas_alur_data["kas_akun_uuid"] = $kas_akun_uuid;
                $kas_alur_data["kas_akun_nama"] = $kas_akun_data["nama"];
            }
        }


        $this->db->trans_start();
        try {
            // **
            // hapus semua penjualan detail untuk penjualan id jika penjualan id != 0
            if (!empty($uuid)) {
                $res = $this->transaksi_engine->penjualan_retur_detail_delete_by_penjualan_retur_uuid($uuid);
                if ($res == false ) throw new Exception("Gagal menyimpan retur penjualan #001");
            }

            // **
            // simpan penjualan
            $res = $this->transaksi_engine->penjualan_retur_save($penjualan_retur_data);
            if ($res == false ) throw new Exception("Gagal menyimpan retur penjualan #002");
            if (empty($uuid)) {
                $uuid = $res;
                $penjualan_retur_uuid = $uuid;
            }

            // **
            // get detail retur penjualan untuk set ke kas alur data
            $filters = array();
            $filters["uuid"] = $uuid;
            $filters["cabang_uuid"] = $this->cabang_selected_uuid;
            $res = $this->transaksi_engine->penjualan_retur_get_list($filters);
            $res = $res[0];
            $kas_alur_data['transaksi_penjualan_retur_uuid'] = $uuid;
            $kas_alur_data['transaksi_penjualan_retur_number_formatted'] = $res['number_formatted'];
            $kas_alur_data['keterangan'] = "Transaksi retur penjualan " . $res['number_formatted'];

            // **
            // simpan retur penjualan detail
            foreach ($final_item_list as $penjualan_retur_detail) {
                $penjualan_retur_detail["penjualan_retur_uuid"] = $penjualan_retur_uuid;
                $harga_jual_satuan_terkecil = (float) $penjualan_retur_detail["harga_jual_satuan_terkecil"];
                $item_uuid = $penjualan_retur_detail["item_uuid"];
                $item_struktur_satuan_harga_json = $penjualan_retur_detail["item_struktur_satuan_harga_json"];
                $item_struktur_satuan_harga_list = json_decode($item_struktur_satuan_harga_json, true);

                $selected_satuan = $penjualan_retur_detail["satuan"];
                $jumlah = (int) $penjualan_retur_detail["jumlah"];

                // **
                // simpan retur penjualan detail
                $res = $this->transaksi_engine->penjualan_retur_detail_save($penjualan_retur_detail);
                if ($res == false ) throw new Exception("Gagal menyimpan retur penjualan #003");

                // **
                // get current total stock satuan terkecil
                $stock = $this->item_engine->item_get_total_stock_for_item_uuid($item_uuid);

                // update cache stock
                $res = $this->item_engine->item_update_cache_stock($item_uuid, $stock);
                if ($res == false ) throw new Exception("Gagal menyimpan retur penjualan #004");
            }


            // **
            // update stock  untuk item yang dihapus
            foreach ($deleted_item_data_list as $_item_uuid => $data) {

                // **
                // get current total stock satuan terkecil
                $stock = $this->item_engine->item_get_total_stock_for_item_uuid($item_uuid);

                // update cache stock
                $res = $this->item_engine->item_update_cache_stock($_item_uuid, $stock);
                if ($res == false ) throw new Exception("Gagal menyimpan retur penjualan #005");
            }

            if (!empty($kas_akun_uuid)) {
                // **
                // simpan kas alur
                $res = $this->kas_engine->kas_alur_save($kas_alur_data);
                if ($res == false ) throw new Exception("Gagal menyimpan retur penjualan #006");
                if (empty($kas_alur_uuid)) {
                    $kas_alur_uuid = $res;
                    // **
                    // update kas alur id untuk transaksi retur penjualan
                    $res = $this->transaksi_engine->penjualan_retur_update_kas_alur_uuid($uuid, $kas_alur_uuid);
                    if ($res == false ) throw new Exception("Gagal menyimpan retur penjualan #008");
                }
            }
        } catch (Exception $e) {
            $this->db->trans_rollback();
            return set_http_response_error(501, "Error : " . $e->getMessage());
        }

        $this->db->trans_commit();
        return set_http_response_success("Retur Penjualan telah disimpan", array(), trim($uuid));
    }

    function item_get_detail_by_kode_and_penjualan_uuid()
    {
        $kode_satuan = $this->input->get("kode_satuan");
        $penjualan_uuid = $this->input->get("penjualan_uuid");
        if (empty($kode_satuan)) return set_http_response_error(HTTP_BAD_REQUEST, "EMPTY_KODE");
        if (empty($penjualan_uuid)) return set_http_response_error(HTTP_BAD_REQUEST, "EMPTY_PENJUALAN_uuID");

        $exploded = explode(":", $kode_satuan);
        $kode = $exploded[0];
        $satuan = $exploded[1];

        $filters = array();
        $filters["item_kode"] = $kode;
        $filters["satuan"] = $satuan;
        $filters["penjualan_uuid"] = $penjualan_uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->transaksi_engine->penjualan_detail_get_list($filters);
        if (count($res) == 0) return set_http_response_error(HTTP_BAD_REQUEST, "NO_DATA");
        $res = $res[0];
        $uuid = $res["uuid"];
        // $current_stock = $this->item_engine->item_get_total_stock_for_item_uuid($uuid);
        // $cek_stock_saat_penjualan = (int) $res["cek_stock_saat_penjualan"];

        $struktur_satuan_harga_json = $res["item_struktur_satuan_harga_json"];
        $struktur_satuan_harga_list = json_decode($struktur_satuan_harga_json, true);
        if (json_last_error_msg() != JSON_ERROR_NONE || !is_array($struktur_satuan_harga_list)) return set_http_response_error(HTTP_BAD_REQUEST, "Struktur satuan bermasalah");

        $satuan_list = array();
        $harga_list = array();
        $stock_list = array();

        // $stock = $current_stock;
        foreach ($struktur_satuan_harga_list as $satuan => $l) {
            $row = array(
                "name" => $satuan,
                "label" => $l["satuan"],
                "harga_jual" => $l["harga_jual"],
                "harga_pokok" => $l["harga_pokok"]
            );
            $satuan_list[] = $row;

            // $stock = $stock / (int) $l['konversi'];

            $harga_list[strtoupper($satuan)] = $l['harga_jual'];
            // $stock_list[strtoupper($satuan)] = $stock;
        }

        $jumlah = (int) $res["jumlah"];
        $harga_jual_satuan = (float) $res["harga_jual_satuan"];
        $potongan_persen = (float) $res["potongan_persen"];
        $potongan_harga = (float) $res["potongan_harga"];
        $sub_total = $jumlah * ($harga_jual_satuan - $potongan_harga);

        $data = array();
        $data["kode"] = $res["item_kode"];
        $data["nama"] = $res["item_nama"];
        $data["nama_kategori"] = $res["item_kategori_nama"];
        $data["satuan_list"] = $satuan_list;
        $data["jumlah"] = $res["jumlah"];
        $data["satuan"] = $res["satuan"];
        $data["harga_jual_satuan"] = $res["harga_jual_satuan"];
        $data["potongan_persen"] = $res["potongan_persen"];
        $data["sub_total"] = $sub_total;
        $data["harga_list"] = $harga_list;
        $data["stock_list"] = $stock_list;        
        // $data["cek_stock"] = $cek_stock_saat_penjualan;

        return set_http_response_success("OK", $data);
    }

    function penjualan_get_detail_by_number_formatted()
    {
        $number_formatted = $this->input->get("penjualan_number_formatted");
        if (empty($number_formatted)) return set_http_response_error(HTTP_BAD_REQUEST, "EMPTY_KODE");

        $filters = array();
        $filters["number_formatted"] = $number_formatted;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->transaksi_engine->penjualan_get_list($filters);
        if (count($res) == 0) return set_http_response_error(HTTP_BAD_REQUEST, "NO_DATA");
        $res = $res[0];

        $data = array(
            'uuid' => trim($res["uuid"]),
            'pelanggan_number_formatted' => $res['pelanggan_number_formatted'],
            'pelanggan_nama' => $res['pelanggan_nama'],
            'pelanggan_alamat' => $res['pelanggan_alamat'],
            'pelanggan_no_telepon' => $res['pelanggan_no_telepon']
        );

        return set_http_response_success('OK', $data);
    }
}
