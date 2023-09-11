<?php
$user = get_session("user");
$privilege_list = array();
if (isset($user["privilege_list"])) $privilege_list = $user["privilege_list"];
$allow_user_role_privilege_create = isset($privilege_list["allow_user_role_privilege_create"]) ? $privilege_list["allow_user_role_privilege_create"] : 0;
$allow_user_role_privilege_update = isset($privilege_list["allow_user_role_privilege_update"]) ? $privilege_list["allow_user_role_privilege_update"] : 0;
$allow_user_role_privilege_delete = isset($privilege_list["allow_user_role_privilege_delete"]) ? $privilege_list["allow_user_role_privilege_delete"] : 0;

if (strtolower($user["user_role_name"]) == "super administrator") {
    // $allow_user_role_privilege_create = 1;
    // $allow_user_role_privilege_update = 1;
    // $allow_user_role_privilege_delete = 1;
}

$uuid = "";
$name = "";
$description = "";
$parent_uuid = "";

if(isset($detail) && is_array($detail) && count($detail) > 0){
    $uuid = trim($detail["uuid"]);
    $parent_uuid = trim($detail["parent_uuid"]);
    $name = trim($detail["name"]);
    $description = trim($detail["description"]);    
}

?>

<div class="card card-primary">
    <div class="card-header border-0 pb-0">
        <h1 class="card-title">Detail</h1>
    </div>
    <div class="card-body">
        <form id="form-detail-user-role-privilege" class="form-horizontal" onsubmit="return false">
            <input type="hidden" name="uuid" value="<?= $uuid ?>" />
            <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">

            <div class="form-group row">
                <label class="col-sm-12 col-form-label">Name</label>
                <div class="col-sm-12" >
                    <input type="text" name="name" class="form-control" value="<?= $name ?>" />
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-12 col-form-label">Parent</label>
                <div class="col-sm-12" >
                    <select name="parent_uuid" class="form-control select2">
                        <option value="" selected>-- No Parent --</option>
                        <?php
                        if(isset($parent_list) && is_array($parent_list)){
                            foreach($parent_list as $p){

                                $selected = "";
                                if($parent_uuid == trim($p["uuid"])) $selected = "selected";
                        ?>
                        <option value="<?= trim($p["uuid"]) ?>" <?= $selected ?>><?= $p["description"] ?></option>
                        <?php
                            }
                        }
                        ?>
                    </select>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-12 col-form-label">Description</label>
                <div class="col-sm-12" >
                    <textarea name="description" class="form-control" rows="3"><?= $description ?></textarea>
                </div>
            </div>
        </form>
    </div>

    <div class="card-footer border-0 pt-0 justify-content-between">
        <div class="row">
            <div class="col-md-6">
                <button type="button" onclick="save_detail()" class="btn btn-success btn-xs">Save</button>

                <?php if(!empty($uuid) && $allow_user_role_privilege_delete){ ?>
                <button type="button" onclick="confirm_delete('<?= $uuid ?>')" class="btn btn-outline-danger btn-xs">Delete</button>
                <?php } ?>
            </div>
            
            <div class="col-md-6" style="text-align: right">
                <button type="button" onclick="load_list()" class="btn btn-secondary light btn-xs">Cancel</button>

            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function(){
        $('.form-control').on('keydown', function (event){
            if (event.keyCode == 13){
                if (event.shiftKey) {
                    save_detail();
                }
            }        
        });

        $(".select2").select2({
            theme: 'classic'
        });
    });
</script>