<?php

use yii\helpers\Html;
use yii\helpers\Url;

?>
<!-- Navbar -->
<nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
        <li class="nav-item d-flex align-items-center">
            <!-- SEARCH FORM -->
            <form class="d-none d-md-flex form-inline">
                <div class="input-group input-group-md">
                    <input class="form-control form-control-navbar" type="search" placeholder="Pesquisar Filmes..." aria-label="Search">
                    <div class="input-group-append">
                        <button class="btn btn-navbar" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </form>
        </li>
    </ul>



    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
        <li class="nav-item dropdown">
            <button class="btn btn-link nav-link dropdown-toggle d-inline-flex align-items-center" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-user-circle fa-md mr-1"></i>
                <?= Html::encode(Yii::$app->user->identity->profile->nome ?? Yii::$app->user->identity->username) ?>
            </button>
            <ul class="dropdown-menu dropdown-menu-end border-0 shadow">
                <li>
                    <a class="dropdown-item" href="<?= Url::to(['/profile/index']) ?>">
                        <i class="fas fa-user fa-sm mr-1"></i>
                        Perfil
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="<?= Url::to(['/site/logout']) ?>" data-method="post">
                        <i class="fas fa-sign-out-alt fa-sm mr-1"></i>
                        Logout
                    </a>
                </li>
                <li class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item" href="<?= Url::to(['../../frontend/web']) ?>">
                        <i class="fas fa-globe fa-sm mr-1"></i>
                        Público
                    </a>
                </li>
            </ul>
        </li>
    </ul>
</nav>
<!-- /.navbar -->