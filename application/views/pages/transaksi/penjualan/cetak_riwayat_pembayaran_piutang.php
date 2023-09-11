
<?php
defined('BASEPATH') or exit('No direct script access allowed');

$user = get_session("user");
$cabang = get_session("cabang_selected");
$privilege_list = isset($user["privilege_list"]) ? $user["privilege_list"] : array();
$allow_print = isset($privilege_list["allow_transaksi_penjualan_print_nota"]) ? $privilege_list["allow_transaksi_penjualan_print_nota"] : 0;

if (!$allow_print) {
    echo "Anda tidak punya akses!";
    die();
}

library("pdf_engine");
$pdf = new \Mpdf\Mpdf([
    'mode' => 'utf-8',
    'format' => 'A5-L',
    'default_font_size' => 7,
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

$pdf->SetTitle("");
$pdf->setAutoTopMargin = 'stretch';

$pdf->SetHTMLHeader('
  <table style="width: 100%; border-bottom: 1px solid #000">
    <tr>
      <td style="text-align: left">
        <table>
          <tr>
            <td style="padding-right: 8px" valign="top">
              <img src="' . $logo_url . '" style="width: 48px"/>
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
      <td>
        <table>          
          <tr>
              <td width="80">No. Penjualan</td>
              <td> : ' . $body["no"] . '</td>
          </tr>
          <tr>
              <td width="60">Tanggal</td>
              <td> : ' . $body["tanggal"] . '</td>
          </tr>
          <tr>
              <td width="60">Pelanggan </td>
              <td> : [' . $body["pelanggan_number_formatted"] . '] ' . $body["pelanggan_nama"] . '</td>
          </tr>
          <tr>
              <td></td>
              <td>&nbsp;&nbsp;' . $body["pelanggan_detail"] . '</td>
          </tr>
        </table>
      </td>
    </tr>
  </table>    
');

$footer_string_list =  array();
$footer_string_list[] = '<table width="100%">';

$footer_string_list[] = '<tr>
    <td style="text-align: left">Dicetak oleh ' . $footer["printed_user_name"] . ', pada ' . $footer["printed"] . '</td>
    <td style="text-align: right">Hal. {PAGENO}</td>
</tr>';
$footer_string_list[] = '</table>';

$pdf->setFooter(implode("", $footer_string_list));



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

    <table style="width: 100%; border-bottom: 1px solid #000">
        <tr>
            <td style="text-align: left" width="50%" valign="top">
                <table>
                    <tr>
                        <td>Jatuh Tempo</td>
                        <td> : </td>
                        <td>' . $body["tanggal_jatuh_tempo"] . '</td>
                    </tr>
                </table>
            </td>
            <td style="text-align: right">
                <table>
                    <tr>
                        <td>Total Akhir</td>
                        <td> : </td>
                        <td>' . $body["total_akhir"] . '</td>
                    </tr>
                    <tr>
                        <td>Panjar</td>
                        <td> : </td>
                        <td>' . $body["bayar"] . '</td>
                    </tr>
                    <tr>
                        <td>Total Piutang</td>
                        <td> : </td>
                        <td>' . $body["sisa"] . '</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>   

  <table class="table table-bordered" cellspacing="1" style="background-color: #333; border: 0px solid #000">
      <thead>
          <tr style="background-color: #fff">
              <th style="text-align: right;padding: 2px 5px;">No.</th>
              <th style="text-align: left;padding: 2px 5px;">No. Pembayaran</th>
              <th style="text-align: left;padding: 2px 5px;">Tanggal</th>
              <th style="text-align: left;padding: 2px 5px;">Cara Bayar</th>
              <th style="text-align: right;padding: 2px 5px;">Piutang</th>
              <th style="text-align: right;padding: 2px 5px;">Jumlah Bayar</th>
              <th style="text-align: right;padding: 2px 5px;">Sisa</th>
          </tr>
      </thead>
      <tbody>');

$i = 1;
$content = array();
if(isset($body["content"]) && is_array($body["content"])) $content = $body["content"];
foreach ($content as $l) {
    $pdf->WriteHTML('
          <tr style="background-color: #fff">
              <td style="padding: 2px 5px; text-align: right">' . $l["no"] . '</td>
              <td style="padding: 2px 5px;">' . $l["number_formatted"] . '</td>
              <td style="padding: 2px 5px;">' . $l["tanggal"] . '</td>
              <td style="padding: 2px 5px;">' . $l["cara_bayar"] . '</td>
              <td style="padding: 2px 5px; text-align: right">' . $l["piutang"] . '</td>
              <td style="padding: 2px 5px; text-align: right">' . $l["jumlah_bayar"] . '</td>
              <td style="padding: 2px 5px; text-align: right">' . $l["sisa_piutang"] . '</td>
          </tr>
        ');

    $i++;
}

$pdf->WriteHTML('                  
      </tbody>
  </table>');

$pdf->WriteHTML(
  '
    <table width="100%">
      <tr>
        <td align="left" valign="top">
        </td>
        <td align="right" valign="top"> 
            <br/>
            <br/>
            <table width="50%">
                <tr>
                <td align="center" valign="top">
                    Admin 
                    <br/>
                    <br/>
                    <br/>
                    <br/>
                    <br/>
                    ( ' . $footer["printed_user_name"] . ' )
                </td>
                <td align="center" valign="top">
                    Pelanggan 
                    <br/>
                    <br/>
                    <br/>
                    <br/>
                    <br/>
                    ( ' . $body["pelanggan_nama"] . ' )
                </td>
                </tr>
            </table>
            
        </td>
      </tr>
    </table>
  '
);

$pdf->Output("$judul" . $body["no"] . ".pdf", 'I');
