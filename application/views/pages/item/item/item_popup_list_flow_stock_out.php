<?php

$uri_1 = $this->uri->segment(1);
?>
<!DOCTYPE html>
<html>

<head>
    <?php view("templates/meta"); ?>
    <?php view("templates/style"); ?>
    <link rel="stylesheet" href="https://cdn.datatables.net/keytable/2.6.2/css/keyTable.bootstrap4.min.css">
</head>

<body class="hold-transition sidebar-mini">
    <!-- Main content -->
    <section class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card card-primary mt-4">
                    <div class="card-header border-0 pb-0">
                        <h1 class="card-title">Daftar Item</h1>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive mt-3">
                            <table id="datatable" class="table table-striped table-dashboard-two mg-b-0">
                                <thead>
                                    <tr>
                                        <th>Kode</th>
                                        <th>Barcode</th>
                                        <th>Nama</th>
                                        <th>Stock</th>
                                        <th>Satuan</th>
                                        <th>Kategori</th>
                                        <th>Harga Jual</th>
                                        <th>Tipe</th>
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
    <script src="https://cdn.datatables.net/keytable/2.6.2/js/dataTables.keyTable.min.js"></script>

    <style>
        .btn-sm,
        .btn-group-sm>.btn {
            line-height: 0.5;
        }

        table.dataTable th.focus,
        table.dataTable td.focus {
            outline: none;
        }

        table.dataTable tr.selected td {
            color: #fff;
            background-color: #8c8caa !important;
        }

        table.dataTable tbody th.focus,
        table.dataTable tbody td.focus {
            box-shadow: none;
        }
    </style>

    <script>
        var table;
        $(document).ready(function() {

            table = $("#datatable").DataTable({
                "tabIndex": 0,
                "dom": '<f<t>p>',
                "keys": {
                    keys: [13 /* ENTER */ , 38 /* UP */ , 40 /* DOWN */ ]
                },
                "processing": true, //Feature control the processing indicator.
                "serverSide": true, //Feature control DataTables' server-side processing mode. 
                "autoWidth": false,
                "oLanguage": {
                    "sSearch": "Cari",
                    "sLengthMenu": "Tampilkan _MENU_ baris",
                    "sInfo": "Menampilkan _START_ s/d _END_ dari _TOTAL_ baris",
                },

                "search": {
                    "search": '<?= get('q') ?>'
                },

                // Load data for the table's content from an Ajax source
                "ajax": {
                    "url": "<?php echo site_url($uri_1 . '/item/ajax_popup_list') ?>/out/barang",
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

                "columns": [{
                        "data": "kode"
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
                        "data": "harga_jual",
                        className: "text-right"
                    }, // col-6
                    {
                        "data": "tipe"
                    }, // col-7
                ],
                "initComplete": function(settings, json) {
                    // $('#datatable tbody tr:first').click();
                }
            });

            $('#datatable tbody').on('dblclick', 'tr', function() {
                var data = table.row(this).data();
                returnValue(data.kode + "-" + data.satuan);
            });

            // Handle event when cell gains focus
            $('#datatable').on('key-focus.dt', function(e, datatable, cell) {
                // Select highlighted row
                $(table.row(cell.index().row).node()).addClass('selected');
            });

            // Handle event when cell looses focus
            $('#datatable').on('key-blur.dt', function(e, datatable, cell) {
                // Deselect highlighted row
                $(table.row(cell.index().row).node()).removeClass('selected');
            });

            // Handle key event that hasn't been handled by KeyTable
            $('#datatable').on('key.dt', function(e, datatable, key, cell, originalEvent) {
                // If ENTER key is pressed
                if (key === 13) {
                    // Get highlighted row data
                    var data = table.row(cell.index().row).data();
                    returnValue(data.kode + "-" + data.satuan);
                }
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

        // return the value to the parent window
        function returnValue(choice) {
            opener.setSearchResult(targetField, choice);
            window.close();
        }
    </script>
</body>

</html>