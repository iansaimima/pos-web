<?php
defined('BASEPATH') or exit('No direct script access allowed');

$user = get_session("user");
$cabang = get_session("cabang_selected");
$privilege_list = isset($user["privilege_list"]) ? $user["privilege_list"] : array();
$allow_laporan_kartu_stock = isset($privilege_list["allow_laporan_kartu_stock"]) ? $privilege_list["allow_laporan_kartu_stock"] : 0;

if (!$allow_laporan_kartu_stock) {
    echo "Anda tidak punya akses!";
    exit;
}

library("pdf_engine");
$pdf = new \Mpdf\Mpdf([
    'mode' => 'utf-8',
    'format' => 'A4-P',
    'default_font_size' => 9,
    'default_font' => 'DejaVuSans'
]);

$judul = "Laporan Kartu Stock";

$header = $report_data["header"];
$filters = $report_data["filters"];
$body = $report_data["body"];
$stock_bulan_lalu = $body["stock_bulan_lalu_data"];
$stock_bulan_ini_data_list = $body["stock_bulan_ini_data_list"];

$footer = $report_data["footer"];


$logo_url = base_url("assets/images/default-client-logo.png");

if (file_exists("assets/images/client-logo.png")) {
    $logo_url = base_url("assets/images/client-logo.png?" . time());
}

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
                    <td align="left">' . $filters["bulan"] . ' ' . $filters["tahun"] . '</td>
                </tr>
                <tr>
                    <td width="100">Kode Item : </td>
                    <td align="left">' . $filters["kode_item"]. '</td>
                </tr>
                <tr>
                    <td width="100">Nama : </td>
                    <td align="left">' . $filters["nama"]. '</td>
                </tr>
                <tr>
                    <td width="100">Gudang : </td>
                    <td align="left">' . $filters["gudang"]. '</td>
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
              <th style="padding: 2px 5px; text-align: left; width: 110px">No. Transaksi</th>
              <th style="padding: 2px 5px; text-align: left; width: 90px">Tanggal</th>
              <th style="padding: 2px 5px; text-align: left; width: 50px">Tipe</th>
              <th style="padding: 2px 5px; text-align: left">Keterangan</th>
              <th style="padding: 2px 5px; text-align: right; width: 100px">Masuk</th>
              <th style="padding: 2px 5px; text-align: right; width: 100px">Keluar</th>
              <th style="padding: 2px 5px; text-align: right; width: 100px">Saldo</th>
          </tr>
      </thead>
      <tbody>');

$pdf->WriteHTML('
        <tr style="background-color: #ddd">
            <td style="padding: 2px 5px;">' . $stock_bulan_lalu['no_transaksi'] . '</td>
            <td style="padding: 2px 5px;">' . $stock_bulan_lalu['tanggal'] . '</td>
            <td style="padding: 2px 5px;">' . $stock_bulan_lalu['tipe'] . '</td>
            <td style="padding: 2px 5px;">' . $stock_bulan_lalu['keterangan'] . '</td>
            <td style="padding: 2px 5px; text-align: right">' . $stock_bulan_lalu['masuk'] . '</td>
            <td style="padding: 2px 5px; text-align: right">' . $stock_bulan_lalu['keluar'] . '</td>
            <td style="padding: 2px 5px; text-align: right">' . $stock_bulan_lalu['saldo'] . '</td>
        </tr>
        ');

      $i=1;
foreach ($stock_bulan_ini_data_list as $l) {
    $pdf->WriteHTML('
        <tr style="background-color: #fff">
            <td style="padding: 2px 5px;">' . $l['no_transaksi'] . '</td>
            <td style="padding: 2px 5px;">' . $l['tanggal'] . '</td>
            <td style="padding: 2px 5px;">' . $l['tipe'] . '</td>
            <td style="padding: 2px 5px;">' . $l['keterangan'] . '</td>
            <td style="padding: 2px 5px; text-align: right">' . $l['masuk'] . '</td>
            <td style="padding: 2px 5px; text-align: right">' . $l['keluar'] . '</td>
            <td style="padding: 2px 5px; text-align: right">' . $l['saldo'] . '</td>
        </tr>
        ');

    $i++;
}

$pdf->WriteHTML('                  
      </tbody>
  </table>');

$pdf->WriteHTML('
  <table width="100%" style="margin-top:10px">
    <tr>
      <td><b>Total Masuk</b> : ' . $footer["total_masuk"] . '</td>
      <td><b>Total Keluar</b> : ' . $footer["total_keluar"] . '</td>
      <td><b>Saldo Awal</b> : ' . $footer["saldo_awal"] . '</td>
      <td><b>Saldo Akhir</b> : ' . $footer["saldo_akhir"] . '</td>
    </tr>
  </table>
          ');

$pdf->Output("$judul [" . $filters["kode_item"] . "] " . $filters["nama"] . " Periode " . $filters["bulan"] . " " . $filters["tahun"] . " .pdf",'I');
