<?php
$setting = get_session("setting_data");

$file_logo_header = "assets/images/logo.png";
$settings = get_session("settings");

$app_name = APP_NAME;
if (isset($settings["TOKO_NAMA"])) $app_name = $settings["TOKO_NAMA"]["_value"];

$logo_url = base_url("assets/images/default-client-logo.png");
if (file_exists("assets/images/logo.png")) {
    $logo_url = base_url("assets/images/logo.png?" . time());
}
?>
<!-- App Favicon -->
<link rel="shortcut icon" href="<?= $logo_url ?>" type="image/x-icon">
<link rel="icon" href="<?= $logo_url ?>" type="image/x-icon">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" integrity="sha512-nMNlpuaDPrqlEls3IX/Q56H36qvBASwb3ipuo3MxeWbsQB1881ox0cRv7UPTgBlriqoynt35KjEwgGUeUXIPnw==" crossorigin="anonymous" referrerpolicy="no-referrer" />

<!-- Datatable -->
<link href="<?= base_url('assets') ?>/vendor/datatables/css/jquery.dataTables.min.css" rel="stylesheet">

<link href="<?= base_url('assets') ?>/vendor/bootstrap-select/dist/css/bootstrap-select.min.css" rel="stylesheet">
<link rel="stylesheet" href="<?= base_url('assets') ?>/vendor/toastr/css/toastr.min.css">
<link href="<?= base_url('assets') ?>/css/style.css" rel="stylesheet">
<!-- <link href="<?= base_url('assets_app') ?>/css/app.css" rel="stylesheet"> -->
<link href="https://cdn.lineicons.com/2.0/LineIcons.css" rel="stylesheet">

<?php if (ENVIRONMENT == "production") : ?>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
<?php endif ?>
<style>
    [data-nav-headerbg="color_3"][data-theme-version="dark"] .nav-header,
    [data-nav-headerbg="color_3"] .nav-header {
        background-color: #fff;
    }

    .iframe-rounded {
        border-radius: 6px;
    }

    .brand-logo>h3 {
        margin-left: 12px;
        margin-top: 10px;
    }

    .deznav .deznav-scroll {
        height: calc(100% - 20px);
    }

    .table th,
    .table td {
        padding: 0.3rem !important;
    }

    .table>thead>tr>th,
    .table>tbody>tr>td {
        font-size: 10pt !important;
    }

    .btn {
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
    }

    .btn.no-shadow {
        box-shadow: none;
    }

    .card.height-auto {
        height: auto;
    }

    .breadcrumb {
        background-color: #ffffff;
    }

    /* li.breadcrumb-item > a {
        background-color: #3a7afe;
        color: #fff;
        border-radius: 4px;
        padding: 4px 6px;
    } */

    li.breadcrumb-item.active>a {
        color: #3a7afe;
        /* background-color: #297F00;
        color: #fff;
        border-radius: 4px;
        padding: 4px 6px; */
    }

    .form-control:disabled,
    .form-control[readonly] {
        background-color: #efefef;
    }
</style>