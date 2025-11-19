<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="<?= Yii::$app->homeUrl ?>" class="brand-link d-flex justify-content-center align-items-center margin-auto">
        <img src="<?= Yii::getAlias('@web/favicon-dark.svg') ?>" alt="CineLive" style="width:24px; padding-block: 3px;">
        <span class="brand-text fw-bold ms-1"><?= Yii::$app->user->identity->profile->cinema->nome ?? 'CineLive' ?></span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <?php

            $profile = $data['profile'];
            $gerirUtilizadores = $data['gerirUtilizadores'];
            $gerirFuncionarios = $data['gerirFuncionarios'];
            $gerirCinemas = $data['gerirCinemas'];
            $gerirFilmes = $data['gerirFilmes'];
            $alugueresPendentes = $data['alugueresPendentes'];

            echo \hail812\adminlte\widgets\Menu::widget([
                'items' => [
                    ['label' => 'Dashboard',  'icon' => 'columns', 'url' => ['/site/index']],

                    ['label' => 'Gestão', 'header' => true, 'visible' => $gerirFuncionarios],
                    ['label' => $gerirUtilizadores ? 'Utilizadores' : 'Funcionários',  'icon' => 'users', 'url' => ['/user/index'], 'visible' => $gerirFuncionarios],

                    ['label' => 'Espaços', 'header' => true],
                    ['label' => $gerirCinemas ? 'Cinemas' : 'Cinema',  'icon' => 'building', 'url' => [$gerirCinemas ? '/cinema/index' : ('/cinema/view?id=' . $profile->cinema->id)]],
                    ['label' => 'Salas',  'icon' => 'chair', 'url' => ['/sala/index']],

                    ['label' => 'Filmes', 'header' => true],
                    ['label' => 'Filmes',  'icon' => 'film', 'url' => ['/filme/index']],
                    ['label' => 'Géneros',  'icon' => 'tags', 'url' => ['/genero/index'], 'visible' => $gerirFilmes],
                    ['label' => 'Sessões',  'icon' => 'calendar-alt', 'url' => ['/sessao/index']],

                    ['label' => 'Reservas', 'header' => true],
                    ['label' => 'Compras',  'icon' => 'ticket-alt', 'url' => ['/compra/index']],
                    [
                        'label' => 'Alugueres' . ($alugueresPendentes ? '<i class="fas fa-exclamation text-danger ms-2"></i>' : ''),
                        'icon' => 'clock',
                        'url' => ['/aluguer-sala/index'],
                        'encode' => false,
                    ],

                ],
            ]);
            ?>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>