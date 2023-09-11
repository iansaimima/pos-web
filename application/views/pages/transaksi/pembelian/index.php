<?php
defined('BASEPATH') or exit('No direct script access allowed');

$user = get_session("user");
$privilege_list = isset($user["privilege_list"]) ? $user["privilege_list"] : array();
$allow_create = isset($privilege_list["allow_transaksi_pembelian_create"]) ? $privilege_list["allow_transaksi_pembelian_create"] : 0;
$allow_update = isset($privilege_list["allow_transaksi_pembelian_update"]) ? $privilege_list["allow_transaksi_pembelian_update"] : 0;
$allow_delete = isset($privilege_list["allow_transaksi_pembelian_delete"]) ? $privilege_list["allow_transaksi_pembelian_delete"] : 0;

$uri_1 = $this->uri->segment(1);

$cabang_selected = get_session("cabang_selected");
$gudang_uuid = $cabang_selected["transaksi_pembelian_default_gudang_uuid"];
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
                <?php view('templates/content_header') ?>

                <div class="row">
                    <div class="col-xl-12 col-xxl-12">
                        <div class="card">
                            <div class="card-header border-0 pb-0">
                                <h5 class="card-title">Daftar Pembelian</h5>
                                <div class="card-tools">
                                    <?php
                                    // jika boleh create
                                    if ($allow_create) {
                                    ?>
                                        <a href="javascript:void(0);" class="btn btn-sm btn-primary" onclick="load_detail('')"><i class="fa fa-plus"></i> Tambah</a>
                                    <?php } ?>
                                </div>
                            </div>
                            <div class="card-body pt-2">
                                <div class="row">
                                    <label class="col-sm-1 col-form-label">Gudang</label>
                                    <div class="col-sm-3">
                                        <select name="gudang_uuid" id="select-gudang" class="form-control select2" onchange="initTable()">
                                            <option value="" selected disabled>Pilih Gudang</option>
                                            <?php
                                            if (isset($gudang_list) && is_array($gudang_list)) {
                                                foreach ($gudang_list as $l) {
                                                    $selected = "";
                                                    $selected = $l["uuid"] == $gudang_uuid ? "selected" : "";
                                            ?>
                                                    <option value="<?= $l['uuid'] ?>" <?= $selected ?>>[<?= $l["kode"] ?>] <?= $l['nama'] ?></option>
                                            <?php

                                                    $selected = "";
                                                }
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="table-responsive mt-3">
                                    <table id="datatable" class="table table-striped table-dashboard-two mg-b-0">
                                        <thead>
                                            <tr>
                                                <th style="width: 40px">#</th>
                                                <th style="width: 160px">No.</th>
                                                <th>Tanggal</th>
                                                <th>Kode Pemasok</th>
                                                <th>Nama</th>
                                                <th class="text-right">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div><!-- table-responsive -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--**********************************
            Content body end
        ***********************************-->

        <div class="modal fade" id="modal-detail" style="padding: 0 !important; background-color: #ececec;">
            <div class="modal-dialog modal-dialog-centered " style="width: 100%;margin: 0; max-width: none; height: 100%" role="document">
                <div class="modal-content" id="modal-detail-content" style="box-shadow: none !important; height: 100%; border-radius: 0; border: none; background-color: #ececec;">

                </div>
            </div><!-- modal-dialog -->
        </div><!-- modal -->


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
        var table;
        $(document).ready(function() {

            initTable();

            $(".select2").select2({
                dropdownparent: $("#modal-detail"),
                theme: 'classic',
            });



            // column search
            $('.search-input-text').unbind();
            $('.search-input-text').bind('keyup', function(e) {
                var i = $(this).attr('data-column'); // getting column index
                var v = $(this).val(); // getting search input value
                if (e.keyCode == 13 || v == "") {
                    table.columns(i).search(v).draw();
                }
            });

            $('.search-input-select').on('change', function() {
                var i = $(this).attr('data-column'); // getting column index
                var v = $(this).val(); // getting search input value
                table.columns(i).search(v).draw();
            });

            $("#datatable_filter input").unbind();
            $("#datatable_filter input").bind("keyup", function(e) {
                var v = this.value;
                if (e.keyCode == 13 || v == "") {
                    table.search(v).draw();
                }
            });

            $('[data-toggle="tooltip"]').tooltip();
        });

        function initTable() {
            var gudangUuid = $("#select-gudang").val();

            if (!$.fn.DataTable.isDataTable("#datatable")) {
                table = $("#datatable").DataTable({

                    "processing": true, //Feature control the processing indicator.
                    "serverSide": true, //Feature control DataTables' server-side processing mode. 
                    "autoWidth": true,
                    "oLanguage": {
                        "sSearch": "Cari",
                        "sLengthMenu": "Tampilkan _MENU_ baris",
                        "sInfo": "Menampilkan _START_ s/d _END_ dari _TOTAL_ baris",
                    },

                    // Load data for the table's content from an Ajax source
                    "ajax": {
                        "url": "<?php echo site_url($uri_1 . '/pembelian/ajax_list') ?>/" + gudangUuid,
                        "type": "GET",
                        beforeSend: function(xhr) {
                            $("#loader").show();
                        },
                        complete: function(jqXHR, textStatus) {
                            $("#loader").hide();
                        },
                        statusCode: status_code,
                    },

                    "order": [
                        [1, "desc"]
                    ],
                    "aLengthMenu": [
                        [50, 100, 250, 500, -1],
                        [50, 100, 250, 500, "All"]
                    ],

                    //Set column definition initialisation properties.
                    "columnDefs": [{
                            "targets": 0, // column-1
                            "orderable": false, //set not orderable,
                            "responsivePriority": 1,
                        },
                        {
                            "targets": 1, // column-2
                            "data": null,
                            render: function(data, type, row) {
                                var html = [];

                                <?php if ($allow_update || $allow_delete) { ?>
                                    html.push("<a href='javascript:void(0);' class='text-primary' style='margin-bottom:2px;margin-right:2px' data-toggle='tooltip' title='Edit' onclick=\"load_detail('" + data.uuid + "')\"> " + data.number_formatted + "</a>");
                                <?php } ?>

                                return html.join("");
                            }
                        },
                    ],
                    "columns": [{
                            "data": "no"
                        }, // col-0                  
                        {
                            "data": null
                        }, // col-1
                        {
                            "data": "tanggal"
                        }, // col-2
                        {
                            "data": "pemasok_number_formatted"
                        }, // col-3
                        {
                            "data": "pemasok_nama"
                        }, // col-4
                        {
                            "data": "total_akhir",
                            className: 'text-right'
                        }, // col-5
                    ]
                });
            } else {
                table.ajax.url("<?= base_url($uri_1 . '/pembelian/ajax_list') ?>/" + gudangUuid).draw('page');
            }
        }

        function load_detail(uuid) {
            ajax_get(
                '<?= base_url("admin/pembelian/ajax_detail") ?>/' + uuid, {},
                function(resp) {
                    $("#modal-detail-content").html(resp);
                    $("#modal-detail").modal("show");
                }
            );
        }

        function load_list() {
            initTable();
        }
    </script>
</body>

</html>