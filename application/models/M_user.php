<?php

defined('BASEPATH') OR exit('No direct script access allowed');
class M_user extends MY_Model {
    //put your code here
    
    private $user_engine;
    private $cabang_engine;
    private $actor_user_uuid;
    private $actor_user_name;
    public $allowed_status_list;

    private $allow_user_login;
    private $allow_user_login_create;
    private $allow_user_login_update;
    private $allow_user_login_delete;
    private $allow_user_login_reset_password;

    private $allow_user_akses;
    private $allow_user_akses_create;
    private $allow_user_akses_update;
    private $allow_user_akses_delete;
    public function __construct() {
        parent::__construct();
        $this->user_engine = new User_engine();
        $this->cabang_engine = new Cabang_engine();

        $user = get_session("user");
        $role = strtolower(get_session("role"));
        $this->actor_user_uuid = isset($user["uuid"]) ? $user["uuid"] : '';
        $this->actor_user_name = isset($user["name"]) ? $user["name"] : "";

        $privilege_list = isset($user["privilege_list"]) ? $user["privilege_list"] : array();

        $this->allow_user_login        = isset($privilege_list["allow_user_login"]) ? $privilege_list["allow_user_login"] : 0;
        $this->allow_user_login_create = isset($privilege_list["allow_user_login_create"]) ? $privilege_list["allow_user_login_create"] : 0;
        $this->allow_user_login_update = isset($privilege_list["allow_user_login_update"]) ? $privilege_list["allow_user_login_update"] : 0;
        $this->allow_user_login_delete = isset($privilege_list["allow_user_login_delete"]) ? $privilege_list["allow_user_login_delete"] : 0;
        $this->allow_user_login_reset_password = isset($privilege_list["allow_user_login_reset_password"]) ? $privilege_list["allow_user_login_reset_password"] : 0;

        $this->allow_user_akses        = isset($privilege_list["allow_user_akses"]) ? $privilege_list["allow_user_akses"] : 0;
        $this->allow_user_akses_create = isset($privilege_list["allow_user_akses_create"]) ? $privilege_list["allow_user_akses_create"] : 0;
        $this->allow_user_akses_update = isset($privilege_list["allow_user_akses_update"]) ? $privilege_list["allow_user_akses_update"] : 0;
        $this->allow_user_akses_delete = isset($privilege_list["allow_user_akses_delete"]) ? $privilege_list["allow_user_akses_delete"] : 0;
    }
    
    // ===============================================
    // ** OBJECT USER LOGIN
    // ===============================================
    function user_get_list($filters = array()){
        if(!$this->allow_user_login) return array();
        
        $filters["with_superadmin"] = 0;
        $res = $this->user_engine->user_get_list($filters, true, true);        
        $no = isset($filters["start"]) ? (int) $filters["start"] : 0;
        
        $final_res = array();
        foreach ($res as $r) {
            $no++;
            
            $row = array();
            $row["no"] = $no;
            $row["uuid"] = trim($r["uuid"]);
            $row["name"] = $r["name"];
            $row["username"] = $r["username"];
            $row["role_name"] = $r["user_role_name"];
            
            $final_res[] = $row;
        }
        
        return $final_res;
    }
    
    function user_get_filtered_total($filters = array()){
        if(!$this->allow_user_login) return 0;
        $filters["with_superadmin"] = 0;
        $res = $this->user_engine->user_get_list($filters, false, true);
        return count($res);
    }
    
    function user_get_total(){
        if(!$this->allow_user_login) return 0;
        $filters = array();
        $filters["with_superadmin"] = 0;
        $res = $this->user_engine->user_get_list($filters);
        return count($res);
    }
    
    function user_get($uuid = ""){
        if(!$this->allow_user_login) return array();
        
        $filters = array(
            "uuid" => $uuid
        );
        $res = $this->user_engine->user_get_list($filters);
        if(count($res) == 0) return array();
        
        $res = $res[0];                
        $cabang_uuid_list_json = $res["cabang_uuid_list_json"];
        unset($res["cabang_uuid_list_json"]);
        $cabang_uuid_list = json_decode($cabang_uuid_list_json, true);

        $res["cabang_uuid_list"] = $cabang_uuid_list;

        return $res;
    }
    
    function user_change_password($post = array()){          

        $user = get_session("user");
        $user_uuid = $user["uuid"];
        if(empty($user_uuid)) return set_http_response_error(401, "Invalid Login");
        $old_password = trim($this->input->post("old_password"));
        $new_password = trim($this->input->post("new_password"));
        $confirm_password = trim($this->input->post("confirm_password"));
        
        $filters = array();
        $filters["uuid"]= $user_uuid;
        $res = $this->user_engine->user_get_list($filters);
        if(count($res)== 0) return set_http_response_error(HTTP_BAD_REQUEST, "User tidak valid");
        $res = $res[0];
        $current_password = trim($res["password"]);
        $old_password = $this->encrypt_password($old_password);
                
        if($old_password != $current_password){
            return set_http_response_error(HTTP_BAD_REQUEST, "Password lama salah");
        }
        
        if($new_password != $confirm_password){
            return set_http_response_error(HTTP_BAD_REQUEST, "Password baru tidak sama");
        }
        
        $password = $this->encrypt_password($new_password);
        
        $this->db->trans_start();
        try {                    
            $res = $this->user_engine->user_update_password($user_uuid, $password);
            if($res == false){
                $this->db->trans_rollback();
                return set_http_response_error(501, "Gagal mengganti password #001");
            } 

            set_session("password", $new_password); // tanpa encrypt

            $this->db->trans_commit();
            return set_http_response_success("Password berhasil diganti");
        } catch (Exception $e) {
            $this->db->trans_rollback();
            return set_http_response_error(501, "Gagal mengganti password #002");
        }
    }
    
    function user_delete($uuid = ""){
        if(!$this->allow_user_login_delete) return set_http_response_error (HTTP_FORBIDDEN, "Akses ditolak");
        
        $filters = array(
            "uuid" => $uuid
        );
        $res = $this->user_engine->user_get_list($filters);        
        if(count($res) == 0){
            return set_http_response_error(HTTP_BAD_REQUEST, "Unknown user login");
        }        
        $res = $res[0];
        $uuid = $res["uuid"];
        if(strtolower($res["level"]) == "super administrator") return set_http_response_success ("Sukses");
        
        $this->db->trans_start();
        try{
            $res = $this->user_engine->user_delete($uuid);
            if($res == false ){
                $this->db->trans_rollback();
                return set_http_response_error(501, "Gagal menghapus user #1");
            }

            $this->db->trans_commit();
            return set_http_response_success("User deleted");
        }catch(Exception $e){
            $this->db->trans_rollback();
            return set_http_response_error(501, "Gagal menghapus user #2");
        }
    }
    
    function user_save($post = array()){        
        
        $uuid    = trim($this->input->post('uuid'));
        $user_role_uuid    = trim($this->input->post('user_role_uuid'));
        $name       = trim($this->input->post("name"));
        $email       = trim($this->input->post("email"));
        $notifikasi_email_status       = trim($this->input->post("notifikasi_email_status"));
        $username   = trim($this->input->post("username"));
        $cabang_uuid_list = $this->input->post("cabang_uuid_list");        

        $notifikasi_email_status = strtoupper($notifikasi_email_status);            
        if(!is_array($cabang_uuid_list)) $cabang_uuid_list = array();

        if(count($cabang_uuid_list) == 0) return set_http_response_error(HTTP_BAD_REQUEST, "Belum ada cabang yang dipilih");
        if(empty($name)) return set_http_response_error(HTTP_BAD_REQUEST, "Empty name");
        if(empty($username)) return set_http_response_error(HTTP_BAD_REQUEST, "Empty username");

        if(!filter_var($email, FILTER_VALIDATE_EMAIL)) $email = "";
        if(!in_array($notifikasi_email_status, $this->allowed_status_list)) $notifikasi_email_status = "";
        
        if(!empty($uuid)){
            $filters = array(
                "uuid" => $uuid
            );
            $res = $this->user_engine->user_get_list($filters);     
            if(count($res) == 0){
                return set_http_response_error(HTTP_BAD_REQUEST, "Invalid User Login");
            }
        }      
        
        if(empty($uuid)){            
            if(!$this->allow_user_login_create) return set_http_response_error (HTTP_FORBIDDEN, "Akses ditolak");
        }else{            
            if(!$this->allow_user_login_update) return set_http_response_error (HTTP_FORBIDDEN, "Akses ditolak");
        }

        // **
        // cek cabang uuid list
        $final_cabang_uuid_list = array();
        foreach($cabang_uuid_list as $cabang_uuid) {
            // **
            // check cabang uuid
            $filters = array();
            $filters["uuid"] = $cabang_uuid;
            $res = $this->cabang_engine->cabang_get_list($filters);
            if(count($res) > 0) {
                $final_cabang_uuid_list[] = $cabang_uuid;
            }
        }
        $cabang_uuid_list_json = json_encode($final_cabang_uuid_list);
        
        // **
        // check user_role_uuid
        $filters = array();
        $filters["uuid"] = $user_role_uuid;
        $res = $this->user_engine->user_role_get_list($filters);
        if(count($res) == 0){
            return set_http_response_error(HTTP_BAD_REQUEST, "Invalid User Role");
        }
        $res = $res[0];
        $user_role_uuid = $res["uuid"];

        // **
        // check duplicate username
        $filters = array();
        $filters["username"] = $username;
        $res = $this->user_engine->user_get_list($filters);
        if(count($res) > 0){
            $res = $res[0];
            $curr_uuid = $res["uuid"];
            if($curr_uuid != $uuid) return set_http_response_error(HTTP_BAD_REQUEST, "Username has been registered");
        }       
        
        $password = "";
        if(empty($uuid)){
            $password = trim($this->input->post('password'));
            $confirm_password = trim($this->input->post('confirm_password'));
            if($password !=  $confirm_password){
                return set_http_response_error(HTTP_BAD_REQUEST, "Password does not match");
            }
            
            $password = $this->encrypt_password($password);
        }else{
            $filters = array(
                "uuid" => $uuid
            );
            $res = $this->user_engine->user_get_list($filters);
            $res = $res[0];
            $password = trim($res['password']);
        }                        
        
        $data = array();
        $data['uuid'] = $uuid;
        $data["creator_user_uuid"] = $this->actor_user_uuid;
        $data["creator_user_name"] = $this->actor_user_name;
        $data['name'] = trim($name);
        $data['username'] = trim($username);
        $data['password'] = trim($password);
        $data['user_role_uuid'] = $user_role_uuid;
        $data["cabang_uuid_list_json"] = $cabang_uuid_list_json;
        
        $this->db->trans_start();
        try{
            $res = $this->user_engine->user_save($data);
            if($res == false ) {
                $this->db->trans_rollback();
                return set_http_response_error(501, "Failed to save user login");
            }


            $this->db->trans_commit();
            return set_http_response_success("User login saved");
        }catch(Exception $e){
            $this->db->user_get_filtered_totaltrans_rollback();
            return set_http_response_error(501, "Failed to save user login");
        }
    }
    
    function user_reset_password($post = array()){
        if(!$this->allow_user_login_reset_password) return set_http_response_error(HTTP_FORBIDDEN, "Akses ditolak");
        
        $uuid = $this->input->post("uuid"); 
        $password = $this->input->post("password"); 
        $confirm_password = $this->input->post("confirm_password");

        if(empty($password)) return set_http_response_error(HTTP_BAD_REQUEST, "Empty password");
        if(empty($confirm_password)) return set_http_response_error(HTTP_BAD_REQUEST, "Empty confirm password");

        $filters = array(
            "uuid" => $uuid
        );
        $res = $this->user_engine->user_get_list($filters);
        if(count($res) == 0){
            return set_http_response_error(HTTP_BAD_REQUEST, "Invalid User");
        }
        $res = $res[0];
        $uuid = $res["uuid"];

        if($password !=  $confirm_password) return set_http_response_error(HTTP_BAD_REQUEST, "Password does not match");

        $password = $this->encrypt_password($password);

        $this->db->trans_start();
        try{

            $res = $this->user_engine->user_update_password($uuid, $password);
            if($res == false ){
                $this->db->trans_rollback();
                return set_http_response_error(501, "Failed to reset password");
            }

            $this->db->trans_commit();
            return set_http_response_success("Password has been reset");
        }catch(Exception $e){
            $this->db->trans_rollback();
            return set_http_response_error(501, "Failed to reset password");
        }
    }

    // ===============================================
    // ** OBJECT USER ROLE
    // ===============================================
    function user_role_get_list($filters = array()){
        if(!$this->allow_user_akses) return array();
        
        $filters["with_superadmin"] = 0;
        $res = $this->user_engine->user_role_get_list($filters);
        $no = isset($filters["start"]) ? (int) $filters["start"] : 0;

        $final_res = array();
        foreach($res as $r){
            $no++;
            
            $row = array();
            $row["no"] = $no;
            $row["uuid"] = trim($r["uuid"]);
            $row["name"] = trim($r["name"]);
            
            $final_res[] = $row;
        }

        return $final_res;
    }
    
    function user_role_get_filtered_total($filters = array()){
        if(!$this->allow_user_akses) return 0;
        
        $filters["with_superadmin"] = 0;
        $res = $this->user_engine->user_role_get_list($filters, false, true);
        return count($res);
    }
    
    function user_role_get_total(){
        if(!$this->allow_user_akses) return 0;
        
        $filters = array();
        $filters["with_superadmin"] = 0;
        $res = $this->user_engine->user_role_get_list($filters);
        return count($res);
    }
    
    function user_role_get($uuid = ""){
        if(!$this->allow_user_akses) return array();

        $filters = array();
        $filters["uuid"] = $uuid;
        $res = $this->user_engine->user_role_get_list($filters);
        if(count($res) == 0) return array();
        $res = $res[0];
        return $res;
    }

    function user_role_delete($uuid = ""){               
        if(!$this->allow_user_akses_delete) return set_http_response_error (HTTP_FORBIDDEN, "Akses ditolak");
        
        $filters = array();
        $filters["uuid"] = $uuid;
        $res = $this->user_engine->user_role_get_list($filters);        
        if(count($res) == 0) return set_http_response_error(HTTP_BAD_REQUEST, "Invalid user login level");
        $res = $res[0];
        $uuid = $res["uuid"];
        $name = $res["name"];
        if(strtolower(trim($name)) == "super administrator") return set_http_response_error(401, "Akses ditolak");
        if(strtolower(trim($name)) == "kasir") return set_http_response_error(HTTP_BAD_REQUEST, "Tidak bisa menghapus User Akses Kasir");

        // **
        // check jika user login level sedang digunakan
        $filters = array();
        $filters["user_role_uuid"] = $uuid;
        $res = $this->user_engine->user_get_list($filters);
        if(count($res) > 0) return set_http_response_error(HTTP_BAD_REQUEST, "Role ini sedang digunakan pada user");

        $this->db->trans_start();
        try {            
            $res = $this->user_engine->user_role_delete($uuid);
            if($res == false ){
                $this->db->trans_rollback();
                return set_http_response_error(501, "User level gagal dihapus #001");
            }
            
            $this->db->trans_commit();
            return set_http_response_success("User level telah dihapus");

        } catch (Throwable $th) {
            $this->db->trans_rollback();
            return set_http_response_error(501, "User level gagal dihapus #002");
        }
    }

    function user_role_save($post = array()){

        $uuid = trim($this->input->post("uuid"));
        $name = trim($this->input->post("name"));
        
        if(!empty($uuid)){            
            $filters = array();
            $filters["uuid"] = $uuid;
            $res = $this->user_engine->user_role_get_list($filters);
            if(count($res) == 0) return set_http_response_error(HTTP_BAD_REQUEST, "Invalid User login level");            
            $res = $res[0];
            $uuid = $res["uuid"];
            $curr_name = trim($res["name"]);
            if(strtolower($curr_name) == "super administrator") return set_http_response_error(HTTP_FORBIDDEN, "Akses ditolak");
            if(strtolower($curr_name) == "kasir") {
                if(strtolower($name) != "kasir"){
                    return set_http_response_error(HTTP_BAD_REQUEST, "Tidak bisa mengubah nama user akses kasir");
                }
            }
        }
        
        // check jika mempunyai role access
        if(empty($uuid)){            
            if(!$this->allow_user_akses_create) return set_http_response_error (HTTP_FORBIDDEN, "Akses ditolak");
        }else{            
            if(!$this->allow_user_akses_update) return set_http_response_error (HTTP_FORBIDDEN, "Akses ditolak");
        }

        
        if(empty($name)) return set_http_response_error(HTTP_BAD_REQUEST, "Nama tidak boleh kosong");
        if(strtolower($name) == "super administrator") return set_http_response_error(HTTP_FORBIDDEN, "Akses ditolak");
    
        $user_role_privilege_list = $this->user_engine->user_role_privilege_get_list();
        $privilege_list = array();
        foreach($user_role_privilege_list as $l){
            $privilege_name = trim($l["name"]);

            $allow = 0;
            if(isset($_POST[$privilege_name])){
                $allow = $this->input->post($privilege_name);

                // **
                // jika bukan super administrator dan centang allow user role privilege, 
                // maka set allow = 0
                $user = get_session("user");
                if($privilege_name == "allow_user_role_privilege" && strtolower($user["user_role_name"]) != "super administrator") {
                    $allow = 0;
                }
            }

            $row = array();
            $row["name"] = $privilege_name;
            $row["allow"] = $allow;

            $privilege_list[] = $row;
        }        
        
        $data = array();
        $data["uuid"] = $uuid;
        $data["creator_user_uuid"] = $this->actor_user_uuid;
        $data["creator_user_name"] = $this->actor_user_name;        
        $data["name"] = $name;
        $data["privilege_json"] = json_encode($privilege_list);
        
            
        $this->db->trans_start();
        try {
            $res = $this->user_engine->user_role_save($data);
            if($res == false ){
                $this->db->trans_rollback();
                return set_http_response_error(501, "Gagal menyimpan user role #001");
            }

            if(empty($uuid)) $uuid= $res;

            $this->db->trans_commit();
            return set_http_response_success("User role telah disimpan", array(), trim($uuid));
        } catch (Exception $e) {        
            $this->db->trans_rollback();
            return set_http_response_error(501, "Gagal menyimpan user role #002");
        }
    }
}
