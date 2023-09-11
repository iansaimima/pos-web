<?php

defined('BASEPATH') or exit('No direct script access allowed');
class M_pembelian extends MY_Model
{

    private $transaksi_engine;
    private $pemasok_engine;
    private $item_engine;
    private $gudang_engine;
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
        $this->gudang_engine = new Gudang_engine();
        $this->kas_engine = new Kas_engine();

        $cabang_selected = get_session("cabang_selected");
        $this->cabang_selected_uuid = $cabang_selected["uuid"];
        $this->cabang_selected_kode = $cabang_selected["kode"];

        $user = get_session("user");
        $role = strtolower(get_session("role"));
        $this->actor_user_uuid = isset($user["uuid"]) ? $user["uuid"] : "";
        $this->actor_user_name = isset($user["name"]) ? $user["name"] : "";

        $privilege_list = isset($user["privilege_list"]) ? $user["privilege_list"] : array();

        $this->allow        = isset($privilege_list["allow_transaksi_pembelian"]) ? $privilege_list["allow_transaksi_pembelian"] : 0;
        $this->allow_create = isset($privilege_list["allow_transaksi_pembelian_create"]) ? $privilege_list["allow_transaksi_pembelian_create"] : 0;
        $this->allow_update = isset($privilege_list["allow_transaksi_pembelian_update"]) ? $privilege_list["allow_transaksi_pembelian_update"] : 0;
        $this->allow_delete = isset($privilege_list["allow_transaksi_pembelian_delete"]) ? $privilege_list["allow_transaksi_pembelian_delete"] : 0;

        $this->allow_detail_create = isset($privilege_list["allow_transaksi_pembelian_detail_create"]) ? $privilege_list["allow_transaksi_pembelian_detail_create"] : 0;
        $this->allow_detail_update = isset($privilege_list["allow_transaksi_pembelian_detail_update"]) ? $privilege_list["allow_transaksi_pembelian_detail_update"] : 0;
        $this->allow_detail_delete = isset($privilege_list["allow_transaksi_pembelian_detail_delete"]) ? $privilege_list["allow_transaksi_pembelian_detail_delete"] : 0;
    }

    function pembelian_get_list($filters = array(), $gudang_uuid = "")
    {
        if (!$this->allow) return array();

        if(!empty($gudang_uuid)) $filters["gudang_uuid"] = $gudang_uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->transaksi_engine->pembelian_get_list($filters, true, true);
        $no = isset($filters["start"]) ? (int) $filters["start"] : 0;

        $final_res = array();
        foreach ($res as $r) {
            $no++;

            $potongan = (float) $r['potongan'];
            $total = isset($r['total']) ? (float) $r['total'] : 0;
            $total = $total - $potongan;
            if ($total < 0) $total = 0;

            $row = array();
            $row["no"] = $no;
            $row["uuid"] = trim($r["uuid"]);
            $row["number_formatted"] = $r["number_formatted"];
            $row["no_nota_vendor"] = $r["no_nota_vendor"];
            $row["tanggal"] = date("d M Y", strtotime($r["tanggal"]));
            $row["kas_akun_nama"] = $r["kas_akun_nama"];
            $row["pemasok_number_formatted"] = $r["pemasok_number_formatted"];
            $row["pemasok_nama"] = $r["pemasok_nama"];
            $row["potongan"] = number_format($potongan, 0, ',', '.');
            $row["total_akhir"] = number_format($r['total_akhir'], 0, ',','.');
            $row["sisa"] = number_format($r['sisa'], 0);
            $row["status"] = (int) $r["lunas"] == 1 ? "Lunas" : "Belum Lunas";

            $final_res[] = $row;
        }

        return $final_res;
    }

    function pembelian_get_filtered_total($filters = array(), $gudang_uuid = "")
    {
        if (!$this->allow) return 0;

        if(!empty($gudang_uuid)) $filters["gudang_uuid"] = $gudang_uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->transaksi_engine->pembelian_get_list($filters, false, true);
        return count($res);
    }

    function pembelian_get_total($filters = array(), $gudang_uuid = "")
    {
        if (!$this->allow) return 0;

        if(!empty($gudang_uuid)) $filters["gudang_uuid"] = $gudang_uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->transaksi_engine->pembelian_get_list($filters);
        return count($res);
    }

    function pembelian_get($uuid = "")
    {
        if (!$this->allow) return array();

        $filters = array();
        $filters["uuid"] = $uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->transaksi_engine->pembelian_get_list($filters);
        if (count($res) == 0) return array();
        $pembelian = $res[0];
        $pembelian_uuid = $pembelian["uuid"];

        // **
        // get pembelian detail list for pembelian id
        $filters = array();
        $filters["pembelian_uuid"] = $pembelian_uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $detail_list = $this->transaksi_engine->pembelian_detail_get_list($filters);
        $pembelian["detail"] = $detail_list;

        return $pembelian;
    }

    function pembelian_delete($uuid = "")
    {
        if (!$this->allow_delete) return set_http_response_error(HTTP_FORBIDDEN, "Akses ditolak");

        $filters = array();
        $filters["uuid"] = $uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->transaksi_engine->pembelian_get_list($filters);
        if (count($res) == 0) return set_http_response_error(HTTP_BAD_REQUEST, "Pembelian tidak ditemukan");
        $res = $res[0];
        $uuid = $res["uuid"];
        $pembelian_uuid = $uuid;
        $kas_alur_uuid = $res['kas_alur_uuid'];        

        // **
        // check jika ada pembelian retur untuk pembelian id
        $filters = array();
        $filters["pembelian_uuid"] = $pembelian_uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res= $this->transaksi_engine->pembelian_retur_get_list($filters);
        if(count($res) > 0) return set_http_response_error(HTTP_BAD_REQUEST, "Ada retur pembelian untuk pembelian ini. Pembelian tidak dapat dihapus");

        // **
        // get item id list for pembelian detail by pembelian id
        $filters = array();
        $filters["pembelian_uuid"] = $pembelian_uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->transaksi_engine->pembelian_detail_get_list($filters);

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
            $margin_persen = 0;
            if (count($item_list) > 0) {
                $item = $item_list[0];
                $margin_persen = (float) $item['margin_persen'];
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
            $item_data_list[$item_uuid]["margin_persen"] = $margin_persen;
        }

        $this->db->trans_start();
        try {
            // **
            // hapus pembelian
            $res = $this->transaksi_engine->pembelian_delete($pembelian_uuid);
            if ($res == false ) throw new Exception("Gagal menghapus pembelian #001");

            // **
            // hapus pembelian detail
            $res = $this->transaksi_engine->pembelian_detail_delete_by_pembelian_uuid($pembelian_uuid);
            if ($res == false ) throw new Exception("Gagal menghapus pembelian #002");

            foreach ($item_data_list as $item_uuid => $data) {
                $rata_rata_harga_beli_satuan_terkecil = $data['rata_rata_harga_beli_satuan_terkecil'];
                $stock = $data['stock'];
                $item_struktur_satuan_harga_list = $data['item_struktur_satuan_harga_list'];
                $margin_persen = (float) $data["margin_persen"];

                // **
                // ambil daftar harga beli satuan terkecil berdasarkan item id
                $rata_rata_harga_beli_satuan_terkecil = $this->item_engine->item_get_rata_rata_harga_beli_satuan_terkecil_for_item_uuid($item_uuid);

                // **
                // update item cache harga pokok = rata_rata_harga_beli_satuan_terkecil
                $res = $this->item_engine->item_update_cache_harga_pokok($item_uuid, $rata_rata_harga_beli_satuan_terkecil);
                if ($res == false ) throw new Exception("Gagal menghapus pembelian #003");

                // **
                // get current total stock satuan terkecil
                $stock = $this->item_engine->item_get_total_stock_for_item_uuid($item_uuid);

                // update cache stock
                $res = $this->item_engine->item_update_cache_stock($item_uuid, $stock);
                if ($res == false ) throw new Exception("Gagal menghapus pembelian #004");

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
                if ($res == false ) throw new Exception("Gagal menghapus pembelian #005");
            }

            // **
            // delete kas alur
            $res = $this->kas_engine->kas_alur_delete_for_transaksi_pembelian_uuid($pembelian_uuid);
            if ($res == false ) throw new Exception("Gagal menghapus pembelian #005");
        } catch (Exception $e) {
            $this->db->trans_rollback();
            return set_http_response_error(501, "Error : " . $e->getMessage());
        }

        $this->db->trans_commit();
        return set_http_response_success("Pembelian telah dihapus");
    }

    function pembelian_save()
    {
        $uuid = $this->input->post("uuid");
        $tanggal = $this->input->post("tanggal");
        $pemasok_uuid = $this->input->post("pemasok_uuid");
        $gudang_uuid = $this->input->post("gudang_uuid");
        $item_detail_json = $this->input->post("item_detail");
        $potongan = to_number($this->input->post("potongan"));
        $no_nota_vendor = $this->input->post("no_nota_vendor");
        $kas_akun_uuid = $this->input->post("kas_akun_uuid");
        $bayar = to_number($this->input->post("bayar"));

        // **
        // validasi
        if (empty($tanggal)) return set_http_response_error(HTTP_BAD_REQUEST, "Tanggal tidak valid");
        if(strtotime($tanggal) > time()) {
            return set_http_response_error(HTTP_BAD_REQUEST, "Tanggal Pembelian tidak boleh lebih dari tanggal hari ini");
        }

        $item_detail_list = json_decode($item_detail_json, true);
        if (!is_array($item_detail_list)) $item_detail_list = array();
        if (count($item_detail_list) == 0) return set_http_response_error(HTTP_BAD_REQUEST, "Belum ada item yang dipilih pada pembelian");


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
        // check pemasok uuid
        $filters = array();
        $filters["uuid"] = $pemasok_uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->pemasok_engine->pemasok_get_list($filters);
        if (count($res) == 0) return set_http_response_error(HTTP_BAD_REQUEST, "Pemasok tidak ditemukan");
        $res = $res[0];
        $pemasok_uuid = $res["uuid"];
        $pemasok_number_formatted = trim($res["number_formatted"]);
        $pemasok_nama = trim($res["nama"]);
        $pemasok_alamat = trim($res["alamat"]);
        $pemasok_no_telepon = trim($res["no_telepon"]);

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

        $pembelian_uuid = "";
        $kas_alur_uuid = "";
        $kas_alur_data = array();
        $created = date("Y-m-d H:i:s");
        $creator_user_uuid = $this->actor_user_uuid;
        $creator_user_name = $this->actor_user_name;
        $number = 0;
        $number_formatted = PREFIX_PEMBELIAN . "/" . $this->cabang_selected_kode . "/" . $gudang_kode . "/" . microtime_();
        $tahun = date("Y", strtotime($tanggal . " 00:00:00"));
        $jam = date("H:i:s");

        $current_item_uuid_list = array();
        
        $old_tahun = $tahun;
        $old_gudang_kode = $gudang_kode;
        if (!empty($uuid)) {
            $filters = array();
            $filters["uuid"] = $uuid;
            $filters["cabang_uuid"] = $this->cabang_selected_uuid;
            $res = $this->transaksi_engine->pembelian_get_list($filters);
            if (count($res) == 0) return set_http_response_error(HTTP_BAD_REQUEST, "Pembelian tidak ditemukan");
            $res = $res[0];
            $uuid = $res["uuid"];
            $pembelian_uuid = $uuid;
            $created = $res["created"];
            $creator_user_uuid = $res["creator_user_uuid"];
            $creator_user_name = $res["creator_user_name"];
            $number = (int) $res["number"];
            $number_formatted = trim($res["number_formatted"]);
            $number_formatted = PREFIX_PEMBELIAN . "/" . $this->cabang_selected_kode . "/" . $gudang_kode . "/" . microtime_();

            // **
            // check jika sudah ada retur pembelian, maka abaikan edit
            $filters = array();
            $filters["pembelian_uuid"] = $pembelian_uuid;
            $res = $this->transaksi_engine->pembelian_retur_get_list($filters);
            if(count($res) > 0) return set_http_response_error(HTTP_BAD_REQUEST, "Pembelian ini sudah memiliki Retur dan tidak bisa diubah. Hapus Retur Pembelian lebih dulu untuk bisa mengubah pembelian ini");

            $old_gudang_kode = $res["gudang_kode"];
            $old_tahun = $res["tahun"];

            // **
            // get current pembelian detail list
            $filters = array();
            $filters["pembelian_uuid"] = $pembelian_uuid;
            $filters["cabang_uuid"] = $this->cabang_selected_uuid;
            $list = $this->transaksi_engine->pembelian_detail_get_list($filters);
            foreach ($list as $l) {
                $current_item_uuid = $l['item_uuid'];
                $current_item_uuid_list[] = $current_item_uuid;
            }

            $jam = date("H:i:s", strtotime($res["tanggal"]));

            // **
            // dapatkan kas alur id berdasarkan transaksi_pembelian_uuid
            $filters = array();
            $filters['transaksi_pembelian_uuid'] = $uuid;
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
        // -- generate pembelian detail
        // -- get harga beli satuan terkecil
        // -- get sub_total dari total harga beli
        // -- genereate item id list
        foreach ($item_detail_list as $i) {
            $kode = $i["item_code"];
            $selected_satuan = $i["satuan"];
            $jumlah = to_number($i["jumlah"]);
            $harga_beli = to_number($i["harga_beli"]);
            $potongan_persen = (float) $i['potongan'];
            $potongan_harga = 0;
            if ($harga_beli > 0 && $potongan_persen > 0) {
                $potongan_harga = $harga_beli * ($potongan_persen / 100);
            }
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
            $margin_persen = $res["margin_persen"];
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

            // **
            // hitung cache total
            $total = $jumlah * ($harga_beli - $potongan_harga);
            $sub_total += $total;

            $pembelian_detail_data_list = array(
                "uuid"     => "",
                "created" => date("Y-m-d H:i:s"),
                "creator_user_uuid" => $this->actor_user_uuid,
                "creator_user_name" => $this->actor_user_name,
                "last_updated" => date("Y-m-d H:i:s"),
                "last_updated_user_uuid" => $this->actor_user_uuid,
                "last_updated_user_name" => $this->actor_user_name,
                "pembelian_uuid" => $uuid,
                "item_uuid" => $item_uuid,
                "item_kode" => $item_kode,
                "item_barcode" => $item_barcode,
                "item_nama" => $item_nama,
                "item_struktur_satuan_harga_json" => $item_struktur_satuan_harga_json,
                "item_tipe" => $item_tipe,
                "item_margin_persen" => $margin_persen,
                "item_kategori_uuid" => $item_kategori_uuid,
                "item_kategori_nama" => $item_kategori_nama,
                "item_jenis_perhitungan_harga_jual" => $item_jenis_perhitungan_harga_jual,
                "harga_beli_satuan_terkecil" => $harga_beli_satuan_terkecil,
                "jumlah" => $jumlah,
                "satuan" => $selected_satuan,
                "harga_beli_satuan" => $harga_beli,
                "jumlah_satuan_terkecil" => $jumlah_satuan_terkecil,
                "potongan_persen" => $potongan_persen,
                "potongan_harga" => (int) $potongan_harga,
                "cabang_uuid" => $this->cabang_selected_uuid,
            );

            $final_item_list[] = $pembelian_detail_data_list;
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

        $pembelian_data = array(
            "uuid" => $uuid,
            "created" => $created,
            "creator_user_uuid" => $creator_user_uuid,
            "creator_user_name" => $creator_user_name,
            "last_updated" => date("Y-m-d H:i:s"),
            "last_updated_user_uuid" => $this->actor_user_uuid,
            "last_updated_user_name" => $this->actor_user_name,
            "number" => $number,
            "number_formatted" => $number_formatted,
            "no_nota_vendor" => $no_nota_vendor,
            "tanggal" => $tanggal . " $jam",
            "tahun" => $tahun,
            "pemasok_uuid" => $pemasok_uuid,
            "pemasok_number_formatted" => $pemasok_number_formatted,
            "pemasok_nama" => $pemasok_nama,
            "pemasok_alamat" => $pemasok_alamat,
            "pemasok_no_telepon" => $pemasok_no_telepon,
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

            $kas_alur_data["kas_kategori_uuid"] = -10;
            $kas_alur_data["kas_kategori_nama"] = "Transaksi Pembelian";

            $kas_alur_data["alur_kas"] = "Keluar";

            $kas_alur_data["jumlah_masuk"] = 0;
            $kas_alur_data["jumlah_keluar"] = $bayar;

            $kas_alur_data["keterangan"] = "Transaksi pembelian";

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
            $kas_alur_data["cabang_uuid"] = $this->cabang_selected_uuid;
            $kas_alur_data["cabang_kode"] = $this->cabang_selected_kode;
        } else {
            $kas_alur_tanggal = $kas_alur_data["tanggal"];

            if($kas_alur_tanggal != $tanggal) $kas_alur_tanggal_changed = true;
            
            $kas_alur_data["jumlah_keluar"] = $bayar;
            $kas_alur_data["kas_akun_uuid"] = $kas_akun_uuid;
            $kas_alur_data["kas_akun_nama"] = $kas_akun_data["nama"];
        }


        $this->db->trans_start();
        try {
            // **
            // hapus semua pembelian detail untuk pembelian id jika pembelian id != 0
            if (!empty($uuid)) {
                $res = $this->transaksi_engine->pembelian_detail_delete_by_pembelian_uuid($uuid);
                if ($res == false ) throw new Exception("Gagal menyimpan pembelian #001");
            }

            // **
            // simpan pembelian
            $res = $this->transaksi_engine->pembelian_save($pembelian_data);
            if ($res == false ) throw new Exception("Gagal menyimpan pembelian #002");
            if (empty($uuid)) {
                $uuid = $res;
                $pembelian_uuid = $uuid;
            }

            // **
            // get detail pembelian untuk set ke kas alur data
            $filters = array();
            $filters["uuid"] = $uuid;
            $filters["cabang_uuid"] = $this->cabang_selected_uuid;
            $res = $this->transaksi_engine->pembelian_get_list($filters);
            $res = $res[0];
            $kas_alur_data['transaksi_pembelian_uuid'] = $uuid;
            $kas_alur_data['transaksi_pembelian_number_formatted'] = $res['number_formatted'];
            $kas_alur_data['keterangan'] = "Transaksi pembelian " . $res['number_formatted'];

            // **
            // simpan pembelian detail
            foreach ($final_item_list as $pembelian_detail) {
                $pembelian_detail["pembelian_uuid"] = $pembelian_uuid;
                $harga_beli_satuan_terkecil = (float) $pembelian_detail["harga_beli_satuan_terkecil"];
                $item_uuid = $pembelian_detail["item_uuid"];
                $margin_persen = $pembelian_detail["item_margin_persen"];
                $item_jenis_perhitungan_harga_jual = $pembelian_detail["item_jenis_perhitungan_harga_jual"];
                $item_struktur_satuan_harga_json = $pembelian_detail["item_struktur_satuan_harga_json"];
                $item_struktur_satuan_harga_list = json_decode($item_struktur_satuan_harga_json, true);

                $selected_satuan = $pembelian_detail["satuan"];
                $jumlah = (int) $pembelian_detail["jumlah"];

                // **
                // simpan pembelian detail
                $res = $this->transaksi_engine->pembelian_detail_save($pembelian_detail);
                if ($res == false ) throw new Exception("Gagal menyimpan pembelian #003");

                // **
                // ambil daftar harga beli satuan terkecil berdasarkan item id
                $rata_rata_harga_beli_satuan_terkecil = $this->item_engine->item_get_rata_rata_harga_beli_satuan_terkecil_for_item_uuid($item_uuid);
                if ($rata_rata_harga_beli_satuan_terkecil == 0) $rata_rata_harga_beli_satuan_terkecil = $harga_beli_satuan_terkecil;

                // **
                // update item cache harga pokok = rata_rata_harga_beli_satuan_terkecil
                $res = $this->item_engine->item_update_cache_harga_pokok($item_uuid, $rata_rata_harga_beli_satuan_terkecil);
                if ($res == false ) throw new Exception("Gagal menyimpan pembelian #004");

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
                    $margin = isset($l["margin"]) ? (float) $l["margin"] : 0;
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
                if ($res == false ) throw new Exception("Gagal menyimpan pembelian #005");

                // **
                // get current total stock satuan terkecil
                $stock = $this->item_engine->item_get_total_stock_for_item_uuid($item_uuid);

                // update cache stock
                $res = $this->item_engine->item_update_cache_stock($item_uuid, $stock);
                if ($res == false ) throw new Exception("Gagal menyimpan pembelian #006");
            }


            // **
            // update stock dan rata_rata_harga_beli satuan terkecil untuk item yang dihapus
            foreach ($deleted_item_data_list as $_item_uuid => $data) {
                $rata_rata_harga_beli_satuan_terkecil = $data['rata_rata_harga_beli_satuan_terkecil'];
                $stock = $data['stock'];

                // **
                // ambil daftar harga beli satuan terkecil berdasarkan item id
                $rata_rata_harga_beli_satuan_terkecil = $this->item_engine->item_get_rata_rata_harga_beli_satuan_terkecil_for_item_uuid($item_uuid);
                if ($rata_rata_harga_beli_satuan_terkecil == 0) $rata_rata_harga_beli_satuan_terkecil = $harga_beli_satuan_terkecil;

                // **
                // update item cache harga pokok = rata_rata_harga_beli_satuan_terkecil
                $res = $this->item_engine->item_update_cache_harga_pokok($_item_uuid, $rata_rata_harga_beli_satuan_terkecil);
                if ($res == false ) throw new Exception("Gagal menyimpan pembelian #007");

                // **
                // get current total stock satuan terkecil
                $stock = $this->item_engine->item_get_total_stock_for_item_uuid($item_uuid);

                // update cache stock
                $res = $this->item_engine->item_update_cache_stock($_item_uuid, $stock);
                if ($res == false ) throw new Exception("Gagal menyimpan pembelian #008");
            }

            // **
            // simpan kas alur
            $res = $this->kas_engine->kas_alur_save($kas_alur_data);
            if ($res == false ) throw new Exception("Gagal menyimpan pembelian #009");  
            if (empty($kas_alur_uuid)) {
                $kas_alur_uuid = $res;
                // **
                // update kas alur id untuk transaksi penjualan
                $res = $this->transaksi_engine->penjualan_update_kas_alur_uuid($uuid, $kas_alur_uuid);
                if ($res == false) throw new Exception("Gagal menyimpan pembelian #010");
            }          
        } catch (Exception $e) {
            $this->db->trans_rollback();
            return set_http_response_error(501, "Error : " . $e->getMessage());
        }

        $this->db->trans_commit();
        return set_http_response_success("Pembelian telah disimpan");
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
                "harga_jual" => $l["harga_jual"],
                "harga_beli" => $l["harga_pokok"]
            );
            $satuan_list[] = $row;

            $harga_list[strtoupper($satuan)] = $l['harga_pokok'];
        }

        $data = array();
        $data["kode"] = $res["kode"];
        $data["nama"] = $res["nama"];
        $data["nama_kategori"] = $res["item_kategori_nama"];
        $data["satuan_list"] = $satuan_list;
        $data["harga_list"] = $harga_list;

        return set_http_response_success("OK", $data);
    }

    function pemasok_get_detail_by_uuid($uuid = "")
    {
        if (empty($uuid)) return set_http_response_error(HTTP_BAD_REQUEST, "EMPTY_KODE");

        $filters = array();
        $filters["uuid"] = $uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->pemasok_engine->pemasok_get_list($filters);
        if (count($res) == 0) return set_http_response_error(HTTP_BAD_REQUEST, "NO_DATA");
        $res = $res[0];

        $data = array(
            'nama' => $res["nama"],
            'alamat' => $res['alamat'],
            'no_telepon' => $res['no_telepon'],
            'keterangan' => $res['keterangan']
        );

        return set_http_response_success('OK', $data);
    }




    // ***
    // sisi laporan
    
    function laporan_pembelian_harian(){
        $start_date = $this->input->get("start_date");
        $end_date = $this->input->get("end_date");
        $gudang_uuid = $this->input->get("gudang_uuid");

        if(empty($start_date)) $start_date = date("Y-m-d");
        if(empty($end_date)) $end_date = date("Y-m-d");        

        $list = $this->settings_engine->get_all_settings();
        $settings_list = array();
        foreach($list as $l){
            $_key = $l['_key'];
            $settings_list[$_key] = $l;
        }
        
        $filter_gudang_nama = "Semua gudang";
        $filter_gudang_uuid = "";
        
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
        if(!empty($filter_gudang_uuid)) $filters["gudang_uuid"] = $filter_gudang_uuid;
        $list = $this->transaksi_engine->pembelian_get_list($filters);
        
        $temp_list = array();      
        $total_jumlah_transaksi = 0;
        $grand_total_transaksi = 0;
        $total_jumlah_bayar_tunai = 0;
        $total_jumlah_bayar_non_tunai = 0;  
        foreach($list as $l){
            $tanggal = date("Y-m-d", strtotime($l["tanggal"]));
            $total = (double) $l["total_akhir"];

            $total_tunai = 0;
            $total_non_tunai = 0;
            if(isset($l["metode_pembayaran"]) && strtolower($l["metode_pembayaran"]) == "tunai") {
                $total_tunai = $total;
            }else{
                $total_non_tunai = $total;
            }


            if(!isset($temp_list[$tanggal])){
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
        $i=0;
        foreach($temp_list as $tanggal => $l){
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
                "gudang" => $filter_gudang_nama
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

    function laporan_pembelian_rekap(){
        $start_date = $this->input->get("start_date");
        $end_date = $this->input->get("end_date");
        $pemasok_uuid = get("pemasok_uuid");
        $gudang_uuid = get("gudang_uuid");

        if(empty($start_date)) $start_date = date("Y-m-d");
        if(empty($end_date)) $end_date = date("Y-m-d");

        $list = $this->settings_engine->get_all_settings();
        $settings_list = array();
        foreach($list as $l){
            $_key = $l['_key'];
            $settings_list[$_key] = $l;
        }

        $filter_pemasok_uuid = "";
        $filter_pemasok_nama = "Semua pemasok";
        // **
        // check pemasok uuid
        if (!empty($pemasok_uuid)) {
            $filters = array();
            $filters["uuid"] = $pemasok_uuid;
            $filters["cabang_uuid"] = $this->cabang_selected_uuid;
            $res = $this->pemasok_engine->pemasok_get_list($filters);
            if (count($res) > 0) {
                $res = $res[0];
                $filter_pemasok_uuid = $res["uuid"];
                $filter_pemasok_nama = $res["nama"];
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
        if (!empty($filter_pemasok_uuid)) $filters["pemasok_uuid"] = $filter_pemasok_uuid;
        if (!empty($filter_gudang_uuid)) $filters["gudang_uuid"] = $filter_gudang_uuid;
        $list = $this->transaksi_engine->pembelian_get_list($filters);
        
        $final_list = array();      
        $grand_sub_total = 0;
        $total_potongan = 0;
        $grand_total_akhir = 0;
        $total_bayar_tunai = 0;
        $total_bayar_non_tunai = 0;
        $no=0;
        foreach($list as $l){
            $no++;

            $no_transaksi = $l["number_formatted"];
            $tanggal = date("Y-m-d", strtotime($l["tanggal"]));
            $pemasok_nama = $l["pemasok_nama"];

            $sub_total = (double) $l["sub_total"];
            $potongan = (double) $l["potongan"];
            $total_akhir = (double) $l["total_akhir"];
            $bayar_tunai = (double) $l["bayar"];
            $bayar_non_tunai = (double) $l["sisa"];

            if($bayar_tunai > $total_akhir) $bayar_tunai = $total_akhir;

            $row = array(
                "no" => $no,
                "no_transaksi" => $no_transaksi, 
                "tanggal" => date("d-m-Y", strtotime($tanggal)), 
                "pemasok_nama" => $pemasok_nama,

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
                "pemasok" => $filter_pemasok_nama,
                "gudang" => $filter_gudang_nama
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

    function laporan_pembelian_detail(){
        $start_date = $this->input->get("start_date");
        $end_date = $this->input->get("end_date");
        $pemasok_uuid = get("pemasok_uuid");
        $gudang_uuid = get("gudang_uuid");

        if(empty($start_date)) $start_date = date("Y-m-d");
        if(empty($end_date)) $end_date = date("Y-m-d");

        $list = $this->settings_engine->get_all_settings();
        $settings_list = array();
        foreach($list as $l){
            $_key = $l['_key'];
            $settings_list[$_key] = $l;
        }

        $filter_pemasok_uuid = "";
        $filter_pemasok_nama = "Semua Pemasok";
        // **
        // check pemasok uuid
        if (!empty($pemasok_uuid)) {
            $filters = array();
            $filters["uuid"] = $pemasok_uuid;
            $filters["cabang_uuid"] = $this->cabang_selected_uuid;
            $res = $this->pemasok_engine->pemasok_get_list($filters);
            if (count($res) > 0) {
                $res = $res[0];
                $filter_pemasok_uuid = $res["uuid"];
                $filter_pemasok_nama = $res["nama"];
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
                "pemasok" => $filter_pemasok_nama,
                "gudang" => $filter_gudang_nama
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
        if (!empty($filter_pemasok_uuid)) $filters["pemasok_uuid"] = $filter_pemasok_uuid;
        if (!empty($filter_gudang_uuid)) $filters["gudang_uuid"] = $filter_gudang_uuid;
        $pembelian_list = $this->transaksi_engine->pembelian_get_list($filters);

        if(count($pembelian_list) == 0) return $final_data;

        // **
        // generate pembelian id list
        $grand_total = 0;
        $grand_sub_total = 0;
        $total_potongan = 0;
        $pembelian_data_list = array();
        $pembelian_uuid_list = array();
        foreach($pembelian_list as $l){
            $pembelian_uuid = $l["uuid"];
            $no_pembelian = trim($l["number_formatted"]);
            $tanggal = date("d-m-Y", strtotime($l["tanggal"]));
            $pemasok_number_formatted = trim($l["pemasok_number_formatted"]);
            $pemasok_nama = trim($l["pemasok_nama"]);
            $pemasok_alamat = trim($l["pemasok_alamat"]);

            $sub_total = (double) $l["sub_total"];
            $potongan = (double) $l["potongan"];
            $total_akhir = (double) $l["total_akhir"];

            $pembelian_uuid_list[] = $pembelian_uuid;
            $pembelian_data_list[$pembelian_uuid] = array(
                "no_pembelian" => $no_pembelian, 
                "tanggal" => $tanggal, 
                "pemasok_number_formatted" => $pemasok_number_formatted, 
                "pemasok_nama" => $pemasok_nama, 
                "pemasok_alamat" => $pemasok_alamat, 
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
        // pembelian detail untuk pembelian id list
        $filters = array();
        $filters["pembelian_uuid_list"] = $pembelian_uuid_list;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $detail_list = $this->transaksi_engine->pembelian_detail_get_list($filters);
        foreach($detail_list as $d) {
            $pembelian_uuid = $d["pembelian_uuid"];

            $item_kode = $d["item_kode"];
            $item_nama = $d["item_nama"];
            $jumlah = (int) $d["jumlah"];
            $satuan = trim($d["satuan"]);
            $harga_beli_satuan = (double) $d["harga_beli_satuan"];
            $potongan_persen = (double) $d["potongan_persen"];
            $potongan_harga = (double) $d["potongan_harga"];

            $total = $jumlah * ($harga_beli_satuan - $potongan_harga);

            if(isset($pembelian_data_list[$pembelian_uuid])) {
                $pembelian_data_list[$pembelian_uuid]["details"][] = array(
                    "item_kode" => $item_kode, 
                    "item_nama" => $item_nama, 
                    "jumlah" => number_format($jumlah), 
                    "satuan" => $satuan, 
                    "harga_beli_satuan" => number_format($harga_beli_satuan), 
                    "potongan_persen" => number_format($potongan_persen,2), 
                    "total" => number_format($total)
                );

                $pembelian_data_list[$pembelian_uuid]["footer"]["total_jumlah"] += $jumlah;
                $pembelian_data_list[$pembelian_uuid]["footer"]["total_sub_total"] += $total;
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
                "pemasok" => $filter_pemasok_nama,
                "gudang" => $filter_gudang_nama
            ),


            "body" => $pembelian_data_list,

            "footer" => array(
                "grand_sub_total" => number_format($grand_sub_total),
                "total_potongan" => number_format($total_potongan),
                "grand_total" => number_format($grand_total),
            ),
        );

        return $final_data;
    }
}
