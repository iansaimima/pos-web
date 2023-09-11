<?php
defined('BASEPATH') or exit('No direct script access allowed');

$user = get_session("user");
$cabang = get_session("cabang_selected");
$privilege_list = isset($user["privilege_list"]) ? $user["privilege_list"] : array();
$allow_laporan_pembelian_detail = isset($privilege_list["allow_laporan_pembelian_detail"]) ? $privilege_list["allow_laporan_pembelian_detail"] : 0;

if (!$allow_laporan_pembelian_detail) {
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

$header = $report_data["header"];
$filters = $report_data["filters"];
$body = $report_data["body"];
$footer = $report_data["footer"];

// printr($body);die();

$logo_url = base_url("assets/images/default-client-logo.png");

if (file_exists("assets/images/client-logo.png")) {
    $logo_url = base_url("assets/images/client-logo.png?" . time());
}

// printr($body); die();

$judul = "Laporan Pembelian Detail";

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
                    <td width="100">Pemasok : </td>
                    <td align="left">' . $filters["pemasok"] . '</td>
                </tr>
                <tr>
                    <td width="100">Gudang : </td>
                    <td align="left">' . $filters["gudang"] . '</td>
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
  <table width="100%" cellspacing="0">
      <thead>
          <tr style="background-color: #fff">
              <th style="text-align: left;padding: 2px 5px; border-top: 1px solid #333; border-bottom: 1px solid #333; width: 120px">No. Transaksi</th>
              <th style="text-align: left;padding: 2px 5px; border-top: 1px solid #333; border-bottom: 1px solid #333; width: 90px">Tanggal</th>
              <th style="text-align: left;padding: 2px 5px; border-top: 1px solid #333; border-bottom: 1px solid #333; width: 90px">Kode Pem.</th>
              <th style="text-align: left;padding: 2px 5px; border-top: 1px solid #333; border-bottom: 1px solid #333;">Pemasok</th>
              <th style="text-align: left;padding: 2px 5px; border-top: 1px solid #333; border-bottom: 1px solid #333;">Alamat</th>
          </tr>
      </thead>
      <tbody>');

foreach ($body as $pembelian_id => $l) {
    $detail_list = $body[$pembelian_id]["details"];
    $detail_footer = $body[$pembelian_id]["footer"];
    $pdf->WriteHTML('
          <tr style="background-color: #fff">
              <td style="padding: 2px 5px;">' . $l["no_pembelian"] . '</td>
              <td style="padding: 2px 5px;">' . $l["tanggal"] . '</td>
              <td style="padding: 2px 5px;">' . $l["pemasok_number_formatted"] . '</td>
              <td style="padding: 2px 5px;">' . $l["pemasok_nama"] . '</td>
              <td style="padding: 2px 5px;">' . $l["pemasok_alamat"] . '</td>
          </tr>
        ');

      $pdf->WriteHTML("
        <tr><td colspan='5' style='padding-top:6px'></td> </tr>
        <tr>
          <td colspan='5' >");


      $pdf->WriteHTML("
            <table width='100%' border='0' cellspacing='0'>
              <thead>
                <tr>
                  <td style='font-size: 8pt; font-style: italic; text-align: left; width: 30px'><u>No.</u></td>
                  <td style='font-size: 8pt; font-style: italic; text-align: left'><u>Kd. Item</u></td>
                  <td style='font-size: 8pt; font-style: italic; text-align: left'><u>Nama Item</u></td>
                  <td style='font-size: 8pt; font-style: italic; text-align: right'><u>Jml</u></td>
                  <td style='font-size: 8pt; font-style: italic; text-align: left'><u>Satuan</u></td>
                  <td style='font-size: 8pt; font-style: italic; text-align: right'><u>Harga</u></td>
                  <td style='font-size: 8pt; font-style: italic; text-align: right'><u>Pot %</u></td>
                  <td style='font-size: 8pt; font-style: italic; text-align: right'><u>Total</u></td>
                </tr>
              </thead>

              <tbody>
        ");

        $i=1;
        foreach($detail_list as $d){

          $pdf->WriteHTML("
                <tr>
                  <td style='font-size: 8pt; text-align: left; width: 30px'>" . $i . "</td>
                  <td style='font-size: 8pt; text-align: left; width: 120px'>" . $d["item_kode"] . "</td>
                  <td style='font-size: 8pt; text-align: left;'>" . $d["item_nama"] . "</td>
                  <td style='font-size: 8pt; text-align: right; width: 50px'>" . $d["jumlah"] . "</td>
                  <td style='font-size: 8pt; text-align: left;  width: 70px'>" . $d["satuan"] . "</td>
                  <td style='font-size: 8pt; text-align: right; width: 70px'>" . $d["harga_beli_satuan"] . "</td>
                  <td style='font-size: 8pt; text-align: right; width: 50px'>" . $d["potongan_persen"] . "</td>
                  <td style='font-size: 8pt; text-align: right; width: 100px'>" . $d["total"] . "</td>
                </tr>
          ");

          $i++;
        }

      $pdf->WriteHTML("
                <tr>
                  <td style='font-size: 8pt; text-align: left; width: 30px'></td>
                  <td style='font-size: 8pt; text-align: left'></td>
                  <td style='font-size: 8pt; text-align: left'></td>
                  <td style='font-size: 8pt; text-align: right; border-top: dotted 1px #333'>" . number_format($detail_footer["total_jumlah"]) . "</td>
                  <td style='font-size: 8pt; text-align: left; border-top: dotted 1px #333'></td>
                  <td style='font-size: 8pt; text-align: right; border-top: dotted 1px #333'></td>
                  <td style='font-size: 8pt; text-align: right; border-top: dotted 1px #333'></td>
                  <td style='font-size: 8pt; text-align: right; border-top: dotted 1px #333'>" . number_format($detail_footer["total_sub_total"]) . "</td>
                </tr>
      ");
      
      $pdf->WriteHTML("
                <tr>
                  <td style='font-size: 8pt; padding-top: 6px; padding-bottom:6px; border-bottom: dotted 1px #333; text-align: left; width: 30px'></td>
                  <td style='font-size: 8pt; padding-top: 6px; padding-bottom:6px; border-bottom: dotted 1px #333; text-align: left'></td>
                  <td style='font-size: 8pt; padding-top: 6px; padding-bottom:6px; border-bottom: dotted 1px #333; text-align: left'></td>
                  <td style='font-size: 8pt; padding-top: 6px; padding-bottom:6px; border-bottom: dotted 1px #333; text-align: left;font-weight: bold'>Pot. : </td>
                  <td style='font-size: 8pt; padding-top: 6px; padding-bottom:6px; border-bottom: dotted 1px #333; text-align: right;font-weight: bold'>" . trim($l["potongan"]) . "</td>
                  <td colspan='2' style='font-size: 8pt; padding-top: 6px; padding-bottom:6px; border-bottom: dotted 1px #333; text-align: right;font-weight: bold'>Total Akhir : </td>
                  <td style='font-size: 8pt; padding-top: 6px; padding-bottom:6px; border-bottom: dotted 1px #333; text-align: right;font-weight: bold'>" . trim($l["total_akhir"]) . "</td>
                </tr>
      ");


      $pdf->WriteHTML("
              </tbody>
            </table>
      ");


      $pdf->WriteHTML(" 
          </td>
        </tr>
        "
      );
}

$pdf->WriteHTML(
  "
        <tr style='background-color: #fff'>
            <td colspan='5'>
              <table width='100%' cellspacing='1'>
                <tr>
                  <td style='font-size: 8pt; padding-top: 6px; border-top: solid 1px #333;text-align: right;font-weight: bold'>Total Keseluruhan </td>
                  <td style='font-size: 8pt; padding-top: 6px; border-top: solid 1px #333;text-align: right;font-weight: bold; width: 100px'>Sub Total : </td>
                  <td style='font-size: 8pt; padding-top: 6px; border-top: solid 1px #333;text-align: right;font-weight: bold; width: 100px'>" . trim($footer["grand_sub_total"]) . "</td>                  
                </tr>
                <tr>
                  <td></td>
                  <td style='font-size: 8pt; text-align: right;font-weight: bold; width: 100px'>Potongan : </td>
                  <td style='font-size: 8pt; text-align: right;font-weight: bold; width: 100px'>" . trim($footer["total_potongan"]) . "</td>                  
                </tr>
                <tr>
                  <td></td>
                  <td style='font-size: 8pt; text-align: right;font-weight: bold; width: 100px'>Total Akhir : </td>
                  <td style='font-size: 8pt; text-align: right;font-weight: bold; width: 100px'>" . trim($footer["grand_total"]) . "</td>
                </tr>
              </table>
            </td>
        </tr>
  "
);

$pdf->WriteHTML('                  
      </tbody>
  </table>');

$pdf->Output("$judul Periode " . $filters["start_date"] . " sd " . $filters["end_date"] . " " . $filters["pemasok"] . ".pdf", 'I');
