<?php

use common\models\Cinema;
use frontend\helpers\CookieHelper;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var common\models\Filme $filme */

$cinema_id = $cinema_id ?? CookieHelper::get('cinema_id', array_key_first(Cinema::findAtivos()))

?>

<a href="<?= Url::to(['filme/view', 'id' => $filme->id,
    'cinema_id' => $cinema_id]) ?>"
   class="card-filme text-center text-decoration-none text-black d-flex flex-column gap-1">
    <?= Html::img($filme->posterUrl, [
        'class' => 'card-img-top shadow-sm rounded-4',
        'alt' => $filme->titulo,
        'style' => 'object-fit: cover; aspect-ratio: 2/3;'
    ]) ?>
    <h5 class="fw-semibold fs-6"><?= $filme->titulo ?></h5>
</a>
