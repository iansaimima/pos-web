<?php
defined('BASEPATH') or exit('No direct script access allowed');

$user = get_session("user");
$privilege_list = isset($user["privilege_list"]) ? $user["privilege_list"] : array();
$allow_create = isset($privilege_list["allow_transaksi_penjualan_retur_create"]) ? $privilege_list["allow_transaksi_penjualan_retur_create"] : 0;
$allow_update = isset($privilege_list["allow_transaksi_penjualan_retur_update"]) ? $privilege_list["allow_transaksi_penjualan_retur_update"] : 0;
$allow_delete = isset($privilege_list["allow_transaksi_penjualan_retur_delete"]) ? $privilege_list["allow_transaksi_penjualan_retur_delete"] : 0;
?>
<!DOCTYPE html>
<html>

<head>
    <?php view("templates/meta"); ?>
    <?php view("templates/style"); ?>
    <link href="<?= base_url("assets/plugins/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css") ?>" rel="stylesheet">
    <link href="<?= base_url("assets/plugins/bootstrap-daterangepicker/daterangepicker.css") ?>" rel="stylesheet">
</head>

<body class="hold-transition sidebar-mini">
    <div class="wrapper">

        <?php view("templates/header"); ?>
        <?php view("templates/sidebar"); ?>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">

            <!-- Main content -->
            <section class="content">
                <?php view('templates/content_header') ?>
                <div class="row">
                    <div class="col-md-12">
                        <div class="box box-primary">
                            <div class="box-header with-border">
                                <h1 class="box-title">Daftar Retur Penjualan</h1>
                                <div class="box-tools">
                                    <?php
                                    // jika boleh create
                                    if ($allow_create) {
                                    ?>
                                        <a href="javascript:void(0);" class="btn btn-sm btn-primary" onclick="load_detail('')"><i class="fa fa-plus"></i> Tambah</a>
                                    <?php } ?>
                                </div>
                            </div>
                            <div class="box-body">
                                <div class="table-responsive mt-3">
                                    <table id="datatable" class="table table-striped table-dashboard-two mg-b-0">
                                        <thead>
                                            <tr>
                                                <th style="width: 40px">#</th>
                                                <th>No.</th>
                                                <th>Tanggal</th>
                                                <th>Kode Pel.</th>
                                                <th>Nama</th>
                                                <th class="text-right">Total</th>
                                                <!-- <th class="text-right">Sisa</th>
                                                <th>Status</th> -->
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
            <!-- /.content -->
        </div>
        <!-- /.content-wrapper -->

        <div class="modal fade" id="modal-detail" style=" background-color: #ecf0f5" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog" style="width: 100%;margin: 0;">
                <div class="modal-content" id="modal-detail-content" style="background-color: #ecf0f5;box-shadow: none !important; ">

                </div>
            </div><!-- modal-dialog -->
        </div><!-- modal -->

        <?php view("templates/footer") ?>
    </div>
    <!-- ./wrapper -->
    <?php view("templates/script") ?>
    <script src="<?= base_url("assets/plugins/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js") ?>"></script>
    <script src="<?= base_url("assets/plugins/bootstrap-daterangepicker/daterangepicker.js") ?>"></script>

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
                    "url": "<?php echo site_url('admin/penjualan_retur/ajax_list') ?>",
                    "type": "GET",
                    beforeSend: function(xhr) {
                        $("#loader").show();
                    },
                    complete: function(jqXHR, textStatus) {
                        $("#loader").hide();
                    },
                    statusCode: status_code,
                },

                "order": [[1, "desc"]],
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
                                html.push("<a href='javascript:void(0);' style='margin-bottom:2px;margin-right:2px' data-toggle='tooltip' title='Edit' onclick=\"load_detail('" + data.uuid + "')\"> " + data.number_formatted + "</a>");
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
                        "data": "pelanggan_number_formatted"
                    }, // col-3
                    {
                        "data": "pelanggan_nama"
                    }, // col-4
                    {
                        "data": "total_akhir",
                        className: 'text-right'
                    }, // col-5
                    // {
                    //     "data": "sisa",
                    //     className: 'text-right'
                    // }, // col-6
                    // {
                    //     "data": "status"
                    // }, // col-7
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

        function load_detail(uuid) {
            ajax_get(
                '<?= base_url("admin/penjualan_retur/ajax_detail") ?>/' + uuid, {},
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