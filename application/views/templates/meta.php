<?php

$settings = get_session("settings");

$app_name = APP_NAME;
$logo_url = base_url("assets/images/logo-text-with-tagline.png");
if(isset($settings["TOKO_NAMA"])) $app_name = $settings["TOKO_NAMA"]["_value"];
?>
        <title><?= APP_NAME ?> - <?= APP_TAG_LINE ?></title>

        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <!-- Tell the browser to be responsive to screen width -->
        <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
	<meta name="title" content="<?= APP_NAME ?>">
	<meta name="description" content="<?= APP_TAG_LINE ?>">