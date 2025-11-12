<?php

/* @var $this yii\web\View */
/* @var $model common\models\Sessao */

$this->title = 'Editar: ' . $model->nome;
$this->params['breadcrumbs'][] = ['label' => 'Sessões', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Editar';
?>

<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <?=$this->render('_form', [
                        'model' => $model,
                        'cinemasAtivos' => $cinemasAtivos,
                        'filmesEmExibicao' => $filmesEmExibicao,
                        'salasDropdown' => $salasDropdown,
                    ]) ?>
                </div>
            </div>
        </div>
        <!--.card-body-->
    </div>
    <!--.card-->
</div>