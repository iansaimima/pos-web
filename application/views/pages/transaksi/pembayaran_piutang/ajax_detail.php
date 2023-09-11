<?php

$number_formatted = "Otomatis";
$uuid = "";
$tanggal = date("Y-m-d");
$pelanggan_uuid = "";
$cara_bayar = "";
$kas_akun_uuid = "";
$jumlah = 0;
$keterangan = "";

$pembayaran_piutang_detail_list = array();

$judul = "Baru";

$boleh_hapus_pembayaran = 0;

if (isset($detail) && is_array($detail) && count($detail) > 0) {
    $uuid = trim($detail["uuid"]);
    $number_formatted = $detail["number_formatted"];
    $tanggal = date("Y-m-d", strtotime($detail["tanggal"]));
    $pelanggan_uuid = trim($detail["pelanggan_uuid"]);
    $kas_akun_uuid = trim($detail["kas_akun_uuid"]);
    $cara_bayar = $detail["cara_bayar"];
    $jumlah = (float) $detail["jumlah"];

    $keterangan = $detail["keterangan"];

    $pembayaran_piutang_detail_list = $detail["detail"];

    $boleh_hapus_pembayaran = (int) $detail["boleh_hapus_pembayaran"];

    $judul = "Detail";
}

?>
<div class="modal-header" style="padding-bottom: 0px;">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
    <h3 class="modal-title">Pembayaran Piutang <?= $judul ?></h3>
</div>


<div class="modal-body hide-scrollbar">
    <form onsubmit="return false" id="form-pembayaran-piutang" class="form-horizontal" autocomplete="off">
        <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
        <input type="hidden" name="uuid" value="<?= $uuid ?>">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group row mb-2">
                    <label class="col-sm-4 col-form-label">No.</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control input-sm" value="<?= $number_formatted ?>" readonly />
                    </div>
                </div>
                <div class="form-group row mb-2">
                    <label class="col-sm-4">Tanggal Pembayaran</label>
                    <div class="col-sm-8">
                        <input autocomplete="off" type="text" name="tanggal" class="form-control input-sm" id="tanggal-pembayaran" placeholder="yyyy-mm-dd" value="<?= $tanggal ?>" />
                    </div>
                </div>
                <div class="form-group row mb-2">
                    <label class="col-sm-4">Nama Pelanggan</label>
                    <div class="col-sm-8">
                        <select name="pelanggan_uuid" id="pelanggan-uuid" class="form-control input-sm select2" <?= !empty($uuid) ? "disabled" : "" ?>>
                            <option value="">Pilih</option>
                            <?php
                            if (isset($pelanggan_list) && is_array($pelanggan_list)) {
                                foreach ($pelanggan_list as $p) {
                                    $selected = "";
                                    if ($p['uuid'] == $pelanggan_uuid) $selected = "selected";
                            ?>
                                    <option value="<?= $p['uuid'] ?>" <?= $selected ?>><?= $p["nama"] ?></option>
                            <?php
                                }
                            }
                            ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group row mb-2">
                    <label class="col-sm-4">Cara Bayar</label>
                    <div class="col-sm-8">
                        <select name="cara_bayar" class="form-control input-sm">
                            <option value="Tunai" <?= strtolower($cara_bayar) == "tunai" ? "selected" : "" ?>>Tunai</option>
                            <option value="Transfer Bank" <?= strtolower($cara_bayar) == "transfer bank" ? "selected" : "" ?>>Transfer Bank</option>
                        </select>
                    </div>
                </div>
                <div class="form-group row mb-2">
                    <label class="col-sm-4">Masuk ke akun kas</label>
                    <div class="col-sm-8">
                        <select name="kas_akun_uuid" id="kas-akun-uuid" class="form-control input-sm select2">
                            <option value="" selected disabled>Pilih</option>
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
                </div>
                <div class="form-group row mb-2">
                    <label class="col-sm-4">Jumlah Bayar</label>
                    <div class="col-sm-8">
                        <input type="text" min="0" id="jumlah-bayar" name="jumlah_bayar" style="text-align: right;" class="form-control input-sm input-currency" value="<?= number_format($jumlah, 0, ",", ".") ?>" <?= !empty($uuid) ? "disabled" : "" ?> />
                    </div>
                </div>

                <?php
                if (empty($uuid)) {
                ?>
                    <div class="form-group row mb-2">
                        <label class="col-sm-4"></label>
                        <div class="col-sm-8">
                            <button type="button" class="btn btn-primary btn-block" onclick="loadKonfirmasi()">Konfirmasi Pembayaran</button>
                        </div>
                    </div>
                <?php
                }
                ?>
            </div>
        </div>

        <br />
        <div id="detail-konfirmasi-content">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>No. Penjualan</th>
                            <th>Tanggal</th>
                            <th>Jatuh Tempo</th>
                            <th style="text-align: right;">Piutang</th>
                            <th style="text-align: right;">Bayar</th>
                            <th style="text-align: right;">Sisa Piutang</th>
                            <th style="text-align: left;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $total_bayar = 0;
                        $total_sisa = 0;
                        $sisa_jumlah_bayar = 0;
                        $total_piutang = 0;
                        if (is_array($pembayaran_piutang_detail_list)) {
                            foreach ($pembayaran_piutang_detail_list as $l) {

                                $sisa_piutang = $l["sisa_piutang"];
                                $jumlah = $l["jumlah"];
                                $sisa = $sisa_piutang - $jumlah;

                                $total_piutang += $sisa_piutang;
                                $total_sisa += $sisa;


                                $total_bayar += (float) $jumlah;

                                $status = "Belum lunas";
                                $bg_color = "";
                                if ($sisa == 0) {
                                    $status = "Lunas";
                                    $bg_color = "background-color:#A5D6A7";
                                }
                        ?>
                                <tr style="<?= $bg_color ?>">
                                    <td><?= $l["number_formatted"] ?></td>
                                    <td><?= date("d M Y", strtotime($l["tanggal"])) ?></td>
                                    <td><?= date("d M Y", strtotime($l["jatuh_tempo"])) ?></td>
                                    <td align="right"> <?= number_format($sisa_piutang, 0, ",", ".") ?></td>
                                    <td align="right"> <?= number_format($jumlah, 0, ",", ".") ?></td>
                                    <td align="right"> <?= number_format($sisa, 0, ",", ".") ?></td>
                                    <td><?= $status ?></td>
                                </tr>
                        <?php
                            }
                        }
                        ?>
                    </tbody>

                    <tfoot>
                        <tr>
                            <td style="background-color: #eee;" colspan="6"></td>
                            <td style="background-color: #eee;"></td>
                        </tr>
                        <tr>
                            <td colspan="5">Keterangan</td>
                            <td align="right">Total Keseluruhan</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td colspan="3" rowspan="3">
                                <textarea name="keterangan" rows="3" class="form-control input-sm"><?= $keterangan ?></textarea>
                            </td>
                            <td colspan="3" align="right">Piutang</td>
                            <td style="text-align: right;"> <?= number_format($total_piutang, 0, ",", ".") ?></td>
                        </tr>
                        <tr>
                            <td colspan="3" align="right">Jumlah Bayar</td>
                            <td style="text-align: right;"> <?= number_format($jumlah, 0, ",", ".") ?></td>
                        </tr>
                        <tr>
                            <td colspan="3" align="right">Sisa</td>
                            <td style="text-align: right;"> <?= number_format($total_sisa, 0, ",", ".") ?></td>
                        </tr>
                        <?php
                        if ($sisa_jumlah_bayar > 0) {
                        ?>
                            <tr>
                                <td colspan="6" align="right" style="background-color: #FFF9C4; color: #E65100"><i class="fa fa-warning"></i> Kelebihan Bayar</td>
                                <td style="text-align: right; background-color: #FFF9C4; color: #E65100"> <?= number_format($sisa_jumlah_bayar, 0, ",", ".") ?></td>
                            </tr>
                        <?php
                        }
                        ?>
                    </tfoot>

                </table>
            </div>

            <?php
            if (!empty($uuid)) {
            ?>
                <div class="row">
                    <div class="col-md-6">
                        <?php
                        if ($boleh_hapus_pembayaran == 1) {
                        ?>
                            <button type="button" class="btn btn-danger" onclick="confirmDelete('<?= $uuid ?>')"> <i class="fa fa-trash-o"></i> Hapus</button>
                        <?php
                        } else {
                        ?>
                            <div class="alert alert-danger">
                                <p>Tidak bisa menghapus pembayaran piutang ini dikarenakan sudah ada pembayaran piutang setelah pembayaran piutang ini.</p>
                            </div>
                            <h4 style="margin-top: 0;" class="text-danger"></h4>
                        <?php
                        }
                        ?>
                    </div>
                    <?php
                    if ($sisa_jumlah_bayar > 0) {
                    ?>
                        <div class="col-md-6" style="text-align: right;">
                            <h3 class="text-warning" style="margin-top: 0;"> <i class="fa fa-warning"></i> Jumlah bayar melebihi piutang</h3>
                        </div>
                    <?php
                    } else {
                    ?>
                        <div class="col-md-2"></div>
                        <div class="col-md-4" style="text-align: right;">
                            <button type="button" class="btn btn-success" onclick="pembayaranPiutangSave()">Simpan Pembayaran</button>
                        </div>
                    <?php
                    }
                    ?>
                </div>
            <?php
            }
            ?>
        </div>
    </form>
</div>

<style>
    .select2-container {
        width: 100% !important;
        padding: 0;
    }
</style>

<script>
    $(document).ready(function() {
        $(".select2").select2({
            placeholder: "Pilih",
            dropdownparent: $("#modal-pelunasan"),
            theme: 'classic'
        });

        // $('#tanggal-pembayaran').datepicker({
        //     autoclose: true,
        //     todayHighlight: true,
        //     format: 'yyyy-mm-dd'
        // });



        $('#tanggal-pembayaran').bootstrapMaterialDatePicker({
            format: 'YYYY-MM-DD',
            weekStart: 0,
            time: false
        });

        $(".input-currency").on("keyup", function() {
            let val = $(this).val();
            $(this).val(formatCurrency(val));
        });
    });

    function loadKonfirmasi() {
        var formData = $('#form-pembayaran-piutang').serializeArray();

        var jumlahBayar = toNumber($("#jumlah-bayar").val());

        if ($("#pelanggan-uuid").val() == null || $("#pelanggan-uuid").val() == "") {
            show_toast("Error", "Pelanggan harus dipilih", "error");
            return;
        }

        if (jumlahBayar <= 0) {
            show_toast("Error", "Jumlah bayar harus diisi", "error");
            return;
        }

        ajax_get(
            '<?= base_url('admin/pembayaran_piutang/ajax_konfirmasi') ?>',
            formData,
            function(resp) {
                $("#detail-konfirmasi-content").html(resp);
            }
        );
    }

    function pembayaranPiutangSave() {
        var formData = $("#form-pembayaran-piutang").serializeArray();

        if ($("#pelanggan-uuid").val() == null || $("#pelanggan-uuid").val() == "") {
            show_toast("Error", "Pelanggan harus dipilih", "error");
            return;
        }
        if ($("#kas-akun-uuid").val() == null || $("#kas-akun-uuid").val() == "") {
            show_toast("Error", "Akun kas harus dipilih", "error");
            return;
        }

        if ($("#jumlah-bayar").val() == null || $("#jumlah-bayar").val() == "" || $("#jumlah-bayar").val() == "0") {
            show_toast("Error", "Jumlah bayar harus diisi", "error");
            return;
        }
        var jumlahBayar = parseInt($("#jumlah-bayar").val());
        if (jumlahBayar == NaN || jumlahBayar == 0) {
            show_toast("Error", "Jumlah bayar harus diisi", "error");
            return;
        }

        ajax_post(
            '<?= base_url('admin/pembayaran_piutang/ajax_save') ?>',
            formData,
            function(resp) {
                try {
                    var json = JSON.parse(resp);
                    if (json.is_success == 1) {
                        show_toast("Success", json.message, "success");

                        load_list();
                        load_detail(json.id);
                    } else {
                        show_toast("Error", json.message, "error");
                    }
                } catch (error) {
                    show_toast("Error", "Application response error", "error");
                }
            }
        );
    }

    function confirmDelete(uuid) {
        var confirmed = confirm("Anda yakin ingin menhapus pembayaran ini ? ");
        if (!confirmed) return;

        ajax_get(
            '<?= base_url('admin/pembayaran_piutang/ajax_delete') ?>/' + uuid, {},
            function(resp) {
                try {
                    var json = JSON.parse(resp);
                    if (json.is_success == 1) {
                        show_toast("Success", json.message, "success");
                        $("#modal-detail").modal("hide");
                        load_list();
                    } else {
                        show_toast("Error", json.message, "error", true);
                    }
                } catch (error) {
                    show_toast("Error", "Application response error", "error");
                }
            }
        );
    }
</script>