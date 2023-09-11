<?php
defined('BASEPATH') or exit('No direct script access allowed');


$user = get_session("user");
$privilege_list = isset($user["privilege_list"]) ? $user["privilege_list"] : array();
$allow_create = isset($privilege_list["allow_pemasok_create"]) ? $privilege_list["allow_pemasok_create"] : 0;
$allow_update = isset($privilege_list["allow_pemasok_update"]) ? $privilege_list["allow_pemasok_update"] : 0;
$allow_delete = isset($privilege_list["allow_pemasok_delete"]) ? $privilege_list["allow_pemasok_delete"] : 0;
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
                <?php view('templates/content_header') ?>

                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header border-0 pb-0">
                                <h1 class="card-title">Daftar Pemasok</h1>
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
                                                <th>Kode</th>
                                                <th>Nama</th>
                                                <th>Alamat</th>
                                                <th>No. Telp</th>
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
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <h5 class="modal-title"><span id="modal-pemasok-title"></span> Pemasok</h5>
                    </div>

                    <div class="modal-body" id="modal-detail-body">
                    </div>
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
                    "url": "<?php echo site_url('admin/pemasok/ajax_list') ?>",
                    "type": "GET",
                    beforeSend: function(xhr) {
                        $("#loader").show();
                    },
                    complete: function(jqXHR, textStatus) {
                        $("#loader").hide();
                    },
                    statusCode: status_code,
                },

                // "order": [[2, "desc"]],
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
                            html.push("<a href='javascript:void(0);' class='text-primary' style='margin-bottom:2px;margin-right:2px' data-toggle='tooltip' title='Edit' onclick=\"load_detail('" + data.uuid + "')\"> <i class='fa fa-edit'></i> &nbsp;" + data.number_formatted + "</a>");

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
                        "data": "nama"
                    }, // col-2
                    {
                        "data": "alamat"
                    }, // col-3
                    {
                        "data": "no_telepon"
                    }, // col-4
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
                '<?= base_url("admin/pemasok/ajax_detail") ?>/' + uuid, {},
                function(resp) {
                    $("#modal-detail-body").html(resp);
                    $("#modal-detail").modal("show");
                }
            );
        }

        function load_list() {
            table.draw();
        }

        function confirm_delete(uuid) {
            var confirmed = confirm("Anda yakin ingin menghapus data ini ?");
            if (!confirmed) return;

            ajax_get(
                '<?= base_url("admin/pemasok/ajax_delete") ?>/' + uuid, {},
                function(resp) {
                    try {
                        var json = JSON.parse(resp);
                        if (json.is_success == 1) {
                            show_toast("Success", json.message, "success");
                            $("#modal-detail").modal("hide");
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