<footer class="main-footer">
    <span class="fw-normal"><?= Yii::$app->user->identity->profile->cinema->nome ?? 'CineLive' ?> <?= ' (' . Yii::$app->user->identity->roleName . ')' ?></span>
    <div class="float-right d-none d-sm-inline-block">
        <a href="mailto:<?= Yii::$app->params['adminEmail'] ?>">
            <i class="fas fa-exclamation-triangle fa-sm"></i>
            Reportar Problema
        </a>
    </div>
</footer>