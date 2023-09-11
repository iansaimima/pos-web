<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Common_engine extends Db_engine{
    function __construct(){
        parent::__construct();        
    }

    function provinsi_get_list($id = 0){
        $this->db->select("*");
        $this->db->from("ptsp_provinsi");
        if((int) $id != 0) $this->db->where("id", $id);
        $res = $this->db->get()->result_array();

        return $res;
    }

    function kota_kabupaten_get_list($provinsi_id = 0, $id = 0){
        $this->db->select("*");
        $this->db->from("ptsp_kota_kabupaten");

        if((int) $provinsi_id != 0){
            $this->db->where("provinsi_id", $provinsi_id);
        }

        if((int) $id != 0) $this->db->where("id", $id);
        $res = $this->db->get()->result_array();

        return $res;
    }

    function kecamatan_get_list($kota_kabupaten_id = 0, $id = 0){
        $this->db->select("*");
        $this->db->from("ptsp_kecamatan");

        if((int) $kota_kabupaten_id != 0){
            $this->db->where("kota_kabupaten_id", $kota_kabupaten_id);
        }
        if((int) $id != 0) $this->db->where("id", $id);
        $res = $this->db->get()->result_array();

        return $res;
    }

    function kelurahan_get_list($kecamatan_id = 0, $id = 0){
        $this->db->select("*");
        $this->db->from("ptsp_kelurahan");

        if((int) $kecamatan_id != 0){
            $this->db->where("kecamatan_id", $kecamatan_id);
        }
        if((int) $id != 0) $this->db->where("id", $id);
        $res = $this->db->get()->result_array();

        return $res;
    }
}