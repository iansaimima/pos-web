<?php

defined('BASEPATH') or exit('No direct script access allowed');

class M_user_role_privilege extends MY_Model
{
    private $user_engine;

    private $allow_user_akses;
    private $allow_user_role_privilege;
    private $allow_user_role_privilege_create;
    private $allow_user_role_privilege_update;
    private $allow_user_role_privilege_delete;
    function __construct()
    {
        parent::__construct();

        $this->user_engine = new User_engine();

        $user = get_session("user");$privilege_list = isset($user["privilege_list"]) ? $user["privilege_list"] : array();

        $this->allow_user_akses        = isset($privilege_list["allow_user_akses"]) ? $privilege_list["allow_user_akses"] : 0;
        $this->allow_user_role_privilege        = isset($privilege_list["allow_user_role_privilege"]) ? $privilege_list["allow_user_role_privilege"] : 0;
        $this->allow_user_role_privilege_create = isset($privilege_list["allow_user_role_privilege_create"]) ? $privilege_list["allow_user_role_privilege_create"] : 0;
        $this->allow_user_role_privilege_update = isset($privilege_list["allow_user_role_privilege_update"]) ? $privilege_list["allow_user_role_privilege_update"] : 0;
        $this->allow_user_role_privilege_delete = isset($privilege_list["allow_user_role_privilege_delete"]) ? $privilege_list["allow_user_role_privilege_delete"] : 0;
    }

    function user_role_privilege_get_parent_list($filters = array())
    {

        // **
        // get parent list
        $filters = array();
        $filters["is_parent"] = 1;
        $parent_list = $this->user_engine->user_role_privilege_get_list($filters);
        if (count($parent_list) == 0) return array();
        return $parent_list;
    }

    function user_role_privilege_get_list($filters = array())
    {
        if(!$this->allow_user_role_privilege && !$this->allow_user_akses) return array();

        // **
        // get parent list
        $filters = array();
        $filters["is_parent"] = 1;
        $parent_list = $this->user_engine->user_role_privilege_get_list($filters);
        if (count($parent_list) == 0) return array();

        $parent_uuid_list = array();
        foreach ($parent_list as $l) {
            $parent_uuid_list[] = $l["uuid"];
        }

        $filters = array();
        $filters["parent_uuid_list"] = $parent_uuid_list;
        $filters["is_parent"] = 0;
        $list = $this->user_engine->user_role_privilege_get_list($filters);

        $final_res = array();
        foreach ($parent_list as $pl) {
            $uuid = $pl["uuid"];

            $row = $pl;
            $row["child_list"] = array();

            foreach ($list as $l) {
                $parent_uuid = $l["parent_uuid"];

                if ($parent_uuid == $uuid) {
                    $row["child_list"][] = $l;
                }
            }

            $final_res[] = $row;
        }

        return $final_res;
    }

    function user_role_privilege_get($uuid = "")
    {
        if(!$this->allow_user_role_privilege) return array();

        $filters = array();
        $filters["uuid"] = $uuid;
        $res = $this->user_engine->user_role_privilege_get_list($filters);
        if (count($res) == 0) return array();
        $res = $res[0];
        return $res;
    }

    function user_role_privilege_delete($uuid = "")
    {
        if(!$this->allow_user_role_privilege_delete) return set_http_response_error(HTTP_FORBIDDEN, "Akses ditolak");

        $filters = array();
        $filters["uuid"] = $uuid;
        $res = $this->user_engine->user_role_privilege_get_list($filters);
        if (count($res) == 0) return set_http_response_error(HTTP_BAD_REQUEST, "Invalid User Role Privilege");
        $res = $res[0];
        $uuid = $res["uuid"];

        // **
        // check child
        $filters = array();
        $filters["parent_uuid"] = $uuid;
        $res = $this->user_engine->user_role_privilege_get_list($filters);
        if (count($res) > 0) {
            $res = $res[0];
            if ($res["parent_uuid"] != $res["uuid"]) return set_http_response_error(HTTP_BAD_REQUEST, "This privilege has child");
        }

        $this->db->trans_start();
        try {
            $res = $this->user_engine->user_role_privilege_delete($uuid);
            if ($res == false ) {
                throw new Exception("Failed to delete User Role Privilege");
            }

            // **
            // ambil semua parent role, untuk urutkan ulang order
            $filters = array();
            $filters["is_parent"] = 1;
            $res = $this->user_engine->user_role_privilege_get_list($filters);

            $data_list = array();
            $order_number = 1;
            foreach($res as $r){
                $row = $r;

                $row["order_number"] = $order_number;
                $order_number ++;

                $data_list[] = $row;
            }

            $res = $this->user_engine->user_role_privilege_bulk_save($data_list);    
        } catch (Exception $e) {
            $this->db->trans_rollback();
            return set_http_response_error(501, "Error: ", $e->getMessage());
        }

        $this->db->trans_commit();
        return set_http_response_success("Success to delete User Role Privilege");
    }

    function user_role_privilege_save()
    {
        $uuid = $this->input->post("uuid");
        $name = $this->input->post("name");
        $description = $this->input->post("description");
        $parent_uuid = $this->input->post("parent_uuid");

        if (empty($name)) return set_http_response_error(HTTP_BAD_REQUEST, "Empty Name");
        if (empty($description)) return set_http_response_error(HTTP_BAD_REQUEST, "Empty Description");
        $name = strtolower($name);

        // **
        // get count total parnet
        $filters = array();
        $filters["is_parent"] = 1;
        $parent_list = $this->user_engine->user_role_privilege_get_list($filters);
        $order_number = count($parent_list) + 1;

        if (!empty($uuid)) {
            $filters = array();
            $filters["uuid"] = $uuid;
            $res = $this->user_engine->user_role_privilege_get_list($filters);
            if (count($res) == 0) return set_http_response_error(HTTP_BAD_REQUEST, "Invalid User Role Privilege");
            $res = $res[0];
            $uuid = $res["uuid"];
            $order_number = (int) $res["order_number"];
        }

        if(empty($uuid)) {
            if(!$this->allow_user_role_privilege_create) return set_http_response_error(HTTP_FORBIDDEN, "Akses ditolak");
        }else{
            if(!$this->allow_user_role_privilege_update) return set_http_response_error(HTTP_FORBIDDEN, "Akses ditolak");
        }

        // **
        // check parent_uuid
        if (!empty($parent_uuid)) {
            $filters = array();
            $filters["uuid"] = $parent_uuid;
            $res = $this->user_engine->user_role_privilege_get_list($filters);
            if (count($res) == 0) return set_http_response_error(HTTP_BAD_REQUEST, "Invalid Parent");
        }

        // **
        // check duplicate name
        $filters = array();
        $filters["name"] = $name;
        $res = $this->user_engine->user_role_privilege_get_list($filters);
        if (count($res) > 0) {
            $res = $res[0];
            $curr_uuid = $res["uuid"];

            if ($uuid != $curr_uuid) return set_http_response_error(HTTP_BAD_REQUEST, "Duplicate User Role Privilege Name");
        }

        $save_data = array();
        $save_data["uuid"] = $uuid;
        $save_data["name"] = strtolower($name);
        $save_data["description"] = $description;
        $save_data["parent_uuid"] = $parent_uuid;
        $save_data["order_number"] = $order_number;


        $this->db->trans_start();
        try {
            $res = $this->user_engine->user_role_privilege_save($save_data);
            if ($res == false ) {
                throw new Exception("Failed to save User Role Privilege");
            }
        } catch (Exception $e) {
            $this->db->trans_rollback();
            return set_http_response_error(501, "Error: " . $e->getMessage());
        }

        $this->db->trans_commit();
        return set_http_response_success("Success to save User Role Privilege");
    }

    function user_role_privilege_set_parent_move_up($uuid = '')
    {
        // **
        // check uuid
        $filters = array();
        $filters["uuid"] = $uuid;
        $res = $this->user_engine->user_role_privilege_get_list($filters);
        if (count($res) == 0) return set_http_response_error(HTTP_BAD_REQUEST, "Invalid User Role Privilege");
        $res = $res[0];
        $uuid = $res["uuid"];
        $parent_uuid = $res["parent_uuid"];
        $new_order_number = (int) $res['order_number'] - 1;
        if ($parent_uuid != $uuid) return set_http_response_error(HTTP_BAD_REQUEST, "Hanya bisa mengubah posisi role induk");

        if($new_order_number < 1) return set_http_response_success("Success");

        // **
        // get role for order number above
        $filters = array();
        $filters['order_number'] = $new_order_number;
        $res = $this->user_engine->user_role_privilege_get_list($filters);
        if(count($res) == 0) return set_http_response_error(HTTP_BAD_REQUEST, "Gagal memindahkan posisi user role privilege #001");
        $res = $res[0];
        $old_uuid = $res['uuid'];
        $old_order_number = (int) $res['order_number'] + 1;
        

        $this->db->trans_start();
        try {
            $res = $this->user_engine->user_role_privilege_set_order_number($uuid, $new_order_number);
            if ($res == false ) {
                throw new Exception("Gagal memindahkan posisi user role privilege #002");
            }

            $res = $this->user_engine->user_role_privilege_set_order_number($old_uuid, $old_order_number);
            if ($res == false ) {
                throw new Exception("Gagal memindahkan posisi user role privilege #003");
            }
        } catch (Exception $e) {
            $this->db->trans_rollback();
            return set_http_response_error(501, "Error: " . $e->getMessage());
        }

        $this->db->trans_commit();
        return set_http_response_success("Success to save User Role Privilege Order Number");
    }

    function user_role_privilege_set_parent_move_down($uuid = '')
    {
        // **
        // check uuid
        $filters = array();
        $filters["uuid"] = $uuid;
        $res = $this->user_engine->user_role_privilege_get_list($filters);
        if (count($res) == 0) return set_http_response_error(HTTP_BAD_REQUEST, "Invalid User Role Privilege");
        $res = $res[0];
        $uuid = $res["uuid"];
        $parent_uuid = (int) $res["parent_uuid"];
        $new_order_number = (int) $res['order_number'] + 1;
        if ($parent_uuid != $uuid) return set_http_response_error(HTTP_BAD_REQUEST, "Hanya bisa mengubah posisi role induk");

        // **
        // get count total parnet
        $filters = array();
        $filters["is_parent"] = 1;
        $parent_list = $this->user_engine->user_role_privilege_get_list($filters);

        if ($new_order_number > count($parent_list)) return set_http_response_success("Success");

        // **
        // get role for order number below
        $filters = array();
        $filters['order_number'] = $new_order_number;
        $res = $this->user_engine->user_role_privilege_get_list($filters);
        if(count($res) == 0) return set_http_response_error(HTTP_BAD_REQUEST, "Gagal memindahkan posisi user role privilege #001");
        $res = $res[0];
        $old_uuid = $res['uuid'];
        $old_order_number = (int) $res['order_number'] - 1;

        $this->db->trans_start();
        try {
            $res = $this->user_engine->user_role_privilege_set_order_number($uuid, $new_order_number);
            if ($res == false ) {
                throw new Exception("Gagal memindahkan posisi user role privilege #002");
            }

            $res = $this->user_engine->user_role_privilege_set_order_number($old_uuid, $old_order_number);
            if ($res == false ) {
                throw new Exception("Gagal memindahkan posisi user role privilege #003");
            }
        } catch (Exception $e) {
            $this->db->trans_rollback();
            return set_http_response_error(501, "Error: " . $e->getMessage());
        }

        $this->db->trans_commit();
        return set_http_response_success("Success to save User Role Privilege Order Number");
    }
}
