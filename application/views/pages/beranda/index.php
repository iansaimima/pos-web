<!DOCTYPE html>
<?php
$user = get_session("user");
$settings = get_session("settings");
$cabang_selected = get_session("cabang_selected");

$uri_1 = $this->uri->segment(1);

$total_penjualan_hari_ini = "0";
$total_penjualan_bulan_ini = "0";
$total_penjualan_tahun_ini = "0";
$total_laba_jual_tahun_ini = "0";

$selisih_penjualan_kemarin_persen = "0%";
$selisih_penjualan_bulan_lalu_persen = "0%";
$selisih_penjualan_tahun_lalu_persen = "0%";
$selisih_laba_jual_tahun_lalu_persen = "0%";

$selisih_penjualan_kemarin_stats = '';
$selisih_penjualan_bulan_lalu_stats = '';
$selisih_penjualan_tahun_lalu_stats = '';
$selisih_laba_jual_tahun_lalu_stats = '';

if (isset($penjualan_total_data) && is_array($penjualan_total_data) && count($penjualan_total_data) > 0) {
    $total_penjualan_hari_ini = $penjualan_total_data['total_hari_ini'];
    $total_penjualan_bulan_ini = $penjualan_total_data['total_bulan_ini'];
    $total_penjualan_tahun_ini = $penjualan_total_data['total_tahun_ini'];
    $total_laba_jual_tahun_ini = $penjualan_total_data['laba_jual_tahun_ini'];

    $selisih_penjualan_kemarin_persen = $penjualan_total_data['selisih_kemarin'];
    $selisih_penjualan_bulan_lalu_persen = $penjualan_total_data['selisih_bulan_lalu'];
    $selisih_penjualan_tahun_lalu_persen = $penjualan_total_data['selisih_tahun_lalu'];
    $selisih_laba_jual_tahun_lalu_persen = $penjualan_total_data['selisih_laba_jual_tahun_lalu'];

    $selisih_penjualan_kemarin_stats = $penjualan_total_data['selisih_kemarin_stats'];
    $selisih_penjualan_bulan_lalu_stats = $penjualan_total_data['selisih_bulan_lalu_stats'];
    $selisih_penjualan_tahun_lalu_stats = $penjualan_total_data['selisih_tahun_lalu_stats'];
    $selisih_laba_jual_tahun_lalu_stats = $penjualan_total_data['selisih_laba_jual_tahun_lalu_stats'];
}

$laba_jual_tahun_ini = array();
$laba_jual_tahun_lalu = array();
$laba_jual_labels = array();
if (isset($grafik_laba_jual) && is_array($grafik_laba_jual) && count($grafik_laba_jual) > 0) {
    $laba_jual_labels = $grafik_laba_jual["bulan_list"];
    $laba_jual_tahun_ini = $grafik_laba_jual["tahun_ini"];
    $laba_jual_tahun_lalu = $grafik_laba_jual["tahun_lalu"];
}

$penjualan_tahun_ini = array();
$penjualan_tahun_lalu = array();
$penjualan_labels = array();
if (isset($grafik_penjualan) && is_array($grafik_penjualan) && count($grafik_penjualan) > 0) {
    $penjualan_labels = $grafik_penjualan["bulan_list"];
    $penjualan_tahun_ini = $grafik_penjualan["tahun_ini"];
    $penjualan_tahun_lalu = $grafik_penjualan["tahun_lalu"];
}

// **
// untuk mockup
// $saldo_saat_ini = 23576000;
// $total_piutang_aktif_tahun_ini = 4586345;
// $total_piutang_aktif = 3494848;

// $total_penjualan_hari_ini = number_format(8000000, 0, ",", ".");
// $selisih_penjualan_kemarin_stats = "up";
// $selisih_penjualan_kemarin_persen = "100%";

// $total_penjualan_bulan_ini = number_format(12000000, 0, ",", ".");
// $selisih_penjualan_bulan_lalu_stats = "up";
// $selisih_penjualan_bulan_lalu_persen = "75%";

// $total_penjualan_tahun_ini = number_format(21546000, 0, ",", ".");
// $selisih_penjualan_tahun_lalu_stats = "up";
// $selisih_penjualan_tahun_lalu_persen = "100%";

// $total_laba_jual_tahun_ini = number_format(6500000, 0, ",", ".");
// $selisih_laba_jual_tahun_lalu_stats = "up";
// $selisih_laba_jual_tahun_lalu_persen = "100%";

?>
<html lang="en">

<head>
    <?php view("templates/meta") ?>
    <?php view("templates/style") ?>
    <!-- Favicon icon -->

    <link href="<?= base_url('assets') ?>/vendor/jqvmap/css/jqvmap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets') ?>/vendor/chartist/css/chartist.min.css">

    <link href="https://cdn.lineicons.com/2.0/LineIcons.css" rel="stylesheet">

</head>

<body>

    <!--*******************
        Preloader start
    ********************-->
    <div id="preloader">
        <div class="sk-three-bounce">
            <div class="sk-child sk-bounce1"></div>
            <div class="sk-child sk-bounce2"></div>
            <div class="sk-child sk-bounce3"></div>
        </div>
    </div>
    <!--*******************
        Preloader end
    ********************-->


    <!--**********************************
        Main wrapper start
    ***********************************-->
    <div id="main-wrapper">

        <?php view("templates/header") ?>
        <?php view("templates/sidebar") ?>

        <!--**********************************
            Content body start
        ***********************************-->
        <div class="content-body">
            <!-- row -->
            <div class="container-fluid">
                <?php view('templates/content_header') ?>
                <div class="row page-titles mx-0">
                    <div class="col-sm-6 p-md-0">
                        <div class="welcome-text">
                            <h4><?= $settings["TOKO_NAMA"]["_value"] ?> </h4>
                            <p class="mb-0"><span class="badge badge-roundedx badge-success"><?= $cabang_selected["kode"] ?></span> <?= ucwords($cabang_selected["nama"]) ?></p>
                            <p class="mb-0"><?= ucwords($cabang_selected["alamat"]) ?></p>
                            <p class="mb-0"><?= ucwords($cabang_selected["no_telepon"]) ?></p>
                        </div>
                    </div>
                    <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0">
                        <h4 class="text-right">Saldo, <?= isset($saldo_saat_ini) ? number_format($saldo_saat_ini, 0, ",", ".") : 0 ?></h4>
                        <p class="mb-0 text-right"><b>Total Piutang Aktif Tahun Ini,</b> <?= isset($total_piutang_aktif_tahun_ini) ? number_format($total_piutang_aktif_tahun_ini, 0, ",", ".") : "0" ?></p>
                        <p class="mb-0 text-right"><b>Total Piutang Aktif,</b> <?= isset($total_piutang_aktif) ? number_format($total_piutang_aktif, 0, ",", ".") : "0" ?></p>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xl-12 col-xxl-12">
                        <div class="row">

                            <div class="col-xl-3 col-xxl-3 col-lg-6 col-sm-6">
                                <div class="widget-stat card bg-primary">
                                    <div class="card-body  p-4">
                                        <div class="media">
                                            <div class="media-body text-white">
                                                <p class="mb-1">Penjualan Hari Ini </p>
                                                <h3 class="text-white"><?= $total_penjualan_hari_ini ?> </h3>
                                                <small>
                                                    <?php
                                                    if (!empty($selisih_penjualan_kemarin_stats)) {
                                                        echo "<i class='lni lni-chevron-" . $selisih_penjualan_kemarin_stats . "'></i> &nbsp;" . $selisih_penjualan_kemarin_persen . " dari kemarin";
                                                    }
                                                    ?>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl-3 col-xxl-3 col-lg-6 col-sm-6">
                                <div class="widget-stat card bg-warning">
                                    <div class="card-body span-4">
                                        <div class="media">
                                            <div class="media-body text-white">
                                                <p class="mb-1">Penjualan Bulan Ini </span></p>
                                                <h3 class="text-white"><?= $total_penjualan_bulan_ini ?></h3>
                                                <small>
                                                    <?php
                                                    if (!empty($selisih_penjualan_bulan_lalu_stats)) {
                                                        echo "<i class='lni lni-chevron-" . $selisih_penjualan_bulan_lalu_stats . "'></i> &nbsp;" . $selisih_penjualan_bulan_lalu_persen . " dari bulan lalu";
                                                    }
                                                    ?>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>


                            <div class="col-xl-3 col-xxl-3 col-lg-6 col-sm-6">
                                <div class="widget-stat card bg-secondary">
                                    <div class="card-body  p-4">
                                        <div class="media">
                                            <div class="media-body text-white">
                                                <p class="mb-1">Penjualan Tahun Ini</p>
                                                <h3 class="text-white"><?= $total_penjualan_tahun_ini ?></h3>
                                                <small>
                                                    <?php
                                                    if (!empty($selisih_penjualan_tahun_lalu_stats)) {
                                                        echo "<i class='lni lni-chevron-" . $selisih_penjualan_tahun_lalu_stats . "'></i> &nbsp;" . $selisih_penjualan_tahun_lalu_persen . " dari tahun lalu";
                                                    }
                                                    ?>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>


                            <div class="col-xl-3 col-xxl-3 col-lg-6 col-sm-6">
                                <div class="widget-stat card bg-danger">
                                    <div class="card-body  p-4">
                                        <div class="media">
                                            <div class="media-body text-white">
                                                <p class="mb-1">Laba Jual Tahun Ini</p>
                                                <h3 class="text-white"><?= $total_laba_jual_tahun_ini ?></h3>
                                                <small>
                                                    <?php
                                                    if (!empty($selisih_laba_jual_tahun_lalu_stats)) {
                                                        echo "<i class='lni lni-chevron-" . $selisih_laba_jual_tahun_lalu_stats . "'></i> &nbsp;" . $selisih_laba_jual_tahun_lalu_persen . " dari tahun lalu";
                                                    }
                                                    ?>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php
                        if(isset($item_stock_minimum_list) && is_array($item_stock_minimum_list) && count($item_stock_minimum_list) > 0){
                        ?>
                        <!-- item dibawah minimum stock -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header border-0 pb-0">
                                        <h4 class="card-title">Stock Dibawah Minimum</h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive" style="max-height: 500px;">
                                            <table class="table table-responsive-sm mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>Kode</th>
                                                        <th>Nama</th>
                                                        <th>Kategori</th>
                                                        <th style="text-align: right;">Stock Saat Ini</th>
                                                        <th style="text-align: right;">Stock Minimum</th>
                                                        <th>Satuan</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                        foreach ($item_stock_minimum_list as $l) {
                                                    ?>
                                                            <tr>
                                                                <td><?= $l["kode"] ?></td>
                                                                <td><?= $l["nama"] ?></td>
                                                                <td><?= $l["kategori_nama"] ?></td>
                                                                <td align="right"><?= $l["stock"] ?></td>
                                                                <td align="right"><?= $l["minimum_stock"] ?></td>
                                                                <td><?= $l["satuan"] ?></td>
                                                            </tr>
                                                    <?php
                                                        }
                                                    ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php
                        }
                        ?>

                        <div class="row">
                            <div class="col-xl-6 col-xxl-6 col-lg-12 col-md-12 d-nonex">
                                <div class="card">
                                    <div class="card-header border-0 pb-0">
                                        <h4 class="card-title">Sudah Jatuh Tempo</h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive" style="max-height: 500px;">
                                            <table class="table table-responsive-sm mb-0">
                                                <thead>
                                                    <tr>
                                                        <th><strong>No. Transaksi</strong></th>
                                                        <th style="text-align: right"><strong>Jatuh Tempo</strong></th>
                                                        <th style="text-align: right"><strong>Piutang</strong></th>
                                                        <th style="text-align: right"><strong>Sisa</strong></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    if (isset($daftar_piutang_sudah_jatuh_tempo) && is_array($daftar_piutang_sudah_jatuh_tempo) && count($daftar_piutang_sudah_jatuh_tempo) > 0) {
                                                        foreach ($daftar_piutang_sudah_jatuh_tempo as $l) {
                                                    ?>
                                                            <tr>
                                                                <td><b><?= $l["no_transaksi"] ?></b></td>
                                                                <td align="right"><?= date("d-m-Y", strtotime($l["tanggal_jatuh_tempo"])) ?></td>
                                                                <td align="right"><?= number_format($l["piutang"], 0, ",", ".") ?></td>
                                                                <td align="right"><?= number_format($l["sisa_piutang"], 0, ",", ".") ?></td>
                                                            </tr>
                                                    <?php
                                                        }
                                                    }
                                                    ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl-6 col-xxl-6 col-lg-12 col-md-12 d-nonex">
                                <div class="card">
                                    <div class="card-header border-0 pb-0">
                                        <h4 class="card-title">Jatuh Tempo Dalam 14 Hari</h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive" style="max-height: 500px;">
                                            <table class="table table-responsive-sm mb-0">
                                                <thead>
                                                    <tr>
                                                        <th><strong>No. Transaksi</strong></th>
                                                        <th style="text-align: right"><strong>Jatuh Tempo</strong></th>
                                                        <th style="text-align: right"><strong>Piutang</strong></th>
                                                        <th style="text-align: right"><strong>Sisa</strong></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    if (isset($daftar_piutang_akan_jatuh_tempo_14_hari) && is_array($daftar_piutang_akan_jatuh_tempo_14_hari) && count($daftar_piutang_akan_jatuh_tempo_14_hari) > 0) {
                                                        foreach ($daftar_piutang_akan_jatuh_tempo_14_hari as $l) {
                                                    ?>
                                                            <tr>
                                                                <td><b><?= $l["no_transaksi"] ?></b></td>
                                                                <td align="right"><?= date("d-m-Y", strtotime($l["tanggal_jatuh_tempo"])) ?></td>
                                                                <td align="right"><?= number_format($l["piutang"], 0, ",", ".") ?></td>
                                                                <td align="right"><?= number_format($l["sisa_piutang"], 0, ",", ".") ?></td>
                                                            </tr>
                                                    <?php
                                                        }
                                                    }
                                                    ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="row">
                    <div class="col-xl-6 col-xxl-6 col-lg-6 col-md-6">
                        <div id="user-activity" class="card">
                            <div class="card-header border-0 pb-0 d-sm-flex d-block">
                                <div>
                                    <h4 class="card-title">Grafik Laba Jual Tahun <?= date('Y') ?></h4>
                                    <p class="mb-1"></p>
                                </div>
                                <div class="card-action">

                                </div>
                            </div>
                            <div class="card-body">
                                <div class="tab-content" id="myTabContent">
                                    <div class="tab-pane fade show active" id="user" role="tabpanel">
                                        <canvas id="grafik-laba-jual" class="chartjs"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="col-xl-6 col-xxl-6 col-lg-6 col-md-6">
                        <div id="user-activity" class="card">
                            <div class="card-header border-0 pb-0 d-sm-flex d-block">
                                <div>
                                    <h4 class="card-title">Grafik Penjualan Tahun <?= date('Y') ?></h4>
                                    <p class="mb-1"></p>
                                </div>
                                <div class="card-action">

                                </div>
                            </div>
                            <div class="card-body">
                                <div class="tab-content" id="myTabContent">
                                    <div class="tab-pane fade show active" id="user" role="tabpanel">
                                        <canvas id="grafik-penjualan" class="chartjs"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--**********************************
            Content body end
        ***********************************-->


        <?php view("templates/footer") ?>

    </div>
    <!--**********************************
        Main wrapper end
    ***********************************-->

    <!--**********************************
        Scripts
    ***********************************-->
    <!-- Required vendors -->
    <?php view("templates/script") ?>

    <script>
        $(document).ready(function() {
            var labaJualLabels = <?= json_encode($laba_jual_labels) ?>;
            var labaJualDatasets = [{
                    label: "Tahun <?= date('Y') ?>",
                    data: <?= json_encode($laba_jual_tahun_ini) ?>,
                    borderColor: 'rgba(26, 51, 213, 1)',
                    borderWidth: "0",
                    backgroundColor: 'rgba(58, 122, 254, 0.5)'
                },
                {
                    label: "Tahun <?= date('Y') - 1 ?>",
                    data: <?= json_encode($laba_jual_tahun_lalu) ?>,
                    borderColor: '#4CAF50',
                    borderWidth: "0",
                    backgroundColor: 'rgba(76,175,80 ,0.5)'

                },
            ];
            generateGrafik('line', 'grafik-laba-jual', 300, labaJualLabels, labaJualDatasets);

            var penjualanLabels = <?= json_encode($penjualan_labels) ?>;
            var penjualanDatasets = [{
                    label: "Tahun <?= date('Y') ?>",
                    data: <?= json_encode($penjualan_tahun_ini) ?>,
                    borderColor: 'rgba(26, 51, 213, 1)',
                    borderWidth: "0",
                    backgroundColor: 'rgba(58, 122, 254, 0.5)'
                },
                {
                    label: "Tahun <?= date('Y') - 1 ?>",
                    data: <?= json_encode($penjualan_tahun_lalu) ?>,
                    borderColor: '#4CAF50',
                    borderWidth: "0",
                    backgroundColor: 'rgba(76,175,80 ,0.5)'

                },
            ];
            generateGrafik('line', 'grafik-penjualan', 300, penjualanLabels, penjualanDatasets);
        });
    </script>

</body>

</html>