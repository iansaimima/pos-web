<?php
$cabang_selected = get_session("cabang_selected");

$uuid = "";
$no_transfer = "Otomatis";
$tanggal = date("Y-m-d");
$dari_gudang_uuid = "";
$ke_gudang_uuid = "";
$keterangan = "";

$item_detail_list = array();
if (isset($detail) && is_array($detail) && count($detail) > 0) {
    $uuid = trim($detail["uuid"]);
    $dari_gudang_uuid = trim($detail["dari_gudang_uuid"]);
    $ke_gudang_uuid = trim($detail["ke_gudang_uuid"]);
    $no_transfer = $detail["number_formatted"];
    $tanggal = $detail["tanggal"];
    $keterangan = trim($detail["keterangan"]);

    $detail_list = $detail["detail"];

    foreach ($detail_list as $dl) {
        $harga_beli = (float) $dl["harga_beli_satuan"];
        $jumlah = (float) $dl["jumlah"];

        $total = $jumlah * ($harga_beli);
        // if($potongan_persen > 0 && $harga_beli > 0){
        //     $potongan_harga = $harga_beli * ($potongan_persen / 100);
        //     $total = $jumlah * ($harga_beli - $potongan_harga);
        // }
        $total = (int) $total;
        $row = array(
            "item_code" => $dl["item_kode"],
            "item_nama" => $dl["item_nama"],
            "item_kategori" => $dl["item_kategori_nama"],
            "jumlah" => $dl["jumlah"],
            "jumlah_formatted" => number_format($dl["jumlah"], 0, ',', '.'),
            "satuan" => $dl["satuan"],
            "harga_beli" => $dl["harga_beli_satuan"],
            "harga_beli_formatted" => number_format($dl["harga_beli_satuan"], 0, ',', '.'),
            "total" => $total,
            "total_formatted" => number_format($total, 0, ',', '.'),
        );

        $item_detail_list[$dl['item_kode'] . "-" . strtoupper($dl['satuan'])] = $row;
    }
}


?>
<div class="modal-header" style="background-color: #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.3)">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
    <h3 class="modal-title"><span id="modal-item-transfer-title"></span> Item Transfer</h3>
</div>

<div class="modal-body hide-scrollbar" style="min-height: calc(100vh - 125px); width: 100%; overflow: auto; padding: 8px">
    <form onsubmit="return false" id="form-item-transfer" class="form-horizontal" autocomplete="off">
        <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
        <input type="hidden" name="uuid" value="<?= $uuid ?>" />
        <div class="row">
            <div class="col-md-12">
                <div class="card mb-10p">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group row mb-2">
                                    <label class="col-sm-5 col-form-label">No. Item Transfer</label>
                                    <div class="col-sm-7">
                                        <div class="input-group input-group-sm">
                                            <input type="text" class="form-control input-sm" value="<?= $no_transfer ?>" readonly />
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row mb-2">
                                    <label class="col-sm-5 col-form-label">Tanggal</label>
                                    <div class="col-sm-7" style="margin-bottom: 4px;">
                                        <div class="input-group input-group-sm">
                                            <input type="text" class="form-control input-sm" name="tanggal" placeholder="yyyy-mm-dd" id="datepicker-tanggal" value="<?= date("Y-m-d", strtotime($tanggal)) ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group row mb-2">
                                    <label class="col-sm-3 col-form-label">Keluar Dari</label>
                                    <div class="col-sm-8">
                                        <div class="input-group input-group-sm">
                                            <select name="dari_gudang_uuid" class="form-control select2">
                                                <option value="" selected disabled>Pilih Gudang</option>
                                                <?php
                                                if (isset($gudang_list) && is_array($gudang_list)) {
                                                    foreach ($gudang_list as $p) {
                                                        $selected = "";
                                                        if ($p['uuid'] == $dari_gudang_uuid) $selected = "selected";
                                                ?>
                                                        <option value="<?= $p['uuid'] ?>" <?= $selected ?>>[<?= $p["kode"] ?>] <?= $p['nama'] ?></option>
                                                <?php
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row mb-2">
                                    <label class="col-sm-3 col-form-label">Masuk Ke</label>
                                    <div class="col-sm-8">
                                        <div class="input-group input-group-sm">
                                            <select name="ke_gudang_uuid" class="form-control select2">
                                                <option value="" selected disabled>Pilih Gudang</option>
                                                <?php
                                                if (isset($gudang_list) && is_array($gudang_list)) {
                                                    foreach ($gudang_list as $p) {
                                                        $selected = "";
                                                        if ($p['uuid'] == $ke_gudang_uuid) $selected = "selected";
                                                ?>
                                                        <option value="<?= $p['uuid'] ?>" <?= $selected ?>>[<?= $p["kode"] ?>] <?= $p['nama'] ?></option>
                                                <?php
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-5">

                                <div class="form-group row mb-2">
                                    <label class="col-sm-3 col-form-label">Keterangan</label>
                                    <div class="col-sm-9">
                                        <textarea rows="3" class="form-control input-sm" name="keterangan"><?= $keterangan ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="row">
            <div class="col-md-12">
                <div class="card height-auto mb-0">
                    <div class="card-body pl-3 pr-3 pt-2">
                        <table class="table table-bordered table-item-transfer" id="table-item-detail">
                            <thead>
                                <tr>
                                    <th style="width: 220px;">Kode</th>
                                    <th>Nama</th>
                                    <th>Kategori</th>
                                    <th style="width: 100px; text-align: right;">Jumlah</th>
                                    <th style="width: 120px">Satuan</th>
                                    <th style="width: 160px; text-align: right;">Harga Satuan</th>
                                    <th style="width: 80px"></th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>

                            <tfoot>
                                <tr>
                                    <td colspan="7" style="padding: 4px;background: #ecf0f5"></td>
                                </tr>
                                <tr>
                                    <td colspan="7"><b>Tambah item</b></td>
                                </tr>
                                <tr>
                                    <td style="background-color: #E8F5E9;">
                                        <div class="input-group form-group-item-transfer">
                                            <input type="text" class="form-control input-sm" id="input-kode-item" />
                                            <span class="input-group-append">
                                                <button class="btn btn-success no-shadow" onclick="searchItem($('#input-kode-item').val())" style="border-radius: 0 !important" type="button"><i class="fa fa-check"></i></button>
                                                <button class="btn btn-outline-secondary no-shadow" onclick="clearNewItem()" style="border-radius: 0 4px 4px 0 !important" type="button"><i class="fa fa-times"></i></button>
                                            </span>
                                        </div>
                                    </td>
                                    <td style=" padding-top: 4px !important;"><span id="new-item-nama"></span></td>
                                    <td style=" padding-top: 4px !important;"><span id="new-item-kategori"></span></td>
                                    <td style="background-color: #E8F5E9;">
                                        <div class="input-group form-group-item-transfer">
                                            <input type="text" class="form-control input-currency input-sm" id="new-item-jumlah" style="text-align: right" />
                                        </div>
                                    </td>
                                    <td style="background-color: #E8F5E9;">
                                        <div class="input-group form-group-item-transfer">
                                            <select class="form-control input-sm" id="input-satuan" onchange="setHargaSatuan($(this).val())">
                                            </select>
                                        </div>
                                    </td>
                                    <td style="background-color: #E8F5E9;">
                                        <div class="input-group form-group-item-transfer">
                                            <input type="text" class="form-control input-currency input-sm" id="new-item-harga-beli" style="text-align: right" />
                                        </div>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <button type="button" id="btn-add-new-item" class="btn btn-primary btn-sm" onclick="addNewItem()"><i class="fa fa-arrow-up"></i></button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <span><i class="fa fa-search"></i> = Tampilkan pencarian</span> <br />
                                        <span><i class="fa fa-check"></i> = Cari</span> <br />
                                    </td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>

                                <tr>
                                    <td colspan="7" style="padding: 8px;background: #ecf0f5"></td>
                                </tr>

                                <tr>
                                    <td colspan="7" style="border: none;">
                                        <div class="row pt-0" style="margin-top: 0;">
                                            <div class="col-md-6 col-sm-6" style="text-align: left;">
                                                <?php
                                                if (!empty($uuid)) {
                                                ?>
                                                    <button type="button" class="btn btn-danger" onclick="confirmDelete('<?= $uuid ?>')"><i class="fa fa-trash"></i> Hapus Item Transfer</button>
                                                <?php
                                                }
                                                ?>
                                            </div>

                                            <div class="col-md-6 col-sm-6" style="text-align: right;">
                                                <button type="button" class="btn btn-secondary light" data-dismiss="modal" aria-label="Close" style="margin-bottom: 0"><i class="fa fa-times"></i> Batal</button>
                                                <button type="button" class="btn btn-success" style="margin-bottom: 0" onclick="save()"><i class="fa fa-save"></i> Simpan Item Transfer</button>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
    .select2-container {
        width: 100% !important;
        padding: 0;
    }

    .form-group-item-transfer .form-control {
        font-size: 9pt;
        padding: 4px;
        height: auto;
        border-radius: 4px;
    }

    .form-group-item-transfer .input-group-append .btn {
        padding: 4px;
        height: 28px;
        border-radius: 4px;
    }

    .form-group-item-transfer .col-form-label {
        font-size: 9pt;
        color: #333;
    }

    .font-9pt {
        font-size: 9pt !important;
        color: #333;
    }

    .form-group-item-transfer select.select2 option {
        font-size: 9pt;
    }

    .table-item-transfer>thead>tr>th,
    .table-item-transfer>tbody>tr>td,
    .table-item-transfer>tfoot>tr>td {
        font-size: 9pt !important;
        color: #333;
        padding: 4px;
    }

    .table-item-transfer>tbody>tr>td .btn,
    .table-item-transfer>tfoot>tr>td .btn {
        font-size: 9pt !important;
        height: 28px !important;
        line-height: 0;
    }

    .select2-dropdown {
        font-size: 9pt;
    }
</style>

<script>
    var itemDetailData = <?= empty($uuid) ? '{}' : json_encode($item_detail_list) ?>;

    var newItemKode = '';
    var newItemNama = '';
    var newItemKategori = '';
    var newJumlah = 0;
    var newSatuan = '';
    var newHargaBeli = 0;

    var itemStrukturHargaList = {};
    var itemStrukturStockList = {};
    $(document).ready(function() {
        if (itemDetailData.length == 0) {
            itemDetailData = {};
        }
        $("#input-kode-item").on("keyup", function(e) {
            if (e.keyCode == 13) {
                searchItem($(this).val());
            } else {
                $("#new-item-nama").html("");
                $("#new-item-kategori").html("");
            }
        });

        $('#datepicker-tanggal').bootstrapMaterialDatePicker({
            format: 'YYYY-MM-DD',
            weekStart: 0,
            time: false
        });

        $(".select2").select2({
            dropdownparent: $("#modal-detail"),
            theme: 'classic',
        });

        $(".input-currency").on("keyup", function() {
            let val = $(this).val();
            $(this).val(formatCurrency(val));
        });

        <?php if (!empty($uuid)) { ?>
            updateTableItemDetailList();
        <?php } ?>
    });

    // open a pop up window
    function popupSearchItem(_kode) {
        if (_kode == null || _kode == undefined) _kode = '';

        var targetField = document.getElementById("input-kode-item");
        var w = window.open('<?= base_url('admin/item/popup_list_flow_stock_in') ?>?q=' + _kode, '_blank', 'width=1000,height=400,scrollbars=1');
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

    function setHargaSatuan(_satuan) {
        var selectedHargaSatuan = itemStrukturHargaList[newItemKode][_satuan];
        $("#new-item-harga-beli").val(formatCurrency(selectedHargaSatuan));
    }

    function clearNewItem() {
        newItemKode = '';
        newItemNama = '';
        newItemKategori = '';
        newJumlah = 0;
        newSatuan = '';
        newHargaBeli = 0;
        newTotal = 0;

        $("#input-kode-item").val('');
        $("#new-item-nama").html('');
        $("#new-item-kategori").html('');
        $("#new-item-jumlah").val('');
        $("#input-satuan").html('');
        $("#new-item-harga-beli").val('');

    }

    function addNewItem() {
        newSatuan = $("#input-satuan").val();
        if (newSatuan == "") return;
        if (newItemKode == "") return;
        // if (newTotal == 0) return;

        newJumlah = toNumber($("#new-item-jumlah").val());
        newHargaBeli = toNumber($("#new-item-harga-beli").val());

        var key = newItemKode + "-" + newSatuan;

        itemDetailData[key] = {
            item_code: newItemKode,
            item_nama: newItemNama,
            item_kategori: newItemKategori,
            jumlah: newJumlah,
            jumlah_formatted: formatCurrency(newJumlah),
            satuan: newSatuan,
            harga_beli: newHargaBeli,
            harga_beli_formatted: formatCurrency(newHargaBeli),
        }

        updateTableItemDetailList();
        clearNewItem();

        $("#btn-add-new-item").focus();
    }

    function deleteItem(itemCode) {
        var confirmed = confirm("Anda yakin ingin menghapus item ini ?");
        if (!confirmed) return;

        if (itemDetailData.hasOwnProperty(itemCode)) {
            delete itemDetailData[itemCode];

            updateTableItemDetailList();
        }
    }

    function updateTableItemDetailList() {

        var tableBodyList = [];

        var rowNumber = 1;

        subTotal = 0;

        $.each(itemDetailData, function(key, a) {
            var itemDetail = itemDetailData[key];

            var _itemKode = itemDetail['item_code'];
            var _itemNama = itemDetail["item_nama"];
            var _itemKategori = itemDetail['item_kategori'];
            var _jumlah = itemDetail['jumlah'];
            var _jumlah_formatted = itemDetail['jumlah_formatted'];
            var _satuan = itemDetail['satuan'];
            var _hargaBeli = itemDetail['harga_beli_formatted'];

            tableBodyList.push(
                `<tr>` +
                `   <td><a href='javascript:void(0);' onclick='searchItem("` + _itemKode + `","` + _satuan + `")'>` + _itemKode + `</a></td>` +
                `   <td>` + _itemNama + `</td>` +
                `   <td>` + _itemKategori + `</td>` +
                `   <td align='right'>` + _jumlah_formatted + `</td>` +
                `   <td>` + _satuan + `</td>` +
                `   <td align='right'>` + _hargaBeli + `</td>` +
                `   <td>` +
                `       <button type="button" class="btn btn-danger btn-sm" onclick="deleteItem('` + _itemKode + '-' + _satuan + `')"><i class="fa fa-times"></i></button>` +
                `   </td>` +
                `</tr>`
            );

            rowNumber++;
        });

        $("#table-item-detail tbody").html(tableBodyList.join(''));
    }


    function searchItem(_kode, _satuan) {
        if (_kode == null || _kode == undefined) return;
        if (_satuan == null || _satuan == null) _satuan = "";

        $("#input-satuan").html("");

        ajax_get(
            '<?= base_url("admin/item/search_flow_stock_in") ?>/', {
                kode: _kode
            },
            function(json) {
                try {
                    if (json.is_success == 1) {
                        let data_list = json.data;

                        if (data_list.length > 1) {
                            popupSearchItem(_kode);
                            return;
                        } else if (data_list.length == 0) {
                            popupSearchItem(_kode);
                            return;
                        }
                        console.log(data_list);

                        let data = data_list[0];

                        newItemKode = data.kode;
                        $("#new-item-nama").html(data.nama);
                        $("#new-item-kategori").html(data.nama_kategori);

                        newItemNama = data.nama;
                        newItemKategori = data.nama_kategori;

                        itemStrukturHargaList[data.kode] = data.harga_list;
                        itemStrukturStockList[data.kode] = data.stock_list;

                        var i;
                        var _selectedSatuan = "";
                        for (i = 0; i < data.satuan_list.length; i++) {
                            var satuan = data.satuan_list[i];
                            if (_selectedSatuan == "") _selectedSatuan = satuan.name;
                            $("#input-satuan").append("<option value='" + satuan.name + "'>" + satuan.label + "</option>");
                        }
                        setHargaSatuan(_selectedSatuan);
                        $("#new-item-jumlah").val(1);
                        $("#new-item-jumlah").focus();
                        
                        if(itemDetailData.hasOwnProperty(newItemKode + "-" + _satuan)){
                            let _selectedItemDetailData = itemDetailData[newItemKode + "-" + _satuan];
                            let _selectedJumlah = _selectedItemDetailData['jumlah'];
                            let _selectedHargaBeli = _selectedItemDetailData["harga_beli"];
                            newHargaBeli = _selectedHargaBeli;

                            $("#new-item-jumlah").val(_selectedJumlah);
                            $("#new-item-harga-beli").val(formatCurrency(_selectedHargaBeli));
                            $("#input-satuan").val(_satuan);
                        }
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
                    console.log(error);
                }
            }
        );
    }

    function confirmDelete(uuid) {
        var confirmed = confirm("Anda yakin ingin menghapus pembelian ini ? ");
        if (!confirmed) return;

        ajax_get(
            '<?= base_url('admin/item_transfer/ajax_delete') ?>/' + uuid, {},
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
        )
    }

    function save() {
        var formData = $("#form-item-transfer").serializeArray();

        var itemDetailList = [];
        $.each(itemDetailData, function(itemKode) {
            itemDetailList.push(itemDetailData[itemKode]);
        });

        formData.push({
            name: 'item_detail',
            value: JSON.stringify(itemDetailList)
        });

        ajax_post(
            '<?= base_url('admin/item_transfer/ajax_save') ?>',
            formData,
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