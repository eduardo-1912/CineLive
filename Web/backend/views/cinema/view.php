<?php

use common\models\Cinema;
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\Cinema */

$currentUser = Yii::$app->user;
$isAdmin = $currentUser->can('admin');
$isGerente = $currentUser->identity->roleName == 'gerente';
$isOwnCinema = $currentUser->id == $model->gerente_id;
$return_path = $isAdmin ? 'index' : 'view?id=' . $currentUser->identity->profile->cinema_id;

$this->title = $model->nome;
$this->params['breadcrumbs'][] = ['label' => 'Cinemas', 'url' => [$return_path]];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>

<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <p>
                        <?php if ($isAdmin || $isGerente && $isOwnCinema): ?>
                            <?= Html::a('Editar', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>

                            <?php if ($isAdmin): ?>
                                <?php if ($model->estado === $model::ESTADO_ATIVO): ?>
                                    <?= Html::a('Encerrar', ['deactivate', 'id' => $model->id], [
                                        'class' => 'btn btn-danger',
                                        'data' => [
                                            'confirm' => 'Tem a certeza que quer encerrar este cinema?',
                                            'method' => 'post',
                                        ],
                                    ]) ?>
                                <?php elseif ($model->estado === $model::ESTADO_ENCERRADO): ?>
                                    <?= Html::a('Ativar', ['activate', 'id' => $model->id], [
                                        'class' => 'btn btn-success',
                                        'data' => [
                                            'confirm' => 'Tem a certeza que quer ativar este cinema?',
                                            'method' => 'post',
                                        ],
                                    ]) ?>
                                <?php endif; ?>
                            <?php endif; ?>
                        <?php endif; ?>

                    </p>

                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            'id',
                            'nome',
                            [
                                'attribute' => 'gerente_id',
                                'label' => 'Gerente',
                                'value' => function ($model) {
                                    return $model->gerente
                                        ? $model->gerente->profile->nome
                                        : '-';
                                },
                            ],
                            'email:email',
                            'telefone',
                            [
                                'label' => 'Morada',
                                'attribute' => 'morada',
                                'value' => function ($model) {
                                    return "{$model->rua}, {$model->codigo_postal} {$model->cidade}";
                                },
                            ],
                            [
                                'label' => 'Horário',
                                'value' => function ($model) {
                                    $abertura = Yii::$app->formatter->asTime($model->horario_abertura, 'HH:mm');
                                    $fecho = Yii::$app->formatter->asTime($model->horario_fecho, 'HH:mm');
                                    return "{$abertura} - {$fecho}";
                                },
                            ],
                            [
                                'attribute' => 'estado',
                                'label' => 'Estado',
                                'format' => 'raw',
                                'value' => function ($model) {
                                    switch ($model->estado) {
                                        case Cinema::ESTADO_ATIVO:
                                            return '<span>Ativo</span>';
                                        case Cinema::ESTADO_ENCERRADO:
                                            return '<span class="text-danger">Encerrado</span>';
                                        default:
                                            return '<span class="text-secondary">Desconhecido</span>';
                                    }
                                },
                                'visible' => Yii::$app->user->can('gerirCinemas'),
                            ],
                            [
                                'label' => 'Localização',
                                'format' => 'raw',
                                'value' => function ($model) {
                                    $nome = urlencode($model->nome);
                                    $latitude = $model->latitude;
                                    $longitude = $model->longitude;
                                    $url = "https://www.google.com/maps?q={$nome}@{$latitude},{$longitude}&hl=pt&z=15&output=embed";

                                    return "<div>
                                                <iframe src='{$url}' width='100%' height='400' class='rounded-3' allowfullscreen loading='lazy'></iframe>
                                            </div>";
                                },
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