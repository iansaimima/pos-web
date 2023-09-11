<?php

$user = get_session("user");
$privilege_list = isset($user["privilege_list"]) ? $user["privilege_list"] : array();
$allow_create = isset($privilege_list["allow_stock_awal_create"]) ? $privilege_list["allow_stock_awal_create"] : 0;
$allow_update = isset($privilege_list["allow_stock_awal_update"]) ? $privilege_list["allow_stock_awal_update"] : 0;
$allow_set_arsip = isset($privilege_list["allow_stock_awal_set_arsip"]) ? $privilege_list["allow_stock_awal_set_arsip"] : 0;
$allow_set_aktif = isset($privilege_list["allow_stock_awal_set_aktif"]) ? $privilege_list["allow_stock_awal_set_aktif"] : 0;

$cabang_selected = get_session("cabang_selected");
$gudang_uuid = $cabang_selected["persediaan_stock_awal_default_gudang_uuid"];

$uuid = "";
$item_uuid = "";
$item_nama = "";
$item_kode = "";
$item_kategori_nama = "";
$jumlah = 1;
$selectedSatuan = "";
$harga_beli = 0;
$total = 0;

$title = "Baru";

$struktur_satuan_harga_list = array();
$struktur_harga_list = array();
$satuan_list = array();
if (isset($detail) && is_array($detail) && count($detail) > 0) {
    $uuid = trim($detail["uuid"]);
    $item_uuid = trim($detail["item_uuid"]);
    $gudang_uuid = trim($detail["gudang_uuid"]);
    $item_nama = $detail["item_nama"];
    $item_kode = $detail["item_kode"];
    $item_kategori_nama = $detail["item_kategori_nama"];
    $jumlah = (int) $detail["jumlah"];
    $selectedSatuan = $detail["satuan"];
    $harga_beli = (float) $detail["harga_beli_satuan"];

    $struktur_satuan_harga_list = json_decode($detail["item_struktur_satuan_harga_json"], true);

    $title = "Ubah";
}

if (count($struktur_satuan_harga_list) > 0) {
    foreach ($struktur_satuan_harga_list as $satuan => $s) {
        $struktur_harga_list[$satuan] = $s["harga_pokok"];

        $row = array(
            "name" => $satuan,
            "label" => $s["satuan"],
            "harga_jual" => $s["harga_jual"],
            "harga_beli" => $s["harga_pokok"],
            "selected" => $satuan == strtoupper($selectedSatuan) ? "selected" : ""
        );
        $satuan_list[] = $row;
    }
}

$total = $harga_beli * $jumlah;
?>

<div class="row">
    <div class="col-md-5 col-sm-12 col-xs-12">
        <?php
        if (!empty($uuid)) {
        ?>
            <div class="alert alert-warning solid">
                <h5 class="text-white"><i class="fa fa-warning"></i> &nbsp; Perhatian</h5>
                <p>
                    Mengubah stock awal item dapat mempengaruhi stock dan harga pokok item.
                </p>
            </div>
        <?php
        }
        ?>
        <div class="card height-auto">
            <div class="card-header border-0 pb-0">
                <h5 class="card-title">
                    <a href="javascript:void(0)" onclick="load_list()"><i class="fa fa-arrow-left "></i></a> &nbsp; Detail Stock Awal - <?= $title ?>
                </h5>
            </div>
            <div class="card-body">

                <h3 style="margin-top: 0; margin-bottom: 20px" class="text-danger">Saldo awal per tanggal <?= date("d-m-Y", strtotime($tanggal_mulai_penggunaan_aplikasi)) ?></h3>

                <form onsubmit="return false" class="form-horizontal" id="form-stock-awal" autocomplete="off">
                    <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
                    <input type="hidden" name="uuid" value="<?= $uuid ?>">

                    <div class="form-group row mb-2">
                        <label class="col-sm-5 col-form-label">Masuk ke Gudang</label>
                        <div class="col-sm-7">
                            <select name="gudang_uuid" class="form-control select2">
                                <option value="" selected disabled>Pilih Gudang</option>
                                <?php
                                if (isset($gudang_list) && is_array($gudang_list)) {
                                    foreach ($gudang_list as $p) {
                                        $selected = "";
                                        if ($p['uuid'] == $gudang_uuid) $selected = "selected";
                                ?>
                                        <option value="<?= $p['uuid'] ?>" <?= $selected ?>>[<?= $p["kode"] ?>] <?= $p['nama'] ?></option>
                                <?php
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group row mb-2">
                        <label class="col-sm-5 col-form-label">Kode</label>
                        <div class="col-sm-7">
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control input-search" name="item_kode" id="input-kode-item" value="<?= $item_kode ?>" <?= (!empty($uuid)) ? "readonly" : "" ?> />
                                <?php
                                if (empty($uuid)) {
                                ?>
                                    <div class="input-group-prepend">
                                        <!-- <button class="btn btn-primary no-shadow" onclick="popupSearchItem()" style="border-radius: 0 !important" type="button"><i class="fa fa-search"></i></button> -->
                                        <button class="btn btn-success no-shadow" onclick="searchItem($('#input-kode-item').val())" style="border-radius: 0 !important" type="button"><i class="fa fa-check"></i></button>
                                        <button class="btn btn-outline-secondary no-shadow" onclick="clearForm()" style="border-radius: 0 4px 4px 0 !important" type="button"><i class="fa fa-times"></i></button>
                                    </div>
                                <?php
                                }
                                ?>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row mb-2">
                        <label class="col-sm-5 col-form-label">Nama</label>
                        <div class="col-sm-7">
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control input-search" id="item-nama" value="<?= $item_nama ?>" disabled>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row mb-2">
                        <label class="col-sm-5 col-form-label">Kategori</label>
                        <div class="col-sm-7">
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control input-search" id="item-kategori" value="<?= $item_kategori_nama ?>" disabled>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row mb-2">
                        <label class="col-sm-5 col-form-label">Jumlah</label>
                        <div class="col-sm-7">
                            <div class="input-group input-group-sm">
                                <input type="text" style="text-align: right;" name="jumlah" class="form-control input-search input-currency" id="jumlah" onkeyup="hitungTotal(event)" value="<?= number_format($jumlah, 0, ',', '.') ?>">
                            </div>
                        </div>
                    </div>

                    <div class="form-group row mb-2">
                        <label class="col-sm-5 col-form-label">Satuan</label>
                        <div class="col-sm-7">
                            <div class="input-group input-group-sm">
                                <select name="satuan" id="input-satuan" class="form-control select-search" onchange="setHargaSatuan($(this).val())"></select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row mb-2">
                        <label class="col-sm-5 col-form-label">Harga Beli Satuan</label>
                        <div class="col-sm-7">
                            <div class="input-group input-group-sm">
                                <input type="text" style="text-align: right;" name="harga_beli" class="form-control input-search input-currency" onkeyup="hitungTotal(event)" id="harga-beli" value="<?= number_format($harga_beli, 0, ',', '.') ?>">
                            </div>
                        </div>
                    </div>

                    <div class="form-group row mb-2">
                        <label class="col-sm-5 col-form-label">Total</label>
                        <div class="col-sm-7">
                            <div class="input-group input-group-sm">
                                <input type="text" style="text-align: right;" class="form-control input-search" id="total" value="<?= number_format($total, 0, ',', '.') ?>" disabled>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="card-footer border-0">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group row mb-2">
                            <div class="col-sm-5">
                                <?php
                                if (!empty($harga_beli)) {
                                ?>
                                    <button type="button" class="btn btn-sm btn-danger light" onclick="confirmDelete('<?= $uuid ?>')"> <i class="fa fa-trash"></i> Hapus</button>
                                <?php
                                }
                                ?>
                            </div>
                            <div class="col-sm-7" style="text-align: right;">

                                <button type="button" class="btn btn-sm btn-secondary light" onclick="load_list()"> <i class="fa fa-times"></i> Batal</button>
                                <button type="button" class="btn btn-sm btn-success" onclick="save()"> <i class="fa fa-save"></i> Simpan</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    var _ItemKode = '';
    var _ItemNama = '';
    var _ItemKategori = '';
    var _Jumlah = 0;
    var _Satuan = '';
    var _HargaBeli = 0;
    var _Total = 0;

    var itemStrukturHargaList = <?= count($struktur_harga_list) == 0 ? '{}' : json_encode($satuan_list) ?>;
    var satuan_list = <?= count($satuan_list) == 0 ? "[]" : json_encode($satuan_list) ?>;
    $(document).ready(function() {

        $(".select2").select2({
            // dropdownparent: $("#modal-detail"),
            theme: 'classic',
        });

        $("#input-kode-item").on("keyup", function(e) {
            if (e.keyCode == 13) {
                searchItem($(this).val());
            } else {
                $("#item-nama").html("");
                $("#item-kategori").html("");
            }
        });

        var i;
        for (i = 0; i < satuan_list.length; i++) {
            var satuan = satuan_list[i];
            var selected = satuan.selected;
            $("#input-satuan").append("<option value='" + satuan.name + "' " + selected + ">" + satuan.label + "</option>");
        }

        $(".input-currency").on("keyup", function() {
            let val = $(this).val();
            $(this).val(formatCurrency(val));
        });
    });

    function clearForm() {
        $(".input-search").val('');
        $(".select-search").html('');
        $("#jumlah").val(1);
    }

    function save() {
        var form_data = $("#form-stock-awal").serializeArray();

        ajax_post(
            '<?= base_url("admin/stock_awal/ajax_save") ?>',
            form_data,
            function(resp) {
                console.log(resp);
                try {
                    var json = JSON.parse(resp);
                    if (json.is_success == 1) {
                        show_toast("Success", json.message, "success");
                        load_detail(json.id);
                    } else {
                        show_toast("Error", json.message, "error");
                    }
                } catch (error) {
                    console.log(error);
                    show_toast("Error", "Application response error", "error");
                }
            }
        );
    }

    function confirmDelete(uuid) {
        var confirmed = confirm("Anda yakin ingin menghapus data ini ? \nProses ini akan mengubah harga pokok dan stock pada item terkait");
        if (!confirmed) return;

        ajax_get(
            '<?= base_url('admin/stock_awal/ajax_delete') ?>/' + uuid, {},
            function(resp) {
                try {
                    var json = JSON.parse(resp);
                    if (json.is_success == 1) {
                        show_toast("Success", json.message, "success");
                        load_list();
                    } else {
                        show_toast("Error", json.message, "error");
                    }
                } catch (error) {
                    show_toast("Error", "Application response error", "error");
                }
            }
        )
    }

    function setHargaSatuan(_satuan) {
        var selectedHargaSatuan = itemStrukturHargaList[_satuan];
        _HargaBeli = selectedHargaSatuan;
        $("#harga-beli").val(formatCurrency(selectedHargaSatuan + ""));

        doHitungTotal();
    }

    function hitungTotal(e) {
        doHitungTotal();
    }

    function doHitungTotal() {
        _Jumlah = toNumber($("#jumlah").val());
        _HargaBeli = toNumber($("#harga-beli").val());

        _Total = _Jumlah * _HargaBeli;
        $("#total").val(formatCurrency(_Total + ""));
    }

    <?php
    if (empty($uuid)) {
    ?>
        // open a pop up window
        function popupSearchItem(kode) {
            if (kode == null || kode == undefined) kode = '';

            var targetField = document.getElementById("input-kode-item");
            var w = window.open('<?= base_url('admin/item/popup_list_flow_stock_in') ?>?q=' + kode, '_blank', 'width=1000,height=400,scrollbars=1');
            // pass the targetField to the pop up window
            w.targetField = targetField;
            w.focus();
        }

        // this function is called by the pop up window
        function setSearchResult(targetField, returnValue) {
            targetField.value = returnValue;
            searchItem(returnValue);
            window.focus();
        }

        function searchItem(kode) {
            if (kode == null || kode == undefined) return;

            $("#input-satuan").html("");

            ajax_get(
                '<?= base_url("admin/item/search_flow_stock_in") ?>/', {
                    kode: kode
                },
                function(json) {
                    try {
                        if (json.is_success == 1) {
                            let data_list = json.data;

                            if (data_list.length > 1) {
                                popupSearchItem(kode);
                                return;
                            } else if (data_list.length == 0) {
                                popupSearchItem(kode);
                                return;
                            }

                            let data = data_list[0];
                            _ItemKode = data.kode;
                            $("#input-kode-item").val(data.kode);
                            $("#item-nama").val(data.nama);
                            $("#item-kategori").val(data.nama_kategori);

                            itemStrukturHargaList = data.harga_list;
                            satuan_list = data.satuan_list;

                            _ItemNama = data.nama;
                            _ItemKategori = data.nama_kategori;

                            var i;
                            for (i = 0; i < satuan_list.length; i++) {
                                var satuan = satuan_list[i];
                                $("#input-satuan").append("<option value='" + satuan.name + "'>" + satuan.label + "</option>");
                            }

                            var selectedSatuan = $("#input-satuan:first").val();
                            var selectedHargaSatuan = data.harga_list[selectedSatuan];

                            _HargaBeli = selectedHargaSatuan;

                            $("#harga-beli").val(formatCurrency(selectedHargaSatuan + ""));

                            doHitungTotal();
                        } else {

                            switch (json.message) {
                                case "NO_DATA":
                                    popupSearchItem();
                                    break;
                                case "EMPTY_KODE":
                                    show_toast("Perhatian", "Kode item tidak boleh kosong", "warning");
                                    break;
                                case "EMPTY_KEYWORD":
                                    show_toast("Perhatian", "Kode item tidak boleh kosong", "warning");
                                    break;
                                default:
                                    show_toast("Error", json.message, "error");
                                    break;
                            }
                        }
                    } catch (error) {

                    }
                }
            );
        }
    <?php
    }
    ?>
</script>