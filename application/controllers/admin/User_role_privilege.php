<?php

defined('BASEPATH') or exit('No direct script access allowed');


class User_role_privilege extends MY_Controller
{

    function __construct()
    {
        parent::__construct();
        model("m_user_role_privilege", "user_role_privilege");
    }

    function index()
    {
        $data = array();
        $data["breadcrumb"] = array(
            "User" => "", "User Role Privilege" => "active"
        );      
        view("pages/user_login/role_privilege/index", $data);
    }

    function ajax_list()
    {
        $data = array();
        $data["list"]   = $this->user_role_privilege->user_role_privilege_get_list($_GET);
        view("pages/user_login/role_privilege/ajax_list", $data);
    }

    function ajax_detail($uuid = "")
    {
        $data = array();
        $data["detail"] = $this->user_role_privilege->user_role_privilege_get($uuid);
        $data["parent_list"] = $this->user_role_privilege->user_role_privilege_get_parent_list();

        view("pages/user_login/role_privilege/ajax_detail", $data);
    }

    function ajax_delete($uuid = "")
    {
        $res = $this->user_role_privilege->user_role_privilege_delete($uuid);

        echo json_encode($res);
    }

    function ajax_save()
    {
        $res = $this->user_role_privilege->user_role_privilege_save();

        echo json_encode($res);
    }

    function ajax_move_up($uuid = '')
    {
        $res = $this->user_role_privilege->user_role_privilege_set_parent_move_up($uuid);

        echo json_encode($res);
    }

    function ajax_move_down($uuid = '')
    {
        $res = $this->user_role_privilege->user_role_privilege_set_parent_move_down($uuid);

        echo json_encode($res);
    }
}
