<?php
$cabang_selected = get_session("cabang_selected");
$gudang_uuid = $cabang_selected["persediaan_stock_opname_default_gudang_uuid"];

$uuid = "";
$no_stock_opname = "Otomatis";
$tanggal = date("Y-m-d");
$keterangan = "";

$item_detail_list = array();
if (isset($detail) && is_array($detail) && count($detail) > 0) {
    $uuid = trim($detail["uuid"]);
    $gudang_uuid = trim($detail["gudang_uuid"]);
    $no_stock_opname = $detail["number_formatted"];
    $tanggal = $detail["tanggal"];
    $keterangan = trim($detail["keterangan"]);

    $detail_list = $detail["detail"];

    foreach ($detail_list as $dl) {
        $row = array(
            "item_code" => $dl["item_kode"],
            "item_nama" => $dl["item_nama"],
            "item_kategori" => $dl["item_kategori_nama"],
            "satuan" => $dl["satuan_terkecil"],
            "stock_system_satuan_terkecil" => $dl["stock_system_satuan_terkecil"],
            "stock_system_satuan_terkecil_formatted" => number_format($dl["stock_system_satuan_terkecil"], 0, ',', '.'),
            "stock_fisik_satuan_terkecil" => $dl["stock_fisik_satuan_terkecil"],
            "stock_fisik_satuan_terkecil_formatted" => number_format($dl["stock_fisik_satuan_terkecil"], 0, ',', '.'),
            "stock_selisih_satuan_terkecil" => $dl["stock_selisih_satuan_terkecil"],
            "stock_selisih_satuan_terkecil_formatted" => number_format($dl["stock_selisih_satuan_terkecil"], 0, ',', '.'),
        );

        $item_detail_list[$dl['item_kode']] = $row;
    }
}

$uri_1 = $this->uri->segment(1);

?>
<div class="modal-header" style="background-color: #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.3)">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
    <h3 class="modal-title"><span id="modal-stock-opname-title"></span> Stock Opname</h3>
</div>

<div class="modal-body hide-scrollbar">
    <form onsubmit="return false" id="form-stock-opname" class="form-horizontal" autocomplete="off">
        <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
        <input type="hidden" name="uuid" value="<?= $uuid ?>" />
        <div class="row">
            <div class="col-md-12">
                <div class="card mb-10p">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-md-5">
                                <div class="form-group row mb-0 form-group-stock-opname">
                                    <label class="col-sm-3 col-form-label">No. Stock Opname</label>
                                    <div class="col-sm-9">
                                        <div class="input-group input-group-sm">
                                            <input type="text" class="form-control input-sm" value="<?= $no_stock_opname ?>" readonly />
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row mb-0 form-group-stock-opname">
                                    <label class="col-sm-3 col-form-label">Tanggal</label>
                                    <div class="col-sm-9" style="margin-bottom: 4px;">
                                        <div class="input-group input-group-sm">
                                            <input type="text" class="form-control input-sm" name="tanggal" placeholder="yyyy-mm-dd" id="datepicker-tanggal" value="<?= date("Y-m-d", strtotime($tanggal)) ?>">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group row mb-0 form-group-stock-opname">
                                    <label class="col-sm-3 col-form-label">Gudang</label>
                                    <div class="col-sm-9">
                                        <div class="input-group input-group-sm">
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
                                </div>
                            </div>

                            <div class="col-md-7">
                                <div class="form-group row mb-0 form-group-stock-opname">
                                    <label class="col-sm-3 col-form-label">Keterangan</label>
                                    <div class="col-sm-9">
                                        <div class="input-group input-group-sm">
                                            <textarea rows="5" class="form-control input-sm" name="keterangan"><?= $keterangan ?></textarea>
                                        </div>
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
                <div class="card height-auto">
                    <div class="card-body p-3">
                        <table class="table table-bordered table-striped table-stock-opname" id="table-item-detail">
                            <thead>
                                <tr>
                                    <th style="width: 220px;">Kode</th>
                                    <th>Nama</th>
                                    <th>Kategori</th>
                                    <th style="width: 120px">Satuan</th>
                                    <th style="width: 100px; text-align: right;">System</th>
                                    <th style="width: 100px; text-align: right;">Fisik</th>
                                    <th style="width: 160px; text-align: right;">Selisih</th>
                                    <th style="width: 80px"></th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>

                            <tfoot>
                                <tr>
                                    <td colspan="8" style="padding: 4px;background: #ecf0f5"></td>
                                </tr>
                                <tr>
                                    <td colspan="8"><b>Tambah item</b></td>
                                </tr>
                                <tr>
                                    <td style="background-color: #E8F5E9;">
                                        <div class="input-group input-group-sm">
                                            <input type="text" class="form-control input-sm" id="input-kode-item" />
                                            <div class="input-group-prepend">
                                                <button class="btn btn-success no-shadow" onclick="searchItem($('#input-kode-item').val())" style="border-radius: 0 !important" type="button"><i class="fa fa-check"></i></button>
                                                <button class="btn btn-outline-secondary no-shadow" onclick="clearNewItem()" style="border-radius: 0 4px 4px 0 !important" type="button"><i class="fa fa-times"></i></button>

                                            </div>
                                        </div>
                                    </td>
                                    <td style=" padding-top: 4px !important;"><span id="new-item-nama"></span></td>
                                    <td style=" padding-top: 4px !important;"><span id="new-item-kategori"></span></td>
                                    <td style=" padding-top: 4px !important;"><span id="new-item-satuan"></span></td>
                                    <td style="text-align: right;"><span id="new-item-stock-system"></span></td>
                                    <td style="background-color: #E8F5E9;">
                                        <div class="input-group input-group-sm">
                                            <input type="text" class="form-control input-currency input-sm" id="input-stock-fisik" onkeyup="hitungSelisih(event, $('#btn-add-new-item'))" style="text-align: right" />
                                        </div>
                                    </td>
                                    <td style="text-align: right; padding-top: 4px !important;"><span id="new-item-stock-selisih"></span></td>

                                    <td>
                                        <a href="javascript:void(0);" id="btn-add-new-item" onclick="addNewItem()" class="text-primary"><i class="fa fa-arrow-up"></i> Tambah</a>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <span><i class="fa fa-search"></i> = Tampilkan pencarian</span> <br />
                                        <span><i class="fa fa-check"></i> = Cari</span> <br />
                                    </td>
                                    <td colspan="7"></td>
                                </tr>

                                <tr>
                                    <td colspan="8" style="padding: 4px;background: #ecf0f5"></td>
                                </tr>

                                <tr>
                                    <td colspan="8">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="btn-group btn-group-sm">
                                                    <?php
                                                    if (!empty($uuid)) {
                                                    ?>
                                                        <button type="button" class="btn btn-danger light" onclick="confirmDelete('<?= $uuid ?>')"><i class="fa fa-trash"></i> Hapus Stock Opname</button>
                                                    <?php
                                                    }
                                                    ?>
                                                </div>
                                            </div>

                                            <div class="col-md-6 col-sm-6 " style="text-align: right;">

                                                <div class="btn-group btn-group-sm mr-4">
                                                    <?php
                                                    if (!empty($uuid)) {
                                                    ?>
                                                        <a href="<?= base_url("admin/stock_opname/cetak/" . $uuid) ?>" target="_blank" class="btn btn-outline-info light" style="margin-bottom: 0"><i class="fa fa-print"></i> Cetak</a>
                                                    <?php
                                                    }
                                                    ?>

                                                </div>
                                                <div class="btn-group btn-group-sm">
                                                    <button type="button" class="btn btn-secondary light" data-dismiss="modal" aria-label="Close" style="margin-bottom: 0"><i class="fa fa-times"></i> Batal</button>
                                                    <button type="button" class="btn btn-success" style="margin-bottom: 0" onclick="save()"><i class="fa fa-save"></i> Simpan Stock Opname</button>
                                                </div>
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

<div class="modal-footer" style="display: inline;">

</div>


<style>
    .select2-container {
        width: 100% !important;
        padding: 0;
    }

    .form-group-stock-opname .form-control {
        font-size: 9pt;
        padding: 4px;
        height: auto;
        border-radius: 4px;
    }

    .form-group-stock-opname .input-group-append .btn {
        padding: 4px;
        height: 28px;
        border-radius: 4px;
    }

    .form-group-stock-opname .col-form-label {
        font-size: 9pt;
        color: #333;
    }

    .font-9pt {
        font-size: 9pt !important;
        color: #333;
    }

    .form-group-stock-opname select.select2 option {
        font-size: 9pt;
    }

    .table-stock-opname>thead>tr>th,
    .table-stock-opname>tbody>tr>td,
    .table-stock-opname>tfoot>tr>td {
        font-size: 9pt !important;
        color: #333;
        padding: 4px;
    }

    .table-stock-opname>tbody>tr>td .btn,
    .table-stock-opname>tfoot>tr>td .btn {
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

    var uuid = '<?= $uuid ?>';

    var newItemKode = '';
    var newItemNama = '';
    var newItemKategori = '';
    var newSatuan = '';

    var newStockSystem = 0;
    var newStockFisik = 0;
    var newStockSelisih = 0;
    $(document).ready(function() {
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

    function hitungSelisih(e, nextFocus) {
        newStockFisik = toNumber($("#input-stock-fisik").val());
        newStockSelisih = newStockFisik - newStockSystem;

        if (newStockSelisih < 0) {
            $("#new-item-stock-selisih").html("-" + formatCurrency(newStockSelisih));
        } else {
            $("#new-item-stock-selisih").html(parseInt(newStockSelisih));
        }

        if (e.keyCode == 13) {
            nextFocus.focus();
        }
    }

    function clearNewItem() {

        newItemKode = '';
        newItemNama = '';
        newItemKategori = '';
        newSatuan = '';

        newStockSystem = 0;
        newStockFisik = 0;
        newStockSelisih = 0;

        $("#input-kode-item").val('');
        $("#new-item-nama").html('');
        $("#new-item-kategori").html('');
        $("#new-item-satuan").html('');

        $("#input-stock-fisik").val('0');
        $("#new-item-stock-system").html('');
        $("#new-item-stock-selisih").html('');

    }

    function addNewItem() {
        if (newItemKode == "") return;

        var key = newItemKode;

        itemDetailData[key] = {
            item_code: newItemKode,
            item_nama: newItemNama,
            item_kategori: newItemKategori,
            satuan: newSatuan,
            stock_system_satuan_terkecil: newStockSystem,
            stock_system_satuan_terkecil_formatted: formatCurrency(newStockSystem),
            stock_fisik_satuan_terkecil: newStockFisik,
            stock_fisik_satuan_terkecil_formatted: formatCurrency(newStockFisik),
            stock_selisih_satuan_terkecil: newStockSelisih,
            stock_selisih_satuan_terkecil_formatted: newStockSelisih < 0 ? "-" + formatCurrency(newStockSelisih) : formatCurrency(newStockSelisih),
        }

        updateTableItemDetailList();
        clearNewItem();
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
        $.each(itemDetailData, function(key) {

            var itemDetail = itemDetailData[key];

            var _itemCode = itemDetail['item_code'];
            var _itemNama = itemDetail["item_nama"];
            var _itemKategori = itemDetail['item_kategori'];
            var _satuan = itemDetail['satuan'];
            var _stock_system_satuan_terkecil = itemDetail["stock_system_satuan_terkecil"];
            var _stock_system_satuan_terkecil_formatted = itemDetail["stock_system_satuan_terkecil_formatted"];
            var _stock_fisik_satuan_terkecil = itemDetail["stock_fisik_satuan_terkecil"];
            var _stock_fisik_satuan_terkecil_formatted = itemDetail["stock_fisik_satuan_terkecil_formatted"];
            var _stock_selisih_satuan_terkecil = itemDetail["stock_selisih_satuan_terkecil"];
            var _stock_selisih_satuan_terkecil_formatted = itemDetail["stock_selisih_satuan_terkecil_formatted"];

            var classBg = "";
            if (_stock_selisih_satuan_terkecil < 0) {
                classBg = "bg-danger text-white";
            } else {
                classBg = "bg-info text-white";
            }


            tableBodyList.push(
                `<tr>` +
                `   <td><a href='javascript:void(0);' onclick='searchItem("` + _itemCode + `")'>` + _itemCode + `</a></td>` +
                `   <td>` + _itemNama + `</td>` +
                `   <td>` + _itemKategori + `</td>` +
                `   <td>` + _satuan + `</td>` +
                `   <td align='right'>` + _stock_system_satuan_terkecil_formatted + `</td>` +
                `   <td align='right'>` + _stock_fisik_satuan_terkecil_formatted + `</td>` +
                `   <td align='right' class="` + classBg + `">` + _stock_selisih_satuan_terkecil_formatted + `</td>` +
                `   <td>` +
                `       <a href="javascript:void(0);" class="text-danger" onclick="deleteItem('` + _itemCode + `')"><i class="fa fa-times"></i> Hapus</button>` +
                `   </td>` +
                `</tr>`
            );

            rowNumber++;
        });

        $("#table-item-detail tbody").html(tableBodyList.join(''));
        $("#btn-add-new-item span").html("Tambah");
    }

    function searchItem(kode, _satuan) {
        if (kode == null || kode == undefined) return;
        if (_satuan == null || _satuan == null) _satuan = "";

        $("#input-satuan").html("");


        var tanggal = $("#datepicker-tanggal").val();
        ajax_get(
            '<?= base_url("admin/item/search_item_for_stock_opname") ?>/stock_opname', {
                kode: kode,
                tanggal: tanggal,
                stock_opname_uuid: '<?= $uuid ?>'
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
                        console.log(data);

                        newItemKode = data.kode;
                        $("#new-item-nama").html(data.nama);
                        $("#new-item-kategori").html(data.nama_kategori);
                        $("#new-item-satuan").html(data.satuan);

                        newItemNama = data.nama;
                        newItemKategori = data.nama_kategori;
                        newSatuan = data.satuan;
                        newStockSystem = data.stock_system;
                        newStockFisik = data.stock_fisik;
                        newStockSelisih = data.stock_selisih;

                        $("#btn-add-new-item span").html("Tambah");

                        if (itemDetailData.hasOwnProperty(newItemKode)) {

                            $("#btn-add-new-item span").html("Ubah");
                            newStockSystem = itemDetailData[newItemKode]["stock_system_satuan_terkecil"];
                            newStockFisik = itemDetailData[newItemKode]["stock_fisik_satuan_terkecil"];
                            newStockSelisih = itemDetailData[newItemKode]["stock_selisih_satuan_terkecil"];
                        }


                        $("#input-stock-fisik").val(formatCurrency(newStockFisik));
                        $("#new-item-stock-system").html(formatCurrency(newStockSystem));
                        $("#new-item-stock-selisih").html(newStockSelisih < 0 ? "-" + formatCurrency(newStockSelisih) : formatCurrency(newStockSelisih));



                        /*
                        newItemKode = data.kode;
                        $("#input-kode-item").val(data.kode);
                        $("#new-item-nama").html(data.nama);
                        $("#new-item-kategori").html(data.nama_kategori);

                        itemStrukturHargaList = data.harga_list;
                        satuan_list = data.satuan_list;

                        newItemNama = data.nama;
                        newItemKategori = data.nama_kategori;

                        var i;
                        for (i = 0; i < satuan_list.length; i++) {
                            var satuan = satuan_list[i];
                            $("#input-satuan").append("<option value='" + satuan.name + "'>" + satuan.label + "</option>");
                        }

                        var selectedSatuan = $("#input-satuan:first").val();
                        var selectedHargaSatuan = data.harga_list[selectedSatuan];

                        newHargaBeli = selectedHargaSatuan;

                        $("#new-item-harga-beli").val(formatCurrency(selectedHargaSatuan + ""));
                        $("#new-item-jumlah").val(1);

                        if(itemDetailData.hasOwnProperty(newItemKode + "-" + _satuan)){
                            let _selectedItemDetailData = itemDetailData[newItemKode + "-" + _satuan];
                            let _selectedJumlah = _selectedItemDetailData['jumlah'];
                            let _selectedHargaBeli = _selectedItemDetailData["harga_beli"];
                            newHargaBeli = _selectedHargaBeli;

                            $("#new-item-jumlah").val(_selectedJumlah);
                            $("#new-item-harga-beli").val(formatCurrency(_selectedHargaBeli));
                            $("#input-satuan").val(_satuan);
                        }

                        doHitungTotal($("#new-item-jumlah"));
                        */
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

    function getItemDetail(itemKode) {
        if (itemKode == null || itemKode == undefined) return;

        var tanggal = $("#datepicker-tanggal").val();
        ajax_get(
            '<?= base_url("admin/stock_opname/item_get_detail_by_kode_and_tanggal") ?>/', {
                item_kode: itemKode,
                tanggal: tanggal,
                stock_opname_uuid: '<?= $uuid ?>'
            },
            function(json) {
                try {
                    if (json.is_success == 1) {
                        let data = json.data;
                        newItemKode = itemKode;
                        $("#new-item-nama").html(data.nama);
                        $("#new-item-kategori").html(data.nama_kategori);
                        $("#new-item-satuan").html(data.satuan);

                        newItemNama = data.nama;
                        newItemKategori = data.nama_kategori;
                        newSatuan = data.satuan;
                        newStockSystem = data.stock_system;
                        newStockFisik = data.stock_fisik;
                        newStockSelisih = data.stock_selisih;

                        $("#btn-add-new-item span").html("Tambah");

                        if (itemDetailData.hasOwnProperty(newItemKode)) {

                            $("#btn-add-new-item span").html("Ubah");
                            newStockSystem = itemDetailData[newItemKode]["stock_system_satuan_terkecil"];
                            newStockFisik = itemDetailData[newItemKode]["stock_fisik_satuan_terkecil"];
                            newStockSelisih = itemDetailData[newItemKode]["stock_selisih_satuan_terkecil"];
                        }


                        $("#input-stock-fisik").val(formatCurrency(newStockFisik));
                        $("#new-item-stock-system").html(formatCurrency(newStockSystem));
                        $("#new-item-stock-selisih").html(newStockSelisih < 0 ? "-" + formatCurrency(newStockSelisih) : formatCurrency(newStockSelisih));

                    } else {

                        switch (json.message) {
                            case "NO_DATA":
                                show_toast("Perhatian", "Item tidak ditemukan", "warning");
                                break;
                            case "EMPTY_KODE":
                                show_toast("Perhatian", "Kode item tidak boleh kosong", "warning");
                                break;
                            case "EMPTY_TANGGAL":
                                show_toast("Perhatian", "Tanggal tidak boleh kosong", "warning");
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

    function confirmDelete(uuid) {
        var confirmed = confirm("Anda yakin ingin menghapus stock opname ini ? ");
        if (!confirmed) return;

        ajax_get(
            '<?= base_url('admin/stock_opname/ajax_delete') ?>/' + uuid, {},
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
        var formData = $("#form-stock-opname").serializeArray();

        var itemDetailList = [];
        $.each(itemDetailData, function(itemKode) {
            itemDetailList.push(itemDetailData[itemKode]);
        });

        formData.push({
            name: 'item_detail',
            value: JSON.stringify(itemDetailList)
        });

        ajax_post(
            '<?= base_url('admin/stock_opname/ajax_save') ?>',
            formData,
            function(resp) {
                try {
                    var json = JSON.parse(resp);
                    if (json.is_success == 1) {
                        show_toast("Success", json.message, "success");
                        load_detail(json.id);
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