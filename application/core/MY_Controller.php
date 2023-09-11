<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of MY_Controller
 *
 * @author ian
 */

class MY_Controller extends CI_Controller
{
    //put your code here

    private $member_user_data;
    private $is_ajax;
    private $by_pass_list_pemohon;

    private $tokenKey;

    public $request_method;
    public function __construct()
    {
        parent::__construct();
        model('m_login', 'login');

        $this->request_method = strtolower($_SERVER["REQUEST_METHOD"]);

        $this->tokenKey = "co3bjJbJj6Pk73uqQzf0gk1qsXSqjCPB";

        $this->is_ajax = false;
        $this->member_user_data = array();

        if (isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && trim($_SERVER["HTTP_X_REQUESTED_WITH"]) == "XMLHttpRequest") {
            $this->is_ajax = true;
        }

        // echo $this->login->encrypt_password("superadmin");

        $uri_1 = $this->uri->segment(1);
        $uri_2 = $this->uri->segment(2);
        $uri_3 = $this->uri->segment(3);
        $uri_4 = $this->uri->segment(4);

        switch ($uri_1) {
            case "admin":
            case "kasir":
                if ($uri_2 != "login") {
                    if ($uri_2 == "user") {
                        if ($uri_3 != "get_csrf_data") {
                            $this->check_login();
                        }
                    }else{
                        $this->check_login();
                    }
                }
                break;            
            default:
                break;
        }
    }

    function check_login()
    {
        if ($this->uri->segment(2) != "login") {
            $username = get_session("username");
            $password = get_session("password");

            $res = $this->login->check_login($username, $password);
            if ($res["is_success"] == 0) {
                if (!$this->is_ajax) {
                    if ($this->uri->segment(1) == "admin") redirect_url('admin/login/do_logout');
                    if ($this->uri->segment(1) == "kasir") redirect_url('kasir/login/do_logout');
                }

                // ob_clean();
                $res = set_http_response_error(401, "Please log in again");
                echo json_encode($res);
                exit;
            }
        }
    }

    function check_token($username = "", $password = "")
    {
        $res = $this->login->check_login($username, $password);
        if ($res["is_success"] == 0) {
            $res = set_http_response_error(401, "Please log in again");
            print_json($res);
            die();
        }
    }

    function assemblyToken($data = array())
    {
        $key = $this->tokenKey;

        $plaintext = json_encode($data);
        $ivlen = openssl_cipher_iv_length($cipher = "AES-128-CBC");
        $iv = openssl_random_pseudo_bytes($ivlen);
        $ciphertext_raw = openssl_encrypt($plaintext, $cipher, $key, $options = OPENSSL_RAW_DATA, $iv);
        $hmac = hash_hmac('sha256', $ciphertext_raw, $key, $as_binary = true);
        $ciphertext = base64_encode($iv . $hmac . $ciphertext_raw);

        return $ciphertext;
    }

    function disassemblyToken($token = "")
    {

        $key = $this->tokenKey;

        $c = base64_decode($token);
        $ivlen = openssl_cipher_iv_length($cipher = "AES-128-CBC");
        $iv = substr($c, 0, $ivlen);
        $hmac = substr($c, $ivlen, $sha2len = 32);
        $ciphertext_raw = substr($c, $ivlen + $sha2len);
        $original_plaintext = openssl_decrypt($ciphertext_raw, $cipher, $key, $options = OPENSSL_RAW_DATA, $iv);
        $calcmac = hash_hmac('sha256', $ciphertext_raw, $key, $as_binary = true);
        if (hash_equals($hmac, $calcmac)) // timing attack safe comparison
        {
            $data = json_decode($original_plaintext, true);
            if (!is_array($data)) return array();
            return $data;
        }
        return array();
    }
}
