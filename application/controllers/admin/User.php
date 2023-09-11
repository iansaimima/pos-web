<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of User
 *
 * @author ian
 */
class User extends MY_Controller
{
    //put your code here

    public function __construct()
    {
        parent::__construct();

        model("m_user", "user");
        model("m_cabang", "cabang");
    }

    function index()
    {
        $data = array();
        $data["breadcrumb"] = array(
            "User" => "", "User Login" => "active"
        );
        view("pages/user_login/user/index", $data);
    }

    function ajax_list()
    {
        $data           = $this->user->user_get_list($_GET);
        $rows_total     = $this->user->user_get_total();
        $filtered_total = $this->user->user_get_filtered_total($_GET);

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
        $data["status_list"] = $this->user->allowed_status_list;
        $data["detail"] = $this->user->user_get($uuid);
        $data["user_role_list"] = $this->user->user_role_get_list();
        $data["cabang_list"] = $this->cabang->cabang_get_list_for_combobox();

        view("pages/user_login/user/ajax_detail", $data);
    }

    function ajax_delete($uuid = "")
    {
        if (DEMO == 1) {
            $res = set_http_response_error(HTTP_FORBIDDEN, "Akses ditolak untuk DEMO");
            print_json($res);
            return;
        }

        $res = $this->user->user_delete($uuid);

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
        $res = $this->user->user_save($_POST);

        if (ob_get_contents()) ob_clean();
        echo json_encode($res);
    }

    function ajax_reset_password()
    {
        if (DEMO == 1) {
            $res = set_http_response_error(HTTP_FORBIDDEN, "Akses ditolak untuk DEMO");
            print_json($res);
            return;
        }
        $res = $this->user->user_reset_password($_POST);

        if (ob_get_contents()) ob_clean();
        echo json_encode($res);
    }

    function ajax_change_password()
    {
        if (DEMO == 1) {
            $res = set_http_response_error(HTTP_FORBIDDEN, "Akses ditolak untuk DEMO");
            print_json($res);
            return;
        }

        $res = $this->user->user_change_password($_POST);

        if (ob_get_contents()) ob_clean();
        echo json_encode($res);
    }

    function get_csrf_data()
    {
        $array = array(
            "csrfTokenName" => $this->security->get_csrf_token_name(),
            "csrfHash" => $this->security->get_csrf_hash()
        );
        print_json($array);
    }
}
