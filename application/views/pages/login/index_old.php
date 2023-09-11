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
<!DOCTYPE html>
<html>

<head>
    <?php view("templates/meta") ?>
    <?php view("templates/style") ?>
</head>

<body class="hold-transition login-page">
    <div class="login-box">
        <center>
            <img src="<?= $logo_url ?>" class="img img-responsive img-rounded img-thumbnail" style="width: 50%; margin-bottom: 20px; margin-top: 20px; box-shadow: 2px 2px 12px rgba(0,0,0,0.5)" alt="">
        </center>
        <div class="login-logo">
            <a href="javascript:void(0);"><?= ucwords($uri_1) ?> <?= $app_name ?></a>
        </div>
        <!-- /.login-logo -->
        <div class="login-box-body">
            <p class="login-box-msg" style="font-size: 18px;">Masuk sebagai <?= $uri_1 ?></p>
            <form id="form-login" onsubmit="return false;" autocomplete="off">
                <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
                <div class="form-group has-feedback">
                    <input type="text" class="form-control" name="username" placeholder="Username" autocomplete="off">
                    <span class="form-control-feedback"><i class="fa fa-user"></i></span>
                </div>
                <div class="form-group has-feedback">
                    <input type="password" class="form-control" name="password" placeholder="Password" autocomplete="off">
                    <span class="form-control-feedback"><i class="fa fa-lock"></i></span>
                </div>
                <div class="row" style="margin-top: 12px;">
                    <div class="col-md-6 col-xs-6">

                    </div>
                    <!-- /.col -->
                    <div class="col-md-6">
                        <button type="button" onclick="do_login()" class="btn btn-primary btn-block btn-flat"><i class="fa fa-unlock"></i> &nbsp; Masuk</button>
                    </div>
                    <!-- /.col -->
                </div>

                <div class="row" style="margin-top: 12px;">
                    <div class="col-md-12" style="text-align: right;">
                        <?php
                        if ($uri_1 == "admin") {
                        ?>
                            <a href="<?= base_url('kasir/login') ?>" style="margin-bottom: 4px;">Atau masuk sebagai kasir</a>
                        <?php
                        } else {
                        ?>
                            <a href="<?= base_url('admin/login') ?>" style="margin-bottom: 4px;">Atau masuk sebagai admin</a>
                        <?php
                        }
                        ?>

                        <br />

                        <a href="<?= base_url() ?>">Ke Menu Utama</a>
                    </div>
                </div>
            </form>
        </div>
        <div class="login-box-footer">
        </div>
        <!-- /.login-box-body -->
    </div>
    <!-- /.login-box -->

    <?php view("templates/script"); ?>
    <script>
        function do_login() {
            var form_data = $("#form-login").serializeArray();
            ajax_post(
                '<?= base_url($uri_1) ?>/login/do_login',
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