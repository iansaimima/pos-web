<?php

// ====================================================================================================
// TANYAKAN PADA DEVELOPER LEBIH DULU JIKA INGIN MENGUBAH
// ====================================================================================================
// MENGUBAH ISI FILE INI TANPA MENANYAKAN PADA DEVELOPER DAPAT MENGAKIBATKAN APLIKASI TIDAK BISA
// DIGUNAKAN
// ====================================================================================================

define("APP_THEME", "skin-red");

define("DB_USER", "root");
define("DB_PASS", "root");
define("DB_NAME", "app_pos");

define("ENVIRONTMENT_TYPE", "development");
define("DEFAULT_TIEMZONE", "Asia/Jakarta");
define("BASE_URL",$_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/app-pos/');

define("PREFIX_ITEM_TRANSFER", "IT");
define("PREFIX_STOCK_AWAL", "SA");
define("PREFIX_STOCK_OPNAME", "OP");

define("PREFIX_PEMBELIAN", "BL");
define("PREFIX_PEMBELIAN_RETUR", "RBL");
define("PREFIX_PEMBAYARAN_HUTANG", "BHU");

define("PREFIX_PENJUALAN", "JL");
define("PREFIX_PENJUALAN_RETUR", "RJL");
define("PREFIX_PEMBAYARAN_PIUTANG", "BPI");

define("PREFIX_PEMASOK", "SUP");
define("PREFIX_PELANGGAN", "CUS");

define("KUNCI_TANGGAL_FORM_KASIR", 0);