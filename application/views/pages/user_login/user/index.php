<?php
$user = get_session("user");
$privilege_list = array();
if(isset($user["privilege_list"])) $privilege_list = $user["privilege_list"];
$allow_user_create = isset($privilege_list["allow_user_login_create"]) ? $privilege_list["allow_user_login_create"] : 0;
$allow_user_update = isset($privilege_list["allow_user_login_update"]) ? $privilege_list["allow_user_login_update"] : 0;
$allow_user_delete = isset($privilege_list["allow_user_login_delete"]) ? $privilege_list["allow_user_login_delete"] : 0;

if(strtolower($user["user_role_name"]) == "super administrator") {
    $allow_user_create = 1;
    $allow_user_update = 1;
    $allow_user_delete = 1;
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
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header border-0 pb-0">
                                <h1 class="card-title">User List</h1>
                                <div class="card-tools">
                                    <?php
                                    // jika boleh create
                                    if ($allow_user_create) {
                                    ?>
                                        <a href="javascript:void(0);" class="btn btn-sm btn-primary" onclick="load_detail('')"><i class="fa fa-plus"></i></a>
                                    <?php } ?>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive mt-3">
                                    <table id="datatable" class="table table-striped table-dashboard-two mg-b-0">
                                        <thead>
                                            <tr>
                                                <th style="width: 40px">#</th>
                                                <th style="width: 100px"></th>
                                                <th>Name</th>
                                                <th>Username</th>
                                                <th>Role</th>
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
                                                <td style="padding: 2px">
                                                    <!-- Username -->
                                                    <input data-column="1" type="text" class="search-input-text form-control input-sm" placeholder="Cari ..." />
                                                </td>
                                                <td style="padding: 2px">
                                                    <!-- Username -->
                                                    <input data-column="2" type="text" class="search-input-text form-control input-sm" placeholder="Cari ..." />
                                                </td>
                                            </tr>
                                        </tfoot>
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
            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <h5 class="modal-title"><span id="modal-user-title"></span> User</h5>
                    </div>

                    <div class="modal-body" id="modal-detail-body">
                    </div>
                </div>
            </div><!-- modal-dialog -->
        </div><!-- modal -->

        <div class="modal fade" id="modal-reset-password">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <h5 class="modal-title">Reset Password</h5>
                    </div>

                    <div class="modal-body" id="modal-reset-password-body">
                        <form class="form-horizontal" id="form-user-reset-password" onsubmit="return false">
                            <input type="hidden" name="uuid" value="" id="input-reset-uuid" />
                            <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">

                            <div class="form-group row">
                                <label class="col-form-label col-sm-4">Password</label>
                                <div class="col-sm-8">
                                    <input type="password" class="form-control" id="input-reset-password" name="password" />
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-form-label col-sm-4">Confirm Password</label>
                                <div class="col-sm-8">
                                    <input type="password" class="form-control" id="input-reset-confirm-password" name="confirm_password" />
                                </div>
                            </div>
                        </form>

                        <hr />

                        <div class="row">
                            <div class="col-md-12" style="text-align: right">
                                <button class="btn btn-sm light btn-secondary" onclick="$('#modal-reset-password').modal('hide')"><i class="fa fa-close"></i>&nbsp; Close</button>
                                <button class="btn btn-sm btn-warning" onclick="confirm_reset_password()"><i class="fa fa-warning"></i>&nbsp; Reset</button>
                            </div>
                        </div>
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
                "autoWidth": false,
                "oLanguage": {
                    "sSearch": "Cari",
                    "sLengthMenu": "Tampilkan _MENU_ baris",
                    "sInfo": "Menampilkan _START_ s/d _END_ dari _TOTAL_ baris",
                },

                // Load data for the table's content from an Ajax source
                "ajax": {
                    "url": "<?php echo site_url('admin/user/ajax_list') ?>",
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
                            html.push("<button type='button' data-toggle='tooltip' title='Edit' class='btn btn-xs btn-primary' onclick=\"load_detail('" + data.uuid + "')\"> <i class='fa fa-edit'></i></button>");
                            html.push("<button type='button' data-toggle='tooltip' title='Delete' class='btn btn-xs btn-danger' onclick=\"confirm_delete('" + data.uuid + "')\"> <i class='fa fa-trash-o'></i></button>");
                            html.push("<button type='button' data-toggle='tooltip' title='Reset Password' class='btn btn-xs btn-warning text-white' onclick=\"load_reset_password('" + data.uuid + "')\"> <i class='fa fa-key'></i></button>");
                            <?php if ($allow_user_update) { ?>
                            <?php } ?>

                            <?php if ($allow_user_delete) { ?>
                            <?php } ?>

                            html.push('</div>');
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
                    {
                        "data": "username"
                    }, // col-3
                    {
                        "data": "role_name"
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
                '<?= base_url("admin/user/ajax_detail") ?>/' + uuid, {},
                function(resp) {
                    $("#modal-detail-body").html(resp);
                    $("#modal-detail").modal("show");
                }
            );
        }

        function load_reset_password(uuid) {
            $("#input-reset-uuid").val(uuid);
            $("#input-reset-password").val("");
            $("#input-reset-confirm-password").val("");

            $("#modal-reset-password").modal("show");
        }

        function load_list() {
            table.draw();
        }

        function confirm_delete(uuid) {
            var confirmed = confirm("Are you sure ?");
            if (!confirmed) return;

            ajax_get(
                '<?= base_url("admin/user/ajax_delete") ?>/' + uuid, {},
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


        function confirm_reset_password() {
            var confirmed = confirm("Are you sure ?");
            if (!confirmed) return;

            ajax_post(
                '<?= base_url("admin/user/ajax_reset_password") ?>', {
                    uuid: $("#input-reset-uuid").val(),
                    password: $("#input-reset-password").val(),
                    confirm_password: $("#input-reset-confirm-password").val()
                },
                function(resp) {
                    try {
                        var json = JSON.parse(resp);
                        if (json.is_success == 1) {
                            show_toast("Success", json.message, "success");
                            $("#modal-reset-password").modal("hide");
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