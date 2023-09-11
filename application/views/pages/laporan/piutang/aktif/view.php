<?php
defined('BASEPATH') or exit('No direct script access allowed');

$user = get_session("user");
$cabang = get_session("cabang_selected");
$privilege_list = isset($user["privilege_list"]) ? $user["privilege_list"] : array();
$allow_laporan_piutang_aktif = isset($privilege_list["allow_laporan_piutang_aktif"]) ? $privilege_list["allow_laporan_piutang_aktif"] : 0;

if (!$allow_laporan_piutang_aktif) {
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
$body = $report_data["body"];
$footer = $report_data["footer"];

$logo_url = base_url("assets/images/default-client-logo.png");

if (file_exists("assets/images/client-logo.png")) {
    $logo_url = base_url("assets/images/client-logo.png?" . time());
}

// printr($body); die();

$judul = $body["judul"];

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
                    <td width="120">Periode sampai : </td>
                    <td align="left">' . $body["periode"] . '</td>
                </tr>
                <tr>
                    <td width="140">Periode akuntansi : </td>
                    <td align="left">' . $body["periode_akuntansi"] . '</td>
                </tr>
                <tr>
                    <td width="100">Pelanggan : </td>
                    <td align="left">' . $body["pelanggan_nama"] . '</td>
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
  <table width="100%" cellspacing="0">
      <thead>
          <tr style="background-color: #fff">
              <th style="text-align: left;padding: 2px 5px; border-top: 1px solid #000; border-bottom: 1px solid #000">No. Transaksi</th>
              <th style="text-align: left;padding: 2px 5px; border-top: 1px solid #000; border-bottom: 1px solid #000">Tanggal</th>
              <th style="text-align: left;padding: 2px 5px; border-top: 1px solid #000; border-bottom: 1px solid #000">Tanggal JT</th>
              <th style="text-align: right;padding: 2px 5px; border-top: 1px solid #000; border-bottom: 1px solid #000">Piutang</th>
              <th style="text-align: right;padding: 2px 5px; border-top: 1px solid #000; border-bottom: 1px solid #000">Sisa Piutang</th>
              <th style="text-align: right;padding: 2px 5px; border-top: 1px solid #000; border-bottom: 1px solid #000">Umur dari JT</th>
          </tr>
      </thead>
      <tbody>');

$i = 1;
foreach ($body["content"] as $l) {
    $total_piutang = (double) $l["total_piutang"];
    $total_sisa_piutang = (double) $l["total_sisa_piutang"];
    $pelanggan_number_formatted = $l["number_formatted"];
    $pelanggan_nama = $l["nama"];

    $piutang_data_list = $l["piutang_data"];

    $pdf->WriteHTML('
          <tr style="background-color: #fff">
              <td style="padding: 2px 5px; font-weight: bold">' . $pelanggan_number_formatted . '</td>
              <td style="padding: 2px 5px; font-weight: bold" colspan="5">' . $pelanggan_nama . '</td>
          </tr>
    ');

    foreach($piutang_data_list as $p){
      $pdf->WriteHTML('
        <tr style="background-color: #fff">
            <td style="padding: 2px 5px;">' . $p["no_transaksi"] . '</td>
            <td style="padding: 2px 5px;">' . $p["tanggal"] . '</td>
            <td style="padding: 2px 5px;">' . $p["tanggal_jatuh_tempo"] . '</td>
            <td style="padding: 2px 5px; text-align: right;">' . number_format($p["piutang"],2) . '</td>
            <td style="padding: 2px 5px; text-align: right;">' . number_format($p["sisa_piutang"], 2) . '</td>
            <td style="padding: 2px 5px; text-align: right; width: 100px;">' . number_format($p["umur_dari_jatuh_tempo"]) . '</td>
        </tr>
      ');
    }

    $pdf->WriteHTML('
          <tr style="background-color: #fff">
              <td style="padding: 2px 5px; border-bottom: 1px solid #000" colspan="2"></td>
              <td style="padding: 2px 5px; font-weight: bold; border-bottom: 1px solid #000">Total</td>
              <td style="padding: 2px 5px; text-align: right; border-top: 1px dotted #000; border-bottom: 1px solid #000">' . number_format($total_piutang,2) . '</td>
              <td style="padding: 2px 5px; text-align: right; border-top: 1px dotted #000; border-bottom: 1px solid #000">' . number_format($total_sisa_piutang, 2) . '</td>
              <td style="padding: 2px 5px; text-align: right; border-top: 1px dotted #000; border-bottom: 1px solid #000"></td>
          </tr>
    ');

    

    $i++;
}

$pdf->WriteHTML('                  
      </tbody>
  </table>');

$pdf->Output("$judul Periode sampai " . $body["periode"] . " " . $body["pelanggan_nama"] . ".pdf", 'I');
