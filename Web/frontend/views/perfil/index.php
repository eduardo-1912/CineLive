<?php

use yii\bootstrap4\ActiveForm;
use yii\bootstrap4\Breadcrumbs;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */

$this->title = 'Perfil';

?>

<div class="container">

    <div class="mb-4">
        <h4 class="page-title m-0"><?= Html::encode($this->title) ?></h4>
    </div>

    <div class="row g-3">
        <div class="col-md-5">
            <div class="box-white border shadow-sm">
                <div class="mb-4">
                    <h5 class="page-title mb-3">Dados pessoais</h5>
                    <div>

                    </div>
                    <div class="d-flex gap-2">
                        <a class="btn btn-light rounded-3 w-100">Editar</a>

                        <?= Html::beginForm(['/site/logout'], 'post', ['class' => 'p-0 m-0']) ?>
                        <button type="" class="btn btn-danger rounded-3 w-100">Logout</button>
                        <?= Html::endForm() ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-7 d-flex flex-column gap-3">
            <div class="box-gray shadow-sm"">
                <div class="mb-4">
                    <h5 class="page-title mb-3">Histórico de Compras</h5>
                    <div class="box-white">

                    </div>
                </div>
            </div>
            <div class="box-gray shadow-sm"">
                <div class="mb-4">
                    <h5 class="page-title mb-3">Alugueres de Sala</h5>
                </div>
            </div>
        </div>
    </div>



</div>

