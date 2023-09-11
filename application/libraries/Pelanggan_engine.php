<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Pelanggan_engine extends Db_engine
{
    //put your code here

    public function __construct()
    {
        parent::__construct();
    }

    function pelanggan_get_list($filters = array(), $pagination = false, $datatables = false)
    {
        $column_search  = array(
            null,
            "pelanggan.number_formatted",
            "pelanggan.nama",
            "pelanggan.alamat",
            "pelanggan.no_telepon",
            "pelanggan.potongan_persen",
        );
        $column_order   = $column_search;
        $order          = array(
            "pelanggan.number_formatted" => "asc"
        );

        $this->db->select("
            pelanggan.*

        ");
        $this->db->from("pelanggan");

        foreach ($filters as $key => $value) {
            switch ($key) {
                case "uuid":
                    $this->db->where("pelanggan.uuid", $value);
                    unset($filters[$key]);
                    break;
                case "cabang_uuid":
                    $this->db->where("pelanggan.cabang_uuid", $value);
                    unset($filters[$key]);
                    break;
                case "nama":
                    $this->db->where("LOWER(pelanggan.nama)", strtolower($value));
                    unset($filters[$key]);
                    break;
                case "search_keyword":
                    $this->db->group_start();
                    $this->db->like("pelanggan.number_formatted", $value);
                    $this->db->or_like("pelanggan.nama", $value);
                    $this->db->or_like("pelanggan.no_telepon", $value);
                    $this->db->group_end();
                    unset($filters[$key]);
                    break;
                default:
                    break;
            }
        }

        if ($datatables) {
            $this->generate_datatables_input($filters, $column_search, $column_order, $order, $pagination);
        }

        $res = $this->db->get()->result_array();
        return $res;
    }

    function pelanggan_delete($uuid = "")
    {
        if (empty($uuid)) return false;

        $this->db->where("uuid", $uuid);
        $this->db->delete("pelanggan");

        return $uuid;
    }

    function pelanggan_save($save_data = array())
    {
        $uuid = $save_data["uuid"];

        $data = array();
        $data["created"] = $save_data["created"];
        $data["creator_user_uuid"] = $save_data["creator_user_uuid"];
        $data["creator_user_name"] = $save_data["creator_user_name"];
        $data["last_updated"] = date("Y-m-d H:i:s");
        $data["last_updated_user_uuid"] = $save_data["last_updated_user_uuid"];
        $data["last_updated_user_name"] = $save_data["last_updated_user_name"];

        $data["number"] = $save_data["number"];
        $data["number_formatted"] = $save_data["number_formatted"];
        $data["nama"] = $save_data["nama"];
        $data["alamat"] = $save_data["alamat"];
        $data["no_telepon"] = $save_data["no_telepon"];
        $data["potongan_persen"] = $save_data["potongan_persen"];
        $data["keterangan"] = $save_data["keterangan"];

        $data["cabang_uuid"] = $save_data["cabang_uuid"];

        $this->db->set($data);

        if (empty($uuid)) {
            $uuid = $this->uuid_v4();
            $this->db->set("uuid", $uuid);
            $this->db->insert("pelanggan");

            $result = $this->pelanggan_get_next_number($save_data["cabang_uuid"], $save_data["cabang_kode"]);
            $number = $result["number"];
            $number_formatted = $result["number_formatted"];

            $this->db->where("uuid", $uuid);
            $this->db->set("number", $number);
            $this->db->set("number_formatted", $number_formatted);
            $this->db->update("pelanggan");
        } else {
            $this->db->where("uuid", $uuid);
            $this->db->update("pelanggan");
        }
        return $uuid;
    }

    function pelanggan_get_next_number($cabang_uuid = "", $cabang_kode = "")
    {
        $this->db->select_max("number", "max_number");
        $this->db->from("pelanggan");
        $this->db->where("cabang_uuid", $cabang_uuid);
        $res = $this->db->get()->result_array();
        $res = $res[0];
        $max = (int)$res["max_number"];

        $next = 0;
        if ($max == 0) {
            $next = 1;
        } else {
            $next = $max + 1;
        }

        $number = $next;
        $number_formatted = PREFIX_PELANGGAN . "/$cabang_kode/" . str_pad($number, 5, "0", STR_PAD_LEFT);

        $result = array(
            "number" => $number,
            "number_formatted" => $number_formatted
        );

        return $result;
    }
}
