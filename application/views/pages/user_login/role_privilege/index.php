<?php
$user = get_session("user");
$privilege_list = array();
if(isset($user["privilege_list"])) $privilege_list = $user["privilege_list"];
$allow_user_role_privilege_create = isset($privilege_list["allow_user_role_privilege_create"]) ? $privilege_list["allow_user_role_privilege_create"] : 0;
$allow_user_role_privilege_update = isset($privilege_list["allow_user_role_privilege_update"]) ? $privilege_list["allow_user_role_privilege_update"] : 0;
$allow_user_role_privilege_delete = isset($privilege_list["allow_user_role_privilege_delete"]) ? $privilege_list["allow_user_role_privilege_delete"] : 0;

if(strtolower($user["user_role_name"]) == "super administrator") {
    // $allow_user_role_privilege_create = 1;
    // $allow_user_role_privilege_update = 1;
    // $allow_user_role_privilege_delete = 1;
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
            <div class="container-fluid pb-0">
                <div class="row">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header border-0 pb-0">
                                <h1 class="card-title">User Role Privilege List</h1>
                                <div class="card-tools">
                                    <?php
                                    if($allow_user_role_privilege_create) {
                                    ?>
                                    <a href="#user-role-privilege-detail-container" class="btn btn-xs btn-primary" onclick="load_detail('')"><i class="fa fa-plus"></i></a>
                                    <?php
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="card-body pt-0" >
                                <div class="table-responsive mt-3" id="table-user-role-privilege-container" style="max-height: calc(100vh - 250px);">
                                </div><!-- table-responsive -->
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div id="user-role-privilege-detail-container" style="display: none">
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
            load_list();

            $('[data-toggle="tooltip"]').tooltip();
        });

        function load_detail(uuid) {
            ajax_get(
                '<?= base_url("admin/user_role_privilege/ajax_detail") ?>/' + uuid, {},
                function(resp) {
                    $("#user-role-privilege-detail-container").html(resp);
                    $("#user-role-privilege-detail-container").show();
                }
            );
        }

        function load_list() {
            ajax_get(
                '<?= base_url("admin/user_role_privilege/ajax_list") ?>/', {},
                function(resp) {
                    $("#table-user-role-privilege-container").html(resp);
                }
            );

            $("#user-role-privilege-detail-container").hide();
        }

        function confirm_delete(uuid) {
            var confirmed = confirm("Are you sure ?");
            if (!confirmed) return;

            ajax_get(
                '<?= base_url("admin/user_role_privilege/ajax_delete") ?>/' + uuid, {},
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

        function move_up(uuid) {

            ajax_get(
                '<?= base_url("admin/user_role_privilege/ajax_move_up") ?>/' + uuid, {},
                function(resp) {
                    try {
                        var json = JSON.parse(resp);
                        if (json.is_success == 1) {
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

        function move_down(uuid) {

            ajax_get(
                '<?= base_url("admin/user_role_privilege/ajax_move_down") ?>/' + uuid, {},
                function(resp) {
                    try {
                        var json = JSON.parse(resp);
                        if (json.is_success == 1) {
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

        function save_detail() {
            var form_data = $("#form-detail-user-role-privilege").serializeArray();
            ajax_post(
                '<?= base_url("admin/user_role_privilege/ajax_save") ?>',
                form_data,
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