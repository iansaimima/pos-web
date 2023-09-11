<?php

defined('BASEPATH') or exit('No direct script access allowed');
class M_settings extends MY_Model
{

    private $settings_engine;

    private $allow;
    function __construct()
    {
        parent::__construct();

        $this->settings_engine = new Settings_engine();

        $user = get_session("user");
        $role = strtolower(get_session("role"));
        $this->actor_user_id = isset($user["id"]) ? (int) $user["id"] : 0;
        $this->actor_user_name = isset($user["name"]) ? $user["name"] : "";

        $privilege_list = isset($user["privilege_list"]) ? $user["privilege_list"] : array();

        $this->allow = isset($privilege_list["allow_edit_pengaturan_lain_lain"]) ? $privilege_list["allow_edit_pengaturan_lain_lain"] : 0;
    }

    function settings_get_list()
    {
        $res = $this->settings_engine->get_all_settings();
        $list = array();
        foreach($res as $r){
            $list[$r['_key']] = $r;
        }
        return $list;
    }

    function change_logo($files = array()){
        if(!is_array($files) || count($files) == 0) return set_http_response_error(HTTP_BAD_REQUEST, "File tidak dikenal");
        
        
        $file_name = $files["name"];
        $file_type = $files["type"];
        $file_error= $files["error"];

        $mime_type = mime_content_type($files["tmp_name"]);
        
        if(empty($file_name)) return set_http_response_error(HTTP_BAD_REQUEST, "File tidak dikenal");
        if($file_error > 0) return set_http_response_error(HTTP_BAD_REQUEST, "Terjadi kesalah saat menerima file");
        if($mime_type != "image/png") return set_http_response_error(HTTP_BAD_REQUEST, "Format file harus PNG");
        
        // **
        // get extension
        $explodes = explode(".", $file_name);
        $ext = $explodes[count($explodes) - 1];
        $file_name = str_replace(".$ext", "", $file_name);
        
        $destination = "assets/images/client-logo.png";
        
        if(move_uploaded_file($files['tmp_name'], $destination)){
            return set_http_response_success("Logo telah berhasil diubah");
        }else{
            return set_http_response_error(501, "Gagal untuk mengubah logo");
        }
    }

    function settings_save()
    {
        if (!$this->allow) return set_http_response_error(HTTP_FORBIDDEN, "Akses ditolak");

        $key = $this->input->post("key");
        $value = $this->input->post("value");

        if(strtoupper($key) == "SUBSCRIPTION_MAX_CABANG") return set_http_response_error(HTTP_BAD_REQUEST, "Tidak bisa mengubah settings");
        if(strtoupper($key) == "SUBSCRIPTION_DUE_DATE") return set_http_response_error(HTTP_BAD_REQUEST, "Tidak bisa mengubah settings");
        if(strtoupper($key) == "SUBSCRIPTION_END_DATE") return set_http_response_error(HTTP_BAD_REQUEST, "Tidak bisa mengubah settings");
        if(strtoupper($key) == "SUBSCRIPTION_PERIOD") return set_http_response_error(HTTP_BAD_REQUEST, "Tidak bisa mengubah settings");
        if(strtoupper($key) == "SUBSCRIPTION_HISTORY") return set_http_response_error(HTTP_BAD_REQUEST, "Tidak bisa mengubah settings");

        if (empty($key)) return set_http_response_error(HTTP_BAD_REQUEST, "Key cannot be empty");

        $res = $this->settings_engine->get_settings($key);
        if (count($res) == 0) return set_http_response_error(501, "Invalid settings key");
        $res['_value'] = $value;

        $res = $this->settings_engine->set_settings($res["_key"], $res["_label"], $res["_value"]);

        return set_http_response_success("Settings has been saved");
    }

    function bulk_save()
    {
        if (!$this->allow) return set_http_response_error(HTTP_FORBIDDEN, "Akses ditolak");

        $key_list = array();
        foreach ($_POST as $_key => $_value) {
            $key_list[] = strtoupper($_key);
        }
        if (count($key_list) == 0) return set_http_response_error(HTTP_BAD_REQUEST, "Failed to update settings");

        $list = $this->settings_engine->get_settings_for_key_list($key_list);
        $data_list = array();
        foreach ($list as $l) {
            $key = strtoupper($l["_key"]);
            if (isset($_POST[$key])) {

                $row = $l;
                $row["_value"] = $this->input->post($key);

                $data_list[] = $row;
            }
        }

        $res = $this->settings_engine->bulk_set_settings($data_list);
        if ($res == false) return set_http_response_error(501, "Failed to update settings");
        return set_http_response_success("Pengaturan lain-lain sudah disimpan");
    }

    function settings_delete($key = '')
    {
        if (!$this->allow) return set_http_response_error(HTTP_FORBIDDEN, "Akses ditolak");

        // **
        // check settings key
        $res = $this->settings_engine->get_settings($key);
        if (count($res) == 0) return set_http_response_error(HTTP_BAD_REQUEST, "Settings not found");

        $res = $this->settings_engine->delete_settings($key);

        return set_http_response_success("Pengaturan lain-lain sudah dihapus");
    }
}
