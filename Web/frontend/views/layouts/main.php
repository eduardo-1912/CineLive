<?php

/** @var \yii\web\View $this */
/** @var string $content */

use common\widgets\Alert;
use frontend\assets\AppAsset;
use yii\bootstrap5\Breadcrumbs;
use yii\bootstrap5\Html;
use yii\bootstrap5\Nav;
use yii\bootstrap5\NavBar;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

AppAsset::register($this);
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

    <!-- BOOTSTRAP NÃO ESTÁ A FUNCIONAR SEM ESTE LINK -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!--BOOTSTRAP TOOLTIPS-->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            tooltipTriggerList.forEach(function (tooltipTriggerEl) {
                new bootstrap.Tooltip(tooltipTriggerEl)
            })
        })
    </script>

</head>
<body class="d-flex flex-column h-100">
<?php $this->beginBody() ?>

<nav class="navbar navbar-expand-lg fixed-top">
    <div class="container justify-content-between">

        <!-- LOGO -->
        <a class="d-flex align-items-center gap-1 text-black text-decoration-none" href="<?= Yii::$app->homeUrl ?>">
            <?= file_get_contents(Yii::getAlias('@webroot/icons/logo.svg')) ?>
            <span class="navbar-brand fw-bold me-0 fs-6 p-0">CineLive</span>
        </a>

        <!-- NAV-TOGGLER -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- NAV-ITEMS -->
        <div class="collapse navbar-collapse justify-content-end pt-2 pt-lg-0" id="navbarNav">
            <?php
                $menuItems = [
                    ['label' => 'Filmes', 'url' => ['/filme/index']],
                    ['label' => 'Cinemas', 'url' => ['/cinema/index']],
                    ['label' => 'Serviços', 'url' => ['/servicos/index']],
                ];

                echo Nav::widget([
                    'options' => ['class' => 'navbar-nav font-15 fw-medium ls-10'],
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
            <div class="input-group nav-search" style="overflow: hidden;">
                <?= Html::input('search', 'q', Yii::$app->request->get('q'), [
                    'class' => 'form-control border-0 bg-light',
                    'placeholder' => 'Pesquisar filmes...',
                    'aria-label' => 'Pesquisar filmes',
                    'style' => 'box-shadow:none;',
                ]) ?>

                <button class="btn bg-light border-0 d-inline-flex" type="submit">
                    <?= file_get_contents(Yii::getAlias('@webroot/icons/search.svg')) ?>
                </button>
            </div>
            <?php ActiveForm::end(); ?>

            <!-- LOGIN/USER-->
                <?php if (Yii::$app->user->isGuest): ?>
                    <div class="d-flex align-items-center gap-3">
                        <?php echo Nav::widget([
                            'options' => ['class' => 'navbar-nav font-15 fw-medium ls-10'],
                            'items' => [
                                ['label' => 'Login', 'url' => ['/site/login']],
                            ],
                        ]); ?>
                    </div>

                <?php else: ?>
                    <a href="<?= Url::to(['/user/index']) ?>"
                       class="d-none d-lg-inline-flex align-items-center icon-link"
                       data-bs-toggle="tooltip"
                       data-bs-placement="bottom"
                       data-bs-title="Área de Cliente">
                        <?= file_get_contents(Yii::getAlias('@webroot/icons/user-circle.svg')) ?>
                    </a>

                   <?php
                        echo Nav::widget([
                            'options' => ['class' => 'd-flex d-lg-none navbar-nav font-15 fw-medium ls-10'],
                            'items' => [
                                ['label' => 'Área de Cliente', 'url' => ['/user/index']],
                            ],
                        ]); ?>
                <?php endif; ?>
        </div>
    </div>
</nav>



<main role="main" class="flex-shrink-0" style="margin-top: 63px">
<!--    <div class="container">-->
<!--        --><?php //= Breadcrumbs::widget([
//            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
//        ]) ?>
<!--        --><?php //= Alert::widget() ?>
<!--    </div>-->

    <?= $content ?>

</main>

<footer class="footer mt-auto py-3">
    <div class="container d-flex justify-content-between">
        <p class="mb-0 fw-medium text-muted">
            &copy;<?= date('Y') ?> <?= Html::encode(Yii::$app->name) ?>
        </p>

        <p class="mb-0 d-flex gap-3">
            <?= Html::a('Administração', ['../../backend/web'], ['class' => 'text-muted text-decoration-none']) ?>
        </p>
    </div>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage();
