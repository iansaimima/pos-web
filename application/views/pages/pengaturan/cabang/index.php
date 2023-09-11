<?php
defined('BASEPATH') or exit('No direct script access allowed');


$user = get_session("user");
$privilege_list = isset($user["privilege_list"]) ? $user["privilege_list"] : array();
$allow_create = isset($privilege_list["allow_cabang_create"]) ? $privilege_list["allow_cabang_create"] : 0;
$allow_update = isset($privilege_list["allow_cabang_update"]) ? $privilege_list["allow_cabang_update"] : 0;
$allow_delete = isset($privilege_list["allow_cabang_delete"]) ? $privilege_list["allow_cabang_delete"] : 0;

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

                <div class="row page-titlesx mx-0 mb-4">
                    <div class="col-sm-6 p-md-0">
                        <div class="welcome-text">
                            <p class="mb-0"><button class="btn btn-primary" onclick="load_detail()"><i class="fa fa-plus"></i> Tambah</button></p>
                        </div>
                    </div>
                </div>

                <div id="cabang-list-content">
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
                        <h5 class="modal-title"><span id="modal-cabang-title"></span> cabang</h5>
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
        $(document).ready(function() {
            load_list();
        });

        function load_detail(uuid) {
            ajax_get(
                '<?= base_url($uri_1 . "/cabang/ajax_detail") ?>/' + uuid, {},
                function(resp) {
                    $("#modal-detail-body").html(resp);
                    $("#modal-detail").modal("show");
                }
            );
        }

        function load_list() {
            ajax_get(
                '<?= base_url($uri_1 . '/cabang/ajax_list') ?>', {},
                function(resp) {
                    $("#cabang-list-content").html(resp);
                }
            );
        }
    </script>
</body>

</html>