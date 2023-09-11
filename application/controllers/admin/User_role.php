<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of User_role
 *
 * @author ian
 */
class User_role extends MY_Controller
{
    //put your code here

    public function __construct()
    {
        parent::__construct();

        model("m_user", "user");
        model("m_user_role_privilege", "user_role_privilege");
    }

    function index()
    {
        $data = array();
        $data["breadcrumb"] = array(
            "User" => "", "User Akses" => "active"
        );
        view("pages/user_login/user_role/index", $data);
    }

    function ajax_list()
    {
        $data           = $this->user->user_role_get_list($_GET);
        $rows_total     = $this->user->user_role_get_total();
        $filtered_total = $this->user->user_role_get_filtered_total($_GET);

        $table_data = array(
            "draw"            => isset($_GET["draw"]) ? (int) $_GET["draw"] : 1,
            "recordsTotal"    => $rows_total,
            "recordsFiltered" => $filtered_total,
            "data"            => $data
        );

        if (ob_get_contents()) ob_clean();
        echo json_encode($table_data);
    }

    function ajax_detail($uuid = "")
    {
        $data = array();
        $data["detail"] = $this->user->user_role_get($uuid);
        $data["user_role_privilege_list"] = $this->user_role_privilege->user_role_privilege_get_list();

        view("pages/user_login/user_role/ajax_detail", $data);
    }

    function ajax_delete($uuid = "")
    {
        if (DEMO == 1) {
            $res = set_http_response_error(HTTP_FORBIDDEN, "Akses ditolak untuk DEMO");
            print_json($res);
            return;
        }
        $res = $this->user->user_role_delete($uuid);

        if (ob_get_contents()) ob_clean();
        echo json_encode($res);
    }

    function ajax_save()
    {
        if (DEMO == 1) {
            $uuid = post("uuid");
            if (!empty($uuid)) {
                $res = set_http_response_error(HTTP_FORBIDDEN, "Akses ditolak untuk DEMO");
                print_json($res);
                return;
            }
        }
        $res = $this->user->user_role_save($_POST);

        if (ob_get_contents()) ob_clean();
        echo json_encode($res);
    }
}
