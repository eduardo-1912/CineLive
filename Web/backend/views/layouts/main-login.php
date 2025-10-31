<?php

/* @var $this \yii\web\View */
/* @var $content string */

use backend\assets\AppAsset;
use backend\components\ToastWidget;
use yii\helpers\Html;

\hail812\adminlte3\assets\AdminLteAsset::register($this);
$this->registerCssFile('https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700');
$this->registerCssFile('https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css');
\hail812\adminlte3\assets\PluginAsset::register($this)->add(['fontawesome', 'icheck-bootstrap']);
AppAsset::register($this);

?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>CineLive | <?= Html::encode($this->title) ?></title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php $this->registerCsrfMetaTags() ?>
    <?php $this->head() ?>
</head>
<body class="hold-transition login-page">

<?php  $this->beginBody() ?>
<div class="login-box">
    <div class="login-logo">
        <span class="d-flex flex-column justify-content-center align-items-center">
            <img src="<?= Yii::getAlias('@web/favicon-light.svg') ?>" alt="CineLive" style="width: 5rem;">
            <span class="fw-bold">CineLive</span>
        </span>
    </div>
    <!-- /.login-logo -->

    <?= $content ?>
</div>
<!-- /.login-box -->

<?= ToastWidget::widget() ?>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>