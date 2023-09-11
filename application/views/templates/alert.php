<?php
if(isset($_SESSION['response']) && $_SESSION['response'] != ""){
$alert_type = "alert-success";
$response = $_SESSION['response'];
$description = $_SESSION['description'];

if($response == "ERROR") $alert_type = "alert-danger";
if($response == "WARNING") $alert_type = "alert-warning";
?>
<div class="alert alert-dismissable <?= $alert_type ?>" style="box-shadow: 0 1px 5px rgba(0,0,0,0.5)">
    <button class="close" type="button" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
    <p style="font-size: 1.2em;color: #fff"><?= $description ?></p>
    
</div>
<?php
clear_alert();
}
?>