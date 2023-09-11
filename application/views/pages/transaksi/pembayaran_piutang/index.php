<?php
defined('BASEPATH') or exit('No direct script access allowed');

$user = get_session("user");
$privilege_list = isset($user["privilege_list"]) ? $user["privilege_list"] : array();
$allow_create = isset($privilege_list["allow_transaksi_pembayaran_piutang_create"]) ? $privilege_list["allow_transaksi_pembayaran_piutang_create"] : 0;
$allow_update = isset($privilege_list["allow_transaksi_pembayaran_piutang_update"]) ? $privilege_list["allow_transaksi_pembayaran_piutang_update"] : 0;
$allow_delete = isset($privilege_list["allow_transaksi_pembayaran_piutang_delete"]) ? $privilege_list["allow_transaksi_pembayaran_piutang_delete"] : 0;
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
                                <h1 class="card-title">Daftar Pembayaran Piutang</h1>
                                <div class="card-tools">
                                    <?php
                                    // jika boleh create
                                    if ($allow_create) {
                                    ?>
                                        <a href="javascript:void(0);" class="btn btn-sm btn-primary" onclick="load_detail('')"><i class="fa fa-plus"></i> Tambah</a>
                                    <?php } ?>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive mt-3">
                                    <table id="datatable" class="table table-striped table-dashboard-two mg-b-0">
                                        <thead>
                                            <tr>
                                                <th style="width: 40px">#</th>
                                                <th>No.</th>
                                                <th>Tanggal</th>
                                                <th>Cara Bayar</th>
                                                <th>Kode Pel.</th>
                                                <th>Nama</th>
                                                <th class="text-right">Total</th>
                                                <th>Keterangan</th>
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

        <div class="modal fade" id="modal-detail">
            <div class="modal-dialog modal-dialog-centered modal-lg" style="width: calc(100vw - 25%); max-width: none; margin: auto;">
                <div class="modal-content" id="modal-detail-content">

                </div>
            </div><!-- modal-dialog -->
        </div><!-- modal -->

        <div class="modal fade" id="modal-add-new">
            <div class="modal-dialog modal-dialog-centered modal-lg" style="width: calc(100vw - 25%); max-width: none; margin: auto;">
                <div class="modal-content" id="modal-add-new-content">

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
                    "url": "<?php echo site_url('admin/pembayaran_piutang/ajax_list') ?>",
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
                        "data": "cara_bayar"
                    }, // col-3
                    {
                        "data": "pelanggan_number_formatted"
                    }, // col-4
                    {
                        "data": "pelanggan_nama"
                    }, // col-5
                    {
                        "data": "jumlah",
                        className: 'text-right'
                    }, // col-6
                    {
                        "data": "keterangan"
                    }, // col-7
                ]
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

        function load_add_new(uuid) {
            ajax_get(
                '<?= base_url("admin/pembayaran_piutang/ajax_add_new") ?>/' + uuid, {},
                function(resp) {
                    $("#modal-add-new-content").html(resp);
                    $("#modal-add-new").modal("show");
                }
            );
        }

        function load_detail(uuid) {
            ajax_get(
                '<?= base_url("admin/pembayaran_piutang/ajax_detail") ?>/' + uuid, {},
                function(resp) {
                    $("#modal-detail-content").html(resp);
                    $("#modal-detail").modal("show");
                }
            );
        }

        function load_list() {
            table.draw();
        }
    </script>
</body>
</html>