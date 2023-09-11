<?php
$user = get_session("user");
$settings = get_session("settings");
$cabang_list = get_session("cabang_list");
$cabang_selected = get_session("cabang_selected");

$app_name = APP_NAME;
if (isset($settings["TOKO_NAMA"])) $app_name = $settings["TOKO_NAMA"]["_value"];

$logo_url = base_url("assets/images/logo.png");
$logo_sm_url = base_url("assets/images/logo-with-text.png");
$logo_text_url = base_url("assets/images/logo-text.png");
$uri_1 = $this->uri->segment(1);

$target_uri = isset($_SERVER["REDIRECT_QUERY_STRING"]) ? $_SERVER["REDIRECT_QUERY_STRING"] : "";

?>
<!--**********************************
            Nav header start
        ***********************************-->
<div class="nav-header">
    <a href="<?= base_url($this->uri->segment(1)) ?>" class="brand-logo">
        <img class="logo-abbr d-nonex d-sm-block d-md-block" src="<?= $logo_url ?>" alt="">
        <img class="logo-abbr d-sm-block d-md-none" src="<?= $logo_sm_url ?>" alt="">
        <img class="logo-compact" src="<?= $logo_text_url ?>" alt="">
        <img class="brand-title" src="<?= $logo_text_url ?>" alt="">
        <!-- <h3 class="d-none d-md-block"><?= APP_NAME ?></h3> -->
    </a>

    <div class="nav-control">
        <div class="hamburger">
            <span class="line"></span><span class="line"></span><span class="line"></span>
        </div>
    </div>
</div>
<!--**********************************
            Nav header end
        ***********************************-->

<!--**********************************
            Header start
        ***********************************-->
<div class="header">
    <div class="header-content">
        <nav class="navbar navbar-expand">
            <div class="collapse navbar-collapse justify-content-between">
                <div class="header-left" style="padding-top: 15px;">

                    <?php
                    if (isset($breadcrumb) && is_array($breadcrumb) && count($breadcrumb) > 0) {
                    ?>
                        <ol class="breadcrumb">
                            <?php
                            foreach ($breadcrumb as $title => $active) {
                            ?>
                                <li class="breadcrumb-item <?= $active ?>"><a href="javascript:void(0)"><?= $title ?></a></li>
                            <?php
                            }
                            ?>
                        </ol>
                    <?php
                    }
                    ?>
                </div>

                <ul class="navbar-nav header-right">
                    <li class="nav-item dropdown notification_dropdown">
                        <a class="nav-link bell ai-icon" href="#" role="button" data-toggle="dropdown" aria-expanded="true">

                            <div class="media media-success">
                                <i class="fa fa-home mt-1" style="font-size: 20pt;"></i>
                                <span class="badge badge-roundedx badge-success" style="left: 8px; top: 8px; height: 36px; width: 36px; border-radius: 4px; line-height: 24px; font-size: 75%"><?= isset($cabang_selected["kode"]) ? $cabang_selected["kode"] : "" ?></span> 

                                <h6 class="ml-3"><?= isset($cabang_selected["nama"]) ? $cabang_selected["nama"]: "" ?> <br />
                                    <small class="d-block"><?= isset($cabang_selected["alamat"]) ? $cabang_selected["alamat"]: "" ?></small>
                                </h6>
                            </div>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <div id="DZ_W_Notification1" class="widget-media dz-scroll p-3 ps">
                                <ul class="timeline">
                                    <li>
                                        <div class="timeline-panel pb-3">
                                            <div class="media mr-2 media-success">
                                                <i class="fa fa-home"></i>
                                            </div>
                                            <div class="media-body">
                                                <h6 class="mb-1"><span class="badge badge-roundedx badge-success"><?= isset($cabang_selected["kode"]) ? $cabang_selected["kode"] : "" ?></span> <?= isset($cabang_selected["nama"]) ? $cabang_selected["nama"] : "" ?></h6>
                                                <small class="d-block"><?= isset($cabang_selected["alamat"]) ? $cabang_selected["alamat"] : "" ?></small>
                                                <span class="badge badge-primary">Terpilih</span>
                                            </div>
                                        </div>
                                    </li>
                                    <?php
                                    foreach ($cabang_list as $c_uuid => $c) {
                                        if(count($c) == 0) continue;
                                        $uuid = $c["uuid"];
                                        $kode = $c["kode"];
                                        $nama = $c["nama"];
                                        $alamat = $c["alamat"];

                                        $cabang_selected_uuid = isset($cabang_selected["uuid"]) ? $cabang_selected["uuid"] : "";

                                        if ($uuid == $cabang_selected_uuid) continue;

                                    ?>
                                        <li>
                                            <a href="<?= base_url($this->uri->segment(1) . '/beranda/set_cabang/' . $uuid . "?target=" . $target_uri) ?>">
                                                <div class="timeline-panel pb-3">
                                                    <div class="media mr-2 media-primary">
                                                        <i class="fa fa-home"></i>
                                                    </div>
                                                    <div class="media-body">
                                                        <h6 class="mb-1"><span class="badge badge-roundedx badge-success"><?= $kode ?></span> <?= $nama ?></h6>
                                                        <small class="d-block"><?= $alamat ?></small>
                                                    </div>
                                                </div>
                                            </a>
                                        </li>
                                    <?php
                                    }
                                    ?>
                                </ul>
                                <div class="ps__rail-x" style="left: 0px; bottom: 0px;">
                                    <div class="ps__thumb-x" tabindex="0" style="left: 0px; width: 0px;"></div>
                                </div>
                                <div class="ps__rail-y" style="top: 0px; right: 0px;">
                                    <div class="ps__thumb-y" tabindex="0" style="top: 0px; height: 0px;"></div>
                                </div>
                                <a href="<?= base_url('admin/cabang') ?>" class="all-notification pb-0" >Tambah Cabang</a>
                            </div>
                        </div>
                    </li>

                    <li class="nav-item dropdown header-profile">
                        <a class="nav-link" href="#" role="button" data-toggle="dropdown">
                            <img src="<?= base_url('assets') ?>/images/user_login.png" width="16" alt="" style="padding: 4px" />
                            <div class="header-info">
                                <span>Hey, <strong><?= $user["name"] ?></strong></span>
                                <small><?= $user["user_role_name"] ?></small>
                            </div>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a href="javascript:void(0);" onclick="showModalGantiPassword()" class="dropdown-item ai-icon text-warning">
                                <i class="lni lni-lock-alt"></i>
                                <span class="ml-2"> Ganti Password </span>
                            </a>
                            <a href="<?= base_url($this->uri->segment(1) . "/login/do_logout/") ?>" class="dropdown-item ai-icon text-danger">
                                <i class="lni lni-power-switch"></i>
                                <span class="ml-2">Logout </span>
                            </a>
                        </div>
                    </li>
                </ul>
            </div>
        </nav>
    </div>
</div>

<div class="modal fade" id="modal-change-password">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h5 class="modal-title">Ganti Password</h5>
            </div>

            <div class="modal-body" id="modal-change-password-body">
                <form onsubmit="return false" autocomplete="off" class="form-horizontal" id="form-change-password">
                    <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
                    <div class="form-group first">
                        <label for="old-password">Password Lama</label>
                        <input type="password" name="old_password" placeholder="Masukkan Password Lama" class="form-control input-change-password" id="old-password">
                    </div>
                    <div class="form-group last mb-3">
                        <label for="new-password">Password Baru</label>
                        <input type="password" name="new_password" placeholder="Masukkan Password Baru" class="form-control input-change-password" id="new-password">
                    </div>
                    <div class="form-group last mb-3">
                        <label for="confirm-new-password">Ulang Password Baru</label>
                        <input type="password" name="confirm_password" placeholder="Ulangi Password Baru" class="form-control input-change-password" id="confirm-new-password">
                    </div>
                </form>
            </div>

            <div class="modal-footer text-right">
                <div class="row">
                    <div class="col-md-12 d-flex">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="$('#modal-change-password').modal('hide');">Batal</button>
                        <button type="button" onclick="doChangePassword()" class="btn btn-sm btn-warning ml-2 text-white">Ganti Password</button>
                    </div>
                </div>
            </div>
        </div>
    </div><!-- modal-dialog -->
</div><!-- modal -->


<script>
    function showModalGantiPassword() {
        $(".input-change-password").val("");
        $("#modal-change-password").modal("show");
    }

    function doChangePassword() {
        ajax_post(
            '<?= base_url($uri_1 . '/user/ajax_change_password') ?>',
            $("#form-change-password").serialize(),
            function(resp) {
                try {
                    var json = JSON.parse(resp);
                    if (json.is_success == 1) {
                        show_toast("Success", json.message, "success");
                        $("#modal-change-password").modal("hide");
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
<!--**********************************
    Header end ti-comment-alt
***********************************-->