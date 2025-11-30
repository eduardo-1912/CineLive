<?php

use yii\bootstrap4\ActiveForm;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var common\models\Sessao $sessao */
/** @var array $lugaresSelecionados */
/** @var string $total */
/** @var string $metodo */

?>

<?php $form = ActiveForm::begin(['action' => ['compra/pay'], 'method' => 'post',]); ?>

    <?= Html::hiddenInput('sessao_id', $sessao->id) ?>
    <?= Html::hiddenInput('lugares', implode(',', $lugaresSelecionados)) ?>
    <?= Html::hiddenInput('metodo', $metodo) ?>

    <button type="submit" class="btn btn-danger w-100 fw-medium rounded-3">Pagar <?= $total ?></button>

<?php ActiveForm::end(); ?>