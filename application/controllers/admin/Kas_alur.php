<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Kas_alur extends MY_Controller {
  //put your code here
  
  public function __construct() {
    parent::__construct();
    
    model("m_kas_alur", "kas_alur");
    model("m_kas_akun", "kas_akun");
    model("m_kas_kategori", "kas_kategori");
  }

  function index(){
    //-- Index

    $data = array();
    $data["breadcrumb"] = array(
        "Kas" => "", "Alur Kas" => "active"
    );
    $data["saldo_saat_ini"] = $this->kas_alur->kas_alur_get_saldo_saat_ini();
    $data["kas_akun_list"] = $this->kas_akun->kas_akun_get_list();
    view("pages/kas/kas_alur/index", $data);
  }
  
  function ajax_get_summary(){
    //-- Get Saldo Saat Ini
    $res = $this->kas_alur->kas_alur_get_summary();
    
    if(ob_get_contents()) ob_clean();
    echo json_encode($res);
  }

  function ajax_list(){
    //-- Get List
    $data           = $this->kas_alur->kas_alur_get_list($_GET);      
    $rows_total     = $this->kas_alur->kas_alur_get_total();
    $filtered_total = $this->kas_alur->kas_alur_get_filtered_total($_GET);

    $table_data = array(
      "draw"            => isset($_GET["draw"]) ? (int) $_GET["draw"] : 1, 
      "recordsTotal"    => $rows_total,
      "recordsFiltered" => $filtered_total, 
      "data"            => $data
    );

    if(ob_get_contents()) ob_clean();
    echo json_encode($table_data);
  }

  function ajax_detail($uuid = ""){
    //-- View Detail

    $data = array();
    $data["detail"] = $this->kas_alur->kas_alur_get($uuid);
    $data["kas_akun_list"] = $this->kas_akun->kas_akun_get_list();
    $data["kas_kategori_list"] = $this->kas_kategori->kas_kategori_get_list();
    view("pages/kas/kas_alur/ajax_detail", $data);
  }

  function ajax_delete($uuid = ""){
    //-- Arsip
    $res = $this->kas_alur->kas_alur_delete($uuid);

    if(ob_get_contents()) ob_clean();
    echo json_encode($res);
  } 

  function ajax_save(){
    //-- Save
    $res = $this->kas_alur->kas_alur_save($_POST);

    if(ob_get_contents()) ob_clean();
    echo json_encode($res);
  }
}
