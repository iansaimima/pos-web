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
                                        <th style="width: 40px">#</th>
                                        <th>Kode</th>
                                        <th>Barcode</th>
                                        <th>Nama</th>
                                        <th>Kategori</th>
                                        <th style="text-align: right;">Jumlah</th>
                                        <th>Satuan</th>
                                        <th style="text-align: right;">Harga</th>
                                        <th style="text-align: right;">Pot (%)</th>
                                        <th style="text-align: right;">Total</th>
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
    <!-- /.container-fluid -->

    <?php view("templates/script") ?>

    <script>
        var table;
        var _penjualanUuid = '<?= get('penjualan_uuid') ?>';
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
                    "url": "<?php echo site_url('admin/penjualan_retur/ajax_item_list/') ?>"  + _penjualanUuid,
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

                            html.push(data.no + " &nbsp; <a href='javascript:void(0);' style='margin-bottom:2px;margin-right:2px' data-toggle='tooltip' title='Edit' class='btn btn-xs btn-primary' onclick=\"returnValue('" + data.kode  + ":" + data.satuan.toUpperCase() + "')\">Pilih</a>");

                            return html.join("");
                        }
                    },
                ],
                "columns": [{
                        "data": null
                    }, // col-0
                    {
                        "data": "kode"
                    }, // col-1
                    {
                        "data": "barcode"
                    }, // col-1
                    {
                        "data": "nama"
                    }, // col-2
                    {
                        "data": "kategori"
                    }, // col-3
                    {
                        "data": "jumlah",
                        className: 'text-right'
                    }, // col-4
                    {
                        "data": "satuan"
                    }, // col-5
                    {
                        "data": "harga_jual_satuan",
                        className: 'text-right'
                    }, // col-6
                    {
                        "data": "potongan",
                        className: 'text-right'
                    }, // col-7
                    {
                        "data": "total",
                        className: 'text-right'
                    }, // col-8
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
            opener.setSearchResult(targetField, choice);
            window.close();
        }
    </script>