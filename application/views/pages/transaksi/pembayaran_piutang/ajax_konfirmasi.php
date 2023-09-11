<hr />
<?php
$penjualan_belum_lunas_list = isset($penjualan_belum_lunas_data["daftar_penjualan_belum_lunas"]) ? $penjualan_belum_lunas_data["daftar_penjualan_belum_lunas"] : array();
$saldo_pelanggan = isset($penjualan_belum_lunas_data["saldo_pelanggan"]) ? $penjualan_belum_lunas_data["saldo_pelanggan"] : 0;
$jumlah_bayar = isset($penjualan_belum_lunas_data["jumlah_bayar"]) ? $penjualan_belum_lunas_data["jumlah_bayar"] : 0;
$sisa_jumlah_bayar = isset($penjualan_belum_lunas_data["sisa_jumlah_bayar"]) ? $penjualan_belum_lunas_data["sisa_jumlah_bayar"] : 0;
$total_piutang = isset($penjualan_belum_lunas_data["total_piutang"]) ? $penjualan_belum_lunas_data["total_piutang"] : 0;

if (count($penjualan_belum_lunas_list) == 0) {
    echo "<h3 style='text-align: center'>Tidak ada penjualan yang belum lunas untuk pelanggan terpilih</h3>";
    die();
}
?>
<div class="row">
    <div class="col-md-12">
        <div class="table-responsive">
            <table class="table">
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
                    if (is_array($penjualan_belum_lunas_list)) {
                        foreach ($penjualan_belum_lunas_list as $l) {
                            $sisa = (float) $l["sisa"];
                            $total_sisa += $sisa;

                            $total_bayar += (float) $l["jumlah_bayar"];

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
                                <td align="right"> <?= number_format($l["sisa_piutang"], 0, ",", ".") ?></td>
                                <td align="right"> <?= number_format($l["jumlah_bayar"], 0, ",", ".") ?></td>
                                <td align="right"> <?= number_format($l["sisa"], 0, ",", ".") ?></td>
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
                            <textarea name="keterangan" rows="3" class="form-control input-sm"></textarea>
                        </td>
                        <td colspan="3" align="right">Piutang</td>
                        <td style="text-align: right;"> <?= number_format($total_piutang, 0, ",", ".") ?></td>
                    </tr>
                    <tr>
                        <td colspan="3" align="right">Jumlah Bayar</td>
                        <td style="text-align: right;"> <?= number_format($jumlah_bayar, 0, ",", ".") ?></td>
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
    </div>
</div>

<div class="row">
    <div class="col-md-6"></div>
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
            <button type="button" class="btn btn-success btn-block" onclick="pembayaranPiutangSave()">Simpan Pembayaran</button>
        </div>
    <?php
    }
    ?>
</div>