<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Prasarana Strategis</title>

    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="icon" type="image/x-icon" href="<?= base_url(); ?>public/img/bws_sumatera_iii.png">
    <link rel="stylesheet" href="<?= base_url(); ?>public/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="<?= base_url(); ?>public/css/adminlte.min.css">

    <link rel="stylesheet" href="<?= base_url(); ?>public/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="<?= base_url(); ?>public/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
    <link rel="stylesheet" href="<?= base_url(); ?>public/plugins/datatables-buttons/css/buttons.bootstrap4.min.css">

    <link rel="stylesheet" href="<?= base_url(); ?>public/plugins/select2/css/select2.min.css">
    <link rel="stylesheet" href="<?= base_url(); ?>public/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
    
    <link rel="stylesheet" href="<?= base_url(); ?>public/plugins/summernote/summernote-bs4.min.css">

    <script src="<?= base_url(); ?>public/plugins/jquery/jquery.min.js"></script>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
	<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        thead>tr, tfoot>tr {
            text-align: center;
            vertical-align: middle !important;
            font-weight: bold;
            background-color: #343a40 !important;
            color: #FFFFFF;
        }

        .table th {
            color: #FFFFFF;
            vertical-align: middle !important;
        }

        .modal .modal-header {
            background-color: #343a40;
        }

        .modal-title {
            color: white;
            text-align: center;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: #343a40;
            border-color: #343a40;
            color: white;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            color: white;
        }

        /* make Select2 match Bootstrap .form-control height */
        .select2-container .select2-selection--single {
            height: 38px !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 38px !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px !important;
        }
    </style>
</head>

<body class="hold-transition sidebar-mini layout-navbar-fixed">
    <div class="wrapper">
        <div class="preloader flex-column justify-content-center align-items-center">
            <img class="animation__shake" src="<?= base_url(); ?>public/img/bws_sumatera_iii.jpg" alt="Prasarana Strategis" height="200" width="200">
        </div>
        <?= $_header; ?>

        <?= $_sidebar; ?>

        <div class="content-wrapper">
            <section class="content-header">
            </section>

            <section class="content">
                <div class="container-fluid">
                    <?= $_container; ?>
                </div>
            </section>
        </div>

        <?= $_footer; ?>

        <aside class="control-sidebar control-sidebar-dark">
        </aside>
    </div>

    <script src="<?= base_url(); ?>public/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?= base_url(); ?>public/js/adminlte.min.js"></script>

    <script src="<?= base_url(); ?>public/plugins/summernote/summernote-bs4.min.js"></script>
    <script src="<?= base_url(); ?>public/plugins/datatables/jquery.dataTables.min.js"></script>
    <script src="<?= base_url(); ?>public/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
    <script src="<?= base_url(); ?>public/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
    <script src="<?= base_url(); ?>public/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
    <script src="<?= base_url(); ?>public/plugins/datatables-buttons/js/dataTables.buttons.min.js"></script>
    <script src="<?= base_url(); ?>public/plugins/datatables-buttons/js/buttons.bootstrap4.min.js"></script>
    <script src="<?= base_url(); ?>public/plugins/jszip/jszip.min.js"></script>
    <script src="<?= base_url(); ?>public/plugins/pdfmake/pdfmake.min.js"></script>
    <script src="<?= base_url(); ?>public/plugins/pdfmake/vfs_fonts.js"></script>
    <script src="<?= base_url(); ?>public/plugins/datatables-buttons/js/buttons.html5.min.js"></script>
    <script src="<?= base_url(); ?>public/plugins/datatables-buttons/js/buttons.print.min.js"></script>
    <script src="<?= base_url(); ?>public/plugins/datatables-buttons/js/buttons.colVis.min.js"></script>

    <script src="<?= base_url(); ?>public/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
    <script src="<?= base_url(); ?>public/plugins/select2/js/select2.full.min.js"></script>

    <script>
        $(function () {
            $('.select2').select2();

            $("input[data-bootstrap-switch]").each(function(){
                $(this).bootstrapSwitch();
            });

            $('input[type="date"]').on('focus', function() {
                this.showPicker();
            });
        });
    </script>
</body>

</html>