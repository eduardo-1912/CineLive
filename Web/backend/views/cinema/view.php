<?php

use common\models\Cinema;
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\Cinema */

$currentUser = Yii::$app->user;
$gerirCinemas = $currentUser->can('gerirCinemas');
$isGerente = $currentUser->identity->roleName == 'gerente';
$isOwnCinema = $currentUser->id == $model->gerente_id;

$this->title = $model->nome;
$this->params['breadcrumbs'][] = ['label' => 'Cinemas', 'url' => [$gerirCinemas ? 'index' : ('view?id=' . $currentUser->identity->profile->cinema_id)]];
$this->params['breadcrumbs'][] = $model->nome;
\yii\web\YiiAsset::register($this);
?>

<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <p>
                        <?= Html::a('Salas', ['sala/index', 'cinema_id' => $model->id], ['class' => 'btn btn-primary']) ?>
                        <?= Html::a('Sessões', ['sessao/index', 'cinema_id' => $model->id], ['class' => 'btn btn-secondary']) ?>

                        <?php if ($gerirCinemas || $isGerente && $isOwnCinema): ?>
                            <?= Html::a('Editar', ['update', 'id' => $model->id], ['class' => 'btn btn-warning']) ?>

                            <?php if ($gerirCinemas): ?>
                                <?php if ($model->estado === $model::ESTADO_ATIVO && $model->isClosable()): ?>
                                    <?= Html::a('Encerrar', ['change-status', 'id' => $model->id, 'estado' => $model::ESTADO_ENCERRADO], [
                                        'class' => 'btn btn-danger',
                                        'data' => [
                                            'confirm' => 'Tem a certeza que quer encerrar este cinema?',
                                            'method' => 'post',
                                        ],
                                    ]) ?>
                                <?php elseif ($model->estado === $model::ESTADO_ENCERRADO): ?>
                                    <?= Html::a('Ativar', ['change-status', 'id' => $model->id, 'estado' => $model::ESTADO_ATIVO], [
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
                                    return $model->gerente->profile->nome;
                                },
                                'visible' => !$gerirCinemas && !$isGerente,
                            ],
                            [
                                'attribute' => 'gerente_id',
                                'format' => 'raw',
                                'value' => function ($model) {
                                    return Html::a(
                                        Html::encode($model->gerente->profile->nome),
                                        ['user/view', 'id' => $model->gerente->id],
                                        ['class' => 'text-decoration-none text-primary']
                                    );
                                },
                                'visible' => $gerirCinemas || $isGerente,
                            ],
                            'email:email',
                            'telefone',
                            [
                                'attribute' => 'morada',
                                'value' => function ($model) {
                                    return "{$model->rua}, {$model->codigo_postal} {$model->cidade}";
                                },
                            ],
                            'horario',
                            [
                                'attribute' => 'estado',
                                'format' => 'raw',
                                'value' => fn($model) => $model->estadoFormatado,
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

                                    return "
                                    <div>
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