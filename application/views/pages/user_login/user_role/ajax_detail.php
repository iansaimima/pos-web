<?php
$user = get_session("user");
$privilege_list = array();
if (isset($user["privilege_list"])) $privilege_list = $user["privilege_list"];
$allow_user_akses_create = isset($privilege_list["allow_user_akses_create"]) ? $privilege_list["allow_user_akses_create"] : 0;
$allow_user_akses_update = isset($privilege_list["allow_user_akses_update"]) ? $privilege_list["allow_user_akses_update"] : 0;
$allow_user_akses_delete = isset($privilege_list["allow_user_akses_delete"]) ? $privilege_list["allow_user_akses_delete"] : 0;

if (strtolower($user["user_role_name"]) == "super administrator") {
    $allow_user_akses_create = 1;
    $allow_user_akses_update = 1;
    $allow_user_akses_delete = 1;
}

$uuid = "";
$name = "";
$privilege_list = array();

$title = "New - ";
if (isset($detail) && is_array($detail) && count($detail) > 0) {
    $uuid = trim($detail["uuid"]);
    $name = trim($detail["name"]);
    $privilege_json = trim($detail["privilege_json"]);
    $privilege_list = json_decode($privilege_json, true);

    $title = "Edit - ";
}

if (!is_array($privilege_list)) $privilege_list = array();

$final_privilege_list = array();
foreach ($privilege_list as $l) {
    if (!isset($l["name"])) continue;

    $allow = 0;
    if (isset($l["allow"])) $allow = (int) $l["allow"];
    $final_privilege_list[$l["name"]] = $allow;
}

?>
<div class="card">
    <div class="card-header border-0 pb-0">
        <h1 class="card-title"><?= $title ?>User Role</h1>
    </div>
    <div class="card-body">
        <form class="form-horizontal" onsubmit="return false;" id="form-user-role-detail">
            <input type="hidden" name="uuid" value="<?= $uuid ?>" />
            <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">

            <div class="form-group row mb-0">
                <label class="col-form-label col-sm-4">Name</label>
                <div class="col-sm-8">
                    <input type="text" class="form-control" name="name" value="<?= $name ?>" />
                </div>
            </div>

            <div class="form-group row mb-0">
                <label class="col-form-label col-sm-4">Akses : </label>
                <div class="col-sm-8">
                </div>
            </div>

            <div class="table-responsive" style="max-height: calc(100vh - 400px);">
                <table class="table table-bordered table-striped table-hover">
                    <tbody>
                        <?php
                        if (isset($user_role_privilege_list) && is_array($user_role_privilege_list)) {
                            foreach ($user_role_privilege_list as $p) {
                                $child_list = $p["child_list"];
                                if (!is_array($child_list)) $child_list = array();

                                // **
                                // jika bukan super administrator dan centang allow user role privilege, 
                                // maka lanjutkan ke perulangan selanjutnya
                                if ($p["name"] == "allow_user_role_privilege" && strtolower($user["user_role_name"]) != 'super administrator') continue;

                                $disabled = "disabled";
                                $checked = "";
                                if (isset($final_privilege_list[$p["name"]])) {
                                    if ((int) $final_privilege_list[$p["name"]] == 1) {
                                        $disabled = "";
                                        $checked = "checked";
                                    }
                                }

                        ?>
                                <tr style="background: #eee">
                                    <th> <input type="checkbox" <?= $checked ?> class="allow-<?= $p["name"] ?>-parent" name="<?= $p['name'] ?>" value="1" onchange="set_child_enabled('allow-<?= $p["name"] ?>')" /> </th>
                                    <th colspan="2"><?= $p["description"] ?></th>
                                </tr>
                                <?php
                                foreach ($child_list as $c) {
                                    $checked = "";
                                    if (isset($final_privilege_list[$c["name"]])) {
                                        if ((int) $final_privilege_list[$c["name"]] == 1) {
                                            $checked = "checked";
                                        }
                                    }
                                ?>
                                    <tr>
                                        <td></td>
                                        <td> <input type="checkbox" <?= $disabled ?> <?= $checked ?> class="allow-<?= $p["name"] ?>-child" name="<?= $c['name'] ?>" value="1" /></td>
                                        <td><?= $c["description"] ?></td>

                                    </tr>
                        <?php
                                }
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </form>
    </div>
    <div class="card-footer border-0 pt-0">
        <div class="row">
            <div class="col-md-12" style="text-align: right">
                <button class="btn btn-sm light btn-secondary" onclick="$('#user-role-detail-container').hide()"><i class="fa fa-close"></i>&nbsp; Close</button>
                <button class="btn btn-sm btn-success" onclick="save()"><i class="fa fa-save"></i>&nbsp; Save</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $("#modal-user-title").html('<?= $title ?>');
    });

    function set_child_enabled(target) {
        var parent_checked = $("." + target + "-parent").prop("checked");
        $("." + target + "-child").prop("disabled", !parent_checked);
        if (!parent_checked) {
            $("." + target + "-child").prop("checked", parent_checked);
        }
    }

    function save() {
        var form_data = $("#form-user-role-detail").serializeArray();
        ajax_post(
            '<?= base_url("admin/user_role/ajax_save") ?>',
            form_data,
            function(resp) {
                try {
                    var json = JSON.parse(resp);
                    if (json.is_success == 1) {
                        show_toast("Success", json.message, "success");
                        $("#user-role-detail-container").hide();
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