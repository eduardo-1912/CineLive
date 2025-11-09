<?php

/* @var $this \yii\web\View */
/* @var $content string */

use common\components\ToastWidget;
use yii\bootstrap4\ActiveForm;
use yii\helpers\Html;
use backend\assets\AppAsset;
use hail812\adminlte3\assets\FontAwesomeAsset;
use hail812\adminlte3\assets\AdminLteAsset;
use yii\helpers\Url;

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
    <title><?= Yii::$app->user->identity->profile->cinema->nome ?? Yii::$app->name ?> | <?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>

    <!-- FAVICON (LIGHT-MODE) -->
    <link rel="icon" type="image/svg+xml" href="<?= Yii::getAlias('@web') ?>/favicon-light.svg" media="(prefers-color-scheme: light)">

    <!-- FAVICON (DARK-MODE) -->
    <link rel="icon" type="image/svg+xml" href="<?= Yii::getAlias('@web') ?>/favicon-dark.svg" media="(prefers-color-scheme: dark)">

</head>
<body class="hold-transition sidebar-mini layout-fixed">
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

<!-- MODAL PARA VALIDAR BILHETE -->
<div class="modal fade" id="modal-validar-bilhete" tabindex="-1" role="dialog" aria-labelledby="modalValidarLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalValidarLabel">Validar Bilhete</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?php $form = ActiveForm::begin(['id' => 'form-validar-bilhete', 'action' => Url::to(['/bilhete/validate']), 'method' => 'post',]); ?>

                <div class="form-group">
                    <?= Html::label('Código do Bilhete', 'codigo-bilhete') ?>
                    <?= Html::textInput('codigo', '', ['id' => 'codigo-bilhete', 'class' => 'form-control',
                        'required' => true, 'placeholder' => 'Ex: ABC123',]) ?>
                </div>

                <div class="form-group form-check ms-1">
                    <?= Html::checkbox('confirmar_todos', false, ['class' => 'form-check-input', 'id' => 'confirmar-todos', 'value' => 1,]) ?>
                    <?= Html::label('Confirmar todos os bilhetes pendentes da mesma compra', 'confirmar-todos', ['class' => 'form-check-label']) ?>
                </div>

                <?= Html::submitButton('Validar Bilhete', ['class' => 'btn btn-success btn-block',]) ?>

                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>


<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
