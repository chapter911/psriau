<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="<?= base_url(); ?>" class="brand-link elevation-4">
        <img src="<?= base_url(); ?>public/img/logo_pu.png" alt="Prasarana Strategis"
            class="brand-image img-circle elevation-3" style="opacity: .8">
        <span class="brand-text font-weight-light">Prasarana Strategis</span>
    </a>

    <div class="sidebar">
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <img src="<?= base_url(); ?>public/img/user2-160x160.jpg" class="img-circle elevation-2"
                    alt="User Image">
            </div>
            <div class="info">
                <a href="#" class="d-block"><?= strtoupper(session()->get("nama")); ?></a>
            </div>
        </div>

        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <li class="nav-item">
                    <a href="<?= base_url(); ?>" class="nav-link">
                        <i class="nav-icon fas fa-home"></i>
                        <p>DASHBOARD</p>
                    </a>
                </li>

                <?php foreach ($_menulv1 as $lv1) { ?>
                    <li class="nav-item">
                        <a href="#" class="nav-link">
                            <i class="nav-icon <?= $lv1->icon; ?>"></i>
                            <p><?= strtoupper($lv1->label); ?><?= $lv1->link == "#" ? "<i class='nav-arrow fas fa-angle-left right'></i>" : ""?></p>
                        </a>
                        <?php if($lv1->link == "#") { ?>
                            <ul class="nav nav-treeview">
                                <?php foreach ($_menulv2 as $lv2) {
                                    if($lv2->header == $lv1->id) { ?>
                                        <li class="nav-item">
                                            <a href="<?= $lv2->link == '#' ? '#' : base_url($lv2->link); ?>" class="nav-link">
                                                <i class="nav-icon far fa-circle"></i>
                                                <p><?= strtoupper($lv2->label); ?><?= $lv2->link == "#" ? "<i class='nav-arrow fas fa-angle-left right'></i>" : ""?></p>
                                            </a>
                                            <?php if($lv2->link == "#") { ?>
                                                <ul class="nav nav-treeview">
                                                    <?php foreach ($_menulv3 as $lv3) {
                                                        if($lv3->header == $lv2->id) { ?>
                                                        <li class="nav-item">
                                                            <a href="<?= base_url($lv3->link); ?>" class="nav-link">
                                                                <i class="nav-icon far fa-dot-circle"></i>
                                                                <p><?= strtoupper($lv3->label); ?></p>
                                                            </a>
                                                        </li>
                                                    <?php }
                                                    } ?>
                                                </ul>
                                            <?php } ?>
                                        </li>
                                    <?php }
                                } ?>
                            </ul>
                        <?php } ?>
                    </li>
                <?php } ?>
            </ul>
        </nav>
    </div>
</aside>