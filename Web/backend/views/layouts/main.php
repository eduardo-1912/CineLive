<?php

/* @var $this \yii\web\View */
/* @var $content string */

use backend\components\ToastWidget;
use yii\helpers\Html;
use backend\assets\AppAsset;
use hail812\adminlte3\assets\FontAwesomeAsset;
use hail812\adminlte3\assets\AdminLteAsset;

AppAsset::register($this);
FontAwesomeAsset::register($this);
AdminLteAsset::register($this);
$this->registerCssFile('https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback');


$assetDir = Yii::$app->assetManager->getPublishedUrl('@vendor/almasaeed2010/adminlte/dist');

$publishedRes = Yii::$app->assetManager->publish('@vendor/hail812/yii2-adminlte3/src/web/js');
$this->registerJsFile($publishedRes[1].'/control_sidebar.js', ['depends' => '\hail812\adminlte3\assets\AdminLteAsset']);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/x-icon" href="../web/favicon.ico">
    <?php $this->registerCsrfMetaTags() ?>
    <title>CineLive | <?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>


    <!-- FAVICON (LIGHT-MODE) -->
    <link rel="icon" type="image/svg+xml" href="<?= Yii::getAlias('@web') ?>/favicon-light.svg" media="(prefers-color-scheme: light)">

    <!-- FAVICON (DARK-MODE) -->
    <link rel="icon" type="image/svg+xml" href="<?= Yii::getAlias('@web') ?>/favicon-dark.svg" media="(prefers-color-scheme: dark)">

</head>
<body class="hold-transition sidebar-mini">
<?php $this->beginBody() ?>

<div class="wrapper">
    <!-- Navbar -->
    <?= $this->render('navbar', ['assetDir' => $assetDir]) ?>
    <!-- /.navbar -->

    <!-- Main Sidebar Container -->
    <?= $this->render('sidebar', ['assetDir' => $assetDir]) ?>

    <!-- Content Wrapper. Contains page content -->
    <?= $this->render('content', ['content' => $content, 'assetDir' => $assetDir]) ?>
    <!-- /.content-wrapper -->

    <!-- Control Sidebar -->
    <?= $this->render('control-sidebar') ?>
    <!-- /.control-sidebar -->

    <!-- Main Footer -->
    <?= $this->render('footer') ?>
</div>

<?= ToastWidget::widget() ?>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
