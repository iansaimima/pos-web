<?php
defined('BASEPATH') or exit('No direct script access allowed');

$user = get_session("user");
$privilege_list = isset($user["privilege_list"]) ? $user["privilege_list"] : array();
$allow_laporan_item_transfer = isset($privilege_list["allow_laporan_item_transfer"]) ? $privilege_list["allow_laporan_item_transfer"] : 0;

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php view("templates/meta") ?>
    <?php view("templates/style") ?>
    <!-- Favicon icon -->

    <link href="<?= base_url('assets') ?>/vendor/jqvmap/css/jqvmap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets') ?>/vendor/chartist/css/chartist.min.css">

    <link href="https://cdn.lineicons.com/2.0/LineIcons.css" rel="stylesheet">
    <!-- Material color picker -->
    <link href="<?= base_url('assets') ?>/vendor/bootstrap-material-datetimepicker/css/bootstrap-material-datetimepicker.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
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

                <div class="row">
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-header border-0 ">
                                <h1 class="card-title">Filter</h1>
                            </div>
                            <div class="card-body pt-0" style="margin: 0">
                                <form onsubmit="return false" class="form-horizontal" id="form-item-transfer-report">
                                    <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">

                                    <div class="form-group row">
                                        <label class="col-sm-12">Tanggal Mulai</label>
                                        <div class="col-sm-12">
                                            <input type="text" name="start_date" class="form-control material-datepicker" id="tanggal-mulai" placeholder="dd-mm-yyyy" value="<?= date("01-m-Y") ?>">
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-sm-12">Tanggal Selesai</label>
                                        <div class="col-sm-12">
                                            <input type="text" name="end_date" class="form-control material-datepicker" id="tanggal-selesai" placeholder="dd-mm-yyyy" value="<?= date("t-m-Y") ?>">
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-sm-12">Keluar dari</label>
                                        <div class="col-sm-12">
                                            <select name="dari_gudang_uuid" id="dari-gudang-uuid" class="form-control select2">
                                                <option value="" disabled selected>Pilih Gudang</option>
                                                <?php
                                                if (isset($gudang_list) && is_array($gudang_list)) {
                                                    foreach ($gudang_list as $l) {
                                                ?>
                                                        <option value="<?= $l['uuid'] ?>"><?= $l['kode'] ?> - <?= $l['nama'] ?></option>
                                                <?php
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-sm-12">Masuk Ke</label>
                                        <div class="col-sm-12">
                                            <select name="ke_gudang_uuid" id="ke-gudang-uuid" class="form-control select2">
                                                <option value="" disabled selected>Pilih Gudang</option>
                                                <?php
                                                if (isset($gudang_list) && is_array($gudang_list)) {
                                                    foreach ($gudang_list as $l) {
                                                ?>
                                                        <option value="<?= $l['uuid'] ?>"><?= $l['kode'] ?> - <?= $l['nama'] ?></option>
                                                <?php
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>

                                    <button type="button" onclick="showReport()" class="btn btn-primary btn-block btn-sm"><i class="fa fa-search"></i> Tampilkan Laporan</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-9">
                        <div class="card">
                            <div class="card-header border-0 pb-1">
                                <h5 class="card-title"><span id="judul-laporan">Laporan Item Transfer</span></h5>
                            </div>
                            <div class="card-body p-3 pt-3" style="margin: 0">
                                <iframe class="iframe-rounded" id="frame-item-transfer-report" src="about:blank" frameborder="0" style="width: 100%; height: calc(100vh - 210px)"></iframe>
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

    <!-- momment js is must -->
    <script src="<?= base_url('assets') ?>/vendor/moment/moment.min.js"></script>
    <!-- Material date picker -->
    <script src="<?= base_url('assets') ?>/vendor/bootstrap-material-datetimepicker/js/bootstrap-material-datetimepicker.js"></script>
    <script>
        // $("#loader").show();
        $(document).ready(function() {

            $(".select2").select2({
                theme: 'classic'
            });

            $("#frame-pelanggan").on("load", function() {
                $("#loader").hide();
            });
        });

        function setJudulLaporan(title) {
            if (title == "summary") $("#judul-laporan").html("Laporan Rekap Penjualan");
            if (title == "detail") $("#judul-laporan").html("Laporan Detail Penjualan");
        }

        function showReport() {
            var tanggalMulai = $("#tanggal-mulai").val();
            var tanggalSelesai = $("#tanggal-selesai").val();
            var dariGudangUuid = $("#dari-gudang-uuid").val();
            var keGudangUuid = $("#ke-gudang-uuid").val();

            var exploded = tanggalMulai.split("-");
            tanggalMulai = exploded[2] + "-" + exploded[1] + "-" + exploded[0];
            var exploded = tanggalSelesai.split("-");
            tanggalSelesai = exploded[2] + "-" + exploded[1] + "-" + exploded[0];
            
            if(dariGudangUuid == null || dariGudangUuid.length == 0) {
                alert("Gudang sumber (Keluar dari) harus dipilih");
                return;
            }
            if(keGudangUuid == null || keGudangUuid.length == 0) {
                alert("Gudang tujuan (Masuk ke) harus dipilih");
                return;
            }

            if(dariGudangUuid == keGudangUuid) {
                alert("Gudang sumber (Keluar dari) dan gudang tujuan (Masuk ke) tidak boleh sama");
                return;
            }

            var params = [];
            params.push("start_date=" + tanggalMulai);
            params.push("end_date=" + tanggalSelesai);
            params.push("dari_gudang_uuid=" + dariGudangUuid);
            params.push("ke_gudang_uuid=" + keGudangUuid);

            // tampilkan progress bar
            $("#loader").show();

            // load report
            $("#frame-item-transfer-report").attr('src', "<?= base_url("admin/laporan/item_transfer/view") ?>?" + params.join("&"));

            // hilangkan progress bar ketika laporan telah selesai di-load
            $("#frame-item-transfer-report").on("load", function() {
                $("#loader").hide();
            });
        }
    </script>
</body>

</html>