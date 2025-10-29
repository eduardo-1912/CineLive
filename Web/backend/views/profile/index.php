<?php
use yii\helpers\Html;

/** @var \common\models\User $user */
/** @var \common\models\UserProfile $profile */

$this->title = 'Perfil';
?>

<div class="perfil-index card card-primary">
    <div class="card-header">
        <h3 class="card-title"><?= Html::encode($this->title) ?></h3>
    </div>
    <div class="card-body">
        <p><strong>Username:</strong> <?= Html::encode($user->username) ?></p>
        <p><strong>Nome:</strong> <?= Html::encode($profile->nome ?? '-') ?></p>
        <p><strong>Email:</strong> <?= Html::encode($user->email) ?></p>
        <p><strong>Telemóvel:</strong> <?= Html::encode($profile->telemovel ?? '-') ?></p>
        <p><strong>Função:</strong>
            <?= implode(', ', array_keys(Yii::$app->authManager->getRolesByUser($user->id))) ?>
        </p>
    </div>
</div>
