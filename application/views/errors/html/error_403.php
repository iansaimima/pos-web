
<!DOCTYPE html>
<html>
    <head>
      <?php view("templates/meta") ?>    
      <?php view("templates/style") ?>  
    </head>
    <body>

        <div class="account-pages"></div>
        <div class="clearfix"></div>
        <div class="wrapper-page card-box" style="background-color: rgba(255,255,255,0.7)">
            <div class="ex-page-content text-center">
                <div class="text-error">403</div>
                <h3 class="text-uppercase font-600">Akses ditolak</h3>
                <p class="text-muted">
                    Anda tidak memiliki kuasa untuk mengakses halaman ini. 
                    Silahkan hubungi administrator aplikasi <?= APP_LONG_NAME ?> untuk mendapatkan akses halaman ini.
                </p>
                <br>
                <a class="btn btn-success waves-effect waves-light" href="<?= base_url() ?>"> Kembali ke beranda</a>

            </div>
        </div>
        <!-- End wrapper page -->


    	<script>
          var resizefunc = [];
      </script>

      <?php view("templates/script") ?>
	
	</body>
</html>