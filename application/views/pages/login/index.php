<?php

$settings = get_session("settings");

$app_name = APP_NAME;
if (isset($settings["TOKO_NAMA"])) $app_name = $settings["TOKO_NAMA"]["_value"];

$logo_url = base_url("assets/images/default-client-logo.png");
if (file_exists("assets/images/logo.png")) {
    $logo_url = base_url("assets/images/logo.png?" . time());
}

$uri_1 = $this->uri->segment(1);

?>

<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <?php view("templates/meta") ?>
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,400&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="<?= base_url('assets/fonts/icomoon/style.css') ?>">

    <!-- <link rel="stylesheet" href="css/owl.carousel.min.css"> -->
    <link rel="stylesheet" href="<?= base_url('assets') ?>/vendor/toastr/css/toastr.min.css">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">

    <!-- Style -->
    <link rel="stylesheet" href="<?= base_url('assets/css/style_login.css') ?>">
</head>

<body>


    <div class="d-lg-flex half">
        <div class="bg order-1 order-md-2" style="background-image: url('<?= base_url('assets/images/login-bg.png') ?>');"></div>
        <div class="contents order-2 order-md-1">

            <div class="container">
                <div class="row align-items-center justify-content-center">
                    <div class="col-md-7">
                        <div id="langganan-berakhir" style="display: none;">

                            <div class="alert alert-warning bg-warning">
                                <h4 class="alert-title text-white" style="padding-top: 10px;"><i class="fa fa-warning"></i>&nbsp; Masa Langganan Telah Berakhir</h4>
                                <p class="text-white">
                                    <span id="langganan-berakhir-pesan"></span>
                                    <a href="https://langganan.gutsypos.com/" class="text-white"><h3>Perpanjang Sekarang</h3></a>
                                </p>
                            </div>
                        </div>

                        <h3>Masuk untuk kelola data dengan <?= APP_NAME ?></h3>
                        <p class="mb-4"></p>
                        <form onsubmit="return false" id="form-login" autocomplete="off">
                            <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
                            <div class="form-group first">
                                <label for="username">Username</label>
                                <input type="text" name="username" placeholder="Masukkan username" class="form-control" id="username">
                            </div>
                            <div class="form-group last mb-3">
                                <label for="password">Password</label>
                                <input type="password" name="password" placeholder="Masukkan password" class="form-control" id="password">
                            </div>


                            <button type="button" onclick="do_login()" class="btn btn-block btn-primary">Masuk ke <?= APP_NAME ?></button>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php view("templates/footer"); ?>
    <?php view("templates/script"); ?>

    <script>
        $(document).ready(function() {
            $(".form-control").on('keyup', function(e) {
                if (e.keyCode == 13) {
                    do_login();
                }
            });
        });

        function do_login() {
            var form_data = $("#form-login").serializeArray();
            ajax_post(
                '<?= base_url('admin') ?>/login/do_login',
                form_data,
                function(resp) {
                    try {
                        var json = JSON.parse(resp);
                        var is_success = json.is_success;
                        var message = json.message;

                        if (is_success == 1) {
                            show_toast("Success", "Mengarahkan ke halaman utama ...", "success");
                            window.location.href = "<?= base_url($uri_1) ?>";
                        } else {
                            show_toast("Error", message, "error");
                        }
                    } catch (error) {
                        show_toast("Error", "Application Response Error", "error");
                    }
                }
            );
        }
    </script>
</body>

</html>