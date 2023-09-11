<?php
$cabang_selected = get_session("cabang_selected");
$gudang_uuid = $cabang_selected["transaksi_pembelian_default_gudang_uuid"];
$kas_akun_uuid = $cabang_selected["transaksi_pembelian_default_kas_akun_uuid"];

$uuid = "";
$no_pembelian = "Otomatis";
$no_nota_vendor = '';
$tanggal = date("Y-m-d");
$pemasok_uuid = "";
$pemasok_detail = "";
$sub_total = 0;
$potongan = 0;
$total_akhir = 0;
$bayar = 0;
$sisa = 0;

$item_detail_list = array();
if (isset($detail) && is_array($detail) && count($detail) > 0) {
    $uuid = trim($detail["uuid"]);
    $no_pembelian = $detail["number_formatted"];
    $no_nota_vendor = $detail["no_nota_vendor"];
    $tanggal = $detail["tanggal"];
    $pemasok_uuid = trim($detail["pemasok_uuid"]);
    $gudang_uuid = trim($detail["gudang_uuid"]);
    $kas_akun_uuid = trim($detail["kas_akun_uuid"]);
    $pemasok_detail = $detail["pemasok_alamat"] . "\n" . $detail["pemasok_no_telepon"];
    $sub_total = (float) $detail["sub_total"];
    $potongan = (float) $detail["potongan"];
    $total_akhir = (float) $detail["total_akhir"];
    $bayar = (float) $detail["bayar"];
    $sisa = (float) $detail["sisa"];

    $detail_list = $detail["detail"];

    foreach ($detail_list as $dl) {
        $potongan_persen = (float) $dl["potongan_persen"];
        $potongan_harga = (float) $dl["potongan_harga"];
        $harga_beli = (float) $dl["harga_beli_satuan"];
        $jumlah = (float) $dl["jumlah"];

        $total = $jumlah * ($harga_beli - $potongan_harga);
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
            "potongan" => $potongan_persen,
            "potongan_formatted" => number_format($potongan_persen, 2),
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
    <h3 class="modal-title"><span id="modal-pembelian-title"></span> Pembelian</h3>
</div>

<div class="modal-body hide-scrollbar" style="min-height: calc(100vh - 125px); width: 100%; overflow: auto; padding: 8px">
    <form onsubmit="return false" id="form-pembelian" class="form-horizontal" autocomplete="off">
        <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
        <input type="hidden" name="uuid" value="<?= $uuid ?>" />
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-5">
                                <div class="form-group row mb-2 form-group-transaksi-pembelian">
                                    <label class="col-sm-3 col-form-label">No. Pembelian</label>
                                    <div class="col-sm-9">
                                        <div class="input-group">
                                            <input type="text" class="form-control input-sm" value="<?= $no_pembelian ?>" readonly />
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row mb-0 form-group-transaksi-pembelian">
                                    <label class="col-sm-3 col-form-label">Tanggal</label>
                                    <div class="col-sm-9" style="margin-bottom: 4px;">
                                        <input type="text" class="form-control input-sm" name="tanggal" placeholder="yyyy-mm-dd" id="datepicker-tanggal" value="<?= date("Y-m-d", strtotime($tanggal)) ?>">
                                    </div>
                                </div>
                                <div class="form-group row mb-0 form-group-transaksi-pembelian">
                                    <label class="col-sm-3 col-form-label">No. Nota Pemasok</label>
                                    <div class="col-sm-9" style="margin-bottom: 4px;">
                                        <input type="text" class="form-control input-sm" name="no_nota_vendor" value="<?= $no_nota_vendor ?>">
                                    </div>
                                </div>

                                <div class="form-group row mb-2 form-group-transaksi-pembelian">
                                    <label class="col-sm-3 col-form-label">Masuk ke Gudang</label>
                                    <div class="col-sm-9">
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

                            <div class="col-md-7">
                                <div class="form-group row mb-2 form-group-transaksi-pembelian">
                                    <label class="col-sm-3 col-form-label">Pemasok</label>
                                    <div class="col-sm-9">
                                        <select name="pemasok_uuid" class="form-control select2" onchange="getPemasokDetail($(this).val())">
                                            <option value="" selected disabled>Pilih Pemasok</option>
                                            <?php
                                            if (isset($pemasok_list) && is_array($pemasok_list)) {
                                                foreach ($pemasok_list as $p) {
                                                    $selected = "";
                                                    if ($p['uuid'] == $pemasok_uuid) $selected = "selected";
                                            ?>
                                                    <option value="<?= $p['uuid'] ?>" <?= $selected ?>>[<?= $p["number_formatted"] ?>] <?= $p['nama'] ?></option>
                                            <?php
                                                }
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row mb-0 form-group-transaksi-pembelian">
                                    <label class="col-sm-3 col-form-label">Detail</label>
                                    <div class="col-sm-9">
                                        <textarea rows="5" class="form-control input-sm" readonly id="pemasok-detail"><?= $pemasok_detail ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-12">
                <div class="card mb-0">
                    <div class="card-header border-0 pb-0 pl-3">
                        <h3 class="card-title">Item</h3>
                    </div>
                    <div class="card-body pl-3 pr-3 pt-2">
                        <table class="table table-bordered table-transaksi-pembelian" id="table-item-detail">
                            <thead>
                                <tr>
                                    <th style="width: 220px;">Kode</th>
                                    <th>Nama</th>
                                    <th>Kategori</th>
                                    <th style="width: 100px; text-align: right;">Jumlah</th>
                                    <th style="width: 120px">Satuan</th>
                                    <th style="width: 160px; text-align: right;">Harga Satuan</th>
                                    <th style="width: 80px; text-align: right;">Pot. (%)</th>
                                    <th style="width: 140px; text-align: right;">Sub Total</th>
                                    <th style="width: 80px"></th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>

                            <tfoot>
                                <tr>
                                    <td colspan="9" style="padding: 4px;background: #ecf0f5"></td>
                                </tr>
                                <tr>
                                    <td colspan="9"><b>Tambah item</b></td>
                                </tr>
                                <tr>
                                    <td style="background-color: #E8F5E9;">
                                        <div class="input-group form-group-transaksi-pembelian">
                                            <input type="text" class="form-control input-sm input-search" id="input-kode-item" />
                                            <span class="input-group-append">
                                                <button class="btn btn-success no-shadow" onclick="searchItem($('#input-kode-item').val())" style="border-radius: 0 !important" type="button"><i class="fa fa-check"></i></button>
                                                <button class="btn btn-outline-secondary no-shadow" onclick="clearForm()" style="border-radius: 0 4px 4px 0 !important" type="button"><i class="fa fa-times"></i></button>
                                            </span>
                                        </div>
                                    </td>
                                    <td style=" padding-top: 4px !important;"><span id="new-item-nama" class="label-search"></span></td>
                                    <td style=" padding-top: 4px !important;"><span id="new-item-kategori" class="label-search"></span></td>
                                    <td style="background-color: #E8F5E9;">
                                        <div class="input-group form-group-transaksi-pembelian">
                                            <input type="text" class="form-control input-currency input-sm" id="new-item-jumlah" onkeyup="hitungTotal(event, $('#input-satuan'))" style="text-align: right" />
                                        </div>
                                    </td>
                                    <td style="background-color: #E8F5E9;">
                                        <div class="input-group form-group-transaksi-pembelian">
                                            <select class="form-control input-sm select-search" id="input-satuan" onchange="setHargaSatuan($(this).val())">
                                            </select>
                                        </div>
                                    </td>
                                    <td style="background-color: #E8F5E9;">
                                        <div class="input-group form-group-transaksi-pembelian">
                                            <input type="text" class="form-control input-currency input-sm input-search" id="new-item-harga-beli" onkeyup="hitungTotal(event, $('#new-item-potongan'))" style="text-align: right" />
                                        </div>
                                    </td>
                                    <td style="background-color: #E8F5E9;">
                                        <div class="input-group form-group-transaksi-pembelian">
                                            <input type="number" max="100" class="form-control input-sm input-search" id="new-item-potongan" onkeyup="hitungTotal(event, $('#btn-add-new-item'))" style="text-align: right" />
                                        </div>
                                    </td>
                                    <td style=" padding-top: 4px !important;" class="text-right"><span id="new-item-total" class="label-search">0</span></td>
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
                                    <td></td>
                                    <td></td>
                                </tr>

                                <tr>
                                    <td colspan="9" style="padding: 8px;background: #ecf0f5"></td>
                                </tr>
                                <tr>
                                    <td colspan="7" style="border: none;" align="right"><b>Sub Total</b></td>
                                    <td align="right" colspan="2" style="padding-right: 15px; border: none"><b><span id="sub-total"><?= number_format($sub_total, 0, ',', '.') ?></span></b></td>
                                </tr>
                                <tr>
                                    <td colspan="7" align="right" style="border: none;"><b>Potongan</b></td>
                                    <td align="right" colspan="2" style="background-color: #E8F5E9; border: none">
                                        <div class="input-group form-group-transaksi-pembelian">
                                            <input type="text" name="potongan" class="form-control input-sm input-currency" id="input-potongan" onkeyup="hitungTotalBayar($(this).val())" value="<?= number_format($potongan, 0, ',', '.') ?>" style="text-align: right" />
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="7" style="border: none;" align="right"><b>Total Akhir</b></td>
                                    <td align="right" colspan="2" style="padding-right: 15px; border: none"><b><span id="total-akhir"><?= number_format($total_akhir, 0, ',', '.') ?></span></b></td>
                                </tr>
                                <!-- <tr>
                                    <td colspan="7" align="right"><b>Bayar</b></td>
                                    <td align="right" colspan="2" style="background-color: #E8F5E9"><input type="text" name="bayar" class="form-control input-sm" id="input-bayar" onkeyup="hitungSisaBayar($(this).val())" value="<?= $bayar ?>" style="text-align: right" /></td>                                    
                                </tr>
                                <tr>
                                    <td colspan="7" align="right"><b>Sisa</b></td>
                                    <td align="right" colspan="2" style="padding-right: 15px;"><b><span id="sisa-bayar"><?= number_format($sisa, 0, ',', '.') ?></span></b></td>                                    
                                </tr> -->
                                <tr>
                                    <td colspan="7" align="right" style="border: none"><b>Keluar dari Akun Kas</b></td>
                                    <td align="left" colspan="2" style="background-color: #E8F5E9; border: none">
                                        <div class="input-group form-group-transaksi-pembelian">
                                            <select name="kas_akun_uuid" class="form-control select2">
                                                <option value="" selected disabled>Pilih Akun Kas</option>
                                                <?php
                                                if (isset($kas_akun_list) && is_array($kas_akun_list)) {
                                                    foreach ($kas_akun_list as $ka) {
                                                        $selected = "";
                                                        if ($ka['uuid'] == $kas_akun_uuid) $selected = "selected";
                                                ?>
                                                        <option value="<?= $ka['uuid'] ?>" <?= $selected ?>><?= $ka['nama'] ?></option>
                                                <?php
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </td>
                                </tr>


                                <tr>
                                    <td colspan="9" style="padding: 8px;background: #ecf0f5; border: none"></td>
                                </tr>

                                <tr>
                                    <td colspan="9" style="border: none;">
                                        <div class="row pt-0" style="margin-top: 0;">
                                            <div class="col-md-6 col-sm-6" style="text-align: left;">
                                                <?php
                                                if (!empty($uuid)) {
                                                ?>
                                                    <button type="button" class="btn btn-danger" onclick="confirmDelete('<?= $uuid ?>')"><i class="fa fa-trash"></i> Hapus Pembelian</button>
                                                <?php
                                                }
                                                ?>
                                            </div>

                                            <div class="col-md-6 col-sm-6" style="text-align: right;">
                                                <button type="button" class="btn btn-secondary light" data-dismiss="modal" aria-label="Close" style="margin-bottom: 0"><i class="fa fa-times"></i> Batal</button>
                                                <button type="button" class="btn btn-primary" style="margin-bottom: 0" onclick="save()"><i class="fa fa-save"></i> Simpan Pembelian</button>
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

    .form-group-transaksi-pembelian .form-control {
        font-size: 9pt;
        padding: 4px;
        height: auto;
        border-radius: 4px;
    }

    .form-group-transaksi-pembelian .input-group-append .btn {
        padding: 4px;
        height: 28px;
        border-radius: 4px;
    }

    .form-group-transaksi-pembelian .col-form-label {
        font-size: 9pt;
        color: #333;
    }

    .font-9pt {
        font-size: 9pt !important;
        color: #333;
    }

    .form-group-transaksi-pembelian select.select2 option {
        font-size: 9pt;
    }

    .table-transaksi-pembelian>thead>tr>th,
    .table-transaksi-pembelian>tbody>tr>td,
    .table-transaksi-pembelian>tfoot>tr>td {
        font-size: 9pt !important;
        color: #333;
        padding: 4px;
    }

    .table-transaksi-pembelian>tbody>tr>td .btn,
    .table-transaksi-pembelian>tfoot>tr>td .btn {
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

    var newItemCode = '';
    var newItemNama = '';
    var newItemKategori = '';
    var newJumlah = 0;
    var newSatuan = '';
    var newHargaBeli = 0;
    var newPotonganPersen = 0;
    var newPotonganHarga = 0;
    var newTotal = 0;

    var subTotal = 0;
    var totalBayar = 0;
    var inputPotongan = <?= $potongan ?>;
    var inputBayar = <?= $bayar ?>;
    var sisaBayar = 0;

    var itemStrukturHargaList = {};
    var satuan_list = [];

    var currItemUuid = "";
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

    function clearForm() {
        $(".input-search").val('');
        $(".select-search").html('');
        $(".label-search").html('');
        $("#new-item-jumlah").val(1);
        currItemUuid = "";
    }

    // open a pop up window
    function popupSearchItem(keyword) {
        if (keyword == null || keyword == undefined) keyword = '';

        var targetField = document.getElementById("input-kode-item");
        var w = window.open('<?= base_url('admin/item/popup_list_flow_stock_in') ?>?q=' + keyword, '_blank', 'width=1000,height=400,scrollbars=1');
        // pass the targetField to the pop up window
        w.targetField = targetField;
        w.focus();
    }

    // this function is called by the pop up window
    function setSearchResult(targetField, kodeResult) {
        targetField.value = kodeResult;
        searchItem(kodeResult, '');
        window.focus();
    }

    function doHitungTotal() {
        newPotonganPersen = parseFloat($("#new-item-potongan").val());
        newJumlah = toNumber($("#new-item-jumlah").val());
        newHargaBeli = toNumber($("#new-item-harga-beli").val());
        if(newJumlah == "") newJumlah = 0;

        if (newPotonganPersen > 100) {
            newPotonganPersen = 100;
            $("#new-item-potongan").val(100);
        }

        if (newPotonganPersen == null || newPotonganPersen == NaN || newPotonganPersen == undefined) newPotonganPersen = 0;

        newPotonganHarga = 0;
        if (newPotonganPersen > 0) {
            newPotonganHarga = (newPotonganPersen / 100) * newHargaBeli;
        }
        newPotonganHarga = parseInt(newPotonganHarga);

        newTotal = 0;
        if (newJumlah > 0 && newHargaBeli > 0) {
            newTotal = newJumlah * (newHargaBeli - newPotonganHarga);

            newTotal = parseInt(newTotal);

            $("#new-item-total").html(formatCurrency(newTotal));
        }else{
            newTotal = 0;
            $("#new-item-total").html(formatCurrency(newTotal));
        }
    }

    function setHargaSatuan(_satuan) {
        var selectedHargaSatuan = itemStrukturHargaList[_satuan];
        _HargaBeli = selectedHargaSatuan;
        $("#new-item-harga-beli").val(formatCurrency(selectedHargaSatuan + ""));

        doHitungTotal();
    }

    function hitungTotal(e, nextFocus) {
        
        doHitungTotal();
        if (e.keyCode == 13) {        
            if (nextFocus != null) nextFocus.focus();
        }
    }

    function hitungTotalBayar(inputPotongan) {
        inputPotongan = toNumber(inputPotongan);
        if (inputPotongan == 0) {
            totalBayar = subTotal;
            $("#total-akhir").html(formatCurrency(totalBayar));
            return;
        }

        if (inputPotongan > 0 && subTotal > 0) {
            totalBayar = subTotal - inputPotongan;
        }

        if (inputPotongan > subTotal) {
            totalBayar = 0;
            inputPotongan = subTotal;

            $("#input-potongan").val(formatCurrency(inputPotongan));
            $("#total-akhir").html(formatCurrency(totalBayar));
        } else {
            $("#total-akhir").html(formatCurrency(totalBayar));
        }

        // $("#input-bayar").val('0');
        // $("#sisa-bayar").html(formatCurrency(totalBayar));

        // hitungSisaBayar(0);
    }

    function hitungSisaBayar(inputBayar) {
        inputBayar = parseFloat(inputBayar);
        if (inputBayar > 0 && totalBayar > 0) {
            sisaBayar = totalBayar - inputBayar;
        }
        if (inputBayar == 0) sisaBayar = totalBayar;

        if (inputBayar >= totalBayar) {
            sisaBayar = 0;
            inputBayar = totalBayar;

            $("#input-bayar").val(inputBayar);
            $("#sisa-bayar").html(formatCurrency(sisaBayar));
        } else {
            $("#sisa-bayar").html(formatCurrency(sisaBayar));
        }
    }

    function clearNewItem() {
        newItemCode = '';
        newItemNama = '';
        newItemKategori = '';
        newJumlah = 0;
        newSatuan = '';
        newHargaBeli = 0;
        newTotal = 0;
        currItemUuid = "";

        $("#input-kode-item").val('');
        $("#new-item-nama").html('');
        $("#new-item-kategori").html('');
        $("#new-item-jumlah").val('');
        $("#input-satuan").html('');
        $("#new-item-harga-beli").val('');
        $("#new-item-total").html('');
        $("#new-item-potongan").val('');

    }

    function addNewItem() {
        newSatuan = $("#input-satuan").val();
        if (newSatuan == "") return;
        if (newItemCode == "") return;
        // if (newTotal == 0) return;

        var key = newItemCode + "-" + newSatuan;

        if (newPotonganPersen == null) newPotonganPersen = 0;
        if (newPotonganPersen == undefined) newPotonganPersen = 0;
        if (newPotonganPersen == NaN) newPotonganPersen = 0;


        itemDetailData[key] = {
            item_code: newItemCode,
            item_nama: newItemNama,
            item_kategori: newItemKategori,
            jumlah: newJumlah,
            jumlah_formatted: formatCurrency(newJumlah),
            satuan: newSatuan,
            harga_beli: newHargaBeli,
            harga_beli_formatted: formatCurrency(newHargaBeli),
            potongan: newPotonganPersen,
            potongan_formatted: formatCurrency(newPotonganPersen),
            total: newTotal,
            total_formatted: formatCurrency(newTotal),
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
        inputPotongan = $("#input-potongan").val();
        if (inputPotongan == null || inputPotongan == NaN || inputPotongan == undefined) inputPotongan = 0;

        var tableBodyList = [];

        var rowNumber = 1;

        subTotal = 0;

        $.each(itemDetailData, function(key, a) {
            var itemDetail = itemDetailData[key];

            var _itemCode = itemDetail['item_code'];
            var _itemNama = itemDetail["item_nama"];
            var _itemKategori = itemDetail['item_kategori'];
            var _jumlah = itemDetail['jumlah'];
            var _jumlah_formatted = itemDetail['jumlah_formatted'];
            var _satuan = itemDetail['satuan'];
            var _hargaBeli = itemDetail['harga_beli_formatted'];
            var _potonganPersen = itemDetail['potongan'];
            var _potonganPersenFormatted = itemDetail['potongan_formatted'];
            var _total = itemDetail["total"];
            var _total_formatted = itemDetail["total_formatted"];

            tableBodyList.push(
                `<tr>` +
                `   <td><a href='javascript:void(0);' onclick='searchItem("` + _itemCode + `","` + _satuan + `")'>` + _itemCode + ` </a></td>` +
                `   <td>` + _itemNama + `</td>` +
                `   <td>` + _itemKategori + `</td>` +
                `   <td align='right'>` + _jumlah_formatted + `</td>` +
                `   <td>` + _satuan + `</td>` +
                `   <td align='right'>` + _hargaBeli + `</td>` +
                `   <td align='right'>` + _potonganPersenFormatted + `</td>` +
                `   <td align='right'>` + _total_formatted + `</td>` +
                `   <td>` +
                `       <button type="button" class="btn btn-danger btn-sm" onclick="deleteItem('` + _itemCode + '-' + _satuan + `')"><i class="fa fa-times"></i></button>` +
                `   </td>` +
                `</tr>`
            );

            subTotal += _total;

            rowNumber++;
        });

        subTotal = parseInt(subTotal);

        $("#sub-total").html('' + formatCurrency(subTotal));
        $("#input-potongan").val(inputPotongan);

        totalBayar = subTotal - inputPotongan;
        $("#total-akhir").html(formatCurrency(totalBayar));

        $("#table-item-detail tbody").html(tableBodyList.join(''));

        hitungTotalBayar($('#input-potongan').val());
        hitungSisaBayar($('#input-bayar').val());
    }

    function getPemasokDetail(uuid) {
        if (uuid == null || uuid == undefined) return;

        ajax_get(
            '<?= base_url('admin/pembelian/pemasok_get_detail_by_uuid') ?>/' + uuid, {},
            function(json) {
                try {
                    if (json.is_success == 1) {
                        let data = json.data;
                        $("#pemasok-detail").val(
                            'Alamat : ' +
                            data['alamat'] + '\n' +
                            'No. Telp : ' + data['no_telepon']
                        );
                    } else {
                        show_toast("Perhatian", "Gagal menampilkan detail pemasok", "warning");
                    }
                } catch (error) {

                }
            }
        );
    }

    function searchItem(kode, _satuan) {
        if (kode == null || kode == undefined) return;
        if (_satuan == null || _satuan == null) _satuan = "";

        $("#input-satuan").html("");

        ajax_get(
            '<?= base_url("admin/item/search_flow_stock_in") ?>/', {
                kode: kode,
                uuid: currItemUuid
            },
            function(json) {
                try {
                    if (json.is_success == 1) {
                        let data_list = json.data;
                        
                        if(data_list.length > 1) {
                            popupSearchItem(kode);
                            return;
                        }
                        if(data_list.length == 0){
                            popupSearchItem(kode);
                            return;
                        }
                        let data = data_list[0];

                        newItemCode = data.kode;
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

                        if (itemDetailData.hasOwnProperty(newItemCode + "-" + _satuan)) {
                            let _selectedItemDetailData = itemDetailData[newItemCode + "-" + _satuan];
                            let _selectedJumlah = _selectedItemDetailData['jumlah'];
                            let _selectedHargaBeli = _selectedItemDetailData["harga_beli"];
                            newHargaBeli = _selectedHargaBeli;

                            $("#new-item-jumlah").val(_selectedJumlah);
                            $("#new-item-harga-beli").val(formatCurrency(_selectedHargaBeli));
                            $("#input-satuan").val(_satuan);
                        }

                        doHitungTotal();
                        $("#new-item-jumlah").focus();
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
            '<?= base_url('admin/pembelian/ajax_delete') ?>/' + uuid, {},
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
        var formData = $("#form-pembelian").serializeArray();

        var itemDetailList = [];
        $.each(itemDetailData, function(itemKode) {
            itemDetailList.push(itemDetailData[itemKode]);
        });

        formData.push({
            name: 'item_detail',
            value: JSON.stringify(itemDetailList)
        });

        ajax_post(
            '<?= base_url('admin/pembelian/ajax_save') ?>',
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