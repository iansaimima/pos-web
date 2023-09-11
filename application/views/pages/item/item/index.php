<?php
defined('BASEPATH') or exit('No direct script access allowed');

$user = get_session("user");
$privilege_list = isset($user["privilege_list"]) ? $user["privilege_list"] : array();
$allow_create = isset($privilege_list["allow_item_create"]) ? $privilege_list["allow_item_create"] : 0;
$allow_update = isset($privilege_list["allow_item_update"]) ? $privilege_list["allow_item_update"] : 0;
$allow_delete = isset($privilege_list["allow_item_delete"]) ? $privilege_list["allow_item_delete"] : 0;

$uri_1 = $this->uri->segment(1);

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
                    <div class="col-xl-12 col-xxl-12">
                        <section id="content-item-list">
                            <div class="card">
                                <div class="card-header border-0 pb-0">
                                    <h5 class="card-title">
                                        Daftar Item
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
                                    <div class="">
                                        <div class="row">
                                            <div class="col-sm-2">
                                                <div class="input-group input-group-sm mb-1">
                                                    <select name="gudang_uuid" id="select-gudang" class="form-control select2" onchange="initTable()">                                                        
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
                                            <div class="col-sm-2">
                                                <div class="input-group input-group-sm mb-1">
                                                    <select name="item_kategori_uuid" id="select-item-kategori" class="form-control select2" onchange="initTable()">
                                                        <option value="-" selected>Semua Kategori</option>
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
                                            <div class="col-sm-2">
                                                <div class="input-group input-group-sm mb-1">
                                                    <select id="select-item-status" class="form-control input-sm select2" style="width: 100px; display: inline;" onchange="initTable()">
                                                        <option value="-1">Semua Status</option>
                                                        <option value="0" selected>Aktif</option>
                                                        <option value="1">Arsip</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="table-responsive mt-2">
                                        <table id="datatable" class="table table-striped table-dashboard-two mg-b-0">
                                            <thead>
                                                <tr>
                                                    <th>Kode</th>
                                                    <th>Barcode</th>
                                                    <th>Nama</th>
                                                    <th>Stock</th>
                                                    <th>Satuan</th>
                                                    <th>Kategori</th>
                                                    <th>Harga Pokok</th>
                                                    <th>Harga Jual</th>
                                                    <th>Tipe</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div><!-- table-responsive -->
                                </div>
                            </div>
                        </section>

                        <section class="content" id="content-item-detail" style="display: none">

                        </section>
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

    <style>
        .table>thead>tr>th,
        .table>tbody>tr>td {
            font-size: 10pt !important;
        }
    </style>

    <style>
    </style>

    <script>
        var table;
        $(document).ready(function() {
            $(".select2").select2({
                theme: 'classic'
            });
            initTable("-1");

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
                '<?= base_url($uri_1 . "/item/ajax_detail") ?>/' + uuid, {},
                function(resp) {
                    $("#content-item-detail").html(resp);
                    $("#content-item-detail").show();
                    $("#content-item-list").hide();
                }
            );
        }

        function initTable(arsip) {
            var arsip = $("#select-item-status").val();
            var gudangUuid = $("#select-gudang").val();
            var itemKategoriUuid = $("#select-item-kategori").val();

            if (!$.fn.DataTable.isDataTable("#datatable")) {
                table = $("#datatable").DataTable({
                    "dom": '<f<t>p>',
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
                        "url": "<?php echo site_url($uri_1 . '/item/ajax_list') ?>/" + arsip + "/" + gudangUuid + "/" + itemKategoriUuid,
                        beforeSend: function(xhr) {
                            $("#loader").show();
                        },
                        complete: function(jqXHR, textStatus) {
                            $("#loader").hide();
                        },
                        statusCode: status_code,
                    },

                    "order": [
                        [1, "asc"]
                    ],
                    "aLengthMenu": [
                        [25, 50, 100, 250, 500, -1],
                        [25, 50, 100, 250, 500, "All"]
                    ],

                    //Set column definition initialisation properties.
                    "columnDefs": [{
                            "targets": 0, // column-1
                            "data": null,
                            render: function(data, type, row) {
                                var html = [];

                                html.push("<a href='javascript:void(0);' class='text-primary' style='margin-bottom:2px;margin-right:2px' data-toggle='tooltip' title='Edit' onclick=\"load_detail('" + data.uuid + "')\"> <i class='fa fa-edit'></i>&nbsp; " + data.kode + "</a>");

                                return html.join("");
                            }
                        },
                        {
                            "targets": 9, // column-8
                            "orderable": true,
                            "data": null,
                            render: function(data, type, row) {
                                var html = [];
                                if (parseInt(data.arsip) == 1) {
                                    return "<span class='badge badge-danger'>Arsip</span>";
                                } else {
                                    return "<span class='badge badge-success'>Aktif</span>";
                                }
                            }
                        },
                    ],
                    "columns": [{
                            "data": null
                        }, // col-0
                        {
                            "data": "barcode"
                        }, // col-1
                        {
                            "data": "nama"
                        }, // col-2
                        {
                            "data": "stock",
                            className: "text-right"
                        }, // col-3
                        {
                            "data": "satuan"
                        }, // col-4
                        {
                            "data": "item_kategori_nama"
                        }, // col-5
                        {
                            "data": "harga_pokok",
                            className: "text-right"
                        }, // col-6
                        {
                            "data": "harga_jual",
                            className: "text-right"
                        }, // col-7
                        {
                            "data": "tipe"
                        }, // col-8
                        {
                            "data": null
                        }, // col-9
                    ]
                });
            } else {
                table.ajax.url("<?= base_url($uri_1 . '/item/ajax_list') ?>/" + arsip + "/" + gudangUuid + "/" + itemKategoriUuid).draw('page');
            }
        }

        function load_list() {
            initTable($("#select-item-status").val());
            $("#content-item-detail").hide();
            $("#content-item-list").show();
        }

        function confirm_delete(uuid) {
            var confirmed = confirm("Anda yakin ingin menghapus data ini ?");
            if (!confirmed) return;

            ajax_get(
                '<?= base_url($uri_1 . "/item/ajax_delete") ?>/' + uuid, {},
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