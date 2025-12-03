<?php

use backend\helpers\ActionColumnButtonHelper;
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\User */
/* @var $gerirUtilizadores bool */
/* @var $verFuncionariosCinema bool */
/* @var $isOwnAccount bool */
/* @var $comprasDataProvider yii\data\ActiveDataProvider */


$label = $gerirUtilizadores ? 'Utilizadores' : 'Funcionários';

$this->title = $model->profile->nome ?? $model->username;
$this->params['breadcrumbs'][] = $gerirUtilizadores || $verFuncionariosCinema ? ['label' => $label, 'url' => ['index']] : ['label' => $label];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>

<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <p>
                        <?php if ($gerirUtilizadores || $isOwnAccount): ?>
                            <?= Html::a('Editar', ['update', 'id' => $model->id], ['class' => 'btn btn-warning']); ?>
                        <?php endif; ?>
                    </p>

                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            [
                                'attribute' => 'id',
                                'headerOptions' => ['style' => 'width: 3rem;'],
                            ],
                            'username',
                            'email:email',
                            [
                                'attribute' => 'profile.nome',
                                'value' => $model->profile->nome ?? '-',
                            ],
                            [
                                'attribute' => 'profile.telemovel',
                                'value' => $model->profile->telemovel ?? '-',
                            ],
                            [
                                'attribute' => 'role',
                                'value' => $model->displayRole(),
                            ],
                            [
                                'attribute' => 'cinema_id',
                                'value' => $model->cinema->nome ?? '-',
                                'visible' => $gerirUtilizadores && $model->profile->cinema_id,
                            ],
                            [
                                'attribute' => 'status',
                                'value' => fn($model) => ActionColumnButtonHelper::userEstadoDropdown($model),
                                'format' => 'raw',
                                'visible' => $gerirUtilizadores || $verFuncionariosCinema && !$isOwnAccount,
                            ],
                        ],
                    ]) ?>

                </div>
                <!--.col-md-12-->
            </div>
            <!--.row-->
        </div>
        <!--.card-body-->
    </div>
    <!--.card-->

    <?php if ($model->compras): ?>
        <h3 class="mt-4 mb-3">Compras</h3>
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <?= $this->render('_compras', [
                            'dataProvider' => $comprasDataProvider,
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

</div>