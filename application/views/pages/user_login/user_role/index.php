<?php
$user = get_session("user");
$privilege_list = array();
if(isset($user["privilege_list"])) $privilege_list = $user["privilege_list"];
$allow_user_akses_create = isset($privilege_list["allow_user_akses_create"]) ? $privilege_list["allow_user_akses_create"] : 0;
$allow_user_akses_update = isset($privilege_list["allow_user_akses_update"]) ? $privilege_list["allow_user_akses_update"] : 0;
$allow_user_akses_delete = isset($privilege_list["allow_user_akses_delete"]) ? $privilege_list["allow_user_akses_delete"] : 0;

if(strtolower($user["user_role_name"]) == "super administrator") {
    $allow_user_akses_create = 1;
    $allow_user_akses_update = 1;
    $allow_user_akses_delete = 1;
}
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

                <div class="row">
                    <div class="col-md-5">
                        <div class="card">
                            <div class="card-header border-0 pb-0">
                                <h1 class="card-title">User Akses</h1>
                                <div class="card-tools">
                                    <?php if ($allow_user_akses_create) { ?>
                                        <a href="#user-role-detail-container" class="btn btn-xs btn-primary" onclick="load_detail('')"><i class="fa fa-plus"></i></a>
                                    <?php } ?>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive mt-3">
                                    <table id="datatable" class="table  table-dashboard-two mg-b-0">
                                        <thead>
                                            <tr>
                                                <th style="width: 40px">#</th>
                                                <th style="width: 100px"></th>
                                                <th>Name</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                        <tfoot>
                                            <tr>
                                                <td></td>
                                                <td></td>
                                                <td style="padding: 2px">
                                                    <!-- Nama -->
                                                    <input data-column="0" type="text" class="search-input-text form-control input-sm" placeholder="Cari ..." />
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div><!-- table-responsive -->
                            </div>
                        </div>
                    </div>

                    <div class="col-md-7">
                        <div id="user-role-detail-container" style="display: none">
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
                    "url": "<?php echo site_url('admin/user_role/ajax_list') ?>",
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
                            html.push(`<div class="btn-group">`);
                            <?php if ($allow_user_akses_update) { ?>
                                html.push("<a href='#user-role-detail-container' data-toggle='tooltip' title='Edit' class='btn btn-xs btn-primary' onclick=\"load_detail('" + data.uuid + "')\"> <i class='fa fa-edit'></i></a>");
                            <?php } ?>
                            <?php if ($allow_user_akses_delete) { ?>
                                html.push("<a href=\"javascript:void(0);\" data-toggle='tooltip' title='Delete' class='btn btn-xs btn-danger' onclick=\"confirm_delete('" + data.uuid + "')\"> <i class='fa fa-trash-o'></i></a>");
                            <?php } ?>
                            html.push(`</div>`);
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
                        "data": "name"
                    }, // col-2
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
                '<?= base_url("admin/user_role/ajax_detail") ?>/' + uuid, {},
                function(resp) {
                    $("#user-role-detail-container").html(resp);
                    $("#user-role-detail-container").show();
                }
            );
        }

        function load_list() {
            table.draw();
        }

        function confirm_delete(uuid) {
            var confirmed = confirm("Are you sure ?");
            if (!confirmed) return;

            ajax_get(
                '<?= base_url("admin/user_role/ajax_delete") ?>/' + uuid, {},
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