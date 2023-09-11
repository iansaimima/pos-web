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
?>
<table id="datatable" class="table table-dashboard-two mg-b-0">
    <tbody>
        <?php
        if (isset($list) && is_array($list)) {
            $total_parent = count($list);
            $parent_count = 1;
            foreach ($list as $p) {
                $child_list = $p["child_list"];
                if (!is_array($child_list)) $child_list = array();

                $btn_order_up_class = 'btn-secondary';
                $btn_order_up_disabled = '';
                $btn_order_down_class = 'btn-secondary';
                $btn_order_down_disabled = '';

                if ($parent_count == 1) {
                    $btn_order_up_class = 'btn-outline-secondary';
                    $btn_order_up_disabled = 'disabled';
                }

                if ($parent_count == $total_parent) {
                    $btn_order_down_class = 'btn-outline-secondary';
                    $btn_order_down_disabled = 'disabled';
                }
        ?>
                <tr style="background: #eee">
                    <td style="vertical-align: top;">
                        <div class="btn-group">
                            <button class="btn btn-xs <?= $btn_order_up_class ?>" <?= $btn_order_up_disabled ?> onclick="move_up('<?= trim($p['uuid']) ?>')"><i class="fa fa-arrow-up"></i></button>
                            <button class="btn btn-xs <?= $btn_order_down_class ?>" <?= $btn_order_down_disabled ?> onclick="move_down('<?= trim($p['uuid']) ?>')"><i class="fa fa-arrow-down"></i></button>
                        </div>
                    </td>
                    <td style="vertical-align: top;"><?= $p["name"] ?></td>
                    <td style="vertical-align: top;"><?= $p["description"] ?></td>
                    <td style="vertical-align: top;">
                        <div class="btn-group">

                            <?php if ($allow_user_role_privilege_update) { ?>
                                <button type="button" onclick="load_detail('<?= trim($p['uuid']) ?>')" class="btn btn-xs btn-primary"><i class="fa fa-pencil"></i></button>
                            <?php } ?>
                            <?php
                            if (count($child_list) == 0) {
                            ?>
                                <?php if ($allow_user_role_privilege_delete) { ?>
                                    <button type="button" onclick="confirm_delete('<?= trim($p['uuid']) ?>')" class="btn btn-xs btn-danger"><i class="fa fa-times"></i></button>
                                <?php } ?>
                            <?php
                            }
                            ?>
                        </div>
                    </td>
                </tr>
                <?php
                foreach ($child_list as $c) {
                ?>
                    <tr>
                        <td style="vertical-align: top;"></td>
                        <td style="vertical-align: top;"><i class="fa fa-chevron-right"></i> <?= $c["name"] ?></td>
                        <td style="vertical-align: top;"><i class="fa fa-chevron-right"></i> <?= $c["description"] ?></td>
                        <td style="vertical-align: top;">
                            <div class="btn-group">
                                <?php if ($allow_user_role_privilege_update) { ?>
                                    <button type="button" onclick="load_detail('<?= trim($c['uuid']) ?>')" class="btn btn-xs btn-primary"><i class="fa fa-pencil"></i></button>
                                <?php } ?>

                                <?php if ($allow_user_role_privilege_delete) { ?>
                                    <button type="button" onclick="confirm_delete('<?= trim($c['uuid']) ?>')" class="btn btn-xs btn-danger"><i class="fa fa-times"></i></button>
                                <?php } ?>
                            </div>
                        </td>
                    </tr>
        <?php
                }
                $parent_count++;
            }
        }
        ?>
    </tbody>
</table>