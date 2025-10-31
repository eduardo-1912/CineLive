<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\User */

$keyword = Yii::$app->user->can('gerirUtilizadores') ? 'Utilizador' : 'Funcionário';
$breadcrumb = $keyword == 'Utilizador' ? 'Utilizadores' : 'Funcionários';
$return_path = $keyword == 'Utilizador' ? 'index' : 'funcionarios';

$this->title = 'Criar ' . $keyword;
$this->params['breadcrumbs'][] = ['label' => $breadcrumb, 'url' => [$return_path]];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <?= $this->render('_form', [
                        'model' => $model,
                        'profile' => $profile,
                    ]) ?>

                </div>
            </div>
        </div>
        <!--.card-body-->
    </div>
    <!--.card-->
</div>