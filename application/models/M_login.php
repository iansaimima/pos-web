<?php

defined('BASEPATH') or exit('No direct script access allowed');

class M_login extends MY_Model
{

    private $user_engine;
    private $settings_engine;
    private $cabang_engine;
    private $gudang_engine;
    private $pelanggan_engine;
    public function __construct()
    {
        parent::__construct();

        $this->user_engine = new User_engine();
        $this->settings_engine = new Settings_engine();
        $this->cabang_engine = new Cabang_engine();
        $this->gudang_engine = new Gudang_engine();
        $this->pelanggan_engine = new Gudang_engine();
        $this->pelanggan_engine = new Pelanggan_engine();

        $res = $this->settings_engine->get_all_settings();
        $settings = array();
        foreach ($res as $r) {
            $settings[$r['_key']] = $r;
        }
        set_session("settings", $settings);
    }

    function do_logout()
    {
        set_session("username", "");
        set_session("password", "");
        set_session("user", array());
        set_session("settings", array());
        set_session("cabang_list", array());
        set_session("cabang_selected", array());
    }

    function check_login($username = "", $password = "")
    {
        $uri_1 = $this->uri->segment(1);

        if (empty($username) || empty($password)) {
            return set_http_response_error(401, "Log in Gagal");
        };

        set_session("username", $username);
        set_session("password", $password);

        $cabang_selected = get_session("cabang_selected");
        if (!is_array($cabang_selected)) $cabang_selected = array();


        // **
        // get cabang list
        $filters = array();
        $cabang_list = $this->cabang_engine->cabang_get_list($filters);

        $user_role_privilege_list = $this->user_engine->user_role_privilege_get_list();

        $password = $this->encrypt_password($password);
        $filters = array();
        $filters["username"] = $username;
        $filters["password"] = $password;
        $res = $this->user_engine->user_get_list($filters);
        if (count($res) == 0) {
            return set_http_response_error(HTTP_UNAUTHORIZED, "Log in Gagal");
        }

        $user_detail = $res[0];
        $user_role_name = trim($user_detail["user_role_name"]);
        if (strtolower($user_role_name) == "kasir" && $uri_1 != "kasir") {
            if ($uri_1 != "api") {
                redirect_url('kasir/login/do_logout');
            }
        }
        if (strtolower($user_role_name) != "kasir" && $uri_1 == "kasir") {
            return set_http_response_error(HTTP_BAD_REQUEST, "Anda tidak memiliki akses kasir. Silahkan login melalui menu admin.");
        }
        $cabang_uuid_list_json = trim($user_detail["cabang_uuid_list_json"]);
        $temp_cabang_uuid_list = json_decode($cabang_uuid_list_json, true);
        if (!is_array($temp_cabang_uuid_list)) $temp_cabang_uuid_list = array();
        // **
        // generate temp cabang uuid list ke cabang uuid hash list
        $cabang_uuid_list = array();
        $cabang_uuid_list_ = array();
        foreach ($temp_cabang_uuid_list as $c => $cabang_uuid) {
            $cabang_uuid_list[$cabang_uuid] = array();
            $cabang_uuid_list_[$cabang_uuid] = $cabang_uuid;
        }

        $new_cabang_list = array();
        foreach ($cabang_list as $c) {
            $new_c = array(
                "uuid" => $c["uuid"],
                "kode" => $c["kode"],
                "nama" => $c["nama"],
                "alamat" => $c["alamat"],
                "no_telepon" => $c["no_telepon"],
            );
            $new_cabang_list[] = $new_c;

            if (count($cabang_selected) == 0) {
                if (isset($cabang_uuid_list[$c["uuid"]])) {
                    $cabang_selected = $c;
                }
            } else {
                if ($cabang_selected["uuid"] == $c["uuid"]) {
                    $cabang_selected = $c;
                }
            }

            if (count($cabang_selected) > 0) {
                $transaksi_penjualan_default_pelanggan_uuid = $cabang_selected["transaksi_penjualan_default_pelanggan_uuid"];
                $transaksi_penjualan_default_gudang_uuid = $cabang_selected["transaksi_penjualan_default_gudang_uuid"];
                // **
                // get gudang data
                $filters = array();
                $filters["uuid"] = $transaksi_penjualan_default_gudang_uuid;
                $res = $this->gudang_engine->gudang_get_list($filters);
                $cabang_selected["transaksi_penjualan_default_gudang_kode"] = "";
                $cabang_selected["transaksi_penjualan_default_gudang_nama"] = "";

                $c["transaksi_penjualan_default_gudang_kode"] = "";
                $c["transaksi_penjualan_default_gudang_nama"] = "";
                if (count($res) > 0) {
                    $res = $res[0];

                    $cabang_selected["transaksi_penjualan_default_gudang_kode"] = $res["kode"];
                    $cabang_selected["transaksi_penjualan_default_gudang_nama"] = $res["nama"];

                    $c["transaksi_penjualan_default_gudang_kode"] = $res["kode"];
                    $c["transaksi_penjualan_default_gudang_nama"] = $res["nama"];
                }

                // **
                // get pelanggan data
                $filters = array();
                $filters["uuid"] = $transaksi_penjualan_default_pelanggan_uuid;
                $res = $this->pelanggan_engine->pelanggan_get_list($filters);
                $cabang_selected["transaksi_penjualan_default_pelanggan_number_formatted"] = "";
                $cabang_selected["transaksi_penjualan_default_pelanggan_nama"] = "";
                $cabang_selected["transaksi_penjualan_default_pelanggan_alamat"] = "";
                $cabang_selected["transaksi_penjualan_default_pelanggan_no_telepon"] = "";

                $c["transaksi_penjualan_default_pelanggan_number_formatted"] = "";
                $c["transaksi_penjualan_default_pelanggan_nama"] = "";
                $c["transaksi_penjualan_default_pelanggan_alamat"] = "";
                $c["transaksi_penjualan_default_pelanggan_no_telepon"] = "";
                if (count($res) > 0) {
                    $res = $res[0];

                    $cabang_selected["transaksi_penjualan_default_pelanggan_number_formatted"] = $res["number_formatted"];
                    $cabang_selected["transaksi_penjualan_default_pelanggan_nama"] = $res["nama"];
                    $cabang_selected["transaksi_penjualan_default_pelanggan_alamat"] = $res["alamat"];
                    $cabang_selected["transaksi_penjualan_default_pelanggan_no_telepon"] = $res["no_telepon"];

                    $c["transaksi_penjualan_default_pelanggan_number_formatted"] = $res["number_formatted"];
                    $c["transaksi_penjualan_default_pelanggan_nama"] = $res["nama"];
                    $c["transaksi_penjualan_default_pelanggan_alamat"] = $res["alamat"];
                    $c["transaksi_penjualan_default_pelanggan_no_telepon"] = $res["no_telepon"];
                }
            }



            $cabang_uuid = $c["uuid"];
            if (isset($cabang_uuid_list[$cabang_uuid])) {
                $cabang_uuid_list[$cabang_uuid] = $c;
            }
        }

        $privilege_json = trim($user_detail["privilege_json"]);
        $privilege_list = json_decode($privilege_json, true);
        if (!is_array($privilege_list)) $privilege_list = array();


        $new_privilege_list = array();
        foreach ($user_role_privilege_list as $l) {
            $name = trim($l["name"]);
            $new_privilege_list[$name] = 0;

            foreach ($privilege_list as $l2) {
                $pv_name = $l2["name"];
                if (strtolower($pv_name) == strtolower($l["name"])) {
                    $new_privilege_list[$name] = (int) $l2["allow"];
                }
            }


            if (strtolower($user_role_name) == "super administrator") {
                $new_privilege_list[$name] = 1;
            }
        }

        $new_cabang_list = array();
        foreach ($cabang_uuid_list as $cab_uuid => $cab) {
            $new_cabang_list[] = $cab;
        }

        $user_detail["privilege_list"] = $new_privilege_list;

        set_session("user", $user_detail);
        set_session("cabang_list", $cabang_uuid_list);
        set_session("cabang_selected", $cabang_selected);

        $user_detail = array_merge($user_detail, $cabang_selected);
        $user_detail["cabang_list"] = $new_cabang_list;
        $user_detail["cabang_selected"] = $cabang_selected;

        return set_http_response_success("Success", $user_detail);
    }
}
