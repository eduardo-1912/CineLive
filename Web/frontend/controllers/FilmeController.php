<?php

namespace frontend\controllers;

use common\models\Cinema;
use Yii;
use common\models\Filme;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class FilmeController extends Controller
{
    public function actionIndex($q = null, $cinema_id = null, $estado = null)
    {
        // OBTER TODOS OS CINEMAS ATIVOS
        $cinemas = Cinema::find()->where(['estado' => Cinema::ESTADO_ATIVO])->orderBy('nome')->all();

        // SE ESTADO FOR BREVEMENTE --> NÃO PRECISA DE CINEMA
        if ($estado === 'brevemente') {

            // OBTER TODOS OS FILMES COM ESTADO 'BREVEMENTE'
            $query = Filme::find()->where(['estado' => Filme::ESTADO_BREVEMENTE])->orderBy(['titulo' => SORT_ASC]);

            // PESQUISA POR TÍTULO
            if ($q) {
                $query->andWhere(['like', 'titulo', $q]);
            }

            // OBTER LISTA DE FILMES (COM PESQUISA OU NÃO)
            $filmes = $query->all();

            return $this->render('index', [
                'filmes' => $filmes,
                'q' => $q,
                'cinema_id' => $cinema_id,
                'cinemas' => $cinemas,
                'estado' => $estado,
            ]);
        }

        // SE ESTADO FOR 'EM EXIBIÇÃO' OU 'KIDS' --> PRECISA DE CINEMA
        // SE NENHUM CINEMA ESTIVER SELECIONADO --> REDIRECIONAR PARA O PRIMEIRO DISPONÍVEL
        if (!$cinema_id && !empty($cinemas)) {
            return $this->redirect(['index', 'cinema_id' => $cinemas[0]->id]);
        }

        // OBTER TODOS OS FILMES COM SESSÕES FUTURAS (EM EXIBIÇÃO)
        $query = Filme::find()
            ->alias('f')
            ->joinWith(['sessaos s'])
            ->where(['s.cinema_id' => $cinema_id])
            ->andWhere(['>=', 's.data', date('Y-m-d')])
            ->distinct();

        // PESQUISA POR TÍTULO
        if ($q) {
            $query->andWhere(['like', 'f.titulo', $q]);
        }

        // SE ESTADO FOR 'KIDS' --> ADICIONA QUERY COM RATINGS PARA CRIANÇAS
        if ($estado === 'kids') {
            $query->andWhere(['f.rating' => [Filme::RATING_TODOS, Filme::RATING_M3, Filme::RATING_M6]]);
        }

        // ORDERNAR POR TÍTULO
        $filmes = $query->orderBy(['f.titulo' => SORT_ASC])->all();

        return $this->render('index', [
            'filmes' => $filmes,
            'q' => $q,
            'cinema_id' => $cinema_id,
            'cinemas' => $cinemas,
            'estado' => $estado,
        ]);
    }



    public function actionView($id, $cinema_id = null)
    {
        // OBTER FILME
        $model = $this->findModel($id);

        $dataSelecionada = Yii::$app->request->get('data');
        $horaSelecionada = Yii::$app->request->get('hora');

        // OBTER TODOS OS CINEMAS ATIVOS COM SESSÕES FUTURAS DESTE FILME
        $cinemasDisponiveis = $model->getCinemasComSessoesFuturas();

        // OBTER SESSÕES FUTURAS DO CINEMA SELECIONADO PARA O FILME
        $sessoes = [];
        if ($cinema_id) {

            // ENCONTRAR CINEMA
            $cinema = Cinema::findOne($cinema_id);

            // OBTER SESSÕES FUTURAS ATIVAS
            $sessoes = $cinema ? $cinema->getSessoesFuturas($model->id) : [];

            // 3️⃣ Validar se a hora selecionada ainda é válida
            if ($dataSelecionada && $horaSelecionada) {
                $sessaoExiste = array_filter($sessoes, fn($s) =>
                    $s->data === $dataSelecionada && substr($s->hora_inicio, 0, 5) === $horaSelecionada
                );

                if (empty($sessaoExiste)) {
                    // Redirecionar apenas com data e cinema válidos
                    return $this->redirect([
                        'view',
                        'id' => $id,
                        'cinema_id' => $cinema_id,
                        'data' => $dataSelecionada,
                    ]);
                }
            }
        }

        // 4️⃣ Agrupar sessões por data
        $datasAgrupadas = [];
        foreach ($sessoes as $sessao) {
            $datasAgrupadas[$sessao->data][] = $sessao;
        }

        // 5️⃣ Preparar listas para dropdowns
        $listaCinemas = \yii\helpers\ArrayHelper::map($cinemasDisponiveis, 'id', 'nome');

        $listaDatas = [];
        foreach ($datasAgrupadas as $data => $lista) {
            $listaDatas[$data] = $lista[0]->dataFormatada;
        }

        $listaHoras = [];
        if ($dataSelecionada && isset($datasAgrupadas[$dataSelecionada])) {
            foreach ($datasAgrupadas[$dataSelecionada] as $s) {
                $listaHoras[$s->horaInicioFormatada] = $s->horaInicioFormatada;
            }
        }

        // 6️⃣ Encontrar sessão selecionada (para o botão "Comprar Bilhetes")
        $sessaoSelecionada = null;
        if ($dataSelecionada && $horaSelecionada && isset($datasAgrupadas[$dataSelecionada])) {
            foreach ($datasAgrupadas[$dataSelecionada] as $s) {
                if (substr($s->hora_inicio, 0, 5) === $horaSelecionada) {
                    $sessaoSelecionada = $s;
                    break;
                }
            }
        }

        // 7️⃣ Renderizar a view
        return $this->render('view', [
            'model' => $model,
            'cinema_id' => $cinema_id,
            'listaCinemas' => $listaCinemas,
            'listaDatas' => $listaDatas,
            'listaHoras' => $listaHoras,
            'sessaoSelecionada' => $sessaoSelecionada,
            'dataSelecionada' => $dataSelecionada,
            'horaSelecionada' => $horaSelecionada,
        ]);
    }

    protected function findModel($id)
    {
        if (($model = Filme::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }


}
