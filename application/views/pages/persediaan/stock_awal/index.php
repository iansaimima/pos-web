<?php
defined('BASEPATH') or exit('No direct script access allowed');

$user = get_session("user");
$privilege_list = isset($user["privilege_list"]) ? $user["privilege_list"] : array();
$allow_create = isset($privilege_list["allow_stock_awal_create"]) ? $privilege_list["allow_stock_awal_create"] : 0;
$allow_update = isset($privilege_list["allow_stock_awal_update"]) ? $privilege_list["allow_stock_awal_update"] : 0;
$allow_delete = isset($privilege_list["allow_stock_awal_delete"]) ? $privilege_list["allow_stock_awal_delete"] : 0;

$uri_1 = $this->uri->segment(1);
$cabang_selected = get_session("cabang_selected");
$gudang_uuid = $cabang_selected["persediaan_stock_awal_default_gudang_uuid"];
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

                <section class="content" id="content-stock-awal-list">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header border-0 pb-0">
                                    <h5 class="card-title">
                                        Daftar Stock Awal
                                        Per Tanggal <?= date("d-m-Y", strtotime($tanggal_mulai_penggunaan_aplikasi)) ?>
                                    </h5>
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
                                                    <th>Kode</th>
                                                    <th>Nama</th>
                                                    <th>Kategori</th>
                                                    <th>Jumlah</th>
                                                    <th>Satuan</th>
                                                    <th>Harga Beli</th>
                                                    <th>Total</th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div><!-- table-responsive -->
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="content" id="content-stock-awal-detail" style="display: none">

                </section>
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
        var table;
        $(document).ready(function() {

            initTable();

            $(".select2").select2({
                // dropdownparent: $("#modal-detail"),
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
                console.log(i);
                console.log(v);
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

        function load_detail(uuid) {
            ajax_get(
                '<?= base_url("admin/stock_awal/ajax_detail") ?>/' + uuid, {},
                function(resp) {
                    $("#content-stock-awal-detail").html(resp);
                    $("#content-stock-awal-detail").show();
                    $("#content-stock-awal-list").hide();
                }
            );
        }

        function initTable() {
            var gudangUuid = $("#select-gudang").val();

            if (!$.fn.DataTable.isDataTable("#datatable")) {
                table = $("#datatable").DataTable({

                    "processing": true, //Feature control the processing indicator.
                    "serverSide": true, //Feature control DataTables' server-side processing mode. 
                    "autoWidth": false,
                    "oLanguage": {
                        "sSearch": "Cari",
                        "sLengthMenu": "Tampilkan _MENU_ baris",
                        "sInfo": "Menampilkan _START_ s/d _END_ dari _TOTAL_ baris",
                    },

                    // Load data for the table's content from an Ajax source
                    "ajax": {
                        "url": "<?php echo site_url($uri_1 . '/stock_awal/ajax_list') ?>/" + gudangUuid,
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
                        [2, "asc"]
                    ],
                    "aLengthMenu": [
                        [10, 25, 50, 100, 250, 500, -1],
                        [10, 25, 50, 100, 250, 500, "All"]
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

                                html.push("<a href='javascript:void(0);' class='text-primary' style='margin-bottom:2px;margin-right:2px' data-toggle='tooltip' title='Edit' onclick=\"load_detail('" + data.uuid + "')\"> <i class='fa fa-edit'></i> &nbsp; " + data.item_kode + "</a>");

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
                            "data": "item_nama"
                        }, // col-2
                        {
                            "data": "item_kategori_nama"
                        }, // col-3
                        {
                            "data": "jumlah"
                        }, // col-4
                        {
                            "data": "satuan"
                        }, // col-5
                        {
                            "data": "harga_beli_satuan"
                        }, // col-6
                        {
                            "data": "total"
                        }, // col-7
                    ]
                });
            } else {
                table.ajax.url("<?= base_url($uri_1 . '/stock_awal/ajax_list') ?>/" + gudangUuid).draw('page');
            }
        }

        function load_list() {
            initTable();
            $("#content-stock-awal-detail").hide();
            $("#content-stock-awal-list").show();
        }

        function confirm_delete(uuid) {
            var confirmed = confirm("Anda yakin ingin menghapus data ini ?");
            if (!confirmed) return;

            ajax_get(
                '<?= base_url("admin/stock_awal/ajax_delete") ?>/' + uuid, {},
                function(resp) {
                    try {
                        var json = JSON.parse(resp);
                        if (json.is_success == 1) {
                            show_toast("Success", json.message, "success");
                            load_list();
                        } else {
                            show_toast("Error", json.message, "error");
                        }
                    } catch (error) {
                        show_toast("Error", "Application response error", "error");
                    }
                }
            );
        }
    </script>
</body>

</html>