<?php
defined('BASEPATH') or exit('No direct script access allowed');

$user = get_session("user");
$privilege_list = isset($user["privilege_list"]) ? $user["privilege_list"] : array();
$allow_laporan_penjualan_rekap = isset($privilege_list["allow_laporan_penjualan_rekap"]) ? $privilege_list["allow_laporan_penjualan_rekap"] : 0;

?>
<!DOCTYPE html>
<html>

<head>
    <?php view("templates/meta"); ?>
    <?php view("templates/style"); ?>
    <link href="<?= base_url("assets/plugins/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css") ?>" rel="stylesheet">
</head>

<body class="hold-transition sidebar-mini">
    <div class="wrapper">

        <?php view("templates/header"); ?>
        <?php view("templates/sidebar"); ?>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <!-- <section class="content-header">
                <h1>
                    Laporan Pelanggan
                    <small></small>
                </h1>
            </section> -->

            <!-- Main content -->
            <section class="content" id="content-pelanggan-list">
                <div class="row">
                    <div class="col-md-2">
                        <div class="box box-primary">
                            <div class="box-header with-border">
                                <h1 class="box-title">Filter</h1>
                            </div>
                            <div class="box-body" style="margin: 0">
                                <form onsubmit="return false" class="form-horizontal" id="form-penjualan-rekap-report">  
                                    <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">

                                    <div class="form-group row mb-2">
                                        <label class="col-sm-12">Tanggal Mulai</label>
                                        <div class="col-sm-12">
                                            <div class="input-group">
                                                <input autocomplete="off" type="text" name="start_date" class="form-control" id="tanggal-mulai" placeholder="dd-mm-yyyy" value="<?= date("01-m-Y") ?>" />
                                                <span class="input-group-addon bg-gray" id="span-tanggal-mulai"><i class="fa fa-calendar"></i></span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group row mb-2">
                                        <label class="col-sm-12">Tanggal Selesai</label>
                                        <div class="col-sm-12">
                                            <div class="input-group" id="input-group-tanggal-selesai">
                                                <input autocomplete="off" type="text" name="end_date" class="form-control" id="tanggal-selesai" placeholder="dd-mm-yyyy" value="<?= date("t-m-Y") ?>"/>
                                                <span class="input-group-addon bg-gray" id="span-tanggal-selesai"><i class="fa fa-calendar"></i></span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group row mb-2">
                                        <label class="col-sm-12">Pelanggan</label>
                                        <div class="col-sm-12">
                                            <select name="pelanggan_uuid" id="pelanggan-uuid" class="form-control select2">
                                                <option value="" selected>Semua Pelanggan</option>
                                                <?php
                                                if(isset($pelanggan_list) && is_array($pelanggan_list)) {
                                                    foreach($pelanggan_list as $l) {
                                                ?>
                                                <option value="<?= $l['uuid'] ?>"><?= $l['number_formatted'] ?> <?= $l['nama'] ?></option>                                        
                                                <?php
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="box-footer">
                                <button type="button" onclick="showReport()" class="btn btn-primary"><i class="fa fa-search"></i> Tampilkan Laporan</button>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-10">
                        <div class="box box-primary">
                            <div class="box-header with-border">
                            <h1 class="box-title"><span id="judul-laporan">Laporan Penjualan Rekap</span></h1>
                            </div>
                            <div class="box-body" style="margin: 0">
                                <iframe id="frame-penjualan-rekap-report" src="about:blank" frameborder="0" style="width: 100%; height: calc(100vh - 210px)"></iframe>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <!-- /.content -->

        </div>
        <!-- /.content-wrapper -->

        <?php view("templates/footer") ?>
    </div>
    <!-- ./wrapper -->
    <?php view("templates/script") ?>
    <script src="<?= base_url("assets/plugins/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js") ?>"></script>
    <script>
        // $("#loader").show();
        $(document).ready(function() {
            $("#frame-pelanggan").on("load", function() {
                $("#loader").hide();
            });

            $(".select2").select2({
                theme: 'classic'
            });


            $('#tanggal-mulai, #tanggal-selesai').datepicker({
                autoclose: true,
                todayHighlight: true,
                format: 'dd-mm-yyyy'
            });

            $("#span-tanggal-selesai").on("click", function(){
                $("#tanggal-selesai").datepicker("show");
            });
            $("#span-tanggal-mulai").on("click", function(){
                $("#tanggal-mulai").datepicker("show");
            });
        });

        function setJudulLaporan(title){
            if(title == "summary") $("#judul-laporan").html("Laporan Rekap Penjualan");
            if(title == "detail") $("#judul-laporan").html("Laporan Detail Penjualan");
        }

        function showReport(){
            var tanggalMulai = $("#tanggal-mulai").val();
            var tanggalSelesai = $("#tanggal-selesai").val();
            var pelangganUuid = $("#pelanggan-uuid").val();

            if(pelangganUuid == null) {
                pelangganUuid = "";
            }

            var exploded = tanggalMulai.split("-");
            tanggalMulai = exploded[2] + "-" + exploded[1] + "-" + exploded[0];
            var exploded = tanggalSelesai.split("-");
            tanggalSelesai = exploded[2] + "-" + exploded[1] + "-" + exploded[0];
            
            var params = [];
            params.push("pelanggan_uuid=" + pelangganUuid);
            params.push("start_date=" + tanggalMulai);
            params.push("end_date=" + tanggalSelesai);

            // tampilkan progress bar
            $("#loader").show();

            // load report
            $("#frame-penjualan-rekap-report").attr('src',"<?= base_url("admin/laporan/penjualan/rekap_view") ?>?" + params.join("&"));
            
            // hilangkan progress bar ketika laporan telah selesai di-load
            $("#frame-penjualan-rekap-report").on("load", function() {
                $("#loader").hide();
            });
        }
    </script>
</body>

</html>