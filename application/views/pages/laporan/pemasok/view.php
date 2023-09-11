<?php
defined('BASEPATH') or exit('No direct script access allowed');

$user = get_session("user");
$cabang = get_session("cabang_selected");
$privilege_list = isset($user["privilege_list"]) ? $user["privilege_list"] : array();
$allow_laporan_pemasok = isset($privilege_list["allow_laporan_pemasok"]) ? $privilege_list["allow_laporan_pemasok"] : 0;

if (!$allow_laporan_pemasok) {
    echo "Anda tidak punya akses!";
    exit;
}


library('settings_engine');
$settings_engine = new Settings_engine();
$list = $settings_engine->get_all_settings();
$settings_list = array();
foreach($list as $l){
    $_key = $l['_key'];
    $settings_list[$_key] = $l;
}
$header = array(
  "nama_toko" => $settings_list["TOKO_NAMA"]["_value"],
  "alamat_toko" => $settings_list["TOKO_ALAMAT"]["_value"],
  "no_telepon_toko" => $settings_list["TOKO_NO_TELEPON"]["_value"],
);

$logo_url = base_url("assets/images/default-client-logo.png");

if (file_exists("assets/images/client-logo.png")) {
    $logo_url = base_url("assets/images/client-logo.png?" . time());
}

library("pdf_engine");
$pdf = new \Mpdf\Mpdf([
    'mode' => 'utf-8',
    'format' => 'A4-L',
    'default_font_size' => 9,
    'default_font' => 'DejaVuSans'
]);

$judul = "Laporan Pemasok";

$pdf->SetTitle($judul);
$pdf->setAutoTopMargin = 'stretch';
$pdf->setFooter('
  <table width="100%">
    <tr>
      <td style="text-align: left">Dicetak oleh ' . $user["name"] . ', pada ' . date("d/m/Y H:i:s") . '</td>
      <td style="text-align: right">Hal. {PAGENO}</td>
    </tr>
  </table>
');

$pdf->SetHTMLHeader('
  <table style="width: 100%; border-bottom: 2px solid #000">
    <tr>
      <td style="text-align: left">
        <table>
          <tr>
            <td style="padding-right: 8px" valign="top">
              <img src="' . $logo_url . '" style="width: 72px"/>
            </td>
            <td style="text-align: left" valign="top"> 
              <table>
                <tr>
                  <td><p style="font-size: 10pt;font-weight: bold;">' . $judul . '</p></td>              
                </tr>
                <tr>
                  <td><p style="font-weight: bold;">' . $header["nama_toko"] . '</p></td>              
                </tr>
                <tr>
                  <td><p>Alamat : ' . $cabang["alamat"] . '</p></td>
                </tr>
                <tr>
                  <td><p>No. Telp : ' . $cabang["no_telepon"] . '</p></td>
                </tr>
              </table>                                     
            </td>
        </tr>
        </table>
      </td>
    </tr>
  </table>    
');

$pdf->WriteHTML('
  *{
      font-size: 10pt
  }

  .table > tbody > tr > td,
  .table > tbody > tr > th{
      padding: 2px 5px;
  }
  
  .table{
      width: 100%;          
    }

    table.table > thead > tr > th{ 
       background-color: #1976D2 !important; 
       color: #fff !important; 
       border: 1px solid #000 !important;
       font-weight: bold;
       -webkit-print-color-adjust: exact; 
    }
    .table > thead > tr > th,
    .table > tbody > tr > th,
    .table > tbody > tr > td {
      font-size: 12px;
      border: 1px solid #555;
      background-color: #888;
      padding: 2px 4px !important;
    }

    .table > tbody > tr > th.saldo-sebelumnya{
      font-size: 12px;
      background-color: #A5D6A7 !important;        
    }
    
  @media print{
    body, html, * {
    }

    .table > thead > tr > th{ 
       background-color: #1976D2 !important; 
       color: #fff !important; 
       border-color: #555 !important;
       font-weight: bold;
       -webkit-print-color-adjust: exact; 
    }
    table > thead > tr > th{ 
        font-size: 12px;
       font-weight: bold;
       -webkit-print-color-adjust: exact; 
    }
    .table > thead > tr > th,
    .table > tbody > tr > th,
    .table > tbody > tr > td {
      font-size: 12px;
      border-color: #555 !important;
      padding: 2px 4px !important;
       -webkit-print-color-adjust: exact; 
    }

    .table > tbody > tr > th.saldo-sebelumnya{
      font-size: 12px;
      background-color: #A5D6A7 !important;     
       -webkit-print-color-adjust: exact;    
    }
  }
  ', 1);

$pdf->WriteHTML('
  <table class="table table-bordered" cellspacing="1" style="background-color: #333; border: 0px solid #000">
      <thead>
          <tr style="background-color: #fff">
              <th style="text-align: center;padding: 2px 5px;width:50px">No.</th>
              <th style="text-align: center;padding: 2px 5px;width:80px">Kode</th>
              <th style="text-align: center;padding: 2px 5px;">Nama</th>
              <th style="text-align: center;padding: 2px 5px;">Alamat</th>
              <th style="text-align: center;padding: 2px 5px;">No. Telp</th>
          </tr>
      </thead>
      <tbody>');

      $i=1;
foreach ($pemasok_list as $l) {
    $pdf->WriteHTML('
          <tr style="background-color: #fff">
              <td style="padding: 2px 5px;">' . $i . '</td>
              <td style="padding: 2px 5px;">' . $l["number_formatted"] . '</td>
              <td style="padding: 2px 5px;">' . $l["nama"] . '</td>
              <td style="padding: 2px 5px;">' . $l["alamat"] . '</td>
              <td style="padding: 2px 5px;">' . $l["no_telepon"] . '</td>
          </tr>
        ');

    $i++;
}

$pdf->WriteHTML('                  
      </tbody>
  </table>');

$pdf->Output("$judul.pdf",'I');
