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
            $profile = $currentUser->identity->profile;
            $userCinemaId = $profile->cinema_id;

            $gerirUtilizadores = $currentUser->can('gerirUtilizadores');
            $gerirFuncionarios = $currentUser->can('gerirFuncionarios');
            $gerirCinemas = $currentUser->can('gerirCinemas');
            $gerirFilmes = $currentUser->can('gerirFilmes');

            use common\models\AluguerSala;
            $alugueresPendentes = AluguerSala::find()->where(
            ['estado' => AluguerSala::ESTADO_PENDENTE, 'cinema_id' => $userCinemaId])->exists();

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