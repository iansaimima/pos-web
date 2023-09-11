<?php
defined('BASEPATH') or exit('No direct script access allowed');

$user = get_session("user");
$privilege_list = isset($user["privilege_list"]) ? $user["privilege_list"] : array();
$allow_laporan_stock_item = isset($privilege_list["allow_laporan_stock_item"]) ? $privilege_list["allow_laporan_stock_item"] : 0;

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
                <div class="row page-titles mx-0 d-none">
                    <div class="col-sm-6 p-md-0">
                        <div class="welcome-text">
                            <h4>Laporan</h4>
                            <p class="mb-0">Item</p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12 col-xxl-12 col-xl-12 col-md-12">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="card">
                                    <div class="card-header border-0 pb-2">
                                        <h5 class="card-title">Filter</h5>
                                    </div>
                                    <div class="card-body pt-0" style="margin: 0">
                                        <form onsubmit="return false" class="form-horizontal" id="form-item-report">
                                            <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">

                                            <div class="form-group row">
                                                <label class="col-sm-12">Tipe</label>
                                                <div class="col-sm-12">
                                                    <div class="input-group input-group-sm">
                                                        <select name="tipe" id="tipe" class="form-control single-picker select2">
                                                            <option value="" >Semua Tipe</option>
                                                            <option value="Barang" >Barang</option>
                                                            <option value="Jasa" >Jasa</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label class="col-sm-12">Status</label>
                                                <div class="col-sm-12">
                                                    <div class="input-group input-group-sm">
                                                        <select name="status" id="status" class="form-control single-picker select2">
                                                            <option value="" >Semua Status</option>
                                                            <option value="0" >Aktif</option>
                                                            <option value="1" >Arsip</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label class="col-sm-12">Kategori</label>
                                                <div class="col-sm-12">
                                                    <select name="item_kategori_uuid" id="item-kategori-uuid" class="form-control select2">
                                                        <option value="" selected>Semua Kategori</option>
                                                        <?php
                                                        if (isset($item_kategori_list) && is_array($item_kategori_list)) {
                                                            foreach ($item_kategori_list as $l) {
                                                        ?>
                                                                <option value="<?= $l['uuid'] ?>"><?= $l['nama'] ?></option>
                                                        <?php
                                                            }
                                                        }
                                                        ?>
                                                    </select>
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

                                            <div class="form-group row">
                                                <div class="col-sm-12">
                                                    <div class="input-group input-group-sm">
                                                        <button type="button" onclick="showReport()" class="btn btn-success btn-sm btn-block"><i class="fa fa-search"></i> Tampilkan Laporan</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-9">
                                <div class="card">
                                    <div class="card-header border-0 pb-1">
                                        <h5 class="card-title"><span id="judul-laporan">Laporan Item</span></h5>
                                    </div>
                                    <div class="card-body p-3 pt-3" style="margin: 0">
                                        <iframe class="iframe-rounded" id="frame-item-report" src="about:blank" frameborder="0" style="width: 100%; height: calc(100vh - 210px)"></iframe>
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

            $("#span-tanggal-selesai").on("click", function() {
                $("#tanggal-selesai").datepicker("show");
            });
            $("#span-tanggal-mulai").on("click", function() {
                $("#tanggal-mulai").datepicker("show");
            });
        });

        function yearUp() {
            var curr = parseInt($("#periode-tahun").val());
            $("#periode-tahun").val(curr + 1);
        }

        function yearDown() {
            var curr = parseInt($("#periode-tahun").val());
            $("#periode-tahun").val(curr - 1);
        }

        function setJudulLaporan(title) {
            if (title == "summary") $("#judul-laporan").html("Laporan Rekap Penjualan");
            if (title == "detail") $("#judul-laporan").html("Laporan Detail Penjualan");
        }

        function showReport() {
            var status = $("#status").val();
            var tipe = $("#tipe").val();
            var gudangUuid = $("#gudang-uuid").val();
            var itemKategoriUuid = $("#item-kategori-uuid").val();

            var params = [];
            params.push("tipe=" + tipe);
            params.push("arsip=" + status);
            params.push("gudang_uuid=" + gudangUuid);
            params.push("item_kategori_uuid=" + itemKategoriUuid);

            // tampilkan progress bar
            $("#loader").show();

            // load report
            $("#frame-item-report").attr('src', "<?= base_url("admin/laporan/item/view") ?>?" + params.join("&"));

            // hilangkan progress bar ketika laporan telah selesai di-load
            $("#frame-item-report").on("load", function() {
                $("#loader").hide();
            });
        }

        // open a pop up window
        function popupSearchItem() {
            var targetField = document.getElementById("input-kode-item");
            var w = window.open('<?= base_url('admin/stock_opname/item_popup_list') ?>', '_blank', 'width=800,height=400,scrollbars=1');
            // pass the targetField to the pop up window
            w.targetField = targetField;
            w.focus();
        }

        // this function is called by the pop up window
        function setSearchResult(targetField, returnValue) {
            targetField.value = returnValue;
            getItemNamaByKode(returnValue);
            window.focus();
        }

        function getItemNamaByKode(itemKode) {
            if (itemKode == null || itemKode == undefined) return;
            ajax_get(
                '<?= base_url("admin/item/ajax_item_get_nama_by_kode") ?>/' + itemKode, {},
                function(nama) {
                    if (nama.toLowerCase() == "tidak ditemukan") {
                        show_toast("Tidak Ditemukan", "Kode item " + itemKode + " tidak ditemukan di database", "warning");
                        $("#input-nama").val("");
                        return;
                    }
                    $("#input-nama").val(nama);
                }
            );
        }
    </script>
</body>

</html>