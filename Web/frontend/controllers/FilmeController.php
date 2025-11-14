<?php

namespace frontend\controllers;

use common\models\Cinema;
use Yii;
use common\models\Filme;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Cookie;

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
                'cinema_id' => null,
                'cinemas' => $cinemas,
                'estado' => $estado,
                'currentCinema' => $currentCinema,
                'q' => $q,
            ]);
        }

        // SE UM CINEMA FOI PASSADO POR PARÂMETRO --> CRIAR COOKIE
        if ($cinema_id !== null) {
            Yii::$app->response->cookies->add(new Cookie([
                'name' => 'cinema_id',
                'value' => $cinema_id,
                'expire' => time() + 3600 * 24 * 180, // 180 DIAS
            ]));
        }

        // SE A QUERY NA TIVER CINEMA ID --> USAR O COOKIE
        if ($cinema_id === null) {
            $cinema_id = Yii::$app->request->cookies->getValue('cinema_id', null);
        }

        // SE MESMO ASSIM FOR NULL --> ESCOLHER O PRIMEIRO CINEMA ATIVO
        if ($cinema_id === null) {
            $cinema_id = $cinemas[0]->id;
        }

        // CINEMA ATUAL
        $currentCinema = Cinema::findOne($cinema_id)->nome ?? null;


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
            $query->andWhere(['f.rating' => Filme::ratingsKids()]);
        }

        // ORDERNAR POR TÍTULO
        $filmes = $query->orderBy(['f.titulo' => SORT_ASC])->all();

        // APENAS MOSTRAR FILMES QUE TENHAM SESSÕES ATIVAS (NÃO ESTEJAM A DECORRER OU ESGOTADAS)
        $filmes = array_filter($filmes, fn($filme) => $filme->hasSessoesAtivas());

        // SE PESQUISA NÃO ENCONTRAR NADA --> VER SE EXISTEM FILMES BREVEMENTE COM ESSE TÍTULO
        if ($q && empty($filmes)) {
            $existeBrevemente = Filme::find()
                ->where(['estado' => Filme::ESTADO_BREVEMENTE])
                ->andWhere(['like', 'titulo', $q])
                ->exists();

            if ($existeBrevemente) {
                // REDIRECIONAR AUTOMATICAMENTE PARA O ESTADO 'BREVEMENTE'
                return $this->redirect([
                    'index',
                    'estado' => 'brevemente',
                    'q' => $q
                ]);
            }
        }

        return $this->render('index', [
            'filmes' => $filmes,
            'q' => $q,
            'cinema_id' => $cinema_id,
            'cinemas' => $cinemas,
            'estado' => $estado,
            'currentCinema' => $currentCinema,
        ]);
    }


    public function actionView($id, $cinema_id = null)
    {
        // OBTER FILME
        $model = $this->findModel($id);

        // SE O FILME NÃO TEM SESSÕES ATIVAS --> NÃO DEIXAR VER
        if (!$model->hasSessoesAtivas() && !$model->isEstadoBrevemente()) {
            throw new NotFoundHttpException('Este filme já não está disponível para consulta.');
        }

        // OBTER PARÂMETROS DO URL
        $dataSelecionada = Yii::$app->request->get('data');
        $horaSelecionada = Yii::$app->request->get('hora');

        // SE UM CINEMA FOI PASSADO POR PARÂMETRO
        $sessoes = [];
        if ($cinema_id) {

            // ENCONTRAR CINEMA
            $cinema = Cinema::findOne($cinema_id);

            // OBTER SESSÕES FUTURAS (EXCLUIR SESSÕES ESGOTADAS E A DECORRAR)
            if ($cinema) {
                $sessoes = array_filter($cinema->getSessoesFuturas($model->id), fn($sessao) => $sessao->isEstadoAtiva());
            }

            // VALIDAR SE TEM DATA E HORA SELECIONADA
            if ($dataSelecionada && $horaSelecionada) {

                // VER SE EXISTE ALGUMA SESSÃO COM CONJUNTO DATA + HORA IGUAL À SELECIONADA
                $sessaoExiste = array_filter($sessoes, fn($sessao) =>
                    $sessao->dataFormatada === $dataSelecionada && $sessao->horaInicioFormatada === $horaSelecionada
                );

                // SE NÃO TEM NENHUMA CORRESPONDÊNCIA --> REDIRECIONAR COM APENAS CINEMA E DATA
                if (empty($sessaoExiste)) {
                    return $this->redirect([
                        'view',
                        'id' => $id,
                        'cinema_id' => $cinema_id,
                        'data' => $dataSelecionada,
                    ]);
                }
            }
        }

        // AGRUPAR SESSÕES POR DATA
        $sessoesPorData = [];
        foreach ($sessoes as $sessao) {
            $sessoesPorData[$sessao->dataFormatada][] = $sessao;
        }

        // OBTER TODOS OS CINEMAS ATIVOS COM SESSÕES FUTURAS DESTE FILME
        $listaCinemas = ArrayHelper::map($model->getCinemasComSessoesFuturas(), 'id', 'nome');

        // LISTA DE DATAS (CHAVE => CHAVE)
        $listaDatas = array_combine(array_keys($sessoesPorData), array_keys($sessoesPorData));

        // LISTA DE HORAS (CHAVE => CHAVE)
        $listaHoras = [];
        if ($dataSelecionada && !empty($sessoesPorData[$dataSelecionada])) {
            $listaHoras = ArrayHelper::map($sessoesPorData[$dataSelecionada], 'horaInicioFormatada', 'horaInicioFormatada');
        }

        // ENCONTRAR A SESSÃO SELECIONADA
        $sessaoSelecionada = null;
        if ($dataSelecionada && $horaSelecionada && isset($sessoesPorData[$dataSelecionada])) {

            // PARA CADA SESSÃO DO ARRAY DE SESSÕES POR DATA
            foreach ($sessoesPorData[$dataSelecionada] as $sessao) {

                // SE A HORA INÍCIO É IGUAL À HORA SELECIONADA --> SELECIONAR ESSA SESSÃO
                if ($sessao->horaInicioFormatada === $horaSelecionada) {
                    $sessaoSelecionada = $sessao;
                    break;
                }
            }
        }

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
