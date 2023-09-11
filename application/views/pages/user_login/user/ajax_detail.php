<?php
$user = get_session("user");
$privilege_list = array();
if (isset($user["privilege_list"])) $privilege_list = $user["privilege_list"];
$allow_user_create = isset($privilege_list["allow_user_login_create"]) ? $privilege_list["allow_user_login_create"] : 0;
$allow_user_update = isset($privilege_list["allow_user_login_update"]) ? $privilege_list["allow_user_login_update"] : 0;
$allow_user_delete = isset($privilege_list["allow_user_login_delete"]) ? $privilege_list["allow_user_login_delete"] : 0;

if (strtolower($user["user_role_name"]) == "super administrator") {
    $allow_user_create = 1;
    $allow_user_update = 1;
    $allow_user_delete = 1;
}

$uuid = "";
$name = "";
$username = "";
$user_role_uuid = "";
$cabang_uuid_list = array();

$title = "New -";
if (isset($detail) && is_array($detail) && count($detail) > 0) {
    $uuid = trim($detail["uuid"]);
    $name = trim($detail["name"]);
    $username = trim($detail["username"]);
    $user_role_uuid = trim($detail["user_role_uuid"]);
    $cabang_uuid_list = $detail["cabang_uuid_list"];
    $title = "Edit -";
}

if (!is_array($cabang_uuid_list)) $cabang_uuid_list = array();

?>
<form class="form-horizontal" onsubmit="return false;" id="form-user-detail">
    <input type="hidden" name="uuid" value="<?= $uuid ?>" />
    <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">

    <div class="row">
        <div class="col-md-6">
            <h5>Detail</h5>
            <div class="form-group row mb-2">
                <label class="col-form-label col-sm-4">Name</label>
                <div class="col-sm-8">
                    <input type="text" class="form-control" name="name" value="<?= $name ?>" />
                </div>
            </div>

            <div class="form-group row mb-2">
                <label class="col-form-label col-sm-4">Username</label>
                <div class="col-sm-8">
                    <input type="text" class="form-control" name="username" value="<?= $username ?>" />
                </div>
            </div>

            <div class="form-group row">
                <label class="col-form-label col-sm-4">Akses</label>
                <div class="col-sm-8">
                    <select class="form-control" name="user_role_uuid">
                        <option value="" selected disabled>-- Pilih Akses --</option>
                        <?php
                        if (isset($user_role_list) && is_array($user_role_list)) {
                            foreach ($user_role_list as $l) {
                                $selected = "";
                                if ($l['uuid'] == $user_role_uuid) $selected = "selected";
                        ?>
                                <option value="<?= trim($l["uuid"]) ?>" <?= $selected ?>><?= $l["name"] ?></option>
                        <?php
                            }
                        }
                        ?>
                    </select>
                </div>
            </div>

            <?php
            if (empty($uuid)) {
            ?>
                <hr />


                <div class="form-group row mb-2">
                    <label class="col-form-label col-sm-4">Password</label>
                    <div class="col-sm-8">
                        <input type="password" class="form-control" name="password" />
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-form-label col-sm-4">Confirm Password</label>
                    <div class="col-sm-8">
                        <input type="password" class="form-control" name="confirm_password" />
                    </div>
                </div>
            <?php
            }
            ?>
        </div>

        <div class="col-md-6">
            <h5>Akses Cabang</h5>
            <table class="table">
                <tbody>
                <?php
                if(isset($cabang_list) && is_array($cabang_list)){
                    foreach($cabang_list as $c){
                        $checked = "";
                        foreach($cabang_uuid_list as $index => $cabang_uuid) {
                            if($cabang_uuid == $c["uuid"]) {
                                $checked = "checked";
                                break;
                            }
                        }
                ?>
                <tr>
                    <td style="vertical-align: top;" width="20"> <input type="checkbox" <?= $checked ?> name="cabang_uuid_list[]" value="<?= $c["uuid"] ?>"> </td>
                    <td>
                        <b><?= $c["nama"] ?></b> <br/>
                        <span><?= $c["alamat"] ?></span>
                    </td>
                </tr>
                <?php
                    }
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>
</form>

<hr />

<div class="row">
    <div class="col-md-12" style="text-align: right">
        <button class="btn btn-sm light btn-secondary" onclick="$('#modal-detail').modal('hide')"><i class="fa fa-close"></i>&nbsp; Close</button>
        <button class="btn btn-sm btn-success" onclick="save()"><i class="fa fa-save"></i>&nbsp; Save</button>
    </div>
</div>

<script>
    $(document).ready(function() {
        $("#modal-user-title").html('<?= $title ?>');
    });

    function save() {
        var form_data = $("#form-user-detail").serializeArray();
        ajax_post(
            '<?= base_url("admin/user/ajax_save") ?>',
            form_data,
            function(resp) {
                try {
                    var json = JSON.parse(resp);
                    if (json.is_success == 1) {
                        show_toast("Success", json.message, "success");
                        $("#modal-detail").modal("hide");
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