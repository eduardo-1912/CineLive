<?php

namespace backend\modules\api\controllers;

use common\models\Compra;
use Yii;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use yii\rest\Controller;
use yii\web\NotFoundHttpException;

class CompraController extends Controller
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['authenticator'] = [
            'class' => CompositeAuth::class,
            'authMethods' => [
                HttpBearerAuth::class,
                QueryParamAuth::class,
            ],
        ];

        return $behaviors;
    }

    public function extraFields()
    {
        return [
            'bilhetes',
            'sessao',
            'filme' => function () {
                return $this->sessao ? $this->sessao->filme : null;
            },
            'cinema' => function () {
                return $this->sessao ? $this->sessao->cinema : null;
            },
        ];
    }


    public function actionIndex()
    {
        $user = Yii::$app->user->identity;


        $compras = Compra::find()
            ->where(['cliente_id' => $user->id])
            ->with(['bilhetes', 'sessao.filme', 'sessao.cinema'])
            ->orderBy(['id' => SORT_DESC])
            ->all();

        return array_map(fn($c) =>
        $c->toArray([], ['bilhetes', 'sessao', 'filme', 'cinema']),
            $compras
        );
    }

    public function actionView($id)
    {
        $userId = Yii::$app->user->id;

        $compra = Compra::find()
            ->where(['id' => $id, 'cliente_id' => $userId])
            ->with(['bilhetes', 'sessao.filme', 'sessao.cinema'])
            ->one();

        if (!$compra) {
            throw new NotFoundHttpException("Compra não encontrada.");
        }

        Yii::$app->response->format = 'json';

        // Expand automatico
        return $compra->toArray(
            [],
            ['bilhetes', 'sessao', 'filme', 'cinema']
        );

    }

}