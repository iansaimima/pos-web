<?php
defined('BASEPATH') or exit('No direct script access allowed');

$user = get_session("user");
$cabang = get_session("cabang_selected");
$privilege_list = isset($user["privilege_list"]) ? $user["privilege_list"] : array();
$allow_laporan_persediaan_stock_opname = isset($privilege_list["allow_laporan_persediaan_stock_opname"]) ? $privilege_list["allow_laporan_persediaan_stock_opname"] : 0;

if (!$allow_laporan_persediaan_stock_opname) {
    echo "Anda tidak punya akses!";
    exit;
}

library("pdf_engine");
$pdf = new \Mpdf\Mpdf([
    'mode' => 'utf-8',
    'format' => 'A4-L',
    'default_font_size' => 9,
    'default_font' => 'DejaVuSans'
]);

$header = $report_data["header"];
$filters = $report_data["filters"];
$body = $report_data["body"];

$footer = $report_data["footer"];

$logo_url = base_url("assets/images/default-client-logo.png");

if (file_exists("assets/images/client-logo.png")) {
    $logo_url = base_url("assets/images/client-logo.png?" . time());
}

// printr($body); die();

$judul = "Laporan Stock Opname";

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

      <td valign="bottom" align="right">          
        <table>
          <tr>
            <td>
              <table>          
                <tr>
                    <td width="100">Periode : </td>
                    <td align="left">' . $filters["start_date"] . ' s/d ' . $filters["end_date"] . '</td>
                </tr>
                <tr>
                    <td width="100">Gudang : </td>
                    <td align="left">' . $filters["gudang_nama"] . '</td>
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


$pdf->WriteHTML('');

$pdf->WriteHTML('
  <table class="table table-bordered" cellspacing="1" style="background-color: #333; border: 0px solid #000">
      <thead>
        <tr style="background-color: #fff">
          <th style="text-align: left;padding: 2px 5px;">No.</th>
          <th style="text-align: left;padding: 2px 5px;">Gudang</th>
          <th style="text-align: left;padding: 2px 5px;">Tanggal</th>
          <th style="text-align: left;padding: 2px 5px;">Kode Item</th>
          <th style="text-align: left;padding: 2px 5px;">Nama</th>
          <th style="text-align: right;padding: 2px 5px;">Stock System</th>
          <th style="text-align: right;padding: 2px 5px;">Stock Fisik</th>
          <th style="text-align: right;padding: 2px 5px;">Selisih</th>
          <th style="text-align: left;padding: 2px 5px;">Satuan</th>
        </tr>
      </thead>
      <tbody>');

$i = 1;

foreach ($body as $l) {
    $pdf->WriteHTML('
        <tr style="background-color: #fff">
            <td style="padding: 2px 5px;">' . $l["number_formatted"] . '</td>
            <td style="padding: 2px 5px;">' . $l["gudang_kode"] . " - " .  $l["gudang_nama"] . '</td>
            <td style="padding: 2px 5px;">' . $l["tanggal"] . '</td>
            <td style="padding: 2px 5px;">' . $l["kode"] . '</td>
            <td style="padding: 2px 5px;">' . $l["nama"] . '</td>
            <td style="padding: 2px 5px; text-align: right">' . $l["stock_system"] . '</td>
            <td style="padding: 2px 5px; text-align: right">' . $l["stock_fisik"] . '</td>
            <td style="padding: 2px 5px; text-align: right">' . $l["stock_selisih"] . '</td>
            <td style="padding: 2px 5px;">' . $l["satuan_terkecil"] . '</td>
        </tr>
        ');

    $i++;
}

$pdf->WriteHTML('                  
      </tbody>
  </table>');

$pdf->Output("$judul Periode " . $filters["start_date"] . " sd " . $filters["end_date"] . ".pdf", 'I');
