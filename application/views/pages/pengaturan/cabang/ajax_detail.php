<?php

$user = get_session("user");
$privilege_list = isset($user["privilege_list"]) ? $user["privilege_list"] : array();
$allow_create = isset($privilege_list["allow_cabang_create"]) ? $privilege_list["allow_cabang_create"] : 0;
$allow_update = isset($privilege_list["allow_cabang_update"]) ? $privilege_list["allow_cabang_update"] : 0;
$allow_delete = isset($privilege_list["allow_cabang_delete"]) ? $privilege_list["allow_cabang_delete"] : 0;

$uuid = "";
$kode = "";
$nama = "";
$alamat = "";
$no_telepon = "";
$keterangan = "";
$created_by = "";
$last_updated_by = "";

$bulan_mulai_penggunaan_aplikasi = date("m");
$tahun_mulai_penggunaan_aplikasi = date("Y");

$title = "Tambah -";
if (isset($detail) && is_array($detail) && count($detail) > 0) {
    $uuid = trim($detail["uuid"]);
    $kode = trim($detail["kode"]);
    $nama = trim($detail["nama"]);
    $alamat = trim($detail["alamat"]);
    $no_telepon = trim($detail["no_telepon"]);

    $bulan_mulai_penggunaan_aplikasi = trim($detail["bulan_mulai_penggunaan_aplikasi"]);
    $tahun_mulai_penggunaan_aplikasi = trim($detail["tahun_mulai_penggunaan_aplikasi"]);
    $persediaan_stock_awal_default_gudang_uuid = trim($detail["persediaan_stock_awal_default_gudang_uuid"]);
    $persediaan_stock_opname_default_gudang_uuid = trim($detail["persediaan_stock_opname_default_gudang_uuid"]);
    $transaksi_pembelian_default_gudang_uuid = trim($detail["transaksi_pembelian_default_gudang_uuid"]);
    $transaksi_pembelian_default_kas_akun_uuid = trim($detail["transaksi_pembelian_default_kas_akun_uuid"]);
    $transaksi_penjualan_default_gudang_uuid = trim($detail["transaksi_penjualan_default_gudang_uuid"]);
    $transaksi_penjualan_default_kas_akun_uuid = trim($detail["transaksi_penjualan_default_kas_akun_uuid"]);
    
    $transaksi_pembelian_default_pemasok_uuid = trim($detail["transaksi_pembelian_default_pemasok_uuid"]);
    $transaksi_penjualan_default_pelanggan_uuid = trim($detail["transaksi_penjualan_default_pelanggan_uuid"]);

    $keterangan = trim($detail["keterangan"]);

    $created_by = "Dibuat oleh <b>" . $detail["creator_user_name"] . "</b>, pada <b>" . $detail["created"] . "</b>";
    $last_updated_by = "Terakhir diubah oleh <b>" . $detail["last_updated_user_name"] . "</b>, pada <b>" . $detail["last_updated"] . "</b>";

    $title = "Ubah -";
}

?>
<form class="form-horizontal" onsubmit="return false;" id="form-cabang-detail">
    <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
    <input type="hidden" name="uuid" value="<?= $uuid ?>" />

    <div class="row">
        <div class="col-md-12">
            <h5>Detail</h5>
            <div class="form-group row mb-2">
                <label class="col-form-label col-sm-4">Kode</label>
                <div class="col-sm-8">
                    <input type="text" class="form-control input-sm" name="kode" value="<?= $kode ?>" maxlength="2" />
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
                <label class="col-form-label col-sm-4">Keterangan</label>
                <div class="col-sm-8">
                    <textarea name="keterangan" class="form-control input-sm" rows="3"><?= $keterangan ?></textarea>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <h5 class="mt-4">Mulai Penggunaan Aplikasi</h5>
            <div class="form-group row">
                <label class="col-form-label col-sm-5">Bulan</label>
                <div class="col-sm-7">
                    <select class="form-control select2" name="bulan_mulai_penggunaan_aplikasi">
                        <?php
                        for ($i = 1; $i <= 12; $i++) {
                            $selected = "";
                            $bulan = str_pad($i, 2, '0', STR_PAD_LEFT);

                            if (empty($bulan_mulai_penggunaan_aplikasi)) {
                                if ($i == (int) date("m")) $selected = "selected";
                            }
                            if ($bulan == $bulan_mulai_penggunaan_aplikasi) $selected = "selected";
                        ?>
                            <option value="<?= $bulan ?>" <?= $selected ?>><?= get_nama_bulan($i) ?> </option>
                        <?php
                        }
                        ?>
                    </select>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-form-label col-sm-5">Tahun</label>
                <div class="col-sm-7">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <button class="btn btn-primary" type="button" onclick="yearDown()"> <i class="fa fa-minus"></i> </button>
                        </div>

                        <input type="number" style="text-align: center;" class="form-control" id="number" name="tahun_mulai_penggunaan_aplikasi" min="<?= date("Y") ?>" value="<?= $tahun_mulai_penggunaan_aplikasi ?>">

                        <div class="input-group-append">
                            <button class="btn btn-primary" type="button" style="box-shadow: none;" onclick="yearUp()"> <i class="fa fa-plus"></i> </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <h5 class="mt-4">Default Akun Kas</h5>
            <div class="form-group row">
                <label class="col-form-label col-sm-5">Akun Kas Transaksi Pembelian</label>

                <div class="col-sm-7">
                    <select name="transaksi_pembelian_default_kas_akun_uuid" class="form-control select2" id="select2-default-kas-pembelian">
                        <?php
                        if (isset($kas_akun_list) && is_array($kas_akun_list)) {
                            $kas_akun_uuid = $transaksi_pembelian_default_kas_akun_uuid;
                            foreach ($kas_akun_list as $l) {
                                $selected = "";
                                if ($l['uuid'] == $kas_akun_uuid) $selected = "selected";
                        ?>
                                <option value="<?= $l['uuid'] ?>" <?= $selected ?>><?= $l['nama'] ?></option>
                        <?php
                            }
                        }

                        ?>
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <label class="col-form-label col-sm-5">Akun Kas Transaksi Penjualan</label>

                <div class="col-sm-7">
                    <select name="transaksi_penjualan_default_kas_akun_uuid" class="form-control select2" id="select2-default-kas-penjualan">
                        <?php
                        if (isset($kas_akun_list) && is_array($kas_akun_list)) {
                            $kas_akun_uuid = $transaksi_penjualan_default_kas_akun_uuid;
                            foreach ($kas_akun_list as $l) {
                                $selected = "";
                                if ($l['uuid'] == $kas_akun_uuid) $selected = "selected";
                        ?>
                                <option value="<?= $l['uuid'] ?>" <?= $selected ?>><?= $l['nama'] ?></option>
                        <?php
                            }
                        }

                        ?>
                    </select>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <h5 class="mt-4">Default Transaksi</h5>
            <div class="form-group row">
                <label class="col-form-label col-sm-5">Gudang Transaksi Pembelian</label>

                <div class="col-sm-7">
                    <select name="transaksi_pembelian_default_gudang_uuid" class="form-control select2">
                        <?php
                        if (isset($gudang_list) && is_array($gudang_list)) {
                            $gudang_uuid = $transaksi_pembelian_default_gudang_uuid;
                            foreach ($gudang_list as $l) {
                                $selected = "";
                                if ($l['uuid'] == $transaksi_pembelian_default_gudang_uuid) $selected = "selected";
                        ?>
                                <option value="<?= $l['uuid'] ?>" <?= $selected ?>><?= $l["kode"] ?> - <?= $l['nama'] ?></option>
                        <?php
                            }
                        }

                        ?>
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <label class="col-form-label col-sm-5">Gudang Transaksi Penjualan</label>

                <div class="col-sm-7">
                    <select name="transaksi_penjualan_default_gudang_uuid" class="form-control select2">
                        <?php
                        if (isset($gudang_list) && is_array($gudang_list)) {
                            $gudang_uuid = $transaksi_penjualan_default_gudang_uuid;
                            foreach ($gudang_list as $l) {
                                $selected = "";
                                if ($l['uuid'] == $transaksi_penjualan_default_gudang_uuid) $selected = "selected";
                        ?>
                                <option value="<?= $l['uuid'] ?>" <?= $selected ?>><?= $l["kode"] ?> - <?= $l['nama'] ?></option>
                        <?php
                            }
                        }

                        ?>
                    </select>
                </div>
            </div>
            
            <div class="form-group row">
                <label class="col-form-label col-sm-5">Pelanggan Transaksi Penjualan</label>

                <div class="col-sm-7">
                    <select name="transaksi_penjualan_default_pelanggan_uuid" class="form-control select2">
                        <?php
                        if (isset($pelanggan_list) && is_array($pelanggan_list)) {
                            $pelanggan_uuid = $transaksi_penjualan_default_pelanggan_uuid;
                            foreach ($pelanggan_list as $l) {
                                $selected = "";
                                if ($l['uuid'] == $transaksi_penjualan_default_pelanggan_uuid) $selected = "selected";
                        ?>
                                <option value="<?= $l['uuid'] ?>" <?= $selected ?>><?= $l["number_formatted"] ?> - <?= $l['nama'] ?></option>
                        <?php
                            }
                        }

                        ?>
                    </select>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-form-label col-sm-5">Pemasok Transaksi Penjualan</label>

                <div class="col-sm-7">
                    <select name="transaksi_penjualan_default_pemasok_uuid" class="form-control select2">
                        <?php
                        if (isset($pemasok_list) && is_array($pemasok_list)) {
                            $pemasok_uuid = $transaksi_penjualan_default_pemasok_uuid;
                            foreach ($pemasok_list as $l) {
                                $selected = "";
                                if ($l['uuid'] == $transaksi_penjualan_default_pemasok_uuid) $selected = "selected";
                        ?>
                                <option value="<?= $l['uuid'] ?>" <?= $selected ?>><?= $l["number_formatted"] ?> - <?= $l['nama'] ?></option>
                        <?php
                            }
                        }

                        ?>
                    </select>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <h5 class="mt-4">Default Gudang Persediaan</h5>
            <div class="form-group row">
                <label class="col-form-label col-sm-5">Gudang Stock Awal</label>

                <div class="col-sm-7">
                    <select name="persediaan_stock_awal_default_gudang_uuid" class="form-control select2">
                        <?php
                        if (isset($gudang_list) && is_array($gudang_list)) {
                            $gudang_uuid = $persediaan_stock_awal_default_gudang_uuid;
                            foreach ($gudang_list as $l) {
                                $selected = "";
                                if ($l['uuid'] == $persediaan_stock_awal_default_gudang_uuid) $selected = "selected";
                        ?>
                                <option value="<?= $l['uuid'] ?>" <?= $selected ?>><?= $l["kode"] ?> - <?= $l['nama'] ?></option>
                        <?php
                            }
                        }

                        ?>
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <label class="col-form-label col-sm-5">Gudang Stock Opname</label>

                <div class="col-sm-7">
                    <select name="persediaan_stock_opname_default_gudang_uuid" class="form-control select2">
                        <?php
                        if (isset($gudang_list) && is_array($gudang_list)) {
                            $gudang_uuid = $persediaan_stock_opname_default_gudang_uuid;
                            foreach ($gudang_list as $l) {
                                $selected = "";
                                if ($l['uuid'] == $persediaan_stock_opname_default_gudang_uuid) $selected = "selected";
                        ?>
                                <option value="<?= $l['uuid'] ?>" <?= $selected ?>><?= $l["kode"] ?> - <?= $l['nama'] ?></option>
                        <?php
                            }
                        }

                        ?>
                    </select>
                </div>
            </div>
        </div>
    </div>
</form>

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
        $("#modal-cabang-title").html('<?= $title ?>');

        $(".select2").select2({
            theme: 'classic'
        });
    });

    function yearUp() {
        var curr = parseInt($("#number").val());
        $("#number").val(curr + 1);
    }

    function yearDown() {
        var curr = parseInt($("#number").val());
        $("#number").val(curr - 1);
    }

    function save() {
        var form_data = $("#form-cabang-detail").serializeArray();
        ajax_post(
            '<?= base_url("admin/cabang/ajax_save") ?>',
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