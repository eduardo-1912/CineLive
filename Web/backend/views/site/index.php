<?php

/** @var yii\web\View $this */

use common\models\UserExtension;
use yii\helpers\Url;

$this->title = 'Dashboard';
?>
<div class="site-index container-fluid">

    <?php

    $userId = Yii::$app->user->id;
    $user = UserExtension::findOne($userId);

    echo $user->roleName;

    ?>

    <div class="row row-cols-1 row-cols-sm-3 g-3 text-start">
        <div class="col">
            <div class="small-box bg-info p-0 mb-0">
                <div class="inner d-flex flex-column align-items-start">
                    <h3>150</h3>
                    <p>Bilhetes vendidos este mês</p>
                </div>
                <div class="icon">
                    <i class="fas fa-ticket-alt"></i>
                </div>
                <a href="<?= Url::to(['/compra/index']) ?>" class="small-box-footer">
                    Ver Bilhetes <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col">
            <div class="small-box bg-danger p-0 mb-0">
                <div class="inner d-flex flex-column align-items-start">
                    <h3>150</h3>
                    <p>Filmes em Exibição</p>
                </div>
                <div class="icon">
                    <i class="fas fa-film"></i>
                </div>
                <a href="<?= Url::to(['/filme/index']) ?>" class="small-box-footer">
                    Ver Filmes <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col">
            <div class="small-box bg-warning p-0 mb-0">
                <div class="inner d-flex flex-column align-items-start">
                    <h3>150</h3>
                    <p>Sessões agendadas hoje</p>
                </div>
                <div class="icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <a href="<?= Url::to(['/sessao/index']) ?>" class="small-box-footer">
                    Ver Sessões <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>
</div>

