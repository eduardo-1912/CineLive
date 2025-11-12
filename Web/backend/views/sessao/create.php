<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Sessao */

$this->title = 'Criar Sessão';
$this->params['breadcrumbs'][] = ['label' => 'Sessões', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
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
                        'cinema_id' => $cinema_id,
                    ]) ?>
                </div>
            </div>
        </div>
        <!--.card-body-->
    </div>
    <!--.card-->
</div>