<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="<?= Yii::$app->homeUrl ?>" class="brand-link d-flex justify-content-center align-items-center margin-auto">
        <img src="<?= Yii::getAlias('@web/favicon-dark.svg') ?>" alt="CineLive" style="width:24px; padding-block: 3px;">
        <span class="brand-text fw-bold ms-1"><?= Yii::$app->user->identity->profile->cinema->nome ?? Yii::$app->name ?></span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <?php

            $currentUser = Yii::$app->user;
            $isAdmin = $currentUser->can('admin');

            echo \hail812\adminlte\widgets\Menu::widget([
                'items' => [
                    ['label' => 'Dashboard',  'icon' => 'columns', 'url' => ['/site/index']],

                    ['label' => 'Gestão', 'header' => true, 'visible' => $currentUser->can('gerirFuncionarios'),],
                    ['label' => 'Utilizadores',  'icon' => 'users', 'url' => ['/user/index'], 'visible' => $currentUser->can('gerirUtilizadores')],
                    ['label' => 'Funcionários',  'icon' => 'user-tie', 'url' => ['/user/funcionarios'], 'visible' => $currentUser->identity->roleName == 'gerente'],

                    ['label' => 'Espaços', 'header' => true],
                    ['label' => 'Cinemas',  'icon' => 'building', 'url' => ['/cinema/index'], 'visible' => $currentUser->can('gerirCinemas')],
                    ['label' => 'Cinema',  'icon' => 'building', 'url' => ['/cinema/view?id=' . $currentUser->identity->profile->cinema_id], 'visible' => !$isAdmin],
                    ['label' => 'Salas',  'icon' => 'chair', 'url' => ['/sala/index']],

                    ['label' => 'Filmes', 'header' => true],
                    ['label' => 'Filmes',  'icon' => 'film', 'url' => ['/filme/index']],
                    ['label' => 'Géneros',  'icon' => 'tags', 'url' => ['/genero/index'], 'visible' => $currentUser->can('gerirFilmes')],
                    ['label' => 'Sessões',  'icon' => 'calendar-alt', 'url' => ['/sessao/index']],

                    ['label' => 'Reservas', 'header' => true],
                    ['label' => 'Bilhetes',  'icon' => 'ticket-alt', 'url' => ['/compra/index']],
                    ['label' => 'Alugueres',  'icon' => 'clock', 'url' => ['/aluguer/index']],
                ],
            ]);
            ?>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>