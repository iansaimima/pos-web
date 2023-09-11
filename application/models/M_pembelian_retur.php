<?php

defined('BASEPATH') or exit('No direct script access allowed');
class M_pembelian_retur extends MY_Model
{

    private $transaksi_engine;
    private $pemasok_engine;
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
        $this->pemasok_engine = new Pemasok_engine();
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

        $this->allow        = isset($privilege_list["allow_transaksi_pembelian_retur"]) ? $privilege_list["allow_transaksi_pembelian_retur"] : 0;
        $this->allow_create = isset($privilege_list["allow_transaksi_pembelian_retur_create"]) ? $privilege_list["allow_transaksi_pembelian_retur_create"] : 0;
        $this->allow_update = isset($privilege_list["allow_transaksi_pembelian_retur_update"]) ? $privilege_list["allow_transaksi_pembelian_retur_update"] : 0;
        $this->allow_delete = isset($privilege_list["allow_transaksi_pembelian_retur_delete"]) ? $privilege_list["allow_transaksi_pembelian_retur_delete"] : 0;

        $this->allow_detail_create = isset($privilege_list["allow_transaksi_pembelian_retur_detail_create"]) ? $privilege_list["allow_transaksi_pembelian_retur_detail_create"] : 0;
        $this->allow_detail_update = isset($privilege_list["allow_transaksi_pembelian_retur_detail_update"]) ? $privilege_list["allow_transaksi_pembelian_retur_detail_update"] : 0;
        $this->allow_detail_delete = isset($privilege_list["allow_transaksi_pembelian_retur_detail_delete"]) ? $privilege_list["allow_transaksi_pembelian_retur_detail_delete"] : 0;
    }

    function pembelian_retur_get_list($filters = array(), $gudang_uuid = "")
    {
        if (!$this->allow) return array();

        if (!empty($gudang_uuid)) $filters["gudang_uuid"] = $gudang_uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->transaksi_engine->pembelian_retur_get_list($filters, true, true);
        $no = isset($filters["start"]) ? (int) $filters["start"] : 0;

        $final_res = array();
        foreach ($res as $r) {
            $no++;

            $potongan = (float) $r['potongan'];
            $total = (float) $r['total'];
            $total = $total - $potongan;
            if ($total < 0) $total = 0;

            $row = array();
            $row["no"] = $no;
            $row["uuid"] = trim($r["uuid"]);
            $row["number_formatted"] = $r["number_formatted"];
            $row["pembelian_number_formatted"] = $r["pembelian_number_formatted"];
            $row["tanggal"] = date("d M Y", strtotime($r["tanggal"]));
            $row["kas_akun_nama"] = $r["kas_akun_nama"];
            $row["pemasok_number_formatted"] = $r["pemasok_number_formatted"];
            $row["pemasok_nama"] = $r["pemasok_nama"];
            $row["potongan"] = number_format($potongan, 0);
            $row["total_akhir"] = number_format($r['total_akhir'], 0);
            $row["sisa"] = number_format($r['sisa'], 0);
            $row["status"] = (int) $r["lunas"] == 1 ? "Lunas" : "Belum Lunas";

            $final_res[] = $row;
        }

        return $final_res;
    }

    function pembelian_retur_get_filtered_total($filters = array(), $gudang_uuid = "")
    {
        if (!$this->allow) return 0;

        if (!empty($gudang_uuid)) $filters["gudang_uuid"] = $gudang_uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->transaksi_engine->pembelian_retur_get_list($filters, false, true);
        return count($res);
    }

    function pembelian_retur_get_total($filters = array(), $gudang_uuid = "")
    {
        if (!$this->allow) return 0;

        if (!empty($gudang_uuid)) $filters["gudang_uuid"] = $gudang_uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->transaksi_engine->pembelian_retur_get_list($filters);
        return count($res);
    }

    function pembelian_retur_get($uuid = "")
    {
        if (!$this->allow) return array();

        $filters = array();
        $filters["uuid"] = $uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->transaksi_engine->pembelian_retur_get_list($filters);
        if (count($res) == 0) return array();
        $pembelian_retur = $res[0];
        $pembelian_retur_uuid = $pembelian_retur["uuid"];

        // **
        // get retur pembelian detail list for retur pembelian id
        $filters = array();
        $filters["pembelian_retur_uuid"] = $pembelian_retur_uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $detail_list = $this->transaksi_engine->pembelian_retur_detail_get_list($filters);
        $pembelian_retur["detail"] = $detail_list;

        return $pembelian_retur;
    }

    function pembelian_retur_delete($uuid = "")
    {
        if (!$this->allow_delete) return set_http_response_error(HTTP_FORBIDDEN, "Akses ditolak");

        $filters = array();
        $filters["uuid"] = $uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->transaksi_engine->pembelian_retur_get_list($filters);
        if (count($res) == 0) return set_http_response_error(HTTP_BAD_REQUEST, "retur pembelian tidak ditemukan");
        $res = $res[0];
        $uuid = $res["uuid"];
        $pembelian_retur_uuid = $uuid;
        $kas_alur_uuid = $res['kas_alur_uuid'];
        $gudang_uuid = $res['gudang_uuid'];

        // **
        // get item id list for retur pembelian detail by retur pembelian id
        $filters = array();
        $filters["pembelian_retur_uuid"] = $pembelian_retur_uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->transaksi_engine->pembelian_retur_detail_get_list($filters);

        $item_data_list = array();
        foreach ($res as $r) {
            $item_uuid = $r['item_uuid'];
            $item_struktur_satuan_harga_json = $r["item_struktur_satuan_harga_json"];

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
            // ambil daftar harga beli satuan terkecil berdasarkan item id
            $rata_rata_harga_beli_satuan_terkecil = $this->item_engine->item_get_rata_rata_harga_beli_satuan_terkecil_for_item_uuid($item_uuid);

            // **
            // get current total stock satuan terkecil
            $stock = $this->item_engine->item_get_total_stock_for_item_uuid($item_uuid);

            $item_data_list[$item_uuid]["rata_rata_harga_beli_satuan_terkecil"] = $rata_rata_harga_beli_satuan_terkecil;
            $item_data_list[$item_uuid]["stock"] = $stock;
            $item_data_list[$item_uuid]["item_struktur_satuan_harga_list"] = $item_struktur_satuan_harga_list;
        }

        $this->db->trans_start();
        try {
            // **
            // hapus pembelian_retur
            $res = $this->transaksi_engine->pembelian_retur_delete($pembelian_retur_uuid);
            if ($res == false) throw new Exception("Gagal menghapus retur pembelian #001");

            // **
            // hapus retur pembelian detail
            $res = $this->transaksi_engine->pembelian_retur_detail_delete_by_pembelian_retur_uuid($pembelian_retur_uuid);
            if ($res == false) throw new Exception("Gagal menghapus retur pembelian #002");

            foreach ($item_data_list as $item_uuid => $data) {
                $rata_rata_harga_beli_satuan_terkecil = $data['rata_rata_harga_beli_satuan_terkecil'];
                $stock = $data['stock'];
                $item_struktur_satuan_harga_list = $data['item_struktur_satuan_harga_list'];

                // **
                // ambil daftar harga beli satuan terkecil berdasarkan item id
                $rata_rata_harga_beli_satuan_terkecil = $this->item_engine->item_get_rata_rata_harga_beli_satuan_terkecil_for_item_uuid($item_uuid);

                // **
                // update item cache harga pokok = rata_rata_harga_beli_satuan_terkecil
                $res = $this->item_engine->item_update_cache_harga_pokok($item_uuid, $rata_rata_harga_beli_satuan_terkecil);
                if ($res == false) throw new Exception("Gagal menghapus retur pembelian #003");

                // **
                // get current total stock satuan terkecil
                $stock = $this->item_engine->item_get_total_stock_for_item_uuid($item_uuid);

                // update cache stock
                $res = $this->item_engine->item_update_cache_stock($item_uuid, $stock);
                if ($res == false) throw new Exception("Gagal menghapus retur pembelian #004");

                // **
                // set ulang struktur satuan dan harga
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
                // update item struktur satuan harga
                $res = $this->item_engine->item_update_struktur_satuan_harga_json($item_uuid, json_encode($new_struktur_satuan_harga_list));
                if ($res == false) throw new Exception("Gagal menghapus retur pembelian #005");
            }

            // **
            // delete kas alur
            $res = $this->kas_engine->kas_alur_delete_for_transaksi_pembelian_retur_uuid($pembelian_retur_uuid);
            if ($res == false) throw new Exception("Gagal menghapus retur pembelian #005");
        } catch (Exception $e) {
            $this->db->trans_rollback();
            return set_http_response_error(501, "Error : " . $e->getMessage());
        }

        $this->db->trans_commit();
        return set_http_response_success("retur pembelian telah dihapus");
    }

    function pembelian_retur_save()
    {
        $uuid = $this->input->post("uuid");
        $tanggal = $this->input->post("tanggal");
        $pembelian_uuid = $this->input->post("pembelian_uuid");
        $item_detail_json = $this->input->post("item_detail");
        $potongan = to_number($this->input->post("potongan"));
        $kas_akun_uuid = $this->input->post("kas_akun_uuid");
        $bayar = to_number($this->input->post("bayar"));

        // **
        // validasi
        if (empty($tanggal)) return set_http_response_error(HTTP_BAD_REQUEST, "Tanggal tidak valid");
        $item_detail_list = json_decode($item_detail_json, true);
        if (!is_array($item_detail_list)) $item_detail_list = array();
        if (count($item_detail_list) == 0) return set_http_response_error(HTTP_BAD_REQUEST, "Belum ada item yang dipilih pada pembelian_retur");


        if (strtotime($tanggal) > time()) {
            return set_http_response_error(HTTP_BAD_REQUEST, "Tanggal Pembelian Retur tidak boleh lebih dari tanggal hari ini");
        }

        // **
        // check pembelian uuid
        $filters = array();
        $filters["uuid"] = $pembelian_uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->transaksi_engine->pembelian_get_list($filters);
        if (count($res) == 0) return set_http_response_error(HTTP_BAD_REQUEST, "Pembelian tidak ditemukan");
        $res = $res[0];
        $pembelian_uuid = $res["uuid"];
        $pembelian_number_formatted = trim($res["number_formatted"]);
        $pemasok_uuid = trim($res["pemasok_uuid"]);
        $gudang_uuid = $res["gudang_uuid"];
        $gudang_kode = $res["gudang_kode"];
        $gudang_nama = $res["gudang_nama"];

        // **
        // check kas akun uuid
        $kas_akun_data = array();
        $kas_akun_uuid = "";
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

        $pembelian_retur_uuid = "";
        $kas_alur_uuid = "";
        $kas_alur_data = array();
        $created = date("Y-m-d H:i:s");
        $creator_user_uuid = $this->actor_user_uuid;
        $creator_user_name = $this->actor_user_name;
        $number = 0;
        $number_formatted = PREFIX_PEMBELIAN_RETUR . "/" . $this->cabang_selected_kode . "/" . $gudang_kode . "/" . microtime_();
        $tahun = date("Y", strtotime($tanggal . " 00:00:00"));
        $jam = date("H:i:s");

        // **
        // set gudang id
        // $settings = $this->settings_engine->get_settings('TRANSAKSI_PEMBELIAN_DEFAULT_GUDANG_ID');
        // $gudang_uuid = $settings["_value"];

        $current_item_uuid_list = array();
        $old_tahun = $tahun;
        $old_gudang_kode = $gudang_kode;
        if (!empty($uuid)) {
            $filters = array();
            $filters["uuid"] = $uuid;
            $filters["cabang_uuid"] = $this->cabang_selected_uuid;
            $res = $this->transaksi_engine->pembelian_retur_get_list($filters);
            if (count($res) == 0) return set_http_response_error(HTTP_BAD_REQUEST, "retur pembelian tidak ditemukan");
            $res = $res[0];
            $uuid = $res["uuid"];
            $pembelian_retur_uuid = $uuid;
            $created = $res["created"];
            $creator_user_uuid = $res["creator_user_uuid"];
            $creator_user_name = $res["creator_user_name"];
            $number = (int) $res["number"];
            $number_formatted = trim($res["number_formatted"]);
            $number_formatted = PREFIX_PEMBELIAN_RETUR . "/" . $this->cabang_selected_kode . "/" . $gudang_kode . "/" . microtime_();

            $old_gudang_kode = $res["gudang_kode"];
            $old_tahun = $res["tahun"];

            // **
            // get current retur pembelian detail list
            $filters = array();
            $filters["pembelian_retur_uuid"] = $pembelian_retur_uuid;
            $filters["cabang_uuid"] = $this->cabang_selected_uuid;
            $list = $this->transaksi_engine->pembelian_retur_detail_get_list($filters);
            foreach ($list as $l) {
                $current_item_uuid = $l['item_uuid'];
                $current_item_uuid_list[] = $current_item_uuid;
            }

            $jam = date("H:i:s", strtotime($res["tanggal"]));

            // **
            // dapatkan kas alur id berdasarkan transaksi_pembelian_retur_uuid
            $filters = array();
            $filters['transaksi_pembelian_retur_uuid'] = $uuid;
            $filters["cabang_uuid"] = $this->cabang_selected_uuid;
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
        // -- generate retur pembelian detail
        // -- get harga beli satuan terkecil
        // -- get sub_total dari total harga beli
        // -- genereate item id list
        foreach ($item_detail_list as $i) {
            $kode = $i["item_code"];
            $selected_satuan = $i["satuan"];
            $jumlah = (int) $i["jumlah"];
            $harga_beli = (float) $i["harga_beli"];
            $potongan_persen = (float) $i['potongan'];
            $potongan_harga = 0;
            if ($harga_beli > 0 && $potongan_persen > 0) {
                $potongan_harga = $harga_beli * ($potongan_persen / 100);
            }
            if ((int) $jumlah == 0) return set_http_response_error(HTTP_BAD_REQUEST, "Item dengan kode $kode tidak memiliki jumlah");
            if (empty($selected_satuan)) return set_http_response_error(HTTP_BAD_REQUEST, "Satuan yang dipilih untuk item dengan kode $kode tidak dikenal");

            $filters = array();
            $filters["item_kode"] = $kode;
            $filters["pembelian_uuid"] = $pembelian_uuid;
            $filters["cabang_uuid"] = $this->cabang_selected_uuid;
            $res = $this->transaksi_engine->pembelian_detail_get_list($filters);
            if (count($res) == 0) return set_http_response_error(HTTP_BAD_REQUEST, "Item dengan kode $kode tidak ditemukan pada No. pembelian $pembelian_number_formatted");
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
                if (!$satuan_found) continue;

                $selected_struktur_satuan_harga_list[$satuan] = $s;
                $total_pcs_satuan_terkecil *= $s["konversi"];

                // if ($satuan_found) break;
            }
            $harga_beli_satuan_terkecil = $i["harga_beli"] / $total_pcs_satuan_terkecil;

            $jumlah_satuan_terkecil = $total_pcs_satuan_terkecil * $jumlah;

            // **
            // hitung cache total
            $total = $jumlah * ($harga_beli - $potongan_harga);
            $sub_total += $total;

            $pembelian_retur_detail_data_list = array(
                "uuid"     => "",
                "created" => date("Y-m-d H:i:s"),
                "creator_user_uuid" => $this->actor_user_uuid,
                "creator_user_name" => $this->actor_user_name,
                "last_updated" => date("Y-m-d H:i:s"),
                "last_updated_user_uuid" => $this->actor_user_uuid,
                "last_updated_user_name" => $this->actor_user_name,
                "pembelian_retur_uuid" => $uuid,
                "item_uuid" => $item_uuid,
                "item_kode" => $item_kode,
                "item_barcode" => $item_barcode,
                "item_nama" => $item_nama,
                "item_struktur_satuan_harga_json" => $item_struktur_satuan_harga_json,
                "item_tipe" => $item_tipe,
                "item_kategori_uuid" => $item_kategori_uuid,
                "item_kategori_nama" => $item_kategori_nama,
                "harga_beli_satuan_terkecil" => $harga_beli_satuan_terkecil,
                "jumlah" => $jumlah,
                "satuan" => $selected_satuan,
                "harga_beli_satuan" => $harga_beli,
                "jumlah_satuan_terkecil" => $jumlah_satuan_terkecil,
                "potongan_persen" => $potongan_persen,
                "potongan_harga" => (int) $potongan_harga,
                "cabang_uuid" => $this->cabang_selected_uuid,
            );

            $final_item_list[] = $pembelian_retur_detail_data_list;
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

        $pembelian_retur_data = array(
            "uuid" => $uuid,
            "created" => $created,
            "creator_user_uuid" => $creator_user_uuid,
            "creator_user_name" => $creator_user_name,
            "last_updated" => date("Y-m-d H:i:s"),
            "last_updated_user_uuid" => $this->actor_user_uuid,
            "last_updated_user_name" => $this->actor_user_name,
            "pembelian_uuid" => $pembelian_uuid,
            "pembelian_number_formatted" => $pembelian_number_formatted,
            "number" => $number,
            "number_formatted" => $number_formatted,
            "tanggal" => $tanggal . " $jam",
            "tahun" => $tahun,
            "pemasok_uuid" => $pemasok_uuid,

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

            "old_tahun" => $old_tahun,
            "old_gudang_kode" => $old_gudang_kode,

            "cabang_uuid" => $this->cabang_selected_uuid,
            "cabang_kode" => $this->cabang_selected_kode,
        );

        // **
        // update kas alur data
        $kas_alur_tanggal_changed = false;
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
                $kas_alur_data["number_formatted"] = "KM/" . $this->cabang_selected_kode . "/" . microtime_();

                $kas_alur_data["tanggal"] = $tanggal;

                $kas_alur_data["kas_akun_uuid"] = $kas_akun_uuid;
                $kas_alur_data["kas_akun_nama"] = $kas_akun_data["nama"];

                $kas_alur_data["kas_kategori_uuid"] = -11;
                $kas_alur_data["kas_kategori_nama"] = "Transaksi Retur Pembelian";

                $kas_alur_data["alur_kas"] = "Masuk";

                $kas_alur_data["jumlah_masuk"] = $bayar;
                $kas_alur_data["jumlah_keluar"] = 0;

                $kas_alur_data["keterangan"] = "Transaksi Retur Pembelian";

                $kas_alur_data["transaksi_pembelian_uuid"] = 0;
                $kas_alur_data["transaksi_pembelian_number_formatted"] = "";
                $kas_alur_data["transaksi_pembelian_retur_uuid"] = 0;
                $kas_alur_data["transaksi_pembelian_retur_number_formatted"] = "";

                $kas_alur_data["transaksi_penjualan_uuid"] = 0;
                $kas_alur_data["transaksi_penjualan_number_formatted"] = "";
                $kas_alur_data["transaksi_penjualan_retur_uuid"] = 0;
                $kas_alur_data["transaksi_penjualan_retur_number_formatted"] = "";

                $kas_alur_data["kas_transfer_uuid"] = 0;
                $kas_alur_data["kas_transfer_number_formatted"] = "";

                $kas_akun_data["cabang_uuid"] = $this->cabang_selected_uuid;
                $kas_akun_data["cabang_kode"] = $this->cabang_selected_kode;
            } else {
                $kas_alur_tanggal = $kas_alur_data["tanggal"];
                if ($kas_alur_tanggal != $tanggal) $kas_alur_tanggal_changed = true;

                $kas_alur_data["jumlah_masuk"] = $bayar;
                $kas_alur_data["kas_akun_uuid"] = $kas_akun_uuid;
                $kas_alur_data["kas_akun_nama"] = $kas_akun_data["nama"];
            }
        }

        $this->db->trans_start();
        try {
            // **
            // hapus semua retur pembelian detail untuk retur pembelian id jika retur pembelian id != 0
            if (!empty($uuid)) {
                $res = $this->transaksi_engine->pembelian_retur_detail_delete_by_pembelian_retur_uuid($uuid);
                if ($res == false) throw new Exception("Gagal menyimpan retur pembelian #001");
            }

            // **
            // simpan pembelian_retur
            $res = $this->transaksi_engine->pembelian_retur_save($pembelian_retur_data);
            if ($res == false) throw new Exception("Gagal menyimpan retur pembelian #002");
            if (empty($uuid)) {
                $uuid = $res;
                $pembelian_retur_uuid = $uuid;
            }

            // **
            // get detail retur pembelian untuk set ke kas alur data
            $filters = array();
            $filters["uuid"] = $uuid;
            $filters["cabang_uuid"] = $this->cabang_selected_uuid;
            $res = $this->transaksi_engine->pembelian_retur_get_list($filters);
            $res = $res[0];
            $kas_alur_data['transaksi_pembelian_retur_uuid'] = $uuid;
            $kas_alur_data['transaksi_pembelian_retur_number_formatted'] = $res['number_formatted'];
            $kas_alur_data['keterangan'] = "Transaksi retur pembelian " . $res['number_formatted'];

            // **
            // simpan retur pembelian detail
            foreach ($final_item_list as $pembelian_retur_detail) {
                $pembelian_retur_detail["pembelian_retur_uuid"] = $pembelian_retur_uuid;
                $harga_beli_satuan_terkecil = (float) $pembelian_retur_detail["harga_beli_satuan_terkecil"];
                $item_uuid = $pembelian_retur_detail["item_uuid"];
                $item_struktur_satuan_harga_json = $pembelian_retur_detail["item_struktur_satuan_harga_json"];
                $item_struktur_satuan_harga_list = json_decode($item_struktur_satuan_harga_json, true);

                $selected_satuan = $pembelian_retur_detail["satuan"];
                $jumlah = (int) $pembelian_retur_detail["jumlah"];

                // **
                // simpan retur pembelian detail
                $res = $this->transaksi_engine->pembelian_retur_detail_save($pembelian_retur_detail);
                if ($res == false) throw new Exception("Gagal menyimpan retur pembelian #003");

                // **
                // ambil daftar harga beli satuan terkecil berdasarkan item id
                $rata_rata_harga_beli_satuan_terkecil = $this->item_engine->item_get_rata_rata_harga_beli_satuan_terkecil_for_item_uuid($item_uuid);
                if ($rata_rata_harga_beli_satuan_terkecil == 0) $rata_rata_harga_beli_satuan_terkecil = $harga_beli_satuan_terkecil;

                // **
                // update item cache harga pokok = rata_rata_harga_beli_satuan_terkecil
                $res = $this->item_engine->item_update_cache_harga_pokok($item_uuid, $rata_rata_harga_beli_satuan_terkecil);
                if ($res == false) throw new Exception("Gagal menyimpan retur pembelian #004");

                // **
                // hitung ulang struktur satuan dan harga
                $new_struktur_satuan_harga_list = array();
                $satuan_terkecil = "";
                foreach ($item_struktur_satuan_harga_list as $satuan => $l) {
                    $satuan_terkecil = $satuan;
                    break;
                }
                // set harga untuk satuan terkecil
                $item_struktur_satuan_harga_list[strtoupper($satuan_terkecil)]["harga_pokok"] = $rata_rata_harga_beli_satuan_terkecil;

                $total_pcs_satuan_terkecil = 1;
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
                // update item struktur satuan harga
                $res = $this->item_engine->item_update_struktur_satuan_harga_json($item_uuid, json_encode($new_struktur_satuan_harga_list));
                if ($res == false) throw new Exception("Gagal menyimpan retur pembelian #005");

                // **
                // get current total stock satuan terkecil
                $stock = $this->item_engine->item_get_total_stock_for_item_uuid($item_uuid);

                // update cache stock
                $res = $this->item_engine->item_update_cache_stock($item_uuid, $stock);
                if ($res == false) throw new Exception("Gagal menyimpan retur pembelian #006");
            }


            // **
            // update stock dan rata_rata_harga_beli satuan terkecil untuk item yang dihapus
            foreach ($deleted_item_data_list as $_item_uuid => $data) {
                // **
                // ambil daftar harga beli satuan terkecil berdasarkan item id
                $rata_rata_harga_beli_satuan_terkecil = $this->item_engine->item_get_rata_rata_harga_beli_satuan_terkecil_for_item_uuid($item_uuid);
                if ($rata_rata_harga_beli_satuan_terkecil == 0) $rata_rata_harga_beli_satuan_terkecil = $harga_beli_satuan_terkecil;

                // **
                // update item cache harga pokok = rata_rata_harga_beli_satuan_terkecil
                $res = $this->item_engine->item_update_cache_harga_pokok($_item_uuid, $rata_rata_harga_beli_satuan_terkecil);
                if ($res == false) throw new Exception("Gagal menyimpan retur pembelian #007");

                // **
                // get current total stock satuan terkecil
                $stock = $this->item_engine->item_get_total_stock_for_item_uuid($item_uuid);

                // update cache stock
                $res = $this->item_engine->item_update_cache_stock($_item_uuid, $stock);
                if ($res == false) throw new Exception("Gagal menyimpan retur pembelian #008");
            }

            if (!empty($kas_akun_uuid)) {
                // **
                // simpan kas alur
                $res = $this->kas_engine->kas_alur_save($kas_alur_data);
                if ($res == false) throw new Exception("Gagal menyimpan retur pembelian #009");
                if (empty($kas_alur_uuid) || $kas_alur_tanggal_changed) {
                    $kas_alur_uuid = $res;
                    // **
                    // update kas alur id untuk transaksi pembelian_retur
                    $res = $this->transaksi_engine->pembelian_retur_update_kas_alur_uuid($uuid, $kas_alur_uuid);
                    if ($res == false) throw new Exception("Gagal menyimpan retur pembelian #011");
                }
            } else {
                // **
                // hapus kas alur untuk transaksi retur pembelian id
                $res = $this->kas_engine->kas_alur_delete_for_transaksi_pembelian_retur_uuid($pembelian_retur_uuid);
                if ($res == false) throw new Exception("Gagal menyimpan retur pembelian #012");
            }
        } catch (Exception $e) {
            $this->db->trans_rollback();
            return set_http_response_error(501, "Error : " . $e->getMessage());
        }

        $this->db->trans_commit();
        return set_http_response_success("retur pembelian telah disimpan");
    }

    function item_get_detail_by_kode_and_pembelian_uuid()
    {
        $kode_satuan = $this->input->get("kode_satuan");
        $pembelian_uuid = $this->input->get("pembelian_uuid");
        if (empty($kode_satuan) && (int) $kode_satuan != 0) return set_http_response_error(HTTP_BAD_REQUEST, "EMPTY_KODE");
        if (empty($pembelian_uuid)) return set_http_response_error(HTTP_BAD_REQUEST, "EMPTY_PEMBELIAN_uuid");

        $exploded = explode(":", $kode_satuan);
        $kode = $exploded[0];
        $satuan = $exploded[1];

        $filters = array();
        $filters["item_kode"] = $kode;
        if (!empty($satuan)) $filters["satuan"] = $satuan;
        $filters["pembelian_uuid"] = $pembelian_uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $results = $this->transaksi_engine->pembelian_detail_get_list($filters);
        if (count($results) == 0) return set_http_response_success("OK", array());

        $data_list = array();
        foreach ($results as $res) {
            if (strtolower($res['item_tipe']) == "jasa") return set_http_response_error(HTTP_BAD_REQUEST, "Item dengan tipe jasa tidak bolehkan");

            $struktur_satuan_harga_json = $res["item_struktur_satuan_harga_json"];
            $struktur_satuan_harga_list = json_decode($struktur_satuan_harga_json, true);
            if (json_last_error_msg() != JSON_ERROR_NONE || !is_array($struktur_satuan_harga_list)) return set_http_response_error(HTTP_BAD_REQUEST, "Struktur satuan bermasalah");

            $satuan_list = array();
            $harga_list = array();
            foreach ($struktur_satuan_harga_list as $satuan => $l) {
                $harga_pokok = (float) $l["harga_pokok"];
                if ($harga_pokok > 0) {
                    $harga_pokok = round($harga_pokok);
                }
                $row = array(
                    "name" => $satuan,
                    "label" => $l["satuan"],
                    "harga_jual" => $l["harga_jual"],
                    "harga_beli" => $harga_pokok
                );
                $satuan_list[] = $row;

                $harga_list[strtoupper($satuan)] = $harga_pokok;
            }

            $jumlah = (int) $res["jumlah"];
            $harga_beli_satuan = (float) $res["harga_beli_satuan"];
            $potongan_persen = (float) $res["potongan_persen"];
            $potongan_harga = (float) $res["potongan_harga"];
            $sub_total = $jumlah * ($harga_beli_satuan - $potongan_harga);

            $data = array();
            $data["kode"] = $res["item_kode"];
            $data["nama"] = $res["item_nama"];
            $data["nama_kategori"] = $res["item_kategori_nama"];
            $data["satuan_list"] = $satuan_list;
            $data["jumlah"] = $res["jumlah"];
            $data["satuan"] = $res["satuan"];
            $data["harga_beli_satuan"] = $res["harga_beli_satuan"];
            $data["potongan_persen"] = $res["potongan_persen"];
            $data["sub_total"] = $sub_total;
            $data["harga_list"] = $harga_list;

            $data_list[] = $data;
        }

        return set_http_response_success("OK", $data_list);
    }

    function pembelian_get_detail_by_number_formatted()
    {
        $number_formatted = $this->input->get("pembelian_number_formatted");
        if (empty($number_formatted)) return set_http_response_error(HTTP_BAD_REQUEST, "EMPTY_KODE");

        $filters = array();
        $filters["number_formatted"] = $number_formatted;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->transaksi_engine->pembelian_get_list($filters);
        if (count($res) == 0) return set_http_response_error(HTTP_BAD_REQUEST, "NO_DATA");
        $res = $res[0];

        $data = array(
            'uuid' => trim($res["uuid"]),
            'pemasok_number_formatted' => $res['pemasok_number_formatted'],
            'pemasok_nama' => $res['pemasok_nama'],
            'pemasok_alamat' => $res['pemasok_alamat'],
            'pemasok_no_telepon' => $res['pemasok_no_telepon']
        );

        return set_http_response_success('OK', $data);
    }
}
