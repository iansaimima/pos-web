<!DOCTYPE html>
<html>
    <head>                
        <?php view("templates/meta"); ?>
        <?php view("templates/style"); ?>
    </head>
    <body class="hold-transition register-page" style="background-color: #f0f0f0">
        <div class="container">
            <div class="row">
                <div class="col-md-4 col-md-offset-4">
                    <h1 class="box-title" style="text-align: center"><a href="javascript:void(0);"><?= APP_LONG_NAME ?></a></h1>
                    
                    <?php
                    $is_success = isset($result["is_success"]) ? (int) $result["is_success"] : 0;
                    $message = isset($result["message"]) ? trim($result["message"]) : "";                    
                    if($is_success == 1){
                    ?>

                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h1 class="box-title">Verifikasi Email Sukses</h1>
                        </div>
    
                        <div class="box-body">
                            <h3 class="text-success"> <i class="fa fa-check"></i> <?= $message ?></h3>
                        </div>
    
                        <div class="box-footer">
                            <div class="row">
                                <div class="col-md-12">
                                    <a href="<?= base_url("pemohon") ?>" class="btn btn-primary btn-sm btn-block" ><i class="fa fa-send"></i>&nbsp;Ke Halaman Utama Pemohon</a>
                                </div>
                            </div>
                        </div>                        
                    </div>
                    <?php 
                    } else {
                    ?>

                    <div class="box box-danger">
                        <div class="box-header with-border">
                            <h1 class="box-title">Verifikasi Email Gagal</h1>
                        </div>
    
                        <div class="box-body">
                            <h3 class="text-danger"> <i class="fa fa-times"></i> <?= $message ?></h3>
                        </div>
    
                        <div class="box-footer">
                        </div>                        
                    </div>
                    <?php
                    }
                    ?>
                </div>
            </div>
        </div>  

        <div class="loader-wrapper" id="loader">
            <div class="load-content">
                Mohon tunggu ...
                <br>
                <br>
                <div class="progress">
                    <div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%">
                        <span class="sr-only"></span>
                    </div>
                </div>
            </div>
        </div>

        <style>
            .loader-wrapper{
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: rgba(0,0,0,0.3);
                z-index: 2000;
                display: none;
            }

            .loader-wrapper .load-content{
                position: fixed;
                z-index: 2001;
                top: 50%;
                left: 50%;
                transform: translate(-50%,-50%);

                width: 300px;
                background-color: #fff;
                border-radius: 4px;
                text-align: center;
                padding: 10px;
                box-shadow: 2px 2px 10px rgba(0,0,0,0.3);
            }
        </style>
        <?php view("templates/script") ?>

        <script>
            var selected_provinsi_id = <?= $selected_provinsi_id ?>;
            $(document).ready(function (){                
                //Money Euro
                $('[data-mask]').inputmask();
                load_kota_kabupaten_list(selected_provinsi_id);

                $(".radio-jenis-pemohon").change(function (){
                    var jenis_pemohon = $(".radio-jenis-pemohon:checked").val();                    

                    if(jenis_pemohon == "Perorangan"){
                        $("#div-nama-perusahaan").slideUp();
                        $("#div-file-akta-perusahaan").slideUp();
                        $("#div-no-akta-perusahaan").slideUp();

                        $(".text-nama-pemohon").html("Pemohon");
                        $(".text-no-hp-pemohon").html("Pemohon");
                        $(".text-email-pemohon").html("Pemohon");
                    }else{
                        $("#div-nama-perusahaan").slideDown();
                        $("#div-file-akta-perusahaan").slideDown();
                        $("#div-no-akta-perusahaan").slideDown();

                        $(".text-nama-pemohon").html("Direktur");
                        $(".text-no-hp-pemohon").html("Direktur");
                        $(".text-email-pemohon").html("Direktur");
                    }
                });

                $("#select-provinsi").change(function (){
                    load_kota_kabupaten_list($(this).val());
                });

                $("#select-kota-kabupaten").change(function (){
                    load_kecamatan_list($(this).val());
                });

                $("#select-kecamatan").change(function (){
                    load_kelurahan_list($(this).val());
                });

                $("#file-foto-ktp").bind("change", function (){
                    var size = this.files[0].size;
                    if(size > 512000) {
                        show_toast("Error", "Maximal Ukuran File KTP 500KB", "error");
                        $(this).val("");
                        return;
                    }
                });

                $("#file-foto-npwp").bind("change", function (){
                    var size = this.files[0].size;
                    if(size > 512000) {
                        show_toast("Error", "Maximal Ukuran File NPWP 500KB", "error");
                        $(this).val("");
                        return;
                    }
                });

                $("#file-akta-perusahaan").bind("change", function (){
                    var size = this.files[0].size;
                    if(size > 512000) {
                        show_toast("Error", "Maximal Ukuran File Akta Perusahaan 1MB", "error");
                        $(this).val("");
                        return;
                    }
                });
            });

            function proses_daftar(){
                var captcha = $("#text-captcha").val();
                if(captcha == null || captcha == undefined || captcha == ""){
                    show_toast("Error", "Masukkan kode keamanan", "error");
                    $("#text-captche").focus();
                    return;
                }

                var form= $("#form-daftar")[0];
                var form_data = new FormData(form);

                ajax_post_file(
                    '<?= base_url("pemohon/daftar/proses") ?>', // url
                    form_data, // data
                    function(resp){
                        // do something here  

                        $("#debug").html(resp);
                        try{
                            var json = JSON.parse(resp);
                            if(json.is_success == 1){
                                show_toast("Success", json.message, "success");
                                window.location.href = "<?= base_url("pemohon") ?>";
                            }else{
                                show_toast("Error",json.message, "error");
                            }
                        }catch(error){                            
                        }                           
                    }
                )
            }

            function load_kota_kabupaten_list(provinsi_id){
                ajax_get(
                    '<?= base_url("pemohon/daftar/ajax_get_kota_kabupaten_list") ?>/' + provinsi_id, // url
                    {}, // data
                    function(resp){
                        // do something here  
                        try{
                            var json = JSON.parse(resp);
                            set_select_item("kota-kabupaten", json);
                        }catch(error){
                            show_toast("Error", "Application Response Error", "error");
                        }                
                    }
                )
            }

            function load_kecamatan_list(kota_kabupaten_id){
                ajax_get(
                    '<?= base_url("pemohon/daftar/ajax_get_kecamatan_list") ?>/' + kota_kabupaten_id, // url
                    {}, // data
                    function(resp){
                        // do something here  
                        try{
                            var json = JSON.parse(resp);
                            set_select_item("kecamatan", json);
                        }catch(error){
                            show_toast("Error", "Application Response Error", "error");
                        }                
                    }
                )
            }

            function load_kelurahan_list(kota_kelurahan_id){
                ajax_get(
                    '<?= base_url("pemohon/daftar/ajax_get_kelurahan_list") ?>/' + kota_kelurahan_id, // url
                    {}, // data
                    function(resp){
                        // do something here  
                        try{
                            var json = JSON.parse(resp);
                            set_select_item("kelurahan", json);
                        }catch(error){
                            show_toast("Error", "Application Response Error", "error");
                        }                
                    }
                )
            }

            function set_select_item(target, data_list){
                if(target == null || target == undefined) return;
                if(data_list == null || data_list == undefined) return;
                
                var option_list = [];
                if(target == "kota-kabupaten") option_list.push("<option value=''>Pilih Kota / Kabupaten</option>");
                if(target == "kecamatan") option_list.push("<option value=''>Pilih Kecamatan</option>");
                if(target == "kelurahan") option_list.push("<option value=''>Pilih Kelurahan</option>");
                                
                for(var i=0; i<data_list.length; i++){
                    var data = data_list[i];
                    
                    var id = data.id;
                    var nama = data.nama;

                    option_list.push("<option value='" + id + "'>"+ nama +"</option>");
                }
                $("#select-" + target).html(option_list.join(""));
            }
        </script>
    </body>
</html>
