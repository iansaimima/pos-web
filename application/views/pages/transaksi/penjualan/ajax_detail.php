<?php

$user = get_session("user");
$privilege_list = isset($user["privilege_list"]) ? $user["privilege_list"] : array();
$allow_create = isset($privilege_list["allow_transaksi_penjualan_create"]) ? $privilege_list["allow_transaksi_penjualan_create"] : 0;
$allow_update = isset($privilege_list["allow_transaksi_penjualan_update"]) ? $privilege_list["allow_transaksi_penjualan_update"] : 0;
$allow_delete = isset($privilege_list["allow_transaksi_penjualan_delete"]) ? $privilege_list["allow_transaksi_penjualan_delete"] : 0;
$allow_print_nota = isset($privilege_list["allow_transaksi_penjualan_print_nota"]) ? $privilege_list["allow_transaksi_penjualan_print_nota"] : 0;
$allow_print_riwayat_pembayaran_piutang = isset($privilege_list["allow_transaksi_penjualan_print_riwayat_pembayaran_piutang"]) ? $privilege_list["allow_transaksi_penjualan_print_riwayat_pembayaran_piutang"] : 0;

$uri_1 = $this->uri->segment(1);
$cabang_selected = get_session("cabang_selected");
$gudang_uuid = $cabang_selected["transaksi_penjualan_default_gudang_uuid"];
$kas_akun_uuid = $cabang_selected["transaksi_penjualan_default_kas_akun_uuid"];

$uuid = "";
$no_penjualan = "Otomatis";
$tanggal = date("Y-m-d");
$pelanggan_uuid = "";
$pelanggan_detail = "";
$sub_total = 0;
$potongan = 0;
$potongan_persen = 0;
$total_akhir = 0;
$bayar = 0;
$sisa = 0;
$kembali = 0;
$keterangan = "";
$metode_pembayaran = "Non Tunai";
$jatuh_tempo = date("Y-m-d", strtotime(date("Y-m-d") . "+" . DEFAULT_JUMLAH_HARI_JATUH_TEMPO . " " . DEFAULT_PERIODE_JATUH_TEMPO));


$item_detail_list = array();

$created_text = "";
$last_updated_text = "";
if (isset($detail) && is_array($detail) && count($detail) > 0) {
    $uuid = trim($detail["uuid"]);
    $no_penjualan = $detail["number_formatted"];
    $tanggal = $detail["tanggal"];
    $jatuh_tempo = $detail["jatuh_tempo"];
    $pelanggan_uuid = trim($detail["pelanggan_uuid"]);
    $gudang_uuid = trim($detail["gudang_uuid"]);
    $kas_akun_uuid = trim($detail["kas_akun_uuid"]);
    $pelanggan_detail = $detail["pelanggan_alamat"] . "\n" . $detail["pelanggan_no_telepon"];
    $sub_total = (float) $detail["sub_total"];
    $potongan = (float) $detail["potongan"];
    $potongan_persen = (float) $detail["pelanggan_potongan_persen"];
    $total_akhir = (float) $detail["total_akhir"];
    $bayar = (float) $detail["bayar"];
    $sisa = (float) $detail["sisa"];
    $kembali = $detail["kembali"];
    $bayar = (float) $detail["bayar"];
    $metode_pembayaran = $detail["metode_pembayaran"];
    $keterangan = $detail["keterangan"];

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

    $created_text = "Dibuat oleh : " . $detail["creator_user_name"] . " pada " . $detail["created"];
    $last_updated_text = "Diubah oleh : " . $detail["last_updated_user_name"] . " pada " . $detail["last_updated"];
}

$text_metode_pembayaran = "Sisa";

$row_kembali_display = "style='display: none'";
$row_sisa_display = "";
if ($metode_pembayaran == "Tunai") {
    $text_metode_pembayaran = "Kembali";


    $row_kembali_display = "";
    $row_sisa_display = "style='display: none'";
}

?>
<div class="modal-header" style="padding-bottom: 0px; padding: 0 !important; padding-left: 10px !important">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="padding: 0 !important; padding-right: 10px !important;">
        <span aria-hidden="true">&times;</span>
    </button>
    <h3 class="modal-title"><span id="modal-penjualan-title"></span> Penjualan</h3>
</div>

<div class="modal-body hide-scrollbar" style="min-height: calc(100vh - 125px); width: 100%; overflow: auto; padding: 8px">
    <form onsubmit="return false" id="form-penjualan" class="form-horizontal" autocomplete="off">
        <input type="hidden" name="uuid" value="<?= $uuid ?>" />
        <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
        <div class="row">
            <div class="col-md-12">
                <div class="row pb-0">
                    <div class="col-md-7">
                        <div class="card mb-2 card-body pl-1 pt-0 pb-0 pr-0" style="background-color: transparent;">
                            <div class="row">
                                <div class="col-md-5 col-sm-12">
                                    <div class="form-group row mb-0 form-group-transaksi-penjualan">
                                        <label class="col-sm-4 col-form-label">No. penjualan</label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                                <input type="text" class="form-control input-sm" value="<?= $no_penjualan ?>" readonly />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group row mb-0 form-group-transaksi-penjualan">
                                        <label class="col-sm-4 col-form-label">Tanggal</label>
                                        <div class="col-sm-8" style="margin-bottom: 4px;">
                                            <div class="input-group">
                                                <input type="text" class="form-control input-sm" name="tanggal" placeholder="yyyy-mm-dd" id="datepicker-tanggal" value="<?= date("Y-m-d", strtotime($tanggal)) ?>">
                                            </div><!-- input-group -->
                                        </div>
                                    </div>
                                    <div class="form-group row mb-0 form-group-transaksi-penjualan">
                                        <label class="col-sm-4 col-form-label">Keluar dari Gudang</label>
                                        <div class="col-sm-8">
                                            <select name="gudang_uuid" class="form-control input-sm select2">
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

                                <div class="col-md-7 col-sm-12">
                                    <div class="form-group row mb-0 form-group-transaksi-penjualan">
                                        <label class="col-sm-3 col-form-label">Pelanggan</label>
                                        <div class="col-sm-9">
                                            <div class="input-group ">
                                                <select name="pelanggan_uuid" class="form-control select2" onchange="getpelangganDetail($(this).val())">
                                                    <option value="" selected disabled>Pilih pelanggan</option>
                                                    <?php
                                                    if (isset($pelanggan_list) && is_array($pelanggan_list)) {
                                                        foreach ($pelanggan_list as $p) {
                                                            $selected = "";
                                                            if ($p['uuid'] == $pelanggan_uuid) $selected = "selected";
                                                    ?>
                                                            <option value="<?= $p['uuid'] ?>" <?= $selected ?>>[<?= $p["number_formatted"] ?>] <?= $p['nama'] ?></option>
                                                    <?php
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group row mb-0 form-group-transaksi-penjualan">
                                        <label class="col-sm-3 col-form-label">Detail</label>
                                        <div class="col-sm-9">
                                            <div class="input-group ">
                                                <textarea rows="3" class="form-control input-sm" readonly id="pelanggan-detail"><?= $pelanggan_detail ?></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-5">
                        <div class="card mb-3 height-autox bg-secondary">
                            <div class="card-body pt-2 pb-2">
                                <h1 class="total-akhir" style="font-size: 72px; color: #fff; text-align: right;">0</h1>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-12">
                <div class="card" style="margin-top: -15px">
                    <div class="card-body p-2">
                        <table class="table table-bordered table-striped table-transaksi-penjualan" id="table-item-detail">
                            <thead>
                                <tr>
                                    <th style="width: 220px;" class="font-9pt">Kode</th>
                                    <th class="font-9pt">Nama</th>
                                    <th class="font-9pt">Kategori</th>
                                    <th class="font-9pt" style="width: 100px; text-align: right;">Jumlah</th>
                                    <th class="font-9pt" style="width: 120px">Satuan</th>
                                    <th class="font-9pt" style="width: 160px; text-align: right;">Harga Satuan</th>
                                    <th class="font-9pt" style="width: 80px; text-align: right;">Pot. (%)</th>
                                    <th class="font-9pt" style="width: 140px; text-align: right;">Total</th>
                                    <th class="font-9pt" style="width: 80px"></th>
                                </tr>
                            </thead>
                            <tbody style="min-height: calc(100vh - 100px) !important;">
                            </tbody>

                            <tfoot>
                                <tr>
                                    <td colspan="9" style="padding: 4px;background: #ecf0f5"></td>
                                </tr>
                                <tr>
                                    <td colspan="9"><b>Tambah item</b></td>
                                </tr>
                                <tr>
                                    <td style="background-color: #E8F5E9; padding-top: 1px !important; padding-bottom: 1px !important">
                                        <div class="input-group form-group-transaksi-penjualan">
                                            <input type="text" class="form-control input-sm" style="height: 28px;" id="input-kode-item" />
                                            <div class="input-group-append">
                                                <button class="btn btn-success no-shadow btn-transaksi-penjualan" onclick="searchItem($('#input-kode-item').val())" style="border-radius: 0 !important" type="button"><i class="fa fa-check"></i></button>
                                                <button class="btn btn-outline-secondary no-shadow btn-transaksi-penjualan" onclick="clearNewItem()" style="border-radius: 0 4px 4px 0 !important" type="button"><i class="fa fa-times"></i></button>
                                            </div>
                                        </div>
                                    </td>
                                    <td style=" padding-top: 4px !important;"><span id="new-item-nama"></span></td>
                                    <td style=" padding-top: 4px !important;"><span id="new-item-kategori"></span></td>
                                    <td style="background-color: #E8F5E9; padding-top: 1px !important; padding-bottom: 1px !important">
                                        <div class="input-group  form-group-transaksi-penjualan">
                                            <input type="text" class="form-control input-sm input-currency" id="new-item-jumlah" onkeyup="hitungTotal(event, $('#input-satuan'))" style="text-align: right" />
                                        </div>
                                    </td>
                                    <td style="background-color: #E8F5E9; padding-top: 1px !important; padding-bottom: 1px !important">
                                        <div class="input-group  form-group-transaksi-penjualan">
                                            <select class="form-control input-sm" id="input-satuan" onchange="setHargaSatuan($(this).val())">
                                            </select>
                                        </div>
                                    </td>
                                    <td style="text-align: right; padding-top: 4px !important;">
                                        <span id="harga-jual">0</span>
                                        <!-- <input type="text" class="form-control input-sm" id="new-item-harga-jual" style="border: none;" readonly onkeyup="hitungTotal(event, $('#new-item-potongan'))" style="text-align: right" /> -->
                                    </td>
                                    <td style="background-color: #E8F5E9; padding-top: 1px !important; padding-bottom: 1px !important">
                                        <div class="input-group  form-group-transaksi-penjualan">
                                            <input type="number" max="100" class="form-control input-sm" id="new-item-potongan" onkeyup="hitungTotal(event, $('#btn-add-new-item'))" style="text-align: right" />
                                        </div>
                                    </td>
                                    <td style=" padding-top: 4px !important;" class="text-right"><span id="new-item-total">0</span></td>
                                    <td style=" padding-top: 4px !important;">
                                        <div class="input-group  form-group-transaksi-penjualan">
                                            <a href="javascript:void(0);" id="btn-add-new-item" class="text-primary" onclick="addNewItem()"><i class="fa fa-arrow-up"></i> Tambah</a>
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
                                    <td colspan="9" valign="top" style="padding: 0 !important;">
                                        <table width="100%" style="border: none; margin : 0">
                                            <tr>
                                                <td width="30%" valign="top" style="border: none; padding-top: 0px !important; padding-bottom: 0px !important;">
                                                    <table width="100%">
                                                        <tr>
                                                            <td valign="top" style="border: none;padding-top: 2px !important; padding-bottom: 1px !important;" class="font-9pt">Keterangan</td>
                                                        </tr>
                                                        <tr>
                                                            <td valign="top" style="background-color: #E8F5E9; padding: 3px; border: none; padding-top: 2px !important; padding-bottom: 1px !important;">
                                                                <div class="input-group  form-group-transaksi-penjualan">
                                                                    <textarea name="keterangan" rows="4" class="form-control input-sm"><?= $keterangan ?></textarea>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                    <p style="margin-top: 8px; margin-bottom: 0"><?= $created_text ?></p>
                                                    <p><?= $last_updated_text ?></p>
                                                </td>
                                                <td width="70%" valign="top" align="right" style="border: none; padding-top: 0px !important; padding-bottom: 0px !important;">
                                                    <table>
                                                        <tr>
                                                            <td valign="top" style="border: none; vertical-align: top !important;">
                                                                <table>
                                                                    <tr>
                                                                        <td valign="top" style="padding: 3px; border: none">Metode pembayaran</td>
                                                                        <td valign="top" align="left" style="background-color: #E8F5E9; padding: 3px; width: 180px; border: none; padding-top: 2px !important; padding-bottom: 1px !important;">
                                                                            <div class="input-group  form-group-transaksi-penjualan">
                                                                                <select name="metode_pembayaran" class="form-control" id="select-metode-pembayaran" onchange="setMetodePembayaran($(this).val())">
                                                                                    <option value="Non Tunai" <?= $metode_pembayaran == "Non Tunai" ? "selected" : "" ?>>Non Tunai</option>
                                                                                    <option value="Tunai" <?= $metode_pembayaran == "Tunai" ? "selected" : "" ?>>Tunai</option>
                                                                                </select>
                                                                            </div>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td valign="top" style="padding: 3px; border: none;">Masuk ke akun kas</td>
                                                                        <td valign="top" align="left" style="background-color: #E8F5E9; padding: 3px; width: 180px; border: none; padding-top: 2px !important; padding-bottom: 1px !important;">
                                                                            <div class="input-group  form-group-transaksi-penjualan">
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
                                                                    <tr id="tr-jatuh-tempo" <?= $row_sisa_display ?>>
                                                                        <td valign="top" style="padding: 3px; border: none">Jatuh Tempo</td>
                                                                        <td valign="top" align="left" style="background-color: #E8F5E9; padding: 3px; width: 180px; border: none;padding-top: 2px !important; padding-bottom: 1px !important;">
                                                                            <div class="input-group  form-group-transaksi-penjualan">
                                                                                <input type="text" class="form-control input-sm" name="jatuh_tempo" placeholder="yyyy-mm-dd" id="datepicker-jatuh-tempo" value="<?= date("Y-m-d", strtotime($jatuh_tempo)) ?>">
                                                                            </div><!-- input-group -->
                                                                        </td>
                                                                    </tr>
                                                                </table>
                                                            </td>
                                                            <td width="20" style="border: none;">&nbsp;</td>
                                                            <td valign="top" style="border: none;">
                                                                <table>
                                                                    <tr>
                                                                        <td valign="top" style="padding: 3px; border: none">Sub Total</td>
                                                                        <td align="right" style="padding: 3px; border: none; padding-right: 15px; padding-top: 2px !important; padding-bottom: 1px !important;"><b><span id="sub-total"><?= number_format($sub_total) ?></span></b></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td valign="top" style="padding: 3px; border: none">Potongan</td>
                                                                        <td align="right" style="background-color: #E8F5E9; padding: 3px; border: none; padding-top: 2px !important; padding-bottom: 1px !important;">
                                                                            <div class="input-group  form-group-transaksi-penjualan">
                                                                                <input type="text" name="potongan" class="form-control input-currency input-sm" id="input-potongan" onkeyup="hitungTotalBayar($(this).val())" value="<?= number_format($potongan, 0, ",", ".") ?>" style="text-align: right" />
                                                                            </div>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td valign="top" style="padding: 3px; border: none">Total Akhir</td>
                                                                        <td align="right" style="padding: 3px; border: none; padding-right: 15px; padding-top: 4px !important; padding-bottom: 4px !important;"><b><span class="total-akhir"><?= number_format($total_akhir, 0, ",", ".") ?></span></b></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td valign="top" style="padding: 3px; border: none">Tunai</td>
                                                                        <td align="right" style="background-color: #E8F5E9; padding: 3px; border: none;padding-top: 2px !important; padding-bottom: 1px !important;">
                                                                            <div class="input-group  form-group-transaksi-penjualan">
                                                                                <input type="text" name="bayar" class="form-control input-currency input-sm" id="input-bayar" onkeyup="hitungSisaBayar($(this).val())" value="<?= number_format($bayar, 0, ",", ".") ?>" style="text-align: right" />
                                                                            </div>
                                                                        </td>
                                                                    </tr>
                                                                    <tr id="tr-kembali" <?= $row_kembali_display ?>>
                                                                        <td valign="top" style="padding: 3px; border: none"><span>Kembali</span></td>
                                                                        <td align="right" style="padding: 3px; border: none; padding-right: 15px; padding-top: 2px !important; padding-bottom: 1px !important;"><b><span id="kembali"><?= number_format($kembali, 0, ",", ".") ?></span></b></td>
                                                                    </tr>
                                                                    <tr id="tr-sisa" <?= $row_sisa_display ?>>
                                                                        <td valign="top" style="padding: 3px; border: none"><span>Kredit</span></td>
                                                                        <td align="right" style="padding: 3px; border: none; padding-right: 15px; padding-top: 4px !important; padding-bottom: 4px !important;"><b><span id="sisa-bayar"><?= number_format($sisa, 0, ",", ".") ?></span></b></td>
                                                                    </tr>
                                                                </table>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td colspan="2" style="padding: 8px;background: #ecf0f5; border: none"></td>
                                            </tr>

                                            <tr>
                                                <td style="border: none; padding-left: 10px !important; padding-bottom: 10px !important" class="pl-3">
                                                    <div class="btn-group btn-group-sm">
                                                        <?php
                                                        if (!empty($uuid) && $allow_delete) {
                                                        ?>
                                                            <button type="button" class="btn btn-danger" onclick="confirmDelete('<?= $uuid ?>')"><i class="fa fa-trash"></i> Hapus penjualan</button>
                                                        <?php
                                                        }
                                                        ?>
                                                    </div>
                                                </td>

                                                <td style="border: none; padding-right: 10px !important" align="right">

                                                    <div class="btn-group btn-group-sm mr-3">
                                                        <?php
                                                        if (!empty($uuid)) {
                                                            if (strtolower($metode_pembayaran) == "non tunai" && $allow_print_riwayat_pembayaran_piutang == 1) {
                                                        ?>
                                                                <a href="<?= base_url($uri_1 . "/penjualan/cetak_riwayat_pembayaran_piutang/" . $uuid) ?>" target="_blank" class="btn btn-outline-info"><i class="fa fa-print"></i> Cetak Riwayat Pembayaran</a>
                                                            <?php
                                                            }

                                                            if ($allow_print_nota == 1) {
                                                            ?>
                                                                <a href="<?= base_url($uri_1 . "/penjualan/cetak_nota/" . $uuid) ?>" target="_blank" class="btn btn-outline-info"><i class="fa fa-print"></i> Cetak <?= $metode_pembayaran == "Tunai" ? "Nota" : "Invoice" ?></a>
                                                        <?php
                                                            } // end if allow print nota
                                                        }
                                                        ?>
                                                    </div>

                                                    <div class="btn-group btn-group-sm">
                                                        <button type="button" class="btn btn-outline-secondary light" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i> Tutup</button>
                                                        <?php if ($allow_create && empty($uuid)) { ?>
                                                            <button type="button" class="btn btn-primary" onclick="save()"><i class="fa fa-save"></i> Simpan penjualan</button>
                                                        <?php } else if ($allow_update && !empty($uuid)) { ?>
                                                            <button type="button" class="btn btn-primary" onclick="save()"><i class="fa fa-save"></i> Simpan penjualan</button>
                                                        <?php } ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        </table>
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

    .form-group-transaksi-penjualan .form-control {
        font-size: 9pt;
        padding: 4px;
        height: auto;
        border-radius: 4px;
    }

    .form-group-transaksi-penjualan .input-group-append .btn {
        padding: 4px;
        height: 28px;
        border-radius: 4px;
    }

    .form-group-transaksi-penjualan .col-form-label {
        font-size: 9pt;
        color: #333;
    }

    .font-9pt {
        font-size: 9pt !important;
        color: #333;
    }

    .form-group-transaksi-penjualan select.select2 option {
        font-size: 9pt;
    }

    .table-transaksi-penjualan>thead>tr>th,
    .table-transaksi-penjualan>tbody>tr>td,
    .table-transaksi-penjualan>tfoot>tr>td {
        font-size: 9pt !important;
        color: #333;
        padding: 4px;
    }


    .btn-transaksi-penjualan {
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
    var newHargajual = 0;
    var newPotonganPersen = 0;
    var newPotonganHarga = 0;
    var newTotal = 0;

    var subTotal = 0;
    var totalBayar = 0;
    var inputPotongan = <?= $potongan ?>;
    var inputBayar = <?= $bayar ?>;
    var sisaBayar = 0;
    var kembali = 0;

    var pelangganPotonganPersen = 0;
    var pelangganPotonganHarga = 0;

    var itemStrukturHargaList = {};
    var itemStrukturStockList = {};

    var metodePembayaran = "";

    var cek_stock = 0;
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

        $(".input-currency").on("keyup", function() {
            let val = $(this).val();
            $(this).val(formatCurrency(val));
        });



        $('#datepicker-tanggal').bootstrapMaterialDatePicker({
            format: 'YYYY-MM-DD',
            weekStart: 0,
            time: false
        });

        $('#datepicker-jatuh-tempo').bootstrapMaterialDatePicker({
            format: 'YYYY-MM-DD',
            weekStart: 0,
            time: false
        });


        $("#datepicker-tanggal").on('change', function(ev, date) {
            var tanggal = $(this).val();
            var m = moment(tanggal, 'YYYY-MM-DD');
            m.add(<?= DEFAULT_JUMLAH_HARI_JATUH_TEMPO ?>, '<?= strtolower(DEFAULT_PERIODE_JATUH_TEMPO) ?>');
            var jatuhTempo = m.format('YYYY-MM-DD');

            $("#datepicker-jatuh-tempo").val(jatuhTempo);
        });

        $(".select2").select2({
            dropdownparent: $("#modal-detail"),
            theme: 'classic',
            // dropdownCssClass: 'select2-dropdown'
        });

        <?php if (!empty($uuid)) { ?>
            updateTableItemDetailList();
        <?php } ?>

        hitungSisaBayar(inputBayar);
    });

    function setMetodePembayaran(_value) {
        metodePembayaran = _value;
        if (_value == "Tunai") {
            $("#tr-kembali").show();
            $("#tr-sisa").hide();
            $("#tr-jatuh-tempo").hide();
        } else {
            $("#tr-kembali").hide();
            $("#tr-sisa").show();
            $("#tr-jatuh-tempo").show();
        }

        hitungSisaBayar($("#input-bayar").val());
    }

    // open a pop up window
    function popupSearchItem(kode) {
        if (kode == null || kode == undefined) kode = '';

        var targetField = document.getElementById("input-kode-item");
        var w = window.open('<?= base_url($uri_1 . '/item/popup_list_flow_stock_out') ?>?q=' + kode, '_blank', 'width=1280,height=500,scrollbars=1');
        // pass the targetField to the pop up window
        w.targetField = targetField;
        w.focus();
    }

    // this function is called by the pop up window
    function setSearchResult(targetField, returnValue) {
        var list = returnValue.split("-");

        console.log(list);
        var _kode = "";
        var _satuan = "";
        if (list.length >= 2) {
            _satuan = list[(list.length) - 1];

            list.splice((list.length) - 1);

            _kode = list.join('-');
        }

        targetField.value = _kode;

        searchItem(_kode, _satuan);
        window.focus();
    }

    function hitungTotal(e, nextFocus) {
        doHitungTotal();
        if (e.keyCode == 13) {
            nextFocus.focus();
        }
    }

    function doHitungTotal() {
        newPotonganPersen = parseFloat($("#new-item-potongan").val());
        newJumlah = toNumber($("#new-item-jumlah").val());
        // newHargajual = $("#new-item-harga-jual").val();

        if (newPotonganPersen > 100) {
            newPotonganPersen = 100;
            $("#new-item-potongan").val(100);
        }

        var selectedStockSatuan = itemStrukturStockList[newItemKode][$("#input-satuan").val()];
        if (newJumlah > selectedStockSatuan && cek_stock == 1) {
            newJumlah = selectedStockSatuan;
            $("#new-item-jumlah").val(selectedStockSatuan);
            // doHitungTotal($("#new-item-jumlah"));
            alert("Stock untuk item yang dipilih tidak mencukupi. Sisa stock saat ini : " + selectedStockSatuan + " " + $("#input-satuan").val());
            return;
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
        }else{
            newTotal = 0;
            $("#new-item-total").html(formatCurrency(newTotal));
        }
    }

    function hitungTotalBayar(inputPotongan) {
        inputPotongan = toNumber(inputPotongan);
        if (inputPotongan == 0) {
            totalBayar = subTotal;
            $(".total-akhir").html(formatCurrency(totalBayar));
            return;
        }

        if (inputPotongan > 0 && subTotal > 0) {
            totalBayar = subTotal - inputPotongan;
        }

        if (inputPotongan > 0) {
            pelangganPotonganHarga = subTotal * (pelangganPotonganPersen / 100);
            if (inputPotongan != pelangganPotonganHarga) {
                $("#chk-potongan-persen").prop("checked", false);
            } else {
                $("#chk-potongan-persen").prop("checked", true);
            }
        }

        if (inputPotongan > subTotal) {
            totalBayar = 0;
            inputPotongan = subTotal;

            $("#input-potongan").val(inputPotongan);
            $(".total-akhir").html(formatCurrency(totalBayar));
        } else {
            $(".total-akhir").html(formatCurrency(totalBayar));
        }

        $("#sisa-bayar").html(formatCurrency(totalBayar));
    }

    function hitungSisaBayar(inputBayar) {
        inputBayar = toNumber(inputBayar);
        if (inputBayar == 0 || inputBayar == "") {
            kembali = 0;
            $("#kembali").html(formatCurrency(0));
            $("#sisa-bayar").html(formatCurrency(totalBayar));
            return;
        }

        metodePembayaran = $("#select-metode-pembayaran").val();

        if (metodePembayaran == "Non Tunai") {
            sisaBayar = totalBayar - inputBayar;

            if (inputBayar >= totalBayar) sisaBayar = 0;
            $("#sisa-bayar").html(formatCurrency(sisaBayar));
        } else {
            kembali = inputBayar - totalBayar;
            let minus = kembali < 0;

            if (minus) {
                $("#kembali").html("-" + formatCurrency(kembali));
            } else {

                $("#kembali").html(formatCurrency(kembali));
            }
        }

        return;

        if (inputBayar > 0 && totalBayar > 0) {
            sisaBayar = totalBayar - inputBayar;
            kembali = inputBayar - totalBayar;
        }
        if (inputBayar == 0) sisaBayar = totalBayar;

        if (inputBayar >= totalBayar) {
            sisaBayar = 0;
            // inputBayar = totalBayar;

            // $("#input-bayar").val(inputBayar);
        } else {
            $("#sisa-bayar").html(formatCurrency(sisaBayar));
        }
    }

    function clearNewItem() {
        newItemKode = '';
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
        $("#harga-jual").html('0');
        $("#new-item-total").html('');
        $("#new-item-potongan").val('');
    }

    function addNewItem() {
        newSatuan = $("#input-satuan").val();
        if (newSatuan == "") return;
        if (newItemKode == "") return;
        // if (newTotal == 0) return;

        if (newJumlah <= 0) return;

        var key = newItemKode + "-" + newSatuan;

        itemDetailData[key] = {
            item_code: newItemKode,
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
            var _hargajual = itemDetail['harga_jual_formatted'];
            var _potonganPersen = itemDetail['potongan_formatted'];
            var _total = itemDetail["total"];
            var _total_formatted = itemDetail["total_formatted"];

            tableBodyList.push(
                `<tr>` +
                `   <td><a href='javascript:void(0);' onclick='searchItem("` + _itemCode + `","` + _satuan + `")'>` + _itemCode + ` </a></td>` +
                `   <td>` + _itemNama + `</td>` +
                `   <td>` + _itemKategori + `</td>` +
                `   <td align='right'>` + _jumlah + `</td>` +
                `   <td>` + _satuan + `</td>` +
                `   <td align='right'>` + _hargajual + `</td>` +
                `   <td align='right'>` + _potonganPersen + `</td>` +
                `   <td align='right'>` + _total_formatted + `</td>` +
                `   <td>` +
                `       <a href='javascript:void(0);' class="text-danger" onclick="deleteItem('` + _itemCode + '-' + _satuan + `')"><i class="fa fa-times"></i> Hapus </a>` +
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
        $(".total-akhir").html(formatCurrency(totalBayar));

        $("#table-item-detail > tbody").html(tableBodyList.join(''));

        hitungTotalBayar($('#input-potongan').val());
        hitungSisaBayar($('#input-bayar').val());
    }

    function setPotonganHarga() {
        var checked = $("#chk-potongan-persen").prop("checked");
        if (pelangganPotonganPersen <= 0) checked = false;

        inputPotongan = 0;
        if (checked) {
            if (subTotal > 0) {
                inputPotongan = subTotal * (pelangganPotonganPersen / 100);
            }
        } else {
            inputPotongan = 0;
        }

        totalBayar = subTotal - inputPotongan;
        $("#input-potongan").val(inputPotongan);
        $(".total-akhir").html(formatCurrency(totalBayar));

        $("#input-bayar").val('0');
        $("#sisa-bayar").html(formatCurrency(totalBayar));
    }

    function getpelangganDetail(uuid) {
        if (uuid == null || uuid == undefined) return;

        ajax_get(
            '<?= base_url($uri_1 . '/penjualan/pelanggan_get_detail_by_uuid') ?>/' + uuid, {},
            function(json) {
                try {
                    if (json.is_success == 1) {
                        let data = json.data;
                        $("#pelanggan-detail").val(
                            'Alamat : ' +
                            data['alamat'] + '\n' +
                            'No. Telp : ' + data['no_telepon']
                        );
                        pelangganPotonganPersen = data['potongan_persen'];
                        $("#potongan-persen-pelanggan").html(pelangganPotonganPersen + "%");
                    } else {
                        show_toast("Perhatian", "Gagal menampilkan detail pemasok", "warning");
                    }
                } catch (error) {

                }
            }
        );
    }

    function setHargaSatuan(_satuan) {
        var selectedHargaSatuan = itemStrukturHargaList[newItemKode][_satuan];
        newHargajual = selectedHargaSatuan;
        $("#harga-jual").html(formatCurrency(selectedHargaSatuan));
        doHitungTotal();
        $("#new-item-potongan").focus();
    }

    function searchItem(kode, _satuan) {
        if (kode == null || kode == undefined) return;
        if (_satuan == null || _satuan == null) _satuan = "";

        $("#input-satuan").html("");

        ajax_get(
            '<?= base_url($uri_1 . "/item/search_flow_stock_out") ?>/', {
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
                        console.log(data);

                        newItemKode = data.kode;
                        $("#input-kode-item").val(newItemKode);
                        $("#new-item-nama").html(data.nama);
                        $("#new-item-kategori").html(data.nama_kategori);


                        itemStrukturHargaList[data.kode] = data.harga_list;
                        itemStrukturStockList[data.kode] = data.stock_list;

                        newItemNama = data.nama;
                        newItemKategori = data.nama_kategori;

                        cek_stock = data.cek_stock;

                        var i;
                        for (i = 0; i < data.satuan_list.length; i++) {
                            var satuan = data.satuan_list[i];
                            $("#input-satuan").append("<option value='" + satuan['name'] + "'>" + satuan['label'] + "</option>");
                        }

                        var selectedSatuan = $("#input-satuan:first").val();
                        var selectedHargaSatuan = data.harga_list[selectedSatuan];
                        newHargajual = selectedHargaSatuan;

                        if (itemDetailData.hasOwnProperty(newItemKode + "-" + _satuan)) {
                            let _selectedItemDetailData = itemDetailData[newItemKode + "-" + _satuan];

                            console.log(_selectedItemDetailData);

                            let _selectedJumlah = _selectedItemDetailData['jumlah'];
                            newHargajual = _selectedItemDetailData["harga_jual"];
                            console.log(newHargajual);

                            $("#new-item-jumlah").val(_selectedJumlah);
                            $("#harga-jual").html(formatCurrency(newHargajual));
                            $("#input-satuan").val(_satuan);
                        } else {
                            $("#new-item-jumlah").val(1);
                            $("#harga-jual").html(formatCurrency(newHargajual));

                            if (_satuan != "") {
                                _satuan = _satuan.toUpperCase();
                                $("#input-satuan").val(_satuan);
                                setHargaSatuan(_satuan);
                            }
                        }

                        doHitungTotal();
                        $("#new-item-jumlah").focus();
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
                    console.log(error);
                }
            }
        );
    }

    // function searchItem(itemKode) {
    //     if (itemKode == null || itemKode == undefined) return;

    //     $("#input-satuan").html("");
    //     ajax_get(
    //         '<?= base_url($uri_1 . "/penjualan/item_get_detail_by_kode") ?>/', {
    //             kode: itemKode
    //         },
    //         function(json) {
    //             clearNewItem();
    //             try {
    //                 if (json.is_success == 1) {
    //                     let data = json.data;

    //                     newItemKode = itemKode;
    //                     $("#input-kode-item").val(itemKode);
    //                     $("#new-item-nama").html(data.nama);
    //                     $("#new-item-kategori").html(data.nama_kategori);


    //                     itemStrukturHargaList[data.kode] = data.harga_list;
    //                     itemStrukturStockList[data.kode] = data.stock_list;

    //                     newItemNama = data.nama;
    //                     newItemKategori = data.nama_kategori;

    //                     cek_stock = data.cek_stock;

    //                     var i;
    //                     for (i = 0; i < data.satuan_list.length; i++) {
    //                         var satuan = data.satuan_list[i];
    //                         $("#input-satuan").append("<option value='" + satuan['name'] + "'>" + satuan['label'] + "</option>");
    //                     }

    //                     var selectedSatuan = $("#input-satuan:first").val();
    //                     var selectedHargaSatuan = data.harga_list[selectedSatuan];

    //                     newHargajual = selectedHargaSatuan;

    //                     $("#new-item-jumlah").val(1);
    //                     $("#harga-jual").html(formatCurrency(selectedHargaSatuan));

    //                     $("#new-item-jumlah").focus();


    //                     doHitungTotal($("#new-item-jumlah"));
    //                 } else {
    //                     switch (json.message) {
    //                         case "NO_DATA":
    //                             show_toast("Perhatian", "Item tidak ditemukan", "warning");
    //                             break;
    //                         case "EMPTY_KODE":
    //                             show_toast("Perhatian", "Kode item tidak boleh kosong", "warning");
    //                             break;

    //                         default:
    //                             show_toast("Error", json.message, "error");
    //                             break;
    //                     }
    //                 }
    //             } catch (error) {
    //                 console.log(error);
    //             }
    //         }
    //     );
    // }

    function confirmDelete(uuid) {
        var confirmed = confirm("Anda yakin ingin menghapus penjualan ini ? ");
        if (!confirmed) return;

        ajax_get(
            '<?= base_url($uri_1 . '/penjualan/ajax_delete') ?>/' + uuid, {},
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
        var formData = $("#form-penjualan").serializeArray();

        var itemDetailList = [];
        $.each(itemDetailData, function(itemKode) {
            itemDetailList.push(itemDetailData[itemKode]);
        });

        formData.push({
            name: 'item_detail',
            value: JSON.stringify(itemDetailList)
        });

        ajax_post(
            '<?= base_url($uri_1 . '/penjualan/ajax_save') ?>',
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