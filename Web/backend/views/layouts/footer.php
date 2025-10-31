<?php

use yii\helpers\Html;

?>
<footer class="main-footer">
    <span class="fw-normal"><?= Yii::$app->user->identity->profile->cinema->nome ?? Yii::$app->name ?> <?= ' (' . Yii::$app->user->identity->roleFormatted . ')' ?></span>
    <div class="float-right d-none d-sm-inline-block">
        <a href="mailto:admin@cinelive.pt">
            <i class="fas fa-exclamation-triangle fa-sm"></i>
            Reportar Problema
        </a>
    </div>
</footer>