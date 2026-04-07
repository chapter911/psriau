<nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
    </ul>

    <ul class="navbar-nav ml-auto">
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownText" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <?= strtoupper(session()->get("username")); ?>
            </a>
            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdownText">
                <a class="dropdown-item" href="#">
                    <i class="fas fa-key"></i> Password
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="<?= base_url(); ?>Home/Logout">
                    <i class="fas fa-power-off"></i> Logout
                </a>
            </div>
        </li>
    </ul>
</nav>