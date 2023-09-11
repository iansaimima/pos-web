<?php
defined('BASEPATH') or exit('No direct script access allowed');


$user = get_session("user");
$uri_1 = $this->uri->segment(1);

$settings_data = $settings_list;

$logo_url = base_url("assets/images/default-client-logo.png");
if (file_exists("assets/images/client-logo.png")) {
    $logo_url = base_url("assets/images/client-logo.png?" . time());
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php view("templates/meta") ?>
    <?php view("templates/style") ?>

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
                <div class="row">
                    <div class="col-md-8">
                        <form onsubmit="return false" id="form-data-toko" class="form-horizontal" data-url="<?= $uri_1 ?>/settings/ajax_bulk_save">
                            <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
                            <div class="card">
                                <div class="card-header border-0 pb-0">
                                    <h5 class="card-title">Data Toko</h5>
                                </div>
                                <div class="card-body">
                                    <div class="form-group row">
                                        <label class="col-form-label col-sm-3"><?= $settings_data['TOKO_NAMA']['_label'] ?></label>
                                        <div class="col-sm-9">
                                            <input type="text" name="<?= $settings_data['TOKO_NAMA']['_key'] ?>" class="form-control" value="<?= $settings_data['TOKO_NAMA']['_value'] ?>">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-form-label col-sm-3"><?= $settings_data['TOKO_ALAMAT']['_label'] ?></label>
                                        <div class="col-sm-9">
                                            <textarea name="<?= $settings_data['TOKO_ALAMAT']['_key'] ?>" class="form-control" rows="3"><?= $settings_data['TOKO_ALAMAT']['_value'] ?></textarea>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-form-label col-sm-3"><?= $settings_data['TOKO_NO_TELEPON']['_label'] ?></label>
                                        <div class="col-sm-9">
                                            <input type="text" name="<?= $settings_data['TOKO_NO_TELEPON']['_key'] ?>" class="form-control" value="<?= $settings_data['TOKO_NO_TELEPON']['_value'] ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer border-0 pt-0 d-sm-flex justify-content-between align-items-center">
                                    <span></span>
                                    <button type="button" class="btn btn-primary btn-sm" onclick="settings_save('#form-data-toko')"> Simpan </button>
                                </div>
                            </div>
                        </form>
                    </div>


                    <div class="col-md-4">
                        <form onsubmit="return false" id="form-logo" class="form-horizontal" enctype="multipart/form-data" data-url="<?= $uri_1 ?>/settings/change_logo">
                            <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
                            <div class="card">
                                <div class="card-header border-0 pb-0">
                                    <h5 class="card-title" style="text-align: center; display: block; width: 100%;"><span class="text-center">Logo</span></h5>
                                </div>
                                <input type="file" style="display: none;" id="input-file-logo" name="logo" onchange="previewImage(event)" class="form-control" accept="image/png">

                                <div class="card-body">
                                    <div class="row justify-content-center align-items-center">

                                        <div class="col-md-12 text-center">
                                            <a href="javascript:void(0);" onclick="$('#input-file-logo').click();">
                                                <img src="<?= $logo_url ?>" id="preview-logo" class="img img-responsive img-thumbnail" style="width: 184px; height:184px; min-width: 184px; min-height:184px; box-shadow: 2px 2px 12px rgba(0,0,0,0.5)" alt="">
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <div class="card-footer border-0 d-flex justify-content-center align-items-center">
                                    <button type="button" class="btn btn-primary btn-sm" onclick="settings_saveLogo('#form-logo')"> Simpan </button>
                                </div>
                            </div>
                        </form>
                    </div>                    
                </div>
            </div>
        </div>
        <!--**********************************
            Content body end
        ***********************************-->


        <?php view("templates/footer") ?>
    </div>
    <?php view("templates/script") ?>

    <script>
        function previewImage(event) {
            var output = document.getElementById('preview-logo');
            output.src = URL.createObjectURL(event.target.files[0]);
            console.log(output.src);
            output.onload = function() {
                URL.revokeObjectURL(output.src) // free memory
            }
        }

        function settings_save(formId_) {
            var url_ = $(formId_).data("url");
            ajax_post(
                '<?= base_url() ?>/' + url_,
                $(formId_).serialize(),
                function(resp) {
                    try {
                        var json = JSON.parse(resp);
                        if (json.is_success == 1) {
                            show_toast("Success", json.message, "success");
                        } else {
                            show_toast("Error", json.message, "error");
                        }
                    } catch (error) {
                        show_toast("Error", "Application response error", "error");
                    }
                }
            );
        }

        function settings_saveLogo(formId_) {
            var url_ = $(formId_).data("url");

            var form = $(formId_)[0];
            var formData = new FormData(form);

            ajax_post_file(
                '<?= base_url() ?>/' + url_,
                formData,
                function(resp) {
                    try {
                        var json = JSON.parse(resp);
                        if (json.is_success == 1) {
                            show_toast("Success", json.message, "success");
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