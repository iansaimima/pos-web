
<?php
defined('BASEPATH') or exit('No direct script access allowed');

$user = get_session("user");
$cabang = get_session("cabang_selected");
$privilege_list = isset($user["privilege_list"]) ? $user["privilege_list"] : array();
$allow_print = isset($privilege_list["allow_stock_opname_print"]) ? $privilege_list["allow_stock_opname_print"] : 0;

if (!$allow_print) {
    echo "Anda tidak punya akses!";
    exit;
}

library("pdf_engine");
$pdf = new \Mpdf\Mpdf([
    'mode' => 'utf-8',
    'format' => 'A4-P',
    'default_font_size' => 8,
    'default_font' => 'DejaVuSans'
]);

$header = isset($print_data["header"]) ? $print_data["header"] : array();
$body = isset($print_data["body"]) ? $print_data["body"] : array();
$footer = isset($print_data["footer"]) ? $print_data["footer"] : array();


$logo_url = base_url("assets/images/default-client-logo.png");

if (file_exists("assets/images/client-logo.png")) {
    $logo_url = base_url("assets/images/client-logo.png?" . time());
}

$judul = $body["judul"];

$pdf->SetTitle($judul);
$pdf->setAutoTopMargin = 'stretch';

$footer_string_list =  array();
$footer_string_list[] = '<table width="100%">';
$footer_string_list[] = '<tr>
<td colspan="2" style="text-align: left">Dibuat oleh ' . $footer["creator_user_name"] . ', pada ' . $footer["created"] . '</td>      
</tr>';

if($footer["created"] != $footer["last_updated"]) {
    $footer_string_list[] = '<tr>
    <td colspan="2" style="text-align: left">Diedit oleh ' . $footer["last_updated_user_name"] . ', pada ' . $footer["last_updated"] . '</td>      
</tr>';
}

$footer_string_list[] = '<tr>
    <td style="text-align: left">Dicetak oleh ' . $footer["printed_user_name"] . ', pada ' . $footer["printed"] . '</td>
    <td style="text-align: right">Hal. {PAGENO}</td>
</tr>';
$footer_string_list[] = '</table>';

$pdf->setFooter(implode("", $footer_string_list));



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
<table style="width: 100%">
    <tr>
      <td>
        <table>          
          <tr>
              <td width="100">No.</td>
              <td> : ' . $body["no"] . '</td>
          </tr>
          <tr>
              <td width="100">Tanggal</td>
              <td> : ' . $body["tanggal"] . '</td>
          </tr>
        </table>
      </td>
      <td>
        <table>          
          <tr>
              <td width="100">Ket. :</td>
              <td></td>
          </tr>
          <tr>
              <td colspan="2">' . $body["keterangan"] . '</td>
          </tr>
        </table>
      </td>
    </tr>
</table>');

$pdf->WriteHTML('
  <table class="table table-bordered" cellspacing="1" style="background-color: #333; border: 0px solid #000">
      <thead>
          <tr style="background-color: #fff">
              <th style="text-align: right;padding: 2px 5px;">No.</th>
              <th style="text-align: left;padding: 2px 5px;">Kode</th>
              <th style="text-align: left;padding: 2px 5px;">Nama</th>
              <th style="text-align: left;padding: 2px 5px;">Kategori</th>
              <th style="text-align: right;padding: 2px 5px;">Stock System</th>
              <th style="text-align: right;padding: 2px 5px;">Stock Fisik</th>
              <th style="text-align: right;padding: 2px 5px;">Selisih</th>
              <th style="text-align: right;padding: 2px 5px;">Satuan</th>
          </tr>
      </thead>
      <tbody>');

$i = 1;
$content = array();
if(isset($body["content"]) && is_array($body["content"])) $content = $body["content"];
foreach ($content as $l) {
    $pdf->WriteHTML('
          <tr style="background-color: #fff">
              <td style="padding: 2px 5px;">' . $l["no"] . '</td>
              <td style="padding: 2px 5px;">' . $l["kode"] . '</td>
              <td style="padding: 2px 5px;">' . $l["nama"] . '</td>
              <td style="padding: 2px 5px;">' . $l["kategori"] . '</td>
              <td style="padding: 2px 5px; text-align: right">' . number_format($l["stock_system"], 0, ",", ".") . '</td>
              <td style="padding: 2px 5px; text-align: right">' . number_format($l["stock_fisik"], 0, ",", ".") . '</td>
              <td style="padding: 2px 5px; text-align: right">' . number_format($l["stock_selisih"], 0, ",", ".") . '</td>
              <td style="padding: 2px 5px;">' . $l["satuan_terkecil"] . '</td>
          </tr>
        ');

    $i++;
}

$pdf->WriteHTML('                  
      </tbody>
  </table>');

$pdf->Output("$judul - Tanggal " . $body["tanggal"] . ".pdf", 'I');
