<?php
$uuid = "";
$no_penjualan_retur = "Otomatis";
$tanggal = date("Y-m-d");
$penjualan_uuid = "";
$penjualan_number_formatted = "";
$kas_akun_uuid = "";
$pelanggan_nama = "";
$pelanggan_detail = "";
$sub_total = 0;
$potongan = 0;
$total_akhir = 0;
$bayar = 0;
$sisa = 0;

$item_detail_list = array();
if (isset($detail) && is_array($detail) && count($detail) > 0) {
    $uuid = trim($detail["uuid"]);
    $no_penjualan_retur = $detail["number_formatted"];
    $tanggal = $detail["tanggal"];
    $penjualan_uuid = trim($detail["penjualan_uuid"]);
    $penjualan_number_formatted = $detail["penjualan_number_formatted"];
    $kas_akun_uuid = trim($detail["kas_akun_uuid"]);
    $pelanggan_nama = "[" . $detail["pelanggan_number_formatted"] . "]" . $detail["pelanggan_nama"];
    $pelanggan_detail = $detail["pelanggan_alamat"] . "\n" . $detail["pelanggan_no_telepon"];
    $sub_total = (float) $detail["sub_total"];
    $potongan = (float) $detail["potongan"];
    $total_akhir = (float) $detail["total_akhir"];
    $bayar = (float) $detail["bayar"];
    $sisa = (float) $detail["sisa"];

    $detail_list = $detail["detail"];

    foreach ($detail_list as $dl) {
        $potongan_persen = (float) $dl["potongan_persen"];
        $potongan_harga = (float) $dl["potongan_harga"];
        $harga_jual = (float) $dl["harga_jual_satuan"];
        $jumlah = (float) $dl["jumlah"];

        $total = $jumlah * ($harga_jual - $potongan_harga);
        // if ($potongan_persen > 0 && $harga_jual > 0) {
        //     $potongan_harga = $harga_jual * ($potongan_persen / 100);
        //     $total = $jumlah * ($harga_jual - $potongan_harga);
        // }
        $total = (int) $total;
        $row = array(
            "item_code" => $dl["item_kode"],
            "item_nama" => $dl["item_nama"],
            "item_kategori" => $dl["item_kategori_nama"],
            "jumlah" => $dl["jumlah"],
            "jumlah_formatted" => number_format($dl["jumlah"], 0, ",", "."),
            "satuan" => $dl["satuan"],
            "harga_jual" => $dl["harga_jual_satuan"],
            "harga_jual_formatted" => number_format($dl["harga_jual_satuan"], 0, ",", "."),
            "potongan" => $potongan_persen,
            "potongan_formatted" => number_format($potongan_persen, 2),
            "total" => $total,
            "total_formatted" => number_format($total, 0, ",", "."),
        );

        $item_detail_list[$dl['item_kode'] . "-" . strtoupper($dl['satuan'])] = $row;
    }
}


?>
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
    <h3 class="modal-title"><span id="modal-penjualan-retur-title"></span> Retur Penjualan</h3>
</div>

<div class="modal-body hide-scrollbar" style="min-height: calc(100vh - 125px); width: 100%; overflow: auto; padding: 8px">
    <form onsubmit="return false" id="form-penjualan-retur" class="form-horizontal">
        <input type="hidden" name="uuid" value="<?= $uuid ?>" />
        <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
        <div class="row">
            <div class="col-md-12">
                <div class="panel mb-10p">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group row mb-2">
                                    <label class="col-sm-4 col-form-label">No. Retur Penjualan</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control input-sm" value="<?= $no_penjualan_retur ?>" disabled />
                                    </div>
                                </div>
                                <div class="form-group mb-0">
                                    <label class="col-sm-4 col-form-label">Tanggal</label>
                                    <div class="col-sm-8" style="margin-bottom: 4px;">
                                        <div class="input-group">
                                            <input type="text" class="form-control input-sm" name="tanggal" placeholder="yyyy-mm-dd" id="datepicker-tanggal" value="<?= date("Y-m-d", strtotime($tanggal)) ?>">
                                            <span class="input-group-addon bg-primary b-0 text-white"><i class="fa fa-calendar"></i></span>
                                        </div><!-- input-group -->
                                    </div>
                                </div>
                                <div class="form-group mb-0">
                                    <label class="col-sm-4 col-form-label">No. penjualan</label>
                                    <div class="col-sm-8" style="margin-bottom: 14px;">
                                        <div class="input-group input-group-sm">
                                            <input type="text" class="form-control input-sm" id="input-no-penjualan" name="no_penjualan" value="<?= $penjualan_number_formatted ?>" />
                                            <span class="input-group-btn">
                                                <button class="btn btn-default no-shadow" onclick="popupSearchpenjualan()" style="border-radius: 0 !important" type="button"><i class="fa fa-search"></i></button>
                                                <button class="btn btn-default no-shadow" onclick="getpenjualanDetail($('#input-no-penjualan').val())" style="border-radius: 0 4px 4px 0 !important" type="button"><i class="fa fa-check"></i></button>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-8">
                                <div class="form-group row mb-2">
                                    <label class="col-sm-4 col-form-label">Pelanggan</label>
                                    <div class="col-sm-8">
                                        <input type="text" id="pelanggan-nama" class="form-control input-sm" value="<?= $pelanggan_nama ?>" disabled />
                                    </div>
                                </div>
                                <div class="form-group mb-0">
                                    <label class="col-sm-4 col-form-label">Detail</label>
                                    <div class="col-sm-8">
                                        <textarea rows="5" class="form-control input-sm" readonly id="pelanggan-detail" disabled><?= $pelanggan_detail ?></textarea>
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
                <div class="panel mb-10p">
                    <div class="panel-heading">
                        <h3 class="panel-title">Item</h3>
                    </div>
                    <div class="panel-body">
                        <table class="table table-bordered table-striped" id="table-item-detail">
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
                                        <div class="input-group input-group-sm">
                                            <input type="text" class="form-control input-sm" id="input-kode-item" />
                                            <span class="input-group-btn">
                                                <button class="btn btn-default no-shadow" onclick="popupSearchItem()" style="border-radius: 0 !important" type="button"><i class="fa fa-search"></i></button>
                                                <button class="btn btn-default no-shadow" onclick="getItemDetail($('#input-kode-item').val())" style="border-radius: 0 4px 4px 0 !important" type="button"><i class="fa fa-check"></i></button>
                                            </span>
                                        </div>
                                    </td>
                                    <td><span id="new-item-nama"></span></td>
                                    <td><span id="new-item-kategori"></span></td>
                                    <td style="background-color: #E8F5E9;">
                                        <input type="text" class="form-control input-sm" id="new-item-jumlah" onkeyup="hitungTotal(event, $('#new-item-potongan'))" style="text-align: right" />
                                    </td>
                                    <td style="background-color: #E8F5E9;">
                                        <select class="form-control input-sm" id="input-satuan" onchange="setHargaSatuan($(this).val())">
                                        </select>
                                    </td>
                                    <td style="text-align: right;">
                                        <span id="harga-jual">0</span>
                                        <!-- <input type="text" class="form-control input-sm" id="new-item-harga-jual" style="border: none;" readonly onkeyup="hitungTotal(event, $('#new-item-potongan'))" style="text-align: right" /> -->
                                    </td>
                                    <td style="background-color: #E8F5E9;">
                                        <input type="number" max="100" class="form-control input-sm" id="new-item-potongan" onkeyup="hitungTotal(event, $('#btn-add-new-item'))" style="text-align: right" />
                                    </td>
                                    <td class="text-right"><span id="new-item-total">0</span></td>
                                    <td>
                                        <button type="button" id="btn-add-new-item" class="btn btn-primary btn-sm" onclick="addNewItem()"><i class="fa fa-arrow-up"></i> Tambah</button>
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
                                    <td colspan="2"><b>Keterangan</b></td>
                                    <td colspan="5" align="right"><b>Sub Total</b></td>
                                    <td align="right" colspan="2" style="padding-right: 15px;"><b><span id="sub-total"><?= number_format($sub_total, 0, ",", ".") ?></span></b></td>
                                </tr>
                                <tr>
                                    <td colspan="2" rowspan="3" style="background-color: #E8F5E9">
                                        <textarea name="keterangan" rows="4" class="form-control input-sm"></textarea>
                                    </td>
                                    <td colspan="5" align="right"><b>Potongan</b></td>
                                    <td align="right" colspan="2" style="background-color: #E8F5E9"><input type="text" name="potongan" class="form-control input-sm" id="input-potongan" onkeyup="hitungTotalBayar($(this).val())" value="<?= number_format($potongan, 0, ",", ".") ?>" style="text-align: right" /></td>
                                </tr>
                                <tr>
                                    <td colspan="5" align="right"><b>Total Akhir</b></td>
                                    <td align="right" colspan="2" style="padding-right: 15px;"><b><span id="total-akhir"><?= number_format($total_akhir, 0, ",", ".") ?></span></b></td>
                                </tr>
                                <!-- <tr>
                                    <td colspan="5" align="right"><b>Bayar</b></td>
                                    <td align="right" colspan="2" style="background-color: #E8F5E9"><input type="text" name="bayar" class="form-control input-sm" id="input-bayar" onkeyup="hitungSisaBayar($(this).val())" value="<?= $bayar ?>" style="text-align: right" /></td>
                                </tr>
                                <tr>
                                    <td colspan="7" align="right"><b>Sisa</b></td>
                                    <td align="right" colspan="2" style="padding-right: 15px;"><b><span id="sisa-bayar"><?= number_format($sisa, 0, ",", ".") ?></span></b></td>
                                </tr> -->
                                <tr>
                                    <td colspan="5" align="right"><b>Keluar dari Akun Kas</b></td>
                                    <td align="left" colspan="2" style="background-color: #E8F5E9;">
                                        <select name="kas_akun_uuid" class="form-control select2">
                                            <option value="" selected disabled>Jangan tambahkan ke alur kas</option>
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

<div class="modal-footer" style="padding-top: 0">
    <div class="row">
        <div class="col-md-6 col-sm-6" style="text-align: left;">
            <?php
            if (!empty($uuid)) {
            ?>
                <button class="btn btn-danger" onclick="confirmDelete('<?= $uuid ?>')"><i class="fa fa-trash"></i> Hapus Retur Penjualan</button>
            <?php
            }
            ?>
        </div>

        <div class="col-md-6 col-sm-6">
            <button class="btn btn-default" data-dismiss="modal" aria-label="Close" style="margin-bottom: 0"><i class="fa fa-times"></i> Batal</button>
            <button class="btn btn-primary" style="margin-bottom: 0" onclick="save()"><i class="fa fa-save"></i> Simpan Retur Penjualan</button>
        </div>
    </div>
</div>

<style>
    .select2-container {
        width: 100% !important;
        padding: 0;
    }
</style>

<script>
    var itemDetailData = <?= empty($uuid) ? '{}' : json_encode($item_detail_list) ?>;

    var penjualanUuid = "<?= $penjualan_uuid ?>";

    var newItemCode = '';
    var newItemNama = '';
    var newItemKategori = '';
    var newJumlah = 0;
    var newSatuan = '';
    var newHargajual = 0;
    var newPotonganPersen = 0;
    var newPotonganHarga = 0;
    var newTotal = 0;

    var subTotal = 0;
    var totalBayar = 0;
    var inputPotongan = <?= $potongan ?>;
    var inputBayar = <?= $bayar ?>;
    var sisaBayar = 0;

    var itemStrukturHargaList = {};
    var itemStrukturStockList = {};
    $(document).ready(function() {

        if (itemDetailData.length == 0 ) {
            itemDetailData = {};
        }

        $("#input-kode-item").on("keyup", function(e) {
            if (e.keyCode == 13) {
                getItemDetail($(this).val());
            } else {
                $("#new-item-nama").html("");
                $("#new-item-kategori").html("");
            }
        });

        $(".input-currency").on("keyup", function() {
            let val = $(this).val();
            $(this).val(formatCurrency(val));
        });

        $('#datepicker-tanggal').datepicker({
            autoclose: true,
            todayHighlight: true,
            format: 'yyyy-mm-dd'
        });

        $(".select2").select2({
            dropdownparent: $("#modal-detail"),
            theme: 'classic'
        });

        <?php if (!empty($uuid)) { ?>
            updateTableItemDetailList();
        <?php } ?>
    });

    // open a pop up window
    function popupSearchItem() {
        if (penjualanUuid == null || penjualanUuid == undefined || penjualanUuid == "") {
            alert("Pilih No. Penjualan lebih dulu");
            return;
        }
        var targetField = document.getElementById("input-kode-item");
        var w = window.open('<?= base_url('admin/penjualan_retur/item_popup_list') ?>?penjualan_uuid=' + penjualanUuid, '_blank', 'width=800,height=400,scrollbars=1');
        // pass the targetField to the pop up window
        w.targetField = targetField;
        w.focus();
    }

    // this function is called by the pop up window
    function setSearchResult(targetField, returnValue) {
        var _targetFields = returnValue.split(":");
        targetField.value = _targetFields[0];
        getItemDetail(returnValue);
        window.focus();
    }

    // open a pop up window
    function popupSearchpenjualan() {
        var targetField = document.getElementById("input-no-penjualan");
        var w = window.open('<?= base_url('admin/penjualan_retur/penjualan_popup_list') ?>', '_blank', 'width=1024,height=400,scrollbars=1');
        // pass the targetField to the pop up window
        w.targetField = targetField;
        w.focus();
    }

    // this function is called by the pop up window
    function setSearchResultPenjualan(targetField, returnValue) {
        targetField.value = returnValue;
        getpenjualanDetail(returnValue);
        window.focus();
    }

    function hitungTotal(e, nextFocus) {
        if (e.keyCode == 13) {
            doHitungTotal(nextFocus);
        }
    }

    function setHargaSatuan(_satuan) {
        var selectedHargaSatuan = itemStrukturHargaList[newItemCode][_satuan];
        newHargajual = selectedHargaSatuan;
        $("#harga-jual").html(formatCurrency(selectedHargaSatuan));
        doHitungTotal($("#new-item-potongan"));
    }

    function doHitungTotal(nextFocus) {
        newPotonganPersen = parseFloat($("#new-item-potongan").val());
        newJumlah = $("#new-item-jumlah").val();

        if(newPotonganPersen > 100) {
            newPotonganPersen = 100;
            $("#new-item-potongan").val(100);
        }

        if (newPotonganPersen == null || newPotonganPersen == NaN || newPotonganPersen == undefined) newPotonganPersen = 0;

        newPotonganHarga = 0;
        if (newPotonganPersen > 0) {
            newPotonganHarga = (newPotonganPersen / 100) * newHargajual;
        }
        newPotonganHarga = parseInt(newPotonganHarga);

        newTotal = 0;
        if (newJumlah > 0 && newHargajual > 0) {
            newTotal = newJumlah * (newHargajual - newPotonganHarga);

            newTotal = parseInt(newTotal);

            $("#new-item-total").html(formatCurrency(newTotal));
        }

        nextFocus.focus();
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

            $("#input-potongan").val(inputPotongan);
            $("#total-akhir").html(formatCurrency(totalBayar));
        } else {
            $("#total-akhir").html(formatCurrency(totalBayar));
        }

        // $("#input-bayar").val('0');
        // $("#sisa-bayar").html(formatCurrency(totalBayar));

        // hitungSisaBayar(0);
    }

    function hitungSisaBayar(inputBayar) {
        inputBayar = toNumber(inputBayar);
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
        newHargajual = 0;
        newTotal = 0;

        $("#input-kode-item").val('');
        $("#new-item-nama").html('');
        $("#new-item-kategori").html('');
        $("#new-item-jumlah").val('');
        $("#input-satuan").html('');
        $("#harga-jual").html('');
        $("#new-item-total").html('');
        $("#new-item-potongan").val('');

    }

    function addNewItem() {
        newSatuan = $("#input-satuan").val();
        if (newSatuan == "") return;
        if (newItemCode == "") return;
        if (newTotal == 0) return;

        var key = newItemCode + "-" + newSatuan;

        itemDetailData[key] = {
            item_code: newItemCode,
            item_nama: newItemNama,
            item_kategori: newItemKategori,
            jumlah: newJumlah,
            jumlah_formatted: formatCurrency(newJumlah),
            satuan: newSatuan,
            harga_jual: newHargajual,
            harga_jual_formatted: formatCurrency(newHargajual),
            potongan: newPotonganPersen,
            potongan_formatted: toCurrency(newPotonganPersen),
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
        $.each(itemDetailData, function(key) {

            var itemDetail = itemDetailData[key];

            var _itemCode = itemDetail['item_code'];
            var _itemNama = itemDetail["item_nama"];
            var _itemKategori = itemDetail['item_kategori'];
            var _jumlah = itemDetail['jumlah_formatted'];
            var _satuan = itemDetail['satuan'];
            var _hargaJual = itemDetail['harga_jual_formatted'];
            var _potonganPersen = itemDetail['potongan_formatted'];
            var _total = itemDetail["total"];
            var _total_formatted = itemDetail["total_formatted"];

            tableBodyList.push(
                `<tr>` +
                `   <td>` + _itemCode + `</td>` +
                `   <td>` + _itemNama + `</td>` +
                `   <td>` + _itemKategori + `</td>` +
                `   <td align='right'>` + _jumlah + `</td>` +
                `   <td>` + _satuan + `</td>` +
                `   <td align='right'>` + _hargaJual + `</td>` +
                `   <td align='right'>` + _potonganPersen + `</td>` +
                `   <td align='right'>` + _total_formatted + `</td>` +
                `   <td>` +
                `       <button type="button" class="btn btn-danger btn-sm" onclick="deleteItem('` + _itemCode + '-' + _satuan + `')"><i class="fa fa-times"></i> Hapus</button>` +
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

    function getpenjualanDetail(nopenjualan) {
        if (nopenjualan == null || nopenjualan == undefined) return;

        ajax_get(
            '<?= base_url('admin/penjualan_retur/penjualan_get_detail_by_number_formatted') ?>/', {
                penjualan_number_formatted: nopenjualan
            },
            function(json) {
                try {
                    if (json.is_success == 1) {
                        let data = json.data;
                        penjualanUuid = data.uuid;

                        var pelanggan_number_formatted = "[" + data.pelanggan_number_formatted + "]" + data.pelanggan_nama;
                        var pelanggan_detail = data.pelanggan_alamat + "\n" + data.pelanggan_no_telepon

                        $("#pelanggan-nama").val(pelanggan_number_formatted);
                        $("#pelanggan-detail").val(pelanggan_detail);
                    } else {
                        show_toast("Perhatian", "Gagal menampilkan detail penjualan", "warning");
                    }
                } catch (error) {

                }
            }
        );
    }


    function getItemDetail(itemKode) {
        if (penjualanUuid == null || penjualanUuid == undefined || penjualanUuid == "") {
            alert("Pilih No. penjualan lebih dulu");
            return;
        }
        if (itemKode == null || itemKode == undefined) return;

        $("#input-satuan").html("");
        ajax_get(
            '<?= base_url("admin/penjualan_retur/item_get_detail_by_kode_and_penjualan_uuid") ?>/', {
                kode_satuan: itemKode, 
                penjualan_uuid: penjualanUuid
            },
            function(json) {
                try {
                    if (json.is_success == 1) {
                        let data = json.data;

                        console.log(data);

                        newItemCode = itemKode.split(':')[0];
                        $("#new-item-nama").html(data.nama);
                        $("#new-item-kategori").html(data.nama_kategori);

                        newItemNama = data.nama;
                        newItemKategori = data.nama_kategori;

                        itemStrukturHargaList[data.kode] = data.harga_list;
                        itemStrukturStockList[data.kode] = data.stock_list;

                        newHargajual = parseFloat(data.harga_jual_satuan);
                        $("#harga-jual").html(formatCurrency(newHargajual));

                        $("#new-item-jumlah").val(1);
                        $("#new-item-potongan").val(data.potongan_persen);
                        $("#new-item-total").html(formatCurrency(data.sub_total));

                        newSatuan = data.satuan
                        newTotal = data.subTotal;

                        var key = newItemCode + "-" + newSatuan;

                        var i;
                        for (i = 0; i < data.satuan_list.length; i++) {
                            var satuan = data.satuan_list[i];

                            let selected = "";
                            if (satuan.name == data.satuan) selected = "selected";
                            $("#input-satuan").append("<option value='" + satuan.name + "' " + selected + ">" + satuan.label + "</option>");
                        }

                        doHitungTotal($("#new-item-jumlah"));


                    } else {

                        switch (json.message) {
                            case "NO_DATA":
                                show_toast("Perhatian", "Item tidak ditemukan", "warning");
                                break;
                            case "EMPTY_KODE":
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

    function confirmDelete(uuid) {
        var confirmed = confirm("Anda yakin ingin menghapus retur penjualan ini ? ");
        if (!confirmed) return;

        ajax_get(
            '<?= base_url('admin/penjualan_retur/ajax_delete') ?>/' + uuid, {},
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
        var formData = $("#form-penjualan-retur").serializeArray();

        var itemDetailList = [];
        $.each(itemDetailData, function(itemKode) {
            itemDetailList.push(itemDetailData[itemKode]);
        });

        formData.push({
            name: 'item_detail',
            value: JSON.stringify(itemDetailList)
        }, {
            name: 'penjualan_uuid',
            value: penjualanUuid
        });

        ajax_post(
            '<?= base_url('admin/penjualan_retur/ajax_save') ?>',
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