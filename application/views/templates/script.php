<!--**********************************
    Scripts
***********************************-->
<!-- Required vendors -->
<script src="<?= base_url('assets') ?>/vendor/global/global.min.js"></script>
<script src="<?= base_url('assets') ?>/vendor/bootstrap-select/dist/js/bootstrap-select.min.js"></script>
<script src="<?= base_url('assets') ?>/vendor/chart.js/Chart.bundle.min.js"></script>
<script src="<?= base_url('assets') ?>/js/custom.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js" integrity="sha512-2ImtlRlf2VVmiGZsjm9bEyhjGW4dU7B6TNwh/hx/iSByxNENtj3WVE6o/9Lj4TJeVXPi4bnOIMXFIJJAeufa0A==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="<?= base_url('assets') ?>/js/plugins-init/select2-init.js"></script>

<!-- Apex Chart -->
<!-- <script src="<?= base_url('assets') ?>/vendor/apexchart/apexchart.js"></script> -->

<!-- Vectormap -->
<!-- Chart piety plugin files -->
<script src="<?= base_url('assets') ?>/vendor/peity/jquery.peity.min.js"></script>

<!-- Chartist -->
<!-- <script src="<?= base_url('assets') ?>/vendor/chartist/js/chartist.min.js"></script> -->

<!-- Dashboard 1 -->
<!-- <script src="<?= base_url('assets') ?>/js/dashboard/dashboard-1.js"></script> -->
<!-- Svganimation scripts -->
<script src="<?= base_url('assets') ?>/vendor/svganimation/vivus.min.js"></script>
<script src="<?= base_url('assets') ?>/vendor/svganimation/svg.animation.js"></script>

<!-- select2 -->
<!-- <script src="<?= base_url('assets') ?>/vendor/select2/js/select2.full.min.js"></script> -->

<!-- InputMask -->
<script src="<?= base_url("assets/") ?>vendor/input-mask/jquery.inputmask.js"></script>
<script src="<?= base_url("assets/") ?>vendor/input-mask/jquery.inputmask.date.extensions.js"></script>
<script src="<?= base_url("assets/") ?>vendor/input-mask/jquery.inputmask.extensions.js"></script>

<!-- Toastr -->
<script src="<?= base_url("assets/") ?>vendor/toastr/js/toastr.min.js"></script>

<!-- All init script -->
<script src="<?= base_url("assets/") ?>js/plugins-init/toastr-init.js"></script>

<!-- APP JS -->
<script src="<?= base_url('assets_app') ?>/js/app.js"></script>
<script src="<?= base_url('assets_app') ?>/js/request.js"></script>



<!-- Datatable -->
<script src="<?= base_url('assets') ?>/vendor/datatables/js/jquery.dataTables.min.js"></script>
<script src="<?= base_url('assets') ?>/js/plugins-init/datatables.init.js"></script>
<script>
    function regenerateCsrfToken() {
        ajax_get(
            '<?= base_url('admin/user/get_csrf_data') ?>', {},
            function(json) {
                let csrfTokenName = json["csrfTokenName"];
                let csrfHash = json["csrfHash"];
                $('input[name=' + csrfTokenName + ']').val(csrfHash);
            }
        );
    }
</script>