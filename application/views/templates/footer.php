
            <!--**********************************
                Footer start
            ***********************************-->
            <div class="footer">
                <div class="copyright">
                    <p style="text-align: center;">Copyright © <?= date("Y") ?> <a href="<?= base_url() ?>" target="_blank"><?= APP_NAME ?></a> </p>
                </div>
            </div>
            <!--**********************************
                Footer end
            ***********************************-->
            <div class="loader-wrapper" id="loader">
                <div class="load-content">
                    <br/>
                    <center>
                        <img style="width: 48px;" src="<?= base_url('assets/images/loading.svg') ?>" alt="">
                    </center>
                    <br/>
                    Mohon tunggu ...
                    <br>
                    <br>
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