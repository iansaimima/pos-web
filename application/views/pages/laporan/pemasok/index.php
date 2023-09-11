<?php
defined('BASEPATH') or exit('No direct script access allowed');

$user = get_session("user");
$privilege_list = isset($user["privilege_list"]) ? $user["privilege_list"] : array();
$allow_laporan_pemasok = isset($privilege_list["allow_laporan_pemasok"]) ? $privilege_list["allow_laporan_pemasok"] : 0;

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
                    <div class="col-xl-12 col-xxl-12">
                        <div class="card">
                            <div class="card-header border-0 pb-0">
                                <h1 class="card-title">Laporan Pemasok</h1>
                            </div>
                            <div class="card-body p-3 pt-3" style="margin: 0">
                                <iframe class="iframe-rounded" id="frame-pemasok" src="<?= base_url("admin/laporan/pemasok/view") ?>" frameborder="0" style="width: 100%; height: calc(100vh - 210px)"></iframe>
                            </div>
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
        $("#loader").show();
        $(document).ready(function() {
            $("#frame-pemasok").on("load", function() {
                $("#loader").hide();
            });
        });
    </script>
</body>
</html>