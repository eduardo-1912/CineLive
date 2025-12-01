<?php

/** @var yii\web\View $this */
/** @var common\models\Filme $model */
/** @var array $generoOptions */

$this->title = 'Criar Filme';
$this->params['breadcrumbs'][] = ['label' => 'Filmes', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="filme-create">
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <?= $this->render('_form', [
                            'model' => $model,
                            'generoOptions' => $generoOptions,
                        ]) ?>
                    </div>
                </div>
            </div>
            <!--.card-body-->
        </div>
        <!--.card-->
    </div>

</div>
