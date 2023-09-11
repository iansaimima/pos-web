<?php

$user = get_session("user");
$privilege_list = isset($user["privilege_list"]) ? $user["privilege_list"] : array();
$allow_create = isset($privilege_list["allow_kas_alur_create"]) ? $privilege_list["allow_kas_alur_create"] : 0;
$allow_update = isset($privilege_list["allow_kas_alur_update"]) ? $privilege_list["allow_kas_alur_update"] : 0;
$allow_delete = isset($privilege_list["allow_kas_alur_delete"]) ? $privilege_list["allow_kas_alur_delete"] : 0;

if (isset($detail) && is_array($detail)) {
  $uuid = "";
  $number = "Otomatis";
  $tanggal = date("Y-m-d");
  $kas_akun_uuid = "";
  $kas_kategori_uuid = "";
  $alur_kas = "Masuk";
  $jumlah = 0;
  $keterangan = "";

  $modal_title = "New";

  $transaksi_pembelian_uuid = "";
  $transaksi_penjualan_uuid = "";
  if (count($detail) > 0) {
    $uuid = trim($detail["uuid"]);
    $number = $detail["number_formatted"];
    $tanggal = date("Y-m-d", strtotime($detail["tanggal"]));
    $kas_akun_uuid = trim($detail["kas_akun_uuid"]);
    $kas_kategori_uuid = trim($detail["kas_kategori_uuid"]);
    $alur_kas = trim($detail["alur_kas"]);
    $jumlah_masuk = (int) $detail["jumlah_masuk"];
    $jumlah_keluar = (int) $detail["jumlah_keluar"];
    $keterangan = trim($detail["keterangan"]);
    $modal_title = "Edit";

    $transaksi_pembelian_uuid = $detail["transaksi_pembelian_uuid"];
    $transaksi_penjualan_uuid = $detail["transaksi_penjualan_uuid"];

    if (strtolower($alur_kas) == "masuk") $jumlah = $jumlah_masuk;
    if (strtolower($alur_kas) == "keluar") $jumlah = $jumlah_keluar;
  }

  $alur_kas = ucwords($alur_kas);

  $obj_disabled = "";
  if (!empty($transaksi_pembelian_uuid) || !empty($transaksi_penjualan_uuid)) $obj_disabled = "disabled";
?>
  <form id="form-detail" class="form-horizontal" onsubmit="return false" autocomplete="off">
    <input type="hidden" class="form-control" id="uuid" name="uuid" value="<?= $uuid ?>" />
    <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">

    <div class="form-group row mb-2">
      <label class="col-sm-3 col-form-label">No. Kas</label>
      <div class="col-sm-9">
        <input type="text" class="form-control input-sm" id="no-kas" value="<?= $number ?>" disabled readonly>
      </div>
    </div>

    <div class="form-group row mb-2">
      <label class="col-sm-3 col-form-label">Tanggal</label>
      <div class="col-sm-9">
        <input type="text" class="form-control input-sm" name="tanggal" <?= $obj_disabled ?> placeholder="yyyy-mm-dd" id="datepicker-tanggal" value="<?= date("Y-m-d", strtotime($tanggal)) ?>">
      </div>
    </div>

    <div class="form-group row mb-2">
      <label class="col-form-label col-sm-3">Akun</label>
      <div class="col-sm-9">
        <select name="kas_akun_uuid" id="kas-akun" <?= $obj_disabled ?> class="form-control input-sm combobox">
          <option value="" selected disabled>-- Pilih Akun --</option>
          <?php
          if (isset($kas_akun_list) && is_array($kas_akun_list)) {
            foreach ($kas_akun_list as $l) {
              $selected = "";
              if ($l["uuid"] == $kas_akun_uuid) $selected = "selected";
          ?>
              <option value="<?= $l["uuid"] ?>" <?= $selected ?>><?= $l["nama"] ?></option>
          <?php
            }
          }
          ?>
        </select>
      </div>
    </div>

    <div class="form-group row mb-2">
      <label class="col-sm-3 col-form-label">Arus</label>

      <div class="col-sm-9">
        <label class="radio-inline"> <input type="radio" <?= $obj_disabled ?> onchange="set_kas_kategori_list($(this).val())" <?= strtolower($alur_kas) == "masuk" ? "checked" : "" ?> name="alur_kas" value="Masuk" /> Masuk </label> &nbsp;
        <label class="radio-inline"> <input type="radio" <?= $obj_disabled ?> onchange="set_kas_kategori_list($(this).val())" <?= strtolower($alur_kas) == "keluar" ? "checked" : "" ?> name="alur_kas" value="Keluar" /> Keluar </label>
      </div>
    </div>

    <div class="form-group row mb-2">
      <label class="col-form-label col-sm-3">Kategori</label>
      <div class="col-sm-9">
        <select name="kas_kategori_masuk_uuid" <?= $obj_disabled ?> id="kas-kategori-masuk" class="form-control input-sm combobox select-kas-kategori">
          <option value="" selected disabled>-- Pilih Kategori Masuk --</option>
          <?php
          if (isset($kas_kategori_list) && is_array($kas_kategori_list)) {
            foreach ($kas_kategori_list as $l) {
              if (strtolower(trim($l["alur_kas"])) == "keluar") continue;
              $selected = "";
              if ($l["uuid"] == $kas_kategori_uuid) $selected = "selected";
          ?>
              <option value="<?= trim($l["uuid"]) ?>" <?= $selected ?>><?= $l["nama"] ?></option>
          <?php
            }
          }
          ?>
        </select>
        <select name="kas_kategori_keluar_uuid" <?= $obj_disabled ?> id="kas-kategori-keluar" class="form-control input-sm combobox select-kas-kategori" style="display: none">
          <option value="" selected disabled>-- Pilih Kategori Keluar --</option>
          <?php
          if (isset($kas_kategori_list) && is_array($kas_kategori_list)) {
            foreach ($kas_kategori_list as $l) {
              if (strtolower(trim($l["alur_kas"])) == "masuk") continue;
              $selected = "";
              if ($l["uuid"] == $kas_kategori_uuid) $selected = "selected";
          ?>
              <option value="<?= trim($l["uuid"]) ?>" <?= $selected ?>><?= $l["nama"] ?></option>
          <?php
            }
          }
          ?>
        </select>
      </div>
    </div>

    <div class="form-group row mb-2">
      <label class="col-sm-3 col-form-label">Jumlah</label>
      <div class="col-sm-9">
        <input type="text" class="form-control input-sm input-currency" <?= $obj_disabled ?> required name="jumlah" value="<?= number_format($jumlah, 0, ",", ".") ?>" />
      </div>
    </div>

    <div class="form-group row mb-2">
      <label class="col-sm-3 col-form-label">Keterangan</label>
      <div class="col-sm-9">
        <textarea class="form-control input-sm" rows="3" required <?= $obj_disabled ?> name="keterangan"><?= $keterangan ?></textarea>
      </div>
    </div>

    <hr />
    <div class="row">
      <div class="col-md-6">
        <?php
        if (empty($transaksi_pembelian_uuid) && empty($transaksi_penjualan_uuid)) {
          if (!empty($uuid)) {
            if ($allow_delete == 1) {
        ?>
              <button type="button" onclick="confirm_delete('<?= trim($uuid) ?>')" class="btn btn-sm btn-danger light"><i class='fa fa-trash-o'></i>&nbsp;Hapus</button>
          <?php
            } // if allow delete
          } // if id != 0
        } else {
          ?>
          <button class="btn btn-sm btn-secondary light" onclick="$('#modal-detail').modal('hide')"><i class="fa fa-close"></i>&nbsp; Close</button>
        <?php
        }
        ?>
      </div>
      <div class="col-md-6" style="text-align: right">


        <?php
        if (empty($transaksi_pembelian_uuid) && empty($transaksi_penjualan_uuid)) {
          if ($allow_create  == 1 || $allow_update == 1) {
        ?>
            <button class="btn btn-sm btn-secondary light" onclick="$('#modal-detail').modal('hide')"><i class="fa fa-close"></i>&nbsp; Batal</button>
            <button type="button" onclick="save()" class="btn btn-sm btn-primary"><i class='fa fa-save'></i>&nbsp;Simpan</button>
          <?php
          } // if allow create or update
        } else {
          ?>
          <h5>Dibuat otomatis melalui modul Transaksi</h5>
        <?php
        } // if transaksi pembelian id > 0
        ?>
      </div>
    </div>
  </form>

  <script>
    $(document).ready(function() {
      $("span#modal-title").html("<?= $modal_title ?>");

      set_kas_kategori_list('<?= $alur_kas ?>');

      $(".input-currency").on("keyup", function() {
        let val = $(this).val();
        $(this).val(formatCurrency(val));
      });
    });



    $('#datepicker-tanggal').bootstrapMaterialDatePicker({
      format: 'YYYY-MM-DD',
      weekStart: 0,
      time: false
    });

    function set_kas_kategori_list(value) {
      $(".select-kas-kategori").hide();

      if (value == undefined) value = "Masuk";

      if (value == "Masuk") $("#kas-kategori-masuk").show();
      if (value == "Keluar") $("#kas-kategori-keluar").show();
    }



    function save() {
      var form_data = $("#form-detail").serializeArray();
      ajax_post(
        "<?= base_url("admin/kas_alur/ajax_save") ?>",
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
            show_toast("Error", "Application response error");
          }
        }
      );
    }
  </script>
<?php
}
