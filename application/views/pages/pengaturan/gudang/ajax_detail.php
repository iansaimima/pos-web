<?php

$user = get_session("user");
$privilege_list = isset($user["privilege_list"]) ? $user["privilege_list"] : array();
$allow_create = isset($privilege_list["allow_gudang_create"]) ? $privilege_list["allow_gudang_create"] : 0;
$allow_update = isset($privilege_list["allow_gudang_update"]) ? $privilege_list["allow_gudang_update"] : 0;
$allow_delete = isset($privilege_list["allow_gudang_delete"]) ? $privilege_list["allow_gudang_delete"] : 0;

$uuid = "";
$kode = "";
$nama = "";
$alamat = "";
$no_telepon = "";
$keterangan = "";
$fungsi = "";
$created_by = "";
$last_updated_by = "";

$title = "Tambah -";
if (isset($detail) && is_array($detail) && count($detail) > 0) {
    $uuid = trim($detail["uuid"]);
    $kode = trim($detail["kode"]);
    $nama = trim($detail["nama"]);
    $alamat = trim($detail["alamat"]);
    $no_telepon = trim($detail["no_telepon"]);
    $keterangan = trim($detail["keterangan"]);
    $fungsi = trim($detail["fungsi"]);

    $created_by = "Dibuat oleh <b>" . $detail["creator_user_name"] . "</b>, pada <b>" . $detail["created"] . "</b>";
    $last_updated_by = "Terakhir diubah oleh <b>" . $detail["last_updated_user_name"] . "</b>, pada <b>" . $detail["last_updated"] . "</b>";

    $title = "Ubah -";
}

?>
<form class="form-horizontal" onsubmit="return false;" id="form-gudang-detail">
    <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
    <input type="hidden" name="uuid" value="<?= $uuid ?>" />

    <div class="form-group row mb-2">
        <label class="col-form-label col-sm-4">
            Kode <br/>
            <small>Max. 3 Huruf</small>
        </label>
        <div class="col-sm-2">
            <input type="text" name="kode" class="form-control input-sm" maxlength="3" value="<?= $kode ?>" />            
        </div>        
    </div>

    <div class="form-group row mb-2">
        <label class="col-form-label col-sm-4">Nama</label>
        <div class="col-sm-8">
            <input type="text" class="form-control input-sm" name="nama" value="<?= $nama ?>" />
        </div>
    </div>

    <div class="form-group row mb-2">
        <label class="col-form-label col-sm-4">Alamat</label>
        <div class="col-sm-8">
            <textarea name="alamat" class="form-control input-sm" rows="3"><?= $alamat ?></textarea>
        </div>
    </div>

    <div class="form-group row mb-2">
        <label class="col-form-label col-sm-4">No. Telepon</label>
        <div class="col-sm-8">
            <input type="text" class="form-control input-sm" name="no_telepon" value="<?= $no_telepon ?>" />
        </div>
    </div>

    <div class="form-group row mb-2">
        <label class="col-form-label col-sm-4">Fungsi</label>
        <div class="col-sm-8">
            <input type="text" class="form-control input-sm" name="fungsi" value="<?= $fungsi ?>" />
        </div>
    </div>

    <div class="form-group row mb-2">
        <label class="col-form-label col-sm-4">Keterangan</label>
        <div class="col-sm-8">
            <textarea name="keterangan" class="form-control input-sm" rows="3"><?= $keterangan ?></textarea>
        </div>
    </div>
</form>
<br />
<?php if (!empty($created_by)) : ?>
    <p class="mb-0"><?= $created_by ?></p>
<?php endif ?>
<?php if (!empty($last_updated_by)) : ?>
    <p><?= $last_updated_by ?></p>
<?php endif ?>

<hr />

<div class="row">
    <div class="col-md-6">
        <?php
        if (!empty($uuid)) {
            if ($allow_delete) { ?>
                <button class="btn btn-sm btn-outline-danger" onclick="confirm_delete('<?= $uuid ?>')"><i class="fa fa-trash"></i>&nbsp; Hapus</button>
        <?php
            }
        }
        ?>
    </div>
    <div class="col-md-6" style="text-align: right">
        <button class="btn btn-sm btn-secondary light" onclick="$('#modal-detail').modal('hide')"><i class="fa fa-close"></i>&nbsp; Close</button>
        <?php if ($allow_update || $allow_create) : ?>
            <button class="btn btn-sm btn-success" onclick="save()"><i class="fa fa-save"></i>&nbsp; Save</button>
        <?php endif ?>
    </div>
</div>

<script>
    $(document).ready(function() {
        $("#modal-gudang-title").html('<?= $title ?>');
    });

    function save() {
        var form_data = $("#form-gudang-detail").serializeArray();
        ajax_post(
            '<?= base_url("admin/gudang/ajax_save") ?>',
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