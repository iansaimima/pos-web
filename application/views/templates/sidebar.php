<?php

$user = get_session("user");
$user_role_name = $user["user_role_name"];
$user_role_name = strtolower($user_role_name);

$cabang_list = get_session("cabang_list");
$cabang_selected = get_session("cabang_selected");

$uri_1 = $this->uri->segment(1);
$target_uri = $_SERVER["REDIRECT_QUERY_STRING"];

$privilege_list = isset($user["privilege_list"]) ? $user["privilege_list"] : array();

$allow_beranda = isset($privilege_list["allow_beranda"]) ? $privilege_list["allow_beranda"] : 0;
$allow_transaksi_pembelian = isset($privilege_list["allow_transaksi_pembelian"]) ? $privilege_list["allow_transaksi_pembelian"] : 0;
$allow_transaksi_pembelian_retur = isset($privilege_list["allow_transaksi_pembelian_retur"]) ? $privilege_list["allow_transaksi_pembelian_retur"] : 0;
$allow_transaksi_penjualan = isset($privilege_list["allow_transaksi_penjualan"]) ? $privilege_list["allow_transaksi_penjualan"] : 0;
$allow_transaksi_penjualan_retur = isset($privilege_list["allow_transaksi_penjualan_retur"]) ? $privilege_list["allow_transaksi_penjualan_retur"] : 0;
$allow_transaksi_pembayaran_piutang = isset($privilege_list["allow_transaksi_pembayaran_piutang"]) ? $privilege_list["allow_transaksi_pembayaran_piutang"] : 0;

$allow_kas_alur = isset($privilege_list["allow_kas_alur"]) ? $privilege_list["allow_kas_alur"] : 0;
$allow_kas_akun = isset($privilege_list["allow_kas_akun"]) ? $privilege_list["allow_kas_akun"] : 0;
$allow_kas_kategori = isset($privilege_list["allow_kas_kategori"]) ? $privilege_list["allow_kas_kategori"] : 0;

$allow_item = isset($privilege_list["allow_item"]) ? $privilege_list["allow_item"] : 0;
$allow_item_kategori = isset($privilege_list["allow_item_kategori"]) ? $privilege_list["allow_item_kategori"] : 0;

$allow_stock_awal = isset($privilege_list["allow_stock_awal"]) ? $privilege_list["allow_stock_awal"] : 0;
$allow_stock_opname = isset($privilege_list["allow_stock_opname"]) ? $privilege_list["allow_stock_opname"] : 0;
$allow_item_transfer = isset($privilege_list["allow_item_transfer"]) ? $privilege_list["allow_item_transfer"] : 0;

$allow_pemasok = isset($privilege_list["allow_pemasok"]) ? $privilege_list["allow_pemasok"] : 0;
$allow_pelanggan = isset($privilege_list["allow_pelanggan"]) ? $privilege_list["allow_pelanggan"] : 0;
$allow_gudang = isset($privilege_list["allow_gudang"]) ? $privilege_list["allow_gudang"] : 0;
$allow_cabang = isset($privilege_list["allow_cabang"]) ? $privilege_list["allow_cabang"] : 0;
$allow_edit_pengaturan_lain_lain = isset($privilege_list["allow_edit_pengaturan_lain_lain"]) ? $privilege_list["allow_edit_pengaturan_lain_lain"] : 0;

$allow_user_login = isset($privilege_list["allow_user_login"]) ? $privilege_list["allow_user_login"] : 0;
$allow_user_akses = isset($privilege_list["allow_user_akses"]) ? $privilege_list["allow_user_akses"] : 0;

$allow_laporan_item = isset($privilege_list["allow_laporan_item"]) ? $privilege_list["allow_laporan_item"] : 0;
$allow_laporan_item_transfer = isset($privilege_list["allow_laporan_item_transfer"]) ? $privilege_list["allow_laporan_item_transfer"] : 0;
$allow_laporan_kartu_stock = isset($privilege_list["allow_laporan_kartu_stock"]) ? $privilege_list["allow_laporan_kartu_stock"] : 0;
$allow_laporan_riwayat_stock_item = isset($privilege_list["allow_laporan_riwayat_stock_item"]) ? $privilege_list["allow_laporan_riwayat_stock_item"] : 0;
$allow_laporan_laba_jual = isset($privilege_list["allow_laporan_laba_jual"]) ? $privilege_list["allow_laporan_laba_jual"] : 0;

$allow_laporan_pembelian_rekap = isset($privilege_list["allow_laporan_pembelian_rekap"]) ? $privilege_list["allow_laporan_pembelian_rekap"] : 0;
$allow_laporan_pembelian_detail = isset($privilege_list["allow_laporan_pembelian_detail"]) ? $privilege_list["allow_laporan_pembelian_detail"] : 0;
$allow_laporan_pembelian_harian = isset($privilege_list["allow_laporan_pembelian_harian"]) ? $privilege_list["allow_laporan_pembelian_harian"] : 0;

$allow_laporan_penjualan_rekap = isset($privilege_list["allow_laporan_penjualan_rekap"]) ? $privilege_list["allow_laporan_penjualan_rekap"] : 0;
$allow_laporan_penjualan_detail = isset($privilege_list["allow_laporan_penjualan_detail"]) ? $privilege_list["allow_laporan_penjualan_detail"] : 0;
$allow_laporan_penjualan_harian = isset($privilege_list["allow_laporan_penjualan_harian"]) ? $privilege_list["allow_laporan_penjualan_harian"] : 0;

$allow_laporan_penjualan = isset($privilege_list["allow_laporan_penjualan"]) ? $privilege_list["allow_laporan_penjualan"] : 0;
$allow_laporan_pelanggan = isset($privilege_list["allow_laporan_pelanggan"]) ? $privilege_list["allow_laporan_pelanggan"] : 0;
$allow_laporan_pemasok = isset($privilege_list["allow_laporan_pemasok"]) ? $privilege_list["allow_laporan_pemasok"] : 0;
$allow_laporan_alur_kas = isset($privilege_list["allow_laporan_alur_kas"]) ? $privilege_list["allow_laporan_alur_kas"] : 0;
$allow_laporan_persediaan_stock_opname = isset($privilege_list["allow_laporan_persediaan_stock_opname"]) ? $privilege_list["allow_laporan_persediaan_stock_opname"] : 0;
$allow_laporan_persediaan_stock_awal = isset($privilege_list["allow_laporan_persediaan_stock_awal"]) ? $privilege_list["allow_laporan_persediaan_stock_awal"] : 0;
$allow_laporan_piutang_aktif = isset($privilege_list["allow_laporan_piutang_aktif"]) ? $privilege_list["allow_laporan_piutang_aktif"] : 0;
$allow_laporan_piutang_aktif_sudah_jatuh_tempo = isset($privilege_list["allow_laporan_piutang_aktif_sudah_jatuh_tempo"]) ? $privilege_list["allow_laporan_piutang_aktif_sudah_jatuh_tempo"] : 0;

if($uri_1 == "kasir"){
    $allow_transaksi_pembelian = 0;
    $allow_transaksi_pembelian_retur = 0;
    $allow_transaksi_pembayaran_piutang = 0;
    $allow_kas_alur =  0;
    $allow_kas_akun =  0;
    $allow_kas_kategori =  0;
    $allow_item_kategori =  0;
    
    $allow_stock_awal =  0;
    $allow_stock_opname = 0;
    $allow_item_transfer = 0;

    $allow_pemasok = 0;
    $allow_pelanggan = 0;
    $allow_gudang = 0;
    $allow_cabang = 0;
    $allow_edit_pengaturan_lain_lain = 0;

    $allow_user_login = 0;
    $allow_user_akses = 0;

    $allow_laporan_item = 0;
    $allow_laporan_item_transfer = 0;
    $allow_laporan_kartu_stock = 0;
    $allow_laporan_riwayat_stock_item = 0;
    $allow_laporan_laba_jual = 0;

    $allow_laporan_pembelian_rekap = 0;
    $allow_laporan_pembelian_detail = 0;
    $allow_laporan_pembelian_harian = 0;

    $allow_laporan_penjualan_rekap = 0;
    $allow_laporan_penjualan_detail = 0;
    $allow_laporan_penjualan_harian = 0;

    $allow_laporan_penjualan = 0;
    $allow_laporan_pelanggan = 0;
    $allow_laporan_pemasok = 0;
    $allow_laporan_alur_kas = 0;
    $allow_laporan_persediaan_stock_opname = 0;
    $allow_laporan_persediaan_stock_awal = 0;
    $allow_laporan_piutang_aktif = 0;
    $allow_laporan_piutang_aktif_sudah_jatuh_tempo = 0;

}

$menu_transaksi_list = array("pembelian", "pembelian_retur", "penjualan", "penjualan_retur", "pembayaran_piutang");
$menu_transaksi_pembelian_list = array("pembelian", "pembelian_retur", "pembayaran_hutang");
$menu_transaksi_penjualan_list = array("penjualan", "penjualan_retur", "pembayaran_piutang");

$menu_kas_list = array("kas_alur", "kas_akun", "kas_kategori");
$menu_item_list = array("item", "item_kategori");
$menu_pengaturan_list = array("pemasok", "pelanggan", "settings");
$menu_user_list = array("user", "user_role", "user_role_privilege");

$menu_laporan_list = array("item", "kartu_stock", "laba_jual", "pembelian", "penjualan", "pelanggan", "pemasok", "alur_kas", "stock_opname", "piutang");

$menu_laporan_aktif = "";
if (in_array($this->uri->segment(3), $menu_laporan_list)) $menu_laporan_aktif = "active mm-active";

$transaksi_pembelian_aktif = "";
if (in_array($this->uri->segment(2), $menu_transaksi_pembelian_list)) $transaksi_pembelian_aktif = "active mm-active";

$transaksi_penjualan_aktif = "";
if (in_array($this->uri->segment(2), $menu_transaksi_penjualan_list)) $transaksi_penjualan_aktif = "active mm-active";

$dashboard_aktif = $this->uri->segment(2) == "" ? "active mm-active" : "";

$kas_alur_aktif = $this->uri->segment(2) == "kas_alur" ? "active mm-active" : "";
$kas_kategori_aktif = $this->uri->segment(2) == "kas_kategori" ? "active mm-active" : "";
$kas_akun_aktif = $this->uri->segment(2) == "kas_akun" ? "active mm-active" : "";

$item_aktif = $this->uri->segment(2) == "item" ? "active mm-active" : "";
$item_kategori_aktif = $this->uri->segment(2) == "item_kategori" ? "active mm-active" : "";

$stock_awal_aktif = $this->uri->segment(2) == "stock_awal" ? "active mm-active" : "";
$stock_opname_aktif = $this->uri->segment(2) == "stock_opname" ? "active mm-active" : "";
$item_transfer_aktif = $this->uri->segment(2) == "item_transfer" ? "active mm-active" : "";

$uri_2__uri_3 = $this->uri->segment(2) . "/" . $this->uri->segment(3);
$laporan_item = $uri_2__uri_3 == "laporan/item" ? "active mm-active" : "";
$laporan_item_transfer_aktif = $uri_2__uri_3 == "laporan/item_transfer" ? "active mm-active" : "";
$laporan_kartu_stock_aktif = $uri_2__uri_3 == "laporan/kartu_stock" ? "active mm-active" : "";
$laporan_laba_jual_aktif = $uri_2__uri_3 == "laporan/laba_jual" ? "active mm-active" : "";
$laporan_pembelian_rekap_aktif = $uri_2__uri_3 == "laporan/pembelian/rekap" ? "active mm-active" : "";
$laporan_pembelian_detail_aktif = $uri_2__uri_3 == "laporan/pembelian/detail" ? "active mm-active" : "";
$laporan_pembelian_harian_aktif = $uri_2__uri_3 == "laporan/pembelian/harian" ? "active mm-active" : "";
$laporan_penjualan_rekap_aktif = $uri_2__uri_3 == "laporan/penjualan/rekap" ? "active mm-active" : "";
$laporan_penjualan_detail_aktif = $uri_2__uri_3 == "laporan/penjualan/detail" ? "active mm-active" : "";
$laporan_penjualan_harian_aktif = $uri_2__uri_3 == "laporan/penjualan/harian" ? "active mm-active" : "";
$laporan_piutang_aktif = $uri_2__uri_3 == "laporan/piutang/aktif" ? "active mm-active" : "";
$laporan_alur_kas_aktif = $uri_2__uri_3 == "laporan/alur_kas" ? "active mm-active" : "";
$laporan_stock_opname_aktif = $uri_2__uri_3 == "laporan/stock_opname" ? "active mm-active" : "";
$laporan_pelanggan_aktif = $uri_2__uri_3 == "laporan/pelanggan" ? "active mm-active" : "";
$laporan_pemasok_aktif = $uri_2__uri_3 == "laporan/pemasok" ? "active mm-active" : "";


$pelanggan_aktif = $this->uri->segment(2) == "pelanggan" ? "active mm-active" : "";
$pemasok_aktif = $this->uri->segment(2) == "pemasok" ? "active mm-active" : "";
$gudang_aktif = $this->uri->segment(2) == "gudang" ? "active mm-active" : "";
$cabang_aktif = $this->uri->segment(2) == "cabang" ? "active mm-active" : "";
$settings_aktif = $this->uri->segment(2) == "settings" ? "active mm-active" : "";

$user_aktif = $this->uri->segment(2) == "user" ? "active mm-active" : "";
$user_role_aktif = $this->uri->segment(2) == "user_role" ? "active mm-active" : "";
$user_role_privilege_aktif = $this->uri->segment(2) == "user_role_privilege" ? "active mm-active" : "";
?>

<!--**********************************
            Sidebar start
        ***********************************-->
<div class="deznav">
    <div class="deznav-scroll">
        <ul class="metismenu" id="menu">
            <?php
            if ($allow_beranda) {
            ?>
                <li class="<?= $dashboard_aktif ?>">
                    <a href="<?= base_url($uri_1) ?>" class="ai-icon" aria-expanded="false">
                        <i class="lni lni-dashboard"></i>
                        <span class="nav-text">Dashboard</span>
                    </a>
                </li>
            <?php
            }
            ?>

            <li class="nav-label d-sm-block d-md-none">Ganti Cabang</li>
            <li class="active mm-active d-sm-block d-md-none">
                <a href="javascript:void(0);" class="ai-icon" aria-expanded="false">
                    <span class="badge badge-success"><?= isset($cabang_selected["kode"]) ?$cabang_selected["kode"] : "" ?></span> <?= isset($cabang_selected["nama"]) ? $cabang_selected["nama"] : "" ?> <span class="fa fa-check text-success"></span>
                </a>
            </li>
            <?php
            foreach ($cabang_list as $c_uuid => $c) {
                if (count($c) == 0) continue;
                $cabang_uuid = $c["uuid"];
                $cabang_kode = $c["kode"];
                $cabang_nama = $c["nama"];
                $cabang_alamat = $c["alamat"];

                $cabang_selected_uuid = isset($cabang_selected["uuid"]) ? $cabang_selected["uuid"] : "";

                if ($cabang_uuid == $cabang_selected_uuid) continue;

            ?>
                <li class="d-sm-block d-md-none">
                    <a href="<?= base_url($this->uri->segment(1) . '/beranda/set_cabang/' . $cabang_uuid . "?target=" . $target_uri) ?>">
                        <span class="badge badge-success"><?= $cabang_kode ?></span>
                        <?= $cabang_nama ?>
                    </a>
                </li>
            <?php
            }
            ?>

            <?php
            if ($allow_transaksi_pembelian || $allow_transaksi_pembelian_retur || $allow_transaksi_penjualan || $allow_transaksi_penjualan_retur || $allow_transaksi_pembayaran_piutang) {
            ?>
                <li class="nav-label">Transaksi</li>
                <?php
                if ($allow_transaksi_pembelian || $allow_transaksi_pembelian_retur) {
                ?>
                    <li class="<?= $transaksi_pembelian_aktif ?>">
                        <a class="has-arrow ai-icon" href="javascript:void()" aria-expanded="false">
                            <i class="lni lni-shopping-basket"></i>
                            <span class="nav-text">Pembelian</span>
                        </a>
                        <ul aria-expanded="false">
                            <?php if ($allow_transaksi_pembelian) { ?>
                                <li><a href="<?= base_url($uri_1 . "/pembelian") ?>">Daftar Pembelian</a></li>
                            <?php } ?>

                            <?php if ($allow_transaksi_pembelian_retur) { ?>
                                <li><a href="<?= base_url($uri_1 . "/pembelian_retur") ?>">Retur Pembelian</a></li>
                            <?php } ?>
                        </ul>
                    </li>
                <?php
                }
                ?>

                <?php
                if ($allow_transaksi_penjualan || $allow_transaksi_penjualan_retur || $allow_transaksi_pembayaran_piutang) {
                ?>
                    <li class="<?= $transaksi_penjualan_aktif ?>">
                        <a class="has-arrow ai-icon" href="javascript:void()" aria-expanded="false">
                            <i class="lni lni-cart"></i>
                            <span class="nav-text">Penjualan</span>
                        </a>
                        <ul aria-expanded="false">
                            <?php if ($allow_transaksi_penjualan) { ?>
                                <li><a href="<?= base_url($uri_1 . "/penjualan") ?>">Daftar Penjualan</a></li>
                            <?php } ?>

                            <?php if ($allow_transaksi_pembayaran_piutang) { ?>
                                <li><a href="<?= base_url($uri_1 . "/pembayaran_piutang") ?>">Pembayaran Piutang</a></li>
                            <?php } ?>
                        </ul>
                    </li>
                <?php
                }
                ?>
            <?php
            }
            ?>


            <?php
            if ($allow_kas_akun || $allow_kas_alur || $allow_kas_kategori) {
            ?>
                <li class="nav-label">Kas</li>
                <?php if ($allow_kas_alur) { ?>
                    <li class="<?= $kas_alur_aktif ?>">
                        <a href="<?= base_url($uri_1 . "/kas_alur") ?>" class="ai-icon" aria-expanded="false">
                            <i class="lni lni-investment"></i>
                            <span class="nav-text">Alur Kas</span>
                        </a>
                    </li>
                <?php } ?>

                <?php if ($allow_kas_akun) { ?>
                    <li class="<?= $kas_akun_aktif ?>">
                        <a href="<?= base_url($uri_1 . "/kas_akun") ?>" class="ai-icon" aria-expanded="false">
                            <i class="lni lni-wallet"></i>
                            <span class="nav-text">Akun Kas</span>
                        </a>
                    </li>
                <?php } ?>

                <?php if ($allow_kas_kategori) { ?>
                    <li class="<?= $kas_kategori_aktif ?>">
                        <a href="<?= base_url($uri_1 . "/kas_kategori") ?>" class="ai-icon" aria-expanded="false">
                            <i class="lni lni-grid"></i>
                            <span class="nav-text">Kategori Kas</span>
                        </a>
                    </li>
                <?php } ?>
            <?php
            }
            ?>


            <?php if ($allow_item || $allow_item_kategori) { ?>
                <li class="nav-label">Item</li>

                <?php if ($allow_item) { ?>
                    <li class="<?= $item_aktif ?>">
                        <a href="<?= base_url($uri_1 . "/item") ?>" class="ai-icon" aria-expanded="false">
                            <i class="lni lni-package"></i>
                            <span class="nav-text">Daftar Item</span>
                        </a>
                    </li>
                <?php } ?>

                <?php if ($allow_item_kategori) { ?>
                    <li class="<?= $item_kategori_aktif ?>">
                        <a href="<?= base_url($uri_1 . "/item_kategori") ?>" class="ai-icon" aria-expanded="false">
                            <i class="lni lni-grid"></i>
                            <span class="nav-text">Kategori Item</span>
                        </a>
                    </li>
                <?php  } ?>
            <?php } ?>


            <?php if ($allow_stock_awal || $allow_stock_opname || $allow_item_transfer) { ?>
                <li class="nav-label">Persediaan</li>

                <?php if ($allow_stock_awal) { ?>
                    <li class="<?= $stock_awal_aktif ?>">
                        <a href="<?= base_url($uri_1 . "/stock_awal") ?>" class="ai-icon" aria-expanded="false">
                            <i class="lni lni-stop"></i>
                            <span class="nav-text">Stock Awal</span>
                        </a>
                    </li>
                <?php } ?>

                <?php if ($allow_stock_opname) { ?>
                    <li class="<?= $stock_opname_aktif ?>">
                        <a href="<?= base_url($uri_1 . "/stock_opname") ?>" class="ai-icon" aria-expanded="false">
                            <i class="lni lni-search-alt"></i>
                            <span class="nav-text">Stock Opname</span>
                        </a>
                    </li>
                <?php } ?>

                <?php if ($allow_item_transfer) { ?>
                    <li class="<?= $item_transfer_aktif ?>">
                        <a href="<?= base_url($uri_1 . "/item_transfer") ?>" class="ai-icon" aria-expanded="false">
                            <i class="lni lni-arrows-horizontal"></i>
                            <span class="nav-text">Item Transfer</span>
                        </a>
                    </li>
                <?php } ?>
            <?php } ?>


            <?php
            if (
                $allow_laporan_item ||
                $allow_laporan_item_transfer ||
                $allow_laporan_laba_jual ||
                $allow_laporan_kartu_stock ||
                $allow_laporan_pembelian_rekap ||
                $allow_laporan_pembelian_detail ||
                $allow_laporan_pembelian_harian ||
                $allow_laporan_penjualan_rekap ||
                $allow_laporan_penjualan_detail ||
                $allow_laporan_penjualan_harian ||
                $allow_laporan_piutang_aktif ||
                $allow_laporan_alur_kas ||
                $allow_laporan_persediaan_stock_opname ||
                $allow_laporan_pelanggan ||
                $allow_laporan_pemasok
            ) {
            ?>
                <li class="nav-label">Laporan</li>


                <?php if ($allow_laporan_laba_jual) { ?>
                    <li class="<?= $laporan_laba_jual_aktif ?>">
                        <a href="<?= base_url($uri_1 . "/laporan/laba_jual") ?>" class="ai-icon" aria-expanded="false">
                            <i class="lni lni-empty-file"></i>
                            <span class="nav-text">Laba Jual</span>
                        </a>
                    </li>
                <?php } ?>

                <?php if ($allow_laporan_alur_kas) { ?>
                    <li class="<?= $laporan_alur_kas_aktif ?>">
                        <a href="<?= base_url($uri_1 . "/laporan/alur_kas") ?>" class="ai-icon" aria-expanded="false">
                            <i class="lni lni-empty-file"></i>
                            <span class="nav-text">Alur Kas</span>
                        </a>
                    </li>
                <?php } ?>

                <?php if ($allow_laporan_piutang_aktif) { ?>
                    <li class="<?= $laporan_piutang_aktif ?>">
                        <a href="<?= base_url($uri_1 . "/laporan/piutang/aktif") ?>" class="ai-icon" aria-expanded="false">
                            <i class="lni lni-empty-file"></i>
                            <span class="nav-text">Piutang</span>
                        </a>
                    </li>
                <?php } ?>

                <?php
                if ($allow_laporan_pembelian_detail || $allow_laporan_pembelian_harian || $allow_laporan_pembelian_rekap) {
                ?>
                    <li>
                        <a class="has-arrow ai-icon" href="javascript:void()" aria-expanded="false">
                            <i class="lni lni-empty-file"></i>
                            <span class="nav-text">Pembelian</span>
                        </a>
                        <ul aria-expanded="false">
                            <?php if ($allow_laporan_pembelian_rekap) { ?>
                                <li class="<?= $laporan_pembelian_rekap_aktif ?>"><a href="<?= base_url($uri_1 . "/laporan/pembelian/rekap") ?>">Rekap</a></li>
                            <?php } ?>
                            <?php if ($allow_laporan_pembelian_detail) { ?>
                                <li class="<?= $laporan_pembelian_detail_aktif ?>"><a href="<?= base_url($uri_1 . "/laporan/pembelian/detail") ?>">Detail</a></li>
                            <?php } ?>
                            <?php if ($allow_laporan_pembelian_harian) { ?>
                                <li class="<?= $laporan_pembelian_harian_aktif ?>"><a href="<?= base_url($uri_1 . "/laporan/pembelian/harian") ?>">Harian</a></li>
                            <?php } ?>
                        </ul>
                    </li>
                <?php
                }
                ?>

                <?php
                if ($allow_laporan_penjualan_detail || $allow_laporan_penjualan_harian || $allow_laporan_penjualan_rekap) {
                ?>
                    <li>
                        <a class="has-arrow ai-icon" href="javascript:void()" aria-expanded="false">
                            <i class="lni lni-empty-file"></i>
                            <span class="nav-text">Penjualan</span>
                        </a>
                        <ul aria-expanded="false">
                            <?php if ($allow_laporan_penjualan_rekap) { ?>
                                <li class="<?= $laporan_penjualan_rekap_aktif ?>"><a href="<?= base_url($uri_1 . "/laporan/penjualan/rekap") ?>">Rekap</a></li>
                            <?php } ?>
                            <?php if ($allow_laporan_penjualan_detail) { ?>
                                <li class="<?= $laporan_penjualan_detail_aktif ?>"><a href="<?= base_url($uri_1 . "/laporan/penjualan/detail") ?>">Detail</a></li>
                            <?php } ?>
                            <?php if ($allow_laporan_penjualan_harian) { ?>
                                <li class="<?= $laporan_penjualan_harian_aktif ?>"><a href="<?= base_url($uri_1 . "/laporan/penjualan/harian") ?>">Harian</a></li>
                            <?php } ?>
                        </ul>
                    </li>
                <?php
                }
                ?>

                <?php if ($allow_laporan_item) { ?>
                    <li class="<?= $laporan_item ?>">
                        <a href="<?= base_url($uri_1 . "/laporan/item") ?>" class="ai-icon" aria-expanded="false">
                            <i class="lni lni-empty-file"></i>
                            <span class="nav-text">Item</span>
                        </a>
                    </li>
                <?php } ?>

                <?php if ($allow_laporan_kartu_stock) { ?>
                    <li class="<?= $laporan_kartu_stock_aktif ?>">
                        <a href="<?= base_url($uri_1 . "/laporan/kartu_stock") ?>" class="ai-icon" aria-expanded="false">
                            <i class="lni lni-empty-file"></i>
                            <span class="nav-text">Kartu Stock</span>
                        </a>
                    </li>
                <?php } ?>

                <?php if ($allow_laporan_persediaan_stock_opname) { ?>
                    <li class="<?= $laporan_stock_opname_aktif ?>">
                        <a href="<?= base_url($uri_1 . "/laporan/stock_opname") ?>" class="ai-icon" aria-expanded="false">
                            <i class="lni lni-empty-file"></i>
                            <span class="nav-text">Stock Opname</span>
                        </a>
                    </li>
                <?php } ?>

                <?php if ($allow_laporan_item_transfer) { ?>
                    <li class="<?= $laporan_item_transfer_aktif ?>">
                        <a href="<?= base_url($uri_1 . "/laporan/item_transfer") ?>" class="ai-icon" aria-expanded="false">
                            <i class="lni lni-empty-file"></i>
                            <span class="nav-text">Item Transfer</span>
                        </a>
                    </li>
                <?php } ?>

                <?php if ($allow_laporan_pelanggan) { ?>
                    <li class="<?= $laporan_pelanggan_aktif ?>">
                        <a href="<?= base_url($uri_1 . "/laporan/pelanggan") ?>" class="ai-icon" aria-expanded="false">
                            <i class="lni lni-empty-file"></i>
                            <span class="nav-text">Pelanggan</span>
                        </a>
                    </li>
                <?php } ?>

                <?php if ($allow_laporan_pemasok) { ?>
                    <li class="<?= $laporan_pemasok_aktif ?>">
                        <a href="<?= base_url($uri_1 . "/laporan/pemasok") ?>" class="ai-icon" aria-expanded="false">
                            <i class="lni lni-empty-file"></i>
                            <span class="nav-text">Pemasok</span>
                        </a>
                    </li>
                <?php } ?>
            <?php
            }
            ?>


            <?php if ($allow_pelanggan || $allow_pemasok || $allow_gudang || $allow_cabang || $allow_edit_pengaturan_lain_lain) { ?>
                <li class="nav-label">Pengaturan</li>

                <?php if ($allow_pemasok) { ?>
                    <li class="<?= $pemasok_aktif ?>">
                        <a href="<?= base_url($uri_1 . "/pemasok") ?>" class="ai-icon" aria-expanded="false">
                            <i class="lni lni-car"></i>
                            <span class="nav-text">Pemasok</span>
                        </a>
                    </li>
                <?php } ?>

                <?php if ($allow_pelanggan) { ?>
                    <li class="<?= $pelanggan_aktif ?>">
                        <a href="<?= base_url($uri_1 . "/pelanggan") ?>" class="ai-icon" aria-expanded="false">
                            <i class="lni lni-users"></i>
                            <span class="nav-text">Pelanggan</span>
                        </a>
                    </li>
                <?php } ?>

                <?php if ($allow_gudang) { ?>
                    <li class="<?= $gudang_aktif ?>">
                        <a href="<?= base_url($uri_1 . "/gudang") ?>" class="ai-icon" aria-expanded="false">
                            <i class="lni lni-home"></i>
                            <span class="nav-text">Gudang</span>
                        </a>
                    </li>
                <?php } ?>

                <?php if ($allow_cabang) { ?>
                    <li class="<?= $cabang_aktif ?>">
                        <a href="<?= base_url($uri_1 . "/cabang") ?>" class="ai-icon" aria-expanded="false">
                            <i class="lni lni-apartment"></i>
                            <span class="nav-text">Cabang</span>
                        </a>
                    </li>
                <?php } ?>

                <?php if ($allow_edit_pengaturan_lain_lain) { ?>
                    <li class="<?= $settings_aktif ?>">
                        <a href="<?= base_url($uri_1 . "/settings") ?>" class="ai-icon" aria-expanded="false">
                            <i class="lni lni-cog"></i>
                            <span class="nav-text">Lain-lain</span>
                        </a>
                    </li>
                <?php } ?>

            <?php } ?>


            <?php if ($allow_user_login || $allow_user_akses) { ?>
                <li class="nav-label">User</li>

                <?php if ($allow_user_login) { ?>
                    <li class="<?= $user_aktif ?>">
                        <a href="<?= base_url($uri_1 . "/user") ?>" class="ai-icon" aria-expanded="false">
                            <i class="lni lni-user"></i>
                            <span class="nav-text">User Login</span>
                        </a>
                    </li>
                <?php } ?>

                <?php if ($allow_user_akses) { ?>
                    <li class="<?= $user_role_aktif ?>">
                        <a href="<?= base_url($uri_1 . "/user_role") ?>" class="ai-icon" aria-expanded="false">
                            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                                <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                    <rect x="0" y="0" width="24" height="24"></rect>
                                    <path d="M5.5,4 L9.5,4 C10.3284271,4 11,4.67157288 11,5.5 L11,6.5 C11,7.32842712 10.3284271,8 9.5,8 L5.5,8 C4.67157288,8 4,7.32842712 4,6.5 L4,5.5 C4,4.67157288 4.67157288,4 5.5,4 Z M14.5,16 L18.5,16 C19.3284271,16 20,16.6715729 20,17.5 L20,18.5 C20,19.3284271 19.3284271,20 18.5,20 L14.5,20 C13.6715729,20 13,19.3284271 13,18.5 L13,17.5 C13,16.6715729 13.6715729,16 14.5,16 Z" fill="#000000"></path>
                                    <path d="M5.5,10 L9.5,10 C10.3284271,10 11,10.6715729 11,11.5 L11,18.5 C11,19.3284271 10.3284271,20 9.5,20 L5.5,20 C4.67157288,20 4,19.3284271 4,18.5 L4,11.5 C4,10.6715729 4.67157288,10 5.5,10 Z M14.5,4 L18.5,4 C19.3284271,4 20,4.67157288 20,5.5 L20,12.5 C20,13.3284271 19.3284271,14 18.5,14 L14.5,14 C13.6715729,14 13,13.3284271 13,12.5 L13,5.5 C13,4.67157288 13.6715729,4 14.5,4 Z" fill="#000000" opacity="0.3"></path>
                                </g>
                            </svg>
                            <span class="nav-text">User Akses</span>
                        </a>
                    </li>
                <?php } ?>
            <?php } ?>

            <?php if ($user_role_name == "super administrator") { ?>
                <li class="<?= $user_role_privilege_aktif ?>">
                    <a href="<?= base_url($uri_1 . "/user_role_privilege") ?>" class="ai-icon" aria-expanded="false">
                        <i class="lni lni-consulting"></i>
                        <span class="nav-text">User Role Privilege</span>
                    </a>
                </li>
            <?php } ?>
        </ul>
    </div>
</div>