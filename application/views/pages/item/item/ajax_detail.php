<?php

$uri_1 = $this->uri->segment(1);

$user = get_session("user");
$privilege_list = isset($user["privilege_list"]) ? $user["privilege_list"] : array();
$allow_create = isset($privilege_list["allow_item_create"]) ? $privilege_list["allow_item_create"] : 0;
$allow_update = isset($privilege_list["allow_item_update"]) ? $privilege_list["allow_item_update"] : 0;
$allow_set_arsip = isset($privilege_list["allow_item_set_arsip"]) ? $privilege_list["allow_item_set_arsip"] : 0;
$allow_set_aktif = isset($privilege_list["allow_item_set_aktif"]) ? $privilege_list["allow_item_set_aktif"] : 0;

$uuid = "";
$kode = "";
$barcode = "";
$nama = "";
$keterangan = "";

$item_kategori_uuid = "";
$tipe = "Jasa";
$cek_stock_saat_penjualan = 1;
$struktur_satuan_harga_list = array();
$minimum_stock = 0;
$jenis_perhitungan_harga_jual = "";

$cache_harga_pokok = 0;
$cache_stock = 0;
$margin_persen = 0;

$harga_jual_tipe_jasa = 0;
$satuan_tipe_jasa = "";

$arsip = 0;
$arsip_date = "";
$arsip_user_uuid = "";
$arsip_user_name = "";

$created = "";
$created_by = "";
$last_updated = "";
$last_updated_by = "";

$title = "Tambah";

$input_harga_pokok_disabled = "disabled";
if (isset($detail) && is_array($detail) && count($detail) > 0) {
    $uuid = trim($detail["uuid"]);
    $kode = trim($detail["kode"]);
    $barcode = trim($detail["barcode"]);
    $nama = trim($detail["nama"]);
    $keterangan = trim($detail["keterangan"]);

    $item_kategori_uuid = trim($detail["item_kategori_uuid"]);

    $harga_jual_tipe_jasa = (float) $detail["harga_jual_tipe_jasa"];
    $satuan_tipe_jasa = $detail["satuan_tipe_jasa"];

    $cache_harga_pokok = (float) $detail["cache_harga_pokok"];
    $cache_stock = (int) $detail["cache_stock"];
    $margin_persen = (float) $detail["margin_persen"];
    $tipe = $detail["tipe"];
    $minimum_stock = (int) $detail["minimum_stock"];
    $cek_stock_saat_penjualan = (int) $detail["cek_stock_saat_penjualan"];

    if (strtolower($tipe) == "jasa") $input_harga_pokok_disabled = "";

    $created = $detail["created"];
    $created_by = $detail["creator_user_name"];

    $last_updated = $detail["last_updated"];
    $last_updated_by = $detail["last_updated_user_name"];

    $arsip = (int) $detail["arsip"];
    $arsip_date = $detail["arsip_date"];
    $arsip_user_name = $detail["arsip_user_name"];

    $jenis_perhitungan_harga_jual = trim($detail["jenis_perhitungan_harga_jual"]);

    $struktur_satuan_harga_list = json_decode($detail["struktur_satuan_harga_json"], true);
    if (json_last_error() !== JSON_ERROR_NONE) $struktur_satuan_harga_list = array();

    $title = "Ubah";
}

$status = "";

if (!empty($uuid)) {
    if ($arsip == 0) {
        $status = "<div class='alert alert-success solid'><h4 class='text-white'><i class='fa fa-info-circle'></i> Aktif</h4></div>";
    } else {
        $status = "<div class='alert alert-danger solid'><h4 class='text-white'><i class='fa fa-info-circle'></i> Diarsip oleh $arsip_user_name pada $arsip_date</h4> </div>";
    }
} else {
    $status = "<div class='alert alert-info solid'><h4 class='text-white'><i class='fa fa-info-circle'></i> Belum disimpan</h4></div>";
}

?>
<div class="row">
    <div class="col-md-12 col-lg-12 col-sm-12">
        <div class="card">
            <div class="card-header border-0 pb-0">
                <h5 class="card-title">
                    <a href="javascript:void(0)" onclick="load_list()"><i class="fa fa-arrow-left "></i></a> &nbsp; Detail Item - <?= $title ?>
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12 col-sm-12">
                        <?= $status ?>
                    </div>
                </div>

                <form id="form-item-detail" class="form-horizontal" onsubmit="return false">
                    <input type="hidden" name="uuid" value="<?= $uuid ?>" />
                    <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
                    <div class="row">
                        <div class="col-md-6 col-sm-12">
                            <div class="form-group row mb-2">
                                <label class="col-form-label col-sm-3">Kode</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control input-sm input-detail" name="kode" value="<?= $kode ?>" />
                                </div>
                            </div>

                            <div class="form-group row mb-2">
                                <label class="col-form-label col-sm-3">Barcode</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control input-sm input-detail" name="barcode" value="<?= $barcode ?>" />
                                </div>
                            </div>

                            <div class="form-group row mb-2">
                                <label class="col-form-label col-sm-3">Nama</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control input-sm input-detail" name="nama" value="<?= $nama ?>" />
                                </div>
                            </div>

                            <div class="form-group row mb-2">
                                <label class="col-form-label col-sm-3">Keterangan</label>
                                <div class="col-sm-9">
                                    <textarea name="keterangan" rows="4" class="form-control input-sm input-detail"><?= $keterangan ?></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-sm-12">

                            <div class="form-group row mb-2">
                                <label class="col-form-label col-sm-5">Kategori</label>
                                <div class="col-sm-7">
                                    <select name="item_kategori_uuid" class="form-control input-sm select2 input-detail">
                                        <option value="" selected disabled>Pilih Kategori</option>

                                        <?php
                                        if (isset($item_kategori_list) && is_array($item_kategori_list)) {
                                            foreach ($item_kategori_list as $l) {
                                                $selected = "";

                                                if ($l["uuid"] == $item_kategori_uuid) $selected = "selected";
                                        ?>
                                                <option value="<?= $l['uuid'] ?>" <?= $selected ?>><?= $l["nama"] ?></option>
                                        <?php
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>

                            <?php if (empty($uuid)) {
                            ?>
                                <div class="form-group row mb-2">
                                    <label class="col-form-label col-sm-5">Tipe</label>
                                    <div class="col-sm-7">
                                        <select name="tipe" class="form-control" onchange="setInputBarangVisibility($(this).val())">
                                            <option value="Barang" <?= strtolower($tipe) == "barang" ? "selected" : "" ?>>Barang</option>
                                            <option value="Jasa" <?= strtolower($tipe) == "jasa" ? "selected" : "" ?>>Jasa</option>
                                        </select>
                                    </div>
                                </div>
                            <?php
                            } else {
                            ?>
                                <div class="form-group row mb-2">
                                    <label class="col-form-label col-sm-5">Tipe</label>
                                    <div class="col-sm-7">
                                        <input type="text" class="form-control input-sm input-detail" name="tipe" value="<?= $tipe ?>" readonly />
                                    </div>
                                </div>
                            <?php
                            } ?>

                            <div class="form-group row mb-2 input-tipe-barang">
                                <label class="col-form-label col-sm-5">Cek Stock Saat Penjualan</label>
                                <div class="col-sm-7">
                                    <label class="radion-inline"><input type="radio" name="cek_stock_saat_penjualan" value="1" <?= $cek_stock_saat_penjualan == 1 ? "checked" : "" ?> onchange="set_input_stock_saat_penjualan()"> &nbsp;Ya</label> &nbsp;&nbsp;
                                    <label class="radion-inline"><input type="radio" name="cek_stock_saat_penjualan" value="0" <?= $cek_stock_saat_penjualan == 0 ? "checked" : "" ?> onchange="set_input_stock_saat_penjualan()"> &nbsp;Tidak</label>
                                    <p id="cek-stock-saat-penjualan-keterangan" class="small">
                                        <b>Ya</b> = <u>Tidak bisa</u> menjual item jika stock kurang dari <b>Min. Stock (Satuan Terkecil)</b> atau sudah habis <br />
                                        <b>Tidak</b> = <u>Bisa</u> menjual item jika stock kurang dari <b>Min. Stock (Satuan Terkecil)</b> atau sudah habis
                                    </p>
                                </div>
                            </div>

                            <div class="form-group row mb-0 input-tipe-barang input-cek-stock-saat-jual-ya">
                                <label class="col-form-label col-sm-5">Min. Stock (Satuan terkecil)</label>
                                <div class="col-sm-7">
                                    <input type="text" class="form-control input-sm input-detail input-currency" name="minimum_stock" value="<?= number_format($minimum_stock, 0, ',', '.') ?>" />
                                </div>
                                <label class="col-form-label col-sm-5"></label>
                            </div>

                            <div class="form-group row mb-2 input-tipe-barang input-cek-stock-saat-jual-ya">
                                <label class="col-form-label col-sm-5">Harga Pokok</label>
                                <div class="col-sm-7">
                                    <input type="text" class="form-control input-sm input-detail" id="input-harga-pokok" value="<?= number_format($cache_harga_pokok, 0, ',', '.') ?>" disabled />
                                    <small>*) Harga Pokok hanya bisa diisi melalui <b>Persediaan Stock Awal</b> dan <b>Transaksi Pembelian</b></small>
                                </div>
                            </div>

                            <div class="form-group row mb-2 input-tipe-barang input-cek-stock-saat-jual-tidak">
                                <label class="col-form-label col-sm-5">Harga Pokok</label>
                                <div class="col-sm-7">
                                    <input type="text" class="form-control input-sm input-detail input-currency" name="harga_pokok" value="<?= number_format($cache_harga_pokok, 0, ',', '.') ?>" />
                                </div>
                            </div>

                            <div class="form-group row mb-2 input-tipe-barang input-cek-stock-saat-jual-ya">
                                <label class="col-form-label col-sm-5">Margin</label>
                                <div class="col-sm-3">
                                    <div class="input-group">
                                        <input type="text" name="margin_persen" maxlength="3" class="form-control input-sm input-detail" id="input-margin" value="<?= $margin_persen ?>" />
                                        <div class="input-group-append">
                                            <span class="input-group-text">%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row mb-2 input-tipe-jasa input-cek-stock-saat-jual-tidak">
                                <label class="col-form-label col-sm-5">Harga Jual</label>
                                <div class="col-sm-7">
                                    <input type="text" class="form-control input-sm input-detail input-currency" name="harga_jual_tipe_jasa" value="<?= number_format($harga_jual_tipe_jasa, 0, ',', '.') ?>" />
                                </div>
                            </div>

                            <div class="form-group row mb-2 input-tipe-jasa input-cek-stock-saat-jual-tidak">
                                <label class="col-form-label col-sm-5">Satuan</label>
                                <div class="col-sm-7">
                                    <input type="text" class="form-control input-sm input-detail" name="satuan_tipe_jasa" value="<?= $satuan_tipe_jasa ?>" />
                                </div>
                            </div>

                        </div>
                    </div>
                </form>
            </div>
            <div class="card-footer border-0">
                <div class="row">
                    <div class="col-md-6">
                        <button type="button" class="btn btn-secondary light btn-sm" onclick="load_list()"><i class="fa fa-ban"></i> Batal</button>

                        &nbsp;
                        <?php
                        if (!empty($uuid)) {
                            if ($allow_update) {
                        ?>
                                <button type="button" class="btn btn-success btn-sm" onclick="save()"><i class="fa fa-save"></i> Update</button>
                            <?php
                            } // end if allow update
                        } else {
                            if ($allow_create) {
                            ?>
                                <button type="button" class="btn btn-success btn-sm" onclick="save()"><i class="fa fa-save"></i> Simpan</button>
                        <?php
                            } // end if allow create
                        } // end if !empty($uuid)
                        ?>

                    </div>

                    <div class="col-md-6" style="text-align: right;">
                        <?php
                        if (!empty($uuid)) {
                            if ($allow_set_arsip == 1 && $arsip == 0) {
                        ?>
                                <button type="button" class="btn btn-danger light btn-sm" onclick="set_arsip('<?= $uuid ?>')"><i class="fa fa-archive"></i> Set Arsip</button>
                            <?php
                            } // end if allow set arsip && arsip == 0

                            if ($allow_set_aktif == 1 && $arsip == 1) {
                            ?>
                                <button type="button" class="btn btn-primary btn-sm" onclick="set_aktif('<?= $uuid ?>')"><i class="fa fa-check"></i> Set Aktif</button>
                        <?php
                            } // end if allow set aktif && arsip == 1
                        }
                        ?>
                    </div>
                </div>


            </div>
        </div>
    </div>
</div>

<?php

$prev_stauan_name = "";
$prev_harga_pokok = 0;
if (!empty($uuid)) : ?>
    <div class="row input-tipe-barang input-cek-stock-saat-jual-ya">
        <div class="col-md-12">
            <div class="card card-primary">
                <div class="card-header border-0 pb-0">
                    <h4 class="card-title">Tabel Konversi Satuan dan Harga</h4>
                    <div class="card-tools">
                        <button type="button" class="btn btn-sm btn-primary" onclick="show_table_struktur()"><i class="fa fa-plus"></i> Tambah Jenis Satuan</button>
                    </div>
                </div>
                <div class="card-body table-responsive">
                    <div class="alert alert-warning">
                        <h5><i class="fa fa-warning"></i> &nbsp; Perhatian</h5>
                        <p>
                            Disarankan untuk tidak melakukan perubahan struktur jika item ini telah digunakan pada Transaksi Pembelian, Transaksi Penjualan atau digunakan pada Persedian.
                            Melakukan perubahan struktur setelah digunakan pada Transaksi ataupun Peresediaan dapat menyebabkan perhitungan stock atau harga yang tidak teratur.
                        </p>
                    </div>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Satuan</th>
                                <th>Konversi</th>
                                <th>Harga Pokok</th>
                                <th>Harga Jual</th>
                                <th>Stock</th>
                                <th width="200"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (isset($struktur_satuan_harga_list) && is_array($struktur_satuan_harga_list)) {

                                $length = count($struktur_satuan_harga_list);
                                $count = 1;
                                $stock = $cache_stock;
                                foreach ($struktur_satuan_harga_list as $key => $l) {
                                    $class_name = str_replace(" ", "_", $key);

                                    // $stock = $cache_stock / (int) $l['konversi'];

                                    $harga_pokok = (float) $l["harga_pokok"];
                                    $harga_jual = 0;
                                    $margin_nilai = 0;
                                    if ($margin_persen > 0 && $harga_pokok > 0) {
                                        $margin_nilai = ($harga_pokok * $margin_persen) / 100;
                                    }

                                    $harga_jual = $harga_pokok + $margin_nilai;

                                    $stock = $stock / (int) $l['konversi'];
                                    if($stock > 0) $stock = round($stock, 2, PHP_ROUND_HALF_DOWN);

                                    $stock_str = $stock;
                                    $exp = explode(".", $stock_str);
                                    $stock = (int) $exp[0];
                            ?>
                                    <tr>
                                        <td width="180">
                                            <input type="text" value="<?= $l["satuan"] ?>" class="form-control input-sm input-table-struktur-<?= $class_name ?> satuan-<?= $class_name ?> input-table-struktur-edit" disabled />
                                        </td>
                                        <td width="220">
                                            <div class="input-group input-success">
                                                <?php if (!empty($prev_stauan_name)) : ?>
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text edit-satuan-addon" style="border-radius: 4px 0 0 4px">1 <?= $l['satuan'] ?> = </span></span>
                                                    </div>
                                                <?php endif ?>
                                                <input type="text" value="<?= $l["konversi"] ?>" min="0" data-class_name="<?= $class_name ?>" class="form-control input-currency input-sm 
                                                            input-table-struktur-<?= $class_name ?> 
                                                            konversi-<?= $class_name ?> 
                                                            input-table-struktur-edit 
                                                            edit-struktur-konversi" onkeyup="set_edit_harga_pokok($(this), <?= $prev_harga_pokok ?>)" />

                                                <?php if (!empty($prev_stauan_name)) : ?>
                                                    <div class="input-group-append">
                                                        <span class="input-group-text"><span><?= $prev_stauan_name ?></span></span>
                                                    </div>
                                                <?php endif ?>
                                            </div>
                                        </td>
                                        <td width="180">
                                            <div class="input-group input-success">
                                                <input type="text" style="text-align: right;" value="<?= number_format($harga_pokok, 0, ',', '.') ?>" min="0" data-class_name="<?= $class_name ?>" class="form-control input-sm input-table-struktur-<?= $class_name ?> harga-pokok-<?= $class_name ?> input-table-struktur-edit edit-struktur-harga-pokok" disabled />
                                                <div class="input-group-append">
                                                    <span class="input-group-text">/ <span><?= $l["satuan"] ?></span></span>
                                                </div>
                                            </div>
                                        </td>
                                        <td width="180">
                                            <div class="input-group input-success">
                                                <input type="text" style="text-align: right;" value="<?= number_format($harga_jual, 0, ',', '.') ?>" min="0" data-class_name="<?= $class_name ?>" class="form-control input-currency input-sm input-table-struktur-<?= $class_name ?> harga-jual-<?= $class_name ?> input-table-struktur-edit edit-struktur-harga-jual input-struktur-harga-jual" disabled />
                                                <div class="input-group-append">
                                                    <span class="input-group-text">/ <span><?= $l["satuan"] ?></span></span>
                                                </div>
                                            </div>
                                        </td>
                                        <td width="180">
                                            <div class="input-group input-success">
                                                <input type="text" style="text-align: right;" value="<?= number_format($stock, 2, ',', '.') ?>" min="0" class="form-control input-sm input-table-struktur-<?= $class_name ?> stock-<?= $class_name ?> input-table-struktur-edit" disabled />
                                                <div class="input-group-append">
                                                    <span class="input-group-text"> <span><?= $l["satuan"] ?></span></span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-smx btn-success" onclick="edit_struktur('<?= $class_name ?>', '<?= $key ?>')"> <i class="fa fa-check"></i> </button>
                                                <?php if ($count == $length) : ?>
                                                    <button type="button" class="btn btn-smx btn-danger" onclick="delete_struktur_row('<?= $key ?>')"><i class="fa fa-trash"></i></button>
                                                <?php endif ?>
                                            </div>
                                        </td>
                                    </tr>
                            <?php
                                    $prev_stauan_name = $l["satuan"];
                                    $prev_harga_pokok = (int) $l["harga_pokok"];

                                    $count++;
                                }
                            }
                            ?>
                        </tbody>
                        <tfoot style="display: none" id="table-struktur-new">
                            <tr>
                                <td width="180">
                                    <input type="text" id="new-satuan" class="form-control input-sm input-table-struktur-new" onkeyup="$('.new-satuan-addon span').html($(this).val())" />
                                </td>
                                <td width="220">
                                    <div class="input-group input-primary">

                                        <?php if (!empty($prev_stauan_name)) : ?>
                                            <div class="input-group-prepend">
                                                <span class="input-group-text new-satuan-addon" style="border-radius: 4px 0 0 4px">1 <span>&nbsp;</span>&nbsp;= </span></span>
                                            </div>
                                        <?php endif ?>
                                        <input type="text" min="0" id="new-konversi" class="form-control input-sm input-table-struktur-new" onkeyup="set_new_harga_pokok($(this).val(),<?= $prev_harga_pokok ?>) " />

                                        <?php if (!empty($prev_stauan_name)) : ?>
                                            <div class="input-group-append">
                                                <span class="input-group-text new-satuan-addon"><?= $prev_stauan_name ?> </span>
                                            </div>
                                        <?php endif ?>
                                    </div>
                                </td>
                                <td width="180">
                                    <div class="input-group input-primary">
                                        <input type="text" min="0" id="new-harga-pokok" class="form-control input-sm input-table-struktur-new" disabled />
                                        <div class="input-group-append">
                                            <span class="input-group-text new-satuan-addon">/ <span></span></span>
                                        </div>
                                    </div>
                                </td>
                                <td width="180">
                                    <div class="input-group input-primary">
                                        <input type="text" min="0" id="new-harga-jual" class="form-control input-currency input-sm input-table-struktur-new input-struktur-harga-jual" disabled />
                                        <div class="input-group-append">
                                            <span class="input-group-text new-satuan-addon">/ <span></span> </span>
                                        </div>
                                    </div>
                                </td>
                                <td></td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-smx btn-primary no-shadowx" onclick="add_struktur()"> <i class="fa fa-arrow-up"></i> </button>
                                        <button type="button" class="btn btn-smx btn-secondary light no-shadowx" onclick="hide_table_struktur()"><i class="fa fa-ban"></i></button>
                                    </div>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                    <em>*) Susunlah Table Konversi satuan dan harga dari satuan terkecil.</em>
                </div>
            </div>
        </div>
    </div>
<?php endif ?>

<script>
    var harga_pokok_master = <?= $cache_harga_pokok ?>;
    var prev_harga_pokok = <?= $prev_harga_pokok ?>;
    var tipe = '<?= strtolower($tipe) ?>';
    var margin_persen = <?= $margin_persen ?>;
    $(document).ready(function() {
        initSelect2();

        $(".edit-struktur-harga-jual").on("keyup", function() {
            let class_name = $(this).data("class_name");

            let harga_jual = toNumber($(this).val());
            let harga_pokok = toNumber($(".harga-pokok-" + class_name).val());

            if ($(this).val() == "" || $(this).val() == null || $(this).val() == undefined) {}
        });

        $(".input-currency").on("keyup", function() {
            let val = $(this).val();
            $(this).val(formatCurrency(val));
        });

        setInputBarangVisibility(tipe);
    });

    function set_input_stock_saat_penjualan() {
        var cekStockSaatPenjualan = $("input[name=cek_stock_saat_penjualan]:checked").val();
        if (cekStockSaatPenjualan == "1") {
            $(".input-cek-stock-saat-jual-ya").show();
            $(".input-cek-stock-saat-jual-tidak").hide();
        } else {
            $(".input-cek-stock-saat-jual-ya").hide();
            $(".input-cek-stock-saat-jual-tidak").show();
        }
    }

    function setInputBarangVisibility(tipe) {

        if (tipe.toLowerCase() == "jasa") {
            $(".input-tipe-barang").hide();
            $(".input-tipe-jasa").show();
        } else {
            $(".input-tipe-barang").show();
            $(".input-tipe-jasa").hide();
            set_input_stock_saat_penjualan();

        }
    }

    $(window).resize(function() {
        initSelect2();
    });

    function initSelect2() {
        $(".select2").select2({
            theme: 'classic'
        });
    }

    function show_table_struktur() {
        $(".input-table-struktur-new").val("");
        $('#table-struktur-new').fadeIn(function() {
            $("#new-satuan").focus();
        });
    }

    function hide_table_struktur() {
        $("#table-struktur-new").fadeOut();
    }

    function set_new_harga_pokok(nilai_konversi) {
        var harga_pokok = 0;

        if (nilai_konversi != null && nilai_konversi != undefined) {
            harga_pokok = harga_pokok_master / nilai_konversi;
        }

        if (prev_harga_pokok > 0) {
            harga_pokok = prev_harga_pokok * nilai_konversi;
        }

        if (harga_pokok > 0) {
            harga_pokok = Math.round(harga_pokok);
        }

        $("#new-harga-pokok").val(harga_pokok);

        var margin_nilai = 0;
        if (margin_persen > 0 && harga_pokok > 0) {
            margin_nilai = (harga_pokok * margin_persen) / 100;
        }
        harga_jual = harga_pokok + margin_nilai;


        $("#new-harga-jual").attr({
            "min": harga_jual
        });
        $("#new-harga-jual").val(formatCurrency(harga_jual));
    }

    function set_edit_harga_pokok(_this, _prev_harga_pokok) {
        var class_name = _this.data("class_name");
        var nilai_konversi = toNumber(_this.val());
        var harga_pokok = 0;

        if (nilai_konversi != null && nilai_konversi != undefined) {
            harga_pokok = harga_pokok_master / nilai_konversi;
        }

        if (_prev_harga_pokok > 0) {
            harga_pokok = _prev_harga_pokok * nilai_konversi;
        }

        if (harga_pokok > 0) {
            harga_pokok = Math.round(harga_pokok);
        }

        $(".harga-pokok-" + class_name).val(formatCurrency(harga_pokok + ""));
    }

    function delete_struktur_row(key) {
        if (key == null || key == undefined) {
            show_toast("Error", "Kunci Sruktur satuan dan harga tidak valid");
            return;
        }

        var confirmed = confirm("Yakin ingin hapus satuan ini ?");
        if (!confirmed) return;

        confirmed = confirm("Menghapus struktur ini akan berdampak pada transaksi pembelian maupun penjualan.");
        if (!confirmed) return;

        confirmed = confirm("Menghapus struktur ini juga bisa berdampak pada jumlah stock yang bisa saja tidak sesuai dengan stock fisik di gudang anda.");
        if (!confirmed) return;

        ajax_get(
            '<?= base_url($uri_1 . "/item/ajax_delete_struktur_harga_json_row") ?>', {
                uuid: '<?= $uuid ?>',
                key: key
            },
            function(resp) {
                try {
                    var json = JSON.parse(resp);
                    if (json.is_success == 1) {
                        show_toast("Success", json.message, "success");
                        load_detail('<?= $uuid ?>');
                    } else {
                        show_toast("Error", json.message, "error");
                    }
                } catch (error) {
                    show_toast("Error", "Application response error", "error");
                }
            }
        );
    }

    function edit_struktur(class_name, key) {
        let satuan = $(".satuan-" + class_name).val();
        let konversi = toNumber($(".konversi-" + class_name).val());
        let harga_pokok = toNumber($(".harga-pokok-" + class_name).val());
        let harga_jual = toNumber($(".harga-jual-" + class_name).val());

        let cache_harga_pokok = <?= $cache_harga_pokok ?>;

        if (satuan == "") {
            show_toast("Error", "Satuan tidak boleh kosong", "error");
            return;
        }

        if (konversi <= "0") {
            show_toast("Error", "Konversi harus lebih besar dari 0", "error");
            return;
        }

        if (cache_harga_pokok > 0) {
            if (harga_pokok <= 0) {
                harga_pokok = cache_harga_pokok;
            }
        }

        if (harga_jual <= 0) {
            // show_toast("Error", "Harga jual harus lebih besar dari 0", "error");
            // return;
        }

        if (harga_jual < harga_pokok) {
            // show_toast("Error", "Harga jual tidak boleh lebih kecil dari harga pokok", "error");
            // return;
        }

        var struktur_json = {
            "satuan": satuan,
            "konversi": konversi,
            "harga_pokok": harga_pokok,
            "harga_jual": harga_jual,
            "stock": 0
        };

        ajax_post(
            '<?= base_url($uri_1 . "/item/ajax_update_struktur_harga_json") ?>', {
                uuid: '<?= $uuid ?>',
                key: key,
                action: "edit",
                struktur_satuan_harga_json: JSON.stringify(struktur_json)
            },
            function(resp) {
                try {
                    var json = JSON.parse(resp);
                    if (json.is_success == 1) {
                        show_toast("Success", json.message, "success");
                        load_detail('<?= $uuid ?>');
                    } else {
                        show_toast("Error", json.message, "error");
                    }
                } catch (error) {
                    show_toast("Error", "Application response error", "error");
                }
            }
        );
    }

    function add_struktur() {
        let satuan = $("#new-satuan").val();
        let konversi = toNumber($("#new-konversi").val());
        let harga_pokok = toNumber($("#new-harga-pokok").val());
        let harga_jual = toNumber($("#new-harga-jual").val());



        let cache_harga_pokok = <?= $cache_harga_pokok ?>;

        if (satuan == "") {
            show_toast("Error", "Satuan tidak boleh kosong", "error");
            return;
        }

        if (konversi <= "0") {
            show_toast("Error", "Konversi harus lebih besar dari 0", "error");
            return;
        }

        if (cache_harga_pokok > 0) {
            if (harga_pokok <= 0) {
                show_toast("Error", "Harga pokok harus lebih besar dari 0", "error");
                return;
            }
        }

        if (harga_jual <= 0) {
            // show_toast("Error", "Harga jual harus lebih besar dari 0", "error");
            // return;
        }

        if (harga_jual < harga_pokok) {
            // show_toast("Error", "Harga jual tidak boleh lebih kecil dari harga pokok", "error");
            // return;
        }

        var struktur_json = {
            "satuan": satuan,
            "konversi": konversi,
            "harga_pokok": harga_pokok,
            "harga_jual": harga_jual,
            "stock": 0
        };

        ajax_post(
            '<?= base_url($uri_1 . "/item/ajax_update_struktur_harga_json") ?>', {
                uuid: '<?= $uuid ?>',
                key: satuan,
                action: "add",
                struktur_satuan_harga_json: JSON.stringify(struktur_json)
            },
            function(resp) {
                try {
                    var json = JSON.parse(resp);
                    if (json.is_success == 1) {
                        show_toast("Success", json.message, "success");
                        load_detail('<?= $uuid ?>');
                    } else {
                        show_toast("Error", json.message, "error");
                    }
                } catch (error) {
                    show_toast("Error", "Application response error", "error");
                }
            }
        );
    }

    function save() {
        var form_data = $("#form-item-detail").serializeArray();

        ajax_post(
            '<?= base_url($uri_1 . "/item/ajax_save") ?>',
            form_data,
            function(resp) {
                try {
                    var json = JSON.parse(resp);
                    if (json.is_success == 1) {
                        show_toast("Success", json.message, "success");
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

    function set_arsip(uuid) {
        var confirmed = confirm("Anda yakin ingin arsip item ini ? ");
        if (!confirmed) return;

        ajax_get(
            '<?= base_url($uri_1 . "/item/ajax_set_arsip") ?>/' + uuid, {},
            function(resp) {
                try {
                    var json = JSON.parse(resp);
                    if (json.is_success == 1) {
                        show_toast("Success", json.message, "success");
                        load_detail(uuid);
                    } else {
                        show_toast("Error", json.message, "error");
                    }
                } catch (error) {
                    show_toast("Error", "Application response error", "error");
                }
            }
        );
    }

    function set_aktif(uuid) {
        var confirmed = confirm("Anda yakin ingin aktifkan item ini ? ");
        if (!confirmed) return;

        ajax_get(
            '<?= base_url($uri_1 . "/item/ajax_set_aktif") ?>/' + uuid, {},
            function(resp) {
                try {
                    var json = JSON.parse(resp);
                    if (json.is_success == 1) {
                        show_toast("Success", json.message, "success");
                        load_detail(uuid);
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