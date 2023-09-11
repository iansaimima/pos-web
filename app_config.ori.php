<?php

// ====================================================================================================
// TANYAKAN PADA DEVELOPER LEBIH DULU JIKA INGIN MENGUBAH
// ====================================================================================================
// MENGUBAH ISI FILE INI TANPA MENANYAKAN PADA DEVELOPER DAPAT MENGAKIBATKAN APLIKASI TIDAK BISA
// DIGUNAKAN
// ====================================================================================================

define("DB_USER", "root");
define("DB_PASS", "root");
define("DB_NAME", "sijualpos_web");

define("ENVIRONTMENT_TYPE", "development");
define("DEFAULT_TIEMZONE", "Asia/Makassar");
define("BASE_URL",$_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/sijualpos-web/');

define("PREFIX_STOCK_AWAL", "SA");
define("PREFIX_STOCK_OPNAME", "OP");

define("PREFIX_PEMBELIAN", "BL");
define("PREFIX_PEMBELIAN_RETUR", "RBL");

define("PREFIX_PENJUALAN", "JL");
define("PREFIX_PENJUALAN_RETUR", "RJL");

define("PREFIX_PEMASOK", "SUP");
define("PREFIX_PELANGGAN", "CUS");

define("KUNCI_TANGGAL_FORM_KASIR", 0);