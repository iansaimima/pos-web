<?php

class Settings_engine extends Db_engine{
    function __construct(){
        parent::__construct();
    }

    function get_all_settings(){
        $res = $this->db->select("*")->from("settings")->get()->result_array();

        return $res;
    }

    function get_settings($key = ''){
        $res = $this->db->select("*")->from("settings")->where("_key", $key)->get()->result_array();        
        if(count($res) == 0) return array();
        $res = $res[0];
        return $res;
    }

    function get_settings_for_key_list($key_list = array()){
        if(!is_array($key_list)) return array();
        if(count($key_list) == 0) return array();
        $res = $this->db->select("*")->from("settings")->where_in("_key", $key_list)->get()->result_array();
        return $res;
    }

    function set_settings($key = '', $label = '', $value = ''){

        $this->db->set("_key", $key);
        $this->db->set("_label", $label);
        $this->db->set("_value", $value);
        $this->db->replace("settings");

        return 1;
    }

    function bulk_set_settings($data_list = array()){
        if(!is_array($data_list) || count($data_list) == 0) return 0;

        $this->db->update_batch('settings', $data_list, '_key');

        return 1;
    }

    function delete_settings($key = ''){
        $this->db->where("_key", $key);
        $this->db->delete("settings");

        return 1;
    }
}