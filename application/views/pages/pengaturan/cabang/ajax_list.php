<?php

$user = get_session("user");
$cabang = get_session("cabang_selected");
$privilege_list = isset($user["privilege_list"]) ? $user["privilege_list"] : array();
$allow_create = isset($privilege_list["allow_cabang_create"]) ? $privilege_list["allow_cabang_create"] : 0;
$allow_update = isset($privilege_list["allow_cabang_update"]) ? $privilege_list["allow_cabang_update"] : 0;
$allow_delete = isset($privilege_list["allow_cabang_delete"]) ? $privilege_list["allow_cabang_delete"] : 0;

?>
<div class="row">
    <?php

    if (isset($list) && is_array($list) && count($list) > 0) {

        foreach ($list as $l) {
            $nama = $l["nama"];
            $kode = $l["kode"];
            $alamat = ucwords($l["alamat"]);
            $no_telepon = $l["no_telepon"];
    ?>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header border-0 pb-0">
                <h5 class="card-title"><span class="badge badge-roundedx badge-success"><?= $kode ?></span> <?= $nama ?></h5>
            </div>
            <div class="card-body">
                <label class="mb-0"><b>Alamat</b></label>
                <p class="card-text"><?= $alamat ?></p>

                <label class="mb-0"><b>No. Telepon</b></label>
                <p class="card-text"><?= $no_telepon ?></p>
            </div>
            <div class="card-footer border-0 pt-0">
                <?php
                if($allow_update) {
                ?>
                <a href="javascript:void()" onclick="load_detail('<?= $l['uuid'] ?>')" class="btn btn-sm btn-primary float-right">Ubah</a>
                <?php } ?>
            </div>
        </div>
    </div>
    <?php
        }
    }
    ?>
</div>