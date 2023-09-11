<?php
defined('BASEPATH') or exit('No direct script access allowed');

$user = get_session("user");
$privilege_list = isset($user["privilege_list"]) ? $user["privilege_list"] : array();
$allow_laporan_pembelian_detail = isset($privilege_list["allow_laporan_pembelian_detail"]) ? $privilege_list["allow_laporan_pembelian_detail"] : 0;

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php view("templates/meta") ?>
    <?php view("templates/style") ?>
    <!-- Favicon icon -->

    <link href="<?= base_url('assets') ?>/vendor/jqvmap/css/jqvmap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets') ?>/vendor/chartist/css/chartist.min.css">
    <link href="<?= base_url('assets') ?>/vendor/bootstrap-material-datetimepicker/css/bootstrap-material-datetimepicker.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

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

                <div class="row">
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-header border-0 pb-0">
                                <h1 class="card-title">Filter</h1>
                            </div>
                            <div class="card-body pt-0" style="margin: 0">
                                <form onsubmit="return false" class="form-horizontal" id="form-pembelian-detail-report">
                                    <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">

                                    <div class="form-group row">
                                        <label class="col-sm-12">Tanggal Mulai</label>
                                        <div class="col-sm-12">
                                            <div class="input-group input-group-sm">
                                                <input type="text" name="start_date" class="form-control material-datepicker" id="tanggal-mulai" placeholder="dd-mm-yyyy" value="<?= date("01-m-Y") ?>">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-sm-12">Tanggal Selesai</label>
                                        <div class="col-sm-12">
                                            <div class="input-group input-group-sm">
                                                <input type="text" name="end_date" class="form-control material-datepicker" id="tanggal-selesai" placeholder="dd-mm-yyyy" value="<?= date("t-m-Y") ?>">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-sm-12">Pemasok</label>
                                        <div class="col-sm-12">
                                            <div class="input-group input-group-sm">
                                                <select name="pemasok_uuid" id="pemasok-uuid" class="form-control select2">
                                                    <option value="" selected>Semua Pemasok</option>
                                                    <?php
                                                    if (isset($pemasok_list) && is_array($pemasok_list)) {
                                                        foreach ($pemasok_list as $l) {
                                                    ?>
                                                            <option value="<?= $l['uuid'] ?>"><?= $l['number_formatted'] ?> <?= $l['nama'] ?></option>
                                                    <?php
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-sm-12">Gudang</label>
                                        <div class="col-sm-12">
                                            <select name="gudang_uuid" id="gudang-uuid" class="form-control select2">
                                                <option value="" selected>Semua Gudang</option>
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

                                    <button type="button" onclick="showReport()" class="btn btn-primary btn-sm btn-block"><i class="fa fa-search"></i> Tampilkan Laporan</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-9">
                        <div class="card">
                            <div class="card-header border-0 pb-1">
                                <h1 class="card-title"><span id="judul-laporan">Laporan Pembelian Detail</span></h1>
                            </div>
                            <div class="card-body p-3 pt-3" style="margin: 0">
                                <iframe class="iframe-rounded" id="frame-pembelian-detail-report" src="about:blank" frameborder="0" style="width: 100%; height: calc(100vh - 210px)"></iframe>
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

            $("#frame-pemasok").on("load", function() {
                $("#loader").hide();
            });
        });

        function setJudulLaporan(title) {
            if (title == "summary") $("#judul-laporan").html("Laporan detail pembelian");
            if (title == "detail") $("#judul-laporan").html("Laporan Detail pembelian");
        }

        function showReport() {
            var tanggalMulai = $("#tanggal-mulai").val();
            var tanggalSelesai = $("#tanggal-selesai").val();
            var pemasokUuid = $("#pemasok-uuid").val();
            var gudangUuid = $("#gudang-uuid").val();

            if (pemasokUuid == null) {
                pemasokUuid = "";
            }
            if (gudangUuid == null) {
                gudangUuid = "";
            }

            var exploded = tanggalMulai.split("-");
            tanggalMulai = exploded[2] + "-" + exploded[1] + "-" + exploded[0];
            var exploded = tanggalSelesai.split("-");
            tanggalSelesai = exploded[2] + "-" + exploded[1] + "-" + exploded[0];

            var params = [];
            params.push("gudang_uuid=" + gudangUuid);
            params.push("pemasok_uuid=" + pemasokUuid);
            params.push("start_date=" + tanggalMulai);
            params.push("end_date=" + tanggalSelesai);

            // tampilkan progress bar
            $("#loader").show();

            // load report
            $("#frame-pembelian-detail-report").attr('src', "<?= base_url("admin/laporan/pembelian/detail_view") ?>?" + params.join("&"));

            // hilangkan progress bar ketika laporan telah selesai di-load
            $("#frame-pembelian-detail-report").on("load", function() {
                $("#loader").hide();
            });
        }
    </script>
</body>

</html>