<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\UserSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Users';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col-md-12">
                            <?= Html::a('Create User', ['create'], ['class' => 'btn btn-success']) ?>
                        </div>
                    </div>


                    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

                    <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        'filterModel' => $searchModel,
                        'columns' => [
                            ['class' => 'yii\grid\SerialColumn'],

                            'id',
                            'username',
                            'email:email',
                            [
                                'label' => 'Função',
                                'value' => function($model) {
                                    $roles = Yii::$app->authManager->getRolesByUser($model->id);
                                    return implode(', ', array_keys($roles));
                                },
                            ],
                            [
                                'label' => 'Nome',
                                'value' => function($model) {
                                    return $model->profile->nome ?? '(sem perfil)';
                                },
                            ],
                            [
                                'label' => 'Telemóvel',
                                'value' => function($model) {
                                    return $model->profile->telemovel ?? '-';
                                },
                            ],
                            [
                                'label' => 'Cinema',
                                'value' => function($model) {
                                    return $model->profile->cinema->nome ?? '-';
                                },
                            ],
                            ['class' => 'hail812\adminlte3\yii\grid\ActionColumn'],
                        ],
                        'summaryOptions' => ['class' => 'summary mb-2'],
                        'pager' => [
                            'class' => 'yii\bootstrap4\LinkPager',
                        ]
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
