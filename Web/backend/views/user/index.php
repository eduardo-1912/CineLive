<?php

use backend\components\AppGridView;
use backend\helpers\ActionColumnButtonHelper;
use backend\helpers\LinkHelper;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\UserSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $gerirUtilizadores bool */
/* @var $verFuncionariosCinema bool */
/* @var $roleOptions array */
/* @var $cinemaOptions array */
/* @var $statusOptions array */

$title = $gerirUtilizadores ? 'Utilizadores' : 'Funcionários';

$this->title = $title;
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col-md-12">
                            <?php if($gerirUtilizadores || $verFuncionariosCinema): ?>
                                <?= Html::a('Criar ' . ($gerirUtilizadores ? 'Utilizador' : 'Funcionário'), ['create'], ['class' => 'btn btn-success']) ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?= AppGridView::widget([
                        'dataProvider' => $dataProvider,
                        'filterModel' => $searchModel,
                        'pager' => [
                            'class' => 'yii\bootstrap5\LinkPager',
                        ],
                        'columns' => [
                            [
                                'attribute' => 'id',
                                'headerOptions' => ['style' => 'width: 3rem;'],
                            ],
                            'username',
                            'email:email',
                            [
                                'attribute' => 'nome',
                                'value' => 'profile.nome',
                            ],
                            [
                                'attribute' => 'telemovel',
                                'value' => 'profile.telemovel',
                                'visible' => $verFuncionariosCinema,
                            ],
                            [
                                'attribute' => 'role',
                                'value' => 'roleName',
                                'filter' => $roleOptions,
                                'filterInputOptions' => ['class' => 'form-control', 'prompt' => 'Todos'],
                                'visible' => $gerirUtilizadores,
                            ],
                            [
                                'attribute' => 'cinema_id',
                                'value' => fn($model) => LinkHelper::nullSafe($model->profile->cinema->nome ?? null, 'cinema/view', $model->profile->cinema_id, '-'),
                                'format' => 'raw',
                                'filter' => $cinemaOptions,
                                'filterInputOptions' => ['class' => 'form-control', 'prompt' => 'Todos'],
                                'visible' => $gerirUtilizadores,
                            ],
                            [
                                'attribute' => 'status',
                                'value' => fn($model) => ActionColumnButtonHelper::userEstadoDropdown($model),
                                'format' => 'raw',
                                'filter' => $statusOptions,
                                'filterInputOptions' => ['class' => 'form-control', 'prompt' => 'Todos',],
                                'headerOptions' => ['style' => 'width: 8rem'],
                            ],
                            [
                                'class' => 'backend\components\AppActionColumn',
                                'template' => $gerirUtilizadores ? '{view} {update}' : '{view}',
                                'headerOptions' => ['style' => 'width: 2rem;'],
                            ],
                        ],
                    ]); ?>
                </div>
                <!--.card-body-->
            </div>
            <!--.card-->
        </div>
        <!--.col-md-12-->
    </div>
    <!--.row-->
</div>
