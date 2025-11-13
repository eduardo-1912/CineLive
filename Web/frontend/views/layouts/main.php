<?php

/** @var \yii\web\View $this */
/** @var string $content */

use common\components\ToastWidget;
use common\widgets\Alert;
use frontend\assets\AppAsset;
use yii\bootstrap5\Breadcrumbs;
use yii\bootstrap5\Html;
use yii\bootstrap5\Nav;
use yii\bootstrap5\NavBar;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

AppAsset::register($this);

$iconsPath = '@webroot/icons/';
$perfilPath = '/perfil/index';

?>

<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" class="h-100">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <?php $this->registerCsrfMetaTags() ?>
    <title>CineLive | <?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>

    <!-- FAVICON (LIGHT-MODE) -->
    <link rel="icon" type="image/svg+xml" href="<?= Yii::getAlias('@web') ?>/favicon-light.svg" media="(prefers-color-scheme: light)">

    <!-- FAVICON (DARK-MODE) -->
    <link rel="icon" type="image/svg+xml" href="<?= Yii::getAlias('@web') ?>/favicon-dark.svg" media="(prefers-color-scheme: dark)">

</head>
<body class="d-flex flex-column h-100">
<?php $this->beginBody() ?>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg fixed-top">
    <div class="container justify-content-between">

        <!-- LOGO -->
        <a class="d-flex align-items-center gap-1 text-black text-decoration-none" href="<?= Yii::$app->homeUrl ?>">
            <?= file_get_contents(Yii::getAlias($iconsPath . 'logo.svg')) ?>
            <span class="navbar-brand fw-bold me-0 fs-6 p-0">CineLive</span>
        </a>

        <!-- NAV-TOGGLER (MOBILE) -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- NAV-ITEMS -->
        <div class="collapse navbar-collapse justify-content-end pt-2 pt-lg-0" id="navbarNav">
            <?php
                $menuItems = [
                    ['label' => 'Filmes', 'url' => ['/filme/index']],
                    ['label' => 'Cinemas', 'url' => ['/cinema/index']],
                    ['label' => 'Serviços', 'url' => ['/site/contact']],
                ];
                echo Nav::widget([
                    'options' => ['class' => 'navbar-nav font-15 fw-medium ls-10 gap-1'],
                    'items' => $menuItems,
                ]);
            ?>

            <!-- BARRA DE PESQUISA -->
            <?php $form = ActiveForm::begin([
                'action' => Url::to(['/filme/index']),
                'method' => 'get',
                'options' => [
                    'class' => 'search-form d-none d-lg-flex align-items-center ms-4 me-3',
                    'role' => 'search',
                    'onsubmit' => 'return this.querySelector(\'input[name="q"]\').value.trim().length > 0;',
                ],
            ]); ?>

            <!-- INPUT PESQUISA-->
            <div class="input-group nav-search overflow-hidden">
                <?= Html::input('search', 'q', Yii::$app->request->get('q'), [
                    'class' => 'form-control border-0 bg-light shadow-none',
                    'placeholder' => 'Pesquisar filmes...',
                    'aria-label' => 'Pesquisar filmes',
                ]) ?>

                <!-- HIDDEN-INPUT DE CINEMA -->
                <?= Html::hiddenInput('cinema_id', '', ['id' => 'navbar-cinema-id']) ?>

                <button class="btn bg-light border-0 d-inline-flex" type="submit">
                    <?= file_get_contents(Yii::getAlias($iconsPath . 'search.svg')) ?>
                </button>
            </div>
            <?php ActiveForm::end(); ?>

            <!-- OBTER CINEMA DA LOCAL-STORAGE -->
            <script>

                // OBTER ÚLTIMO CINEMA ESCOLHIDO DA LOCAL STORAGE
                const savedCinema = localStorage.getItem('cinema_id');

                // ATUALIZAR INPUT DA BARRA DE PESQUISA
                const cinemaInput = document.getElementById('navbar-cinema-id');
                if (savedCinema && cinemaInput) {
                    cinemaInput.value = savedCinema;
                }

                // ATUALIZAR O NAV-LINK 'Filmes'
                const filmesLink = document.querySelector('a.nav-link[href$="/filme/index"]');
                if (savedCinema && filmesLink) {
                    const url = new URL(filmesLink.href, window.location.origin);
                    url.searchParams.set('cinema_id', savedCinema);
                    filmesLink.href = url.toString();
                }

            </script>

            <!-- LINK LOGIN -->
            <?php if (Yii::$app->user->isGuest): ?>
                <div class="d-flex align-items-center gap-3">
                    <?php echo Nav::widget([
                        'options' => ['class' => 'navbar-nav font-15 fw-medium ls-10'],
                        'items' => [
                            ['label' => 'Login', 'url' => ['/site/login']],
                        ],
                    ]); ?>
                </div>

            <!-- DROPDOWN ÁREA DE CLIENTE (DESKTOP) -->
            <?php else: ?>
                <div class="dropdown">
                    <button class="btn d-none d-lg-inline-flex align-items-center icon-link p-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <?= file_get_contents(Yii::getAlias($iconsPath . 'user-circle.svg')) ?>
                    </button>

                    <ul class="fs-15 dropdown-menu dropdown-menu-end mt-2">
                        <?php $dropdownItemClasses = 'dropdown-item d-inline-flex align-items-center gap-1' ?>
                        <li>
                            <a class="<?= $dropdownItemClasses ?>" href="<?= Url::to([$perfilPath]) ?>">
                                <?= file_get_contents(Yii::getAlias($iconsPath . 'user.svg')) ?>
                                Perfil
                            </a>
                        </li>
                        <li>
                            <a class="<?= $dropdownItemClasses ?>" href="<?= Url::to(['compra/index']) ?>">
                                <?= file_get_contents(Yii::getAlias($iconsPath . 'ticket.svg')) ?>Compras
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <?= Html::beginForm(['/site/logout'], 'post', ['class' => 'p-0 m-0']) ?>
                        <?= Html::submitButton(
                            file_get_contents(Yii::getAlias($iconsPath . 'logout.svg')) . 'Logout',
                            ['class' => $dropdownItemClasses, 'encode' => false]
                        ) ?>
                        <?= Html::endForm() ?>
                    </ul>
                </div>

                <!-- LINK ÁREA DE CLIENTE (MOBILE) -->
                <?= Nav::widget([
                        'options' => ['class' => 'd-flex d-lg-none navbar-nav font-15 fw-medium ls-10'],
                        'items' => [
                            ['label' => 'Área de Cliente', 'url' => [$perfilPath]],
                        ],
                    ]);
                ?>
            <?php endif; ?>
        </div>
    </div>
</nav>

<!-- MAIN -->
<main role="main" class="flex-shrink-0">

    <div>
        <?= $content ?>

        <?= ToastWidget::widget() ?>
    </div>
</main>

<!-- FOOTER -->
<footer class="footer mt-auto">
    <div class="container d-flex justify-content-between align-items-center">
        <div class="d-flex flex-column gap-2">

            <!-- LOGO -->
            <a class="d-flex align-items-center gap-1 text-black text-decoration-none" href="<?= Yii::$app->homeUrl ?>">
                <?= file_get_contents(Yii::getAlias($iconsPath . 'logo.svg')) ?>
                <span class="navbar-brand fw-bold me-0 fs-6 p-0">CineLive</span>
            </a>

            <!-- SOCIAL MEDIA ICONS -->
            <div class="d-flex gap-2">
                <?php
                    $socialMediaIcons = ['instagram', 'facebook', 'youtube'];

                    foreach($socialMediaIcons as $socialMediaIcon) { ?>
                        <a href="<?= Url::to(['/']) ?>"><?= file_get_contents(Yii::getAlias($iconsPath . $socialMediaIcon . '.svg')); ?></a>
                    <?php }
                ?>
            </div>
        </div>

        <!-- NAV FOOTER LINKS -->
        <div class="d-flex gap-5">
            <div class="d-flex flex-column align-items-end gap-1">
                <?php
                    $navFooterLinks = [
                        'Área de Cliente' => $perfilPath,
                        'Termos e Condições' => '/',
                        'Administração' => '../../backend/web'
                    ];

                    $navFooterClasses = 'text-100 text-decoration-none';

                    foreach ($navFooterLinks as $name => $link) {
                        echo Html::a($name, Url::to([$link]), ['class' => $navFooterClasses]);
                    }
                ?>
            </div>
        </div>
    </div>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage();
