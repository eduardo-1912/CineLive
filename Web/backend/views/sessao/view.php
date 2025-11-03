<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\Sessao */

$this->title =  $model->data . $model->hora_inicio . $model->filme->titulo;
$this->params['breadcrumbs'][] = ['label' => 'Sessões', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>

<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <p>
                        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
                            'class' => 'btn btn-danger',
                            'data' => [
                                'confirm' => 'Are you sure you want to delete this item?',
                                'method' => 'post',
                            ],
                        ]) ?>
                    </p>
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            'id',
                            'data',
                            [
                                'label' => 'Hora',
                                'value' => function ($model) {
                                    $inicio = Yii::$app->formatter->asTime($model->hora_inicio, 'HH:mm');
                                    $fim = Yii::$app->formatter->asTime($model->hora_fim, 'HH:mm');
                                    return "{$inicio} - {$fim}";
                                },
                            ],
                            [
                                'label' => 'Filme',
                                'attribute' => 'filme.titulo',
                            ],
                            [
                                'label' => 'Sala',
                                'attribute' => 'sala.numero',
                            ],
                            [
                                'label' => 'Cinema',
                                'attribute' => 'cinema.nome',
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
</div>