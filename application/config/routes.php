<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$route['default_controller'] = 'beranda';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

// **
// custom route for admin
$route['admin']     = "admin/beranda";
$route['kasir']     = "kasir/penjualan";
$route['admin/laporan']     = "admin";

/*
// **
// custom route for portal
$uri_1 = $this->uri->segment(1);
if($uri_1 != "contact" && $uri_1 != "asset" && $uri_1 != "jenis_izin"){
    // post_type = halaman    
    $route["(:any)"] = "post/index/halaman/$1";
}

$exception_list = array(
    // cms
    "halaman", 
    "contact", 
    "asset", 
    "admin", 
);


if($uri_1 == "asset"){
    $route["asset"] = "asset/index";
}else{        
    // menampilkan semua post selain type halaman, admin dan kontak, sesuai type pada $uri_1
    if(!in_array($uri_1, $exception_list)){
        
        if(in_array($uri_1, get_allowed_post_type())){
            $route['(:any)'] = "post/index/$1"; // menampilkan semua post selain halaman sesuai type
        }
        $route['(:any)/lihat/(:any)'] = "post/index/$1/$2"; // menampilkan detail post selain halaman sesuai type
    
        $route["(:any)/(:any)"] = "post/by_kategori/$1/$2";
    }

}
*/
