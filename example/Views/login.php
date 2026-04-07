<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Prasarana Strategis</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <link rel="stylesheet" href="<?= base_url(); ?>public/plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="<?= base_url(); ?>public/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <link rel="stylesheet" href="<?= base_url(); ?>public/css/adminlte.min.css">
  <link rel="icon" type="image/x-icon" href="<?= base_url(); ?>public/img/logo_pu.png">
</head>
<body class="hold-transition login-page" style="background: url('<?= base_url(); ?>public/img/background_jembatan.jpg') no-repeat center center fixed; background-size: cover;">
<div class="login-box">
  <div class="card card-outline card-primary">
    <div class="card-header text-center">
        <img src="<?= base_url(); ?>public/img/logo_pu.png" alt="Prasarana Strategis"
            class="brand-image img-box elevation-3" height="200" width="200"><br/>
    </div>
    <div class="card-body">
      <h3 class="login-box-msg text-center">PRASARANA STRATEGIS</h3>
      <p class="login-box-msg text-center">Masuk untuk memulai sesi Anda</p>
      <form action="<?= base_url(); ?>Home/Login" method="post">
        <div class="input-group mb-3">
          <input name="username" type="text" class="form-control" placeholder="Username" autofocus>
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-user"></span>
            </div>
          </div>
        </div>
        <div class="input-group mb-3">
          <input name="password" type="password" class="form-control" placeholder="Password">
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-lock"></span>
            </div>
          </div>
        </div>
        <div class="row">
            <div class="col-6">
              <button type="button" class="btn btn-danger btn-block">Lupa Password</button>
            </div>
            <div class="col-6">
              <button type="submit" class="btn btn-primary btn-block">Sign In</button>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="<?= base_url(); ?>public/plugins/jquery/jquery.min.js"></script>
<script src="<?= base_url(); ?>public/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="<?= base_url(); ?>public/js/adminlte.min.js"></script>
</body>
</html>
