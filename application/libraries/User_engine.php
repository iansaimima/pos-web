<?php

defined('BASEPATH') OR exit('No direct script access allowed');
class User_engine extends Db_engine {
    //put your code here
    
    public function __construct() {
        parent::__construct();

        library('uuid');
    }
    
    // ====================================================
    // ** OBJECT USER
    // ====================================================
    function user_get_list($filters = array(), $pagination = false, $datatables = false){
        $column_search  = array(              
            "user.name",  
            "user.username", 
            "user_role.name"
            );
        $column_order   = $column_search;
        $order          = array(
            "user.name" => "asc"
        );
    
        $this->db->select("
            user.*, 
            user_role.name user_role_name,
            user_role.privilege_json privilege_json,

        ");
        $this->db->from("user");
        $this->db->join("user_role", "user.user_role_uuid = user_role.uuid", "left");
        
        
        foreach ($filters as $key => $value) {
            switch ($key) {
                case "uuid":
                    $this->db->where("user.uuid", $value);
                    unset($filters[$key]);
                    break;
                case "user_role_uuid":
                    $this->db->where("user.user_role_uuid", $value);
                    unset($filters[$key]);
                    break;
                case "username":
                    $this->db->where("LOWER(user.username)", strtolower(trim($value)));
                    unset($filters[$key]);
                    break;
                case "password":
                    $this->db->where("password", trim($value));
                    unset($filters[$key]);
                    break;
                case "with_superadmin":
                    if((int) $value == 0){
                        $this->db->where("LOWER(user_role.name) != 'super administrator'");
                    }
                    unset($filters[$key]);
                    break;
                default:
                    break;
            }
        }
        
        if($datatables){
            $this->generate_datatables_input($filters, $column_search, $column_order, $order, $pagination);
        }
        
        $res = $this->db->get()->result_array();
        return $res;
    }
    
    function user_save($data = array()){
        $uuid = $data["uuid"];
        $creator_user_uuid = $data["creator_user_uuid"];
        $creator_user_name = trim($data["creator_user_name"]);
        $name = trim($data["name"]);
        $username = trim($data["username"]);
        $password = trim($data["password"]);
        $user_role_uuid = trim($data["user_role_uuid"]);
        $cabang_uuid_list_json = trim($data["cabang_uuid_list_json"]);

        $this->db->set(array(
            "created" => date("Y-m-d H:i:s"),
            "creator_user_uuid" => $creator_user_uuid, 
            "creator_user_name" => $creator_user_name, 
            "name" => $name, 
            "username" => $username, 
            "password" => $password, 
            "user_role_uuid" => $user_role_uuid,
            "cabang_uuid_list_json" => $cabang_uuid_list_json,
        ));
        
        if(empty($uuid)){
            $uuid = $this->uuid_v4();
            $this->db->set("uuid", $uuid);
            $this->db->insert('user');
        }else{
            $this->db->where("uuid", $uuid);
            $this->db->update('user');
        }

        return $uuid;
    }
    
    function user_delete($uuid = ''){
        if(empty($uuid)) return false;
        
        $this->db->where("uuid", $uuid);
        $this->db->delete("user");
        
        return $uuid;
    }
    
    function user_update_password($uuid = '', $password = ""){
        if(empty($uuid)) return false;
        if(trim($password) == "") return false;
        
        $this->db->set("password", $password);
        $this->db->where("uuid", $uuid);
        $this->db->update("user");
        
        return $uuid;
    }    
    // ====================================================
    // ** END -- OBJECT USER
    // ====================================================
    
    
    // ====================================================
    // ** OBJECT USER ROLE
    // ====================================================
    function user_role_get_list($filters = array(), $pagination = false, $datatables = false){
        $column_search  = array(              
            "name"
        );
        $column_order   = $column_search;
        $order          = array(
            "name" => "asc"
        );
        
        $this->db->select("*");
        $this->db->from("user_role");
        
        foreach ($filters as $key => $value) {
            switch ($key) {
                case "uuid":
                    $this->db->where("uuid", $value);
                    unset($filters[$key]);
                    break;
                case "name":
                    $this->db->where("LOWER(name)", strtolower($value));
                    unset($filters[$key]);
                    break;
                case "with_superadmin":
                    if((int) $value == 0){
                        $this->db->where("LOWER(name) != 'super administrator'");
                    }
                    unset($filters[$key]);
                    break;
                default:
                    break;
            }
        }
        
        if($datatables){
            $this->generate_datatables_input($filters, $column_search, $column_order, $order, $pagination);
        }
        
        $res = $this->db->get()->result_array();
        return $res;
    }
    
    function user_role_save($data = array()){
        $uuid = $data["uuid"];
        $creator_user_uuid = $data["creator_user_uuid"];
        $creator_user_name = trim($data["creator_user_name"]);
        $name = trim($data["name"]);           
        $privilege_json = $data["privilege_json"];

        $this->db->set(array(
            "created" => date("Y-m-d H:i:s"),
            "creator_user_uuid" => $creator_user_uuid, 
            "creator_user_name" => $creator_user_name, 
            "name" => $name, 
            "privilege_json" => $privilege_json
        ));
        
        if(empty($uuid)){
            $uuid = $this->uuid_v4();
            $this->db->set("uuid", $uuid);
            $this->db->insert('user_role');
        }else{
            $this->db->where("uuid", $uuid);
            $this->db->update('user_role');
        }

        return $uuid;
    }
    
    function user_role_delete($uuid = ''){
        if(empty($uuid)) return false;
        
        $this->db->where("uuid", $uuid);
        $this->db->delete("user_role");
        
        return $uuid;
    }
    // ====================================================
    // ** OBJECT USER LOGIN
    // ====================================================

    // ====================================================
    // ** OBJECT USER ROLE PRIVILEGE
    // ====================================================
    function user_role_privilege_get_list($filters = array()){
        if(isset($filters["parent_uuid_list"])){            
            if(!is_array($filters["parent_uuid_list"])) return array();
            if(count($filters["parent_uuid_list"]) == 0) return array();
        }        

        $this->db->select("*");
        $this->db->from("user_role_privilege");        
        foreach ($filters as $key => $value) {
            switch ($key) {
                case "uuid":
                    $this->db->where("uuid", $value);
                    unset($filters[$key]);
                    break;
                case "name":
                    $this->db->where("LOWER(name)", strtolower($value));
                    unset($filters[$key]);
                    break;
                case "parent_uuid":
                    $this->db->where("parent_uuid", (int)trim($value));
                    unset($filters[$key]);
                    break;
                case "order_number":
                    $this->db->where("order_number", (int)trim($value));
                    unset($filters[$key]);
                    break;
                case "parent_uuid_list":
                    $this->db->where_in("parent_uuid", $value);
                    unset($filters[$key]);
                    break;
                
                case "is_parent":
                    if((int) $value == 1){
                        $this->db->where("parent_uuid = uuid");
                    }
                    if((int) $value == 0){
                        $this->db->where("parent_uuid != uuid");
                    }
                    unset($filters[$key]);
                    break;
                
                default:
                    break;
            }
        }
        
        $this->db->order_by('order_number', 'asc');
        $this->db->order_by('name', 'asc');
        $res = $this->db->get()->result_array();
        return $res;
    }
    
    function user_role_privilege_bulk_save($data_list = array()){

        if(count($data_list) == 0) return false;        
        $this->db->update_batch("user_role_privilege", $data_list, "uuid");
        return false;
    }
    
    function user_role_privilege_save($data = array()){
        $uuid = $data["uuid"];
        $name = trim($data["name"]);
        $description = trim($data["description"]);
        $parent_uuid = trim($data["parent_uuid"]);
        $order_number = (int) trim($data["order_number"]);

        $this->db->set(array(
            "name" => strtolower($name),
            "description" => $description,
            "parent_uuid" => $parent_uuid,
            "order_number" => $order_number,
        ));

        if(empty($uuid)){
            $uuid = $this->uuid_v4();
            $this->db->set("uuid", $uuid);
            $this->db->insert('user_role_privilege');
        }else{
            $this->db->where("uuid", $uuid);
            $this->db->update('user_role_privilege');
        }

        
        if(empty($parent_uuid)){
            $parent_uuid = $uuid;

            $this->db->where("uuid", $uuid);
            $this->db->set("parent_uuid", $parent_uuid);
            $this->db->update("user_role_privilege");
        }
        return $uuid;
    }
    
    function user_role_privilege_delete($uuid = ''){
        if(empty($uuid)) return false;
        
        $this->db->where("uuid", $uuid);
        $this->db->delete("user_role_privilege");
        
        return $uuid;
    }

    function user_role_privilege_set_order_number($uuid = '', $order_number = 0){
        if(empty($uuid)) return false;
        
        $this->db->set("order_number", $order_number);
        $this->db->where("uuid", $uuid);
        $this->db->update("user_role_privilege");
        
        return $uuid;
    }
    // ====================================================
    // ** OBJECT USER ROLE PRIVILEGE
    // ====================================================

}
