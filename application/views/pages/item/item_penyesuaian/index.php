<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$allow_user_create = 1;
$allow_user_delete = 1;
$allow_user_update = 1;
?>
<!DOCTYPE html>
<html>
    <head>                
        <?php view("templates/meta"); ?>
        <?php view("templates/style"); ?>
    </head>
    <body class="hold-transition sidebar-mini">
        <div class="wrapper">

            <?php view("templates/header"); ?>                        
            <?php view("templates/sidebar"); ?>                        

            <!-- Content Wrapper. Contains page content -->
            <div class="content-wrapper">
                <!-- Content Header (Page header) -->
                <section class="content-header">
                  <h1>
                    Item Penyesuaian
                    <small></small>
                  </h1>
                </section>

                <!-- Main content -->
                <section class="content">
                    <h1>Modul ini sedang dalam pengembangan</h1>
                </section>
                <!-- /.content -->
            </div>
            <!-- /.content-wrapper -->
            
            <?php view("templates/footer") ?>
        </div>
        <!-- ./wrapper -->        
        <?php view("templates/script") ?>

    </body>
</html>
