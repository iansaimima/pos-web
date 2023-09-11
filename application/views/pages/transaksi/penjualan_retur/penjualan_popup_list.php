<!DOCTYPE html>
<html>

<head>
    <?php view("templates/meta"); ?>
    <?php view("templates/style"); ?>
</head>

<body class="hold-transition sidebar-mini">
    <!-- Main content -->
    <section class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h1 class="box-title">Daftar Item</h1>
                    </div>
                    <div class="box-body">
                        <div class="table-responsive mt-3">
                            <table id="datatable" class="table table-striped table-dashboard-two mg-b-0">
                                <thead>
                                    <tr>
                                        <th style="width: 100px">#</th>
                                        <th>No.</th>
                                        <th>Tanggal</th>
                                        <th>Kode Pelanggan</th>
                                        <th>Nama</th>
                                        <th style="text-align: right;">Total</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                                <tfoot>
                                    <tr>
                                        <td></td>
                                        <td style="padding: 2px">
                                            <!-- No. Penjualan -->
                                            <input data-column="1" type="text" class="search-input-text form-control input-sm" placeholder="Cari ..." />
                                        </td>
                                        <td style="padding: 2px">
                                            <!-- Tanggal -->
                                            <input data-column="2" type="text" class="search-input-text form-control input-sm" placeholder="Cari ..." />
                                        </td>
                                        <td style="padding: 2px">
                                            <!-- Kode Pemasok -->
                                            <input data-column="3" type="text" class="search-input-text form-control input-sm" placeholder="Cari ..." />
                                        </td>
                                        <td style="padding: 2px">
                                            <!-- Nama -->
                                            <input data-column="4" type="text" class="search-input-text form-control input-sm" placeholder="Cari ..." />
                                        </td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div><!-- table-responsive -->
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- /.container-fluid -->

    <?php view("templates/script") ?>

    <script>
        var table;
        $(document).ready(function() {

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
                    "url": "<?php echo site_url('admin/penjualan_retur/ajax_penjualan_list') ?>",
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
                        "data": null,
                        render: function(data, type, row) {
                            var html = [];

                            html.push(data.no + " &nbsp; <a href='javascript:void(0);' style='margin-bottom:2px;margin-right:2px' data-toggle='tooltip' title='Edit' class='btn btn-xs btn-primary' onclick=\"returnValue('" + data.number_formatted + "')\">Pilih</a>");

                            return html.join("");
                        }
                    },
                ],
                "columns": [{
                        "data": null
                    }, // col-0
                    {
                        "data": "number_formatted"
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
                        "data": "total_akhir"
                    }, // col-5
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

        // return the value to the parent window
        function returnValue(choice) {
            opener.setSearchResultPenjualan(targetField, choice);
            window.close();
        }
    </script>