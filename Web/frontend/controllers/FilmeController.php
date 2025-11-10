<?php

namespace frontend\controllers;

use common\models\Cinema;
use Yii;
use common\models\Filme;
use yii\helpers\ArrayHelper;
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
                'cinema_id' => $cinema_id,
                'cinemas' => $cinemas,
                'estado' => $estado,
                'q' => $q,
            ]);
        }

        // SE ESTADO FOR 'EM EXIBIÇÃO' OU 'KIDS' --> PRECISA DE CINEMA
        // SE NENHUM CINEMA ESTIVER SELECIONADO --> REDIRECIONAR PARA O PRIMEIRO DISPONÍVEL
        if (!$cinema_id && !empty($cinemas)) {
            if (!$cinema_id && !empty($cinemas)) {
                return $this->redirect([
                    'index',
                    'cinema_id' => $cinemas[0]->id,
                    'estado' => $estado,
                    'q' => $q
                ]);
            }
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
            $query->andWhere(['f.rating' => Filme::ratingsKids()]);
        }

        // ORDERNAR POR TÍTULO
        $filmes = $query->orderBy(['f.titulo' => SORT_ASC])->all();

        // APENAS MOSTRAR FILMES QUE TENHAM SESSÕES ATIVAS (NÃO ESTEJAM A DECORRER OU ESGOTADAS)
        $filmes = array_filter($filmes, fn($filme) => $filme->hasSessoesAtivas());

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

        // SE O FILME NÃO TEM SESSÕES ATIVAS --> NÃO DEIXAR VER
        if (!$model->hasSessoesAtivas()) {
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

            // OBTER SESSÕES FUTURAS
            if ($cinema) {
                $sessoes = $cinema->getSessoesFuturas($model->id);
            }

            // FILTRAR APENAS POR SESSÕES ATIVAS
            // NÃO FAZEMOS NA QUERY PARA EXCLUIR ESGOTADAS E A DECORRER (ESTADOS DINÂMICOS)
            $sessoes = array_filter($sessoes, fn($sessao) => $sessao->isEstadoAtiva());

            // VALIDAR SE A HORA SELECIONADA É VÁLIDA
            if ($dataSelecionada && $horaSelecionada) {

                // VER SE EXISTE ALGUMA SESSÃO COM CONJUNTO DATA + HORA IGUAL À SELECIONADA
                $sessaoExiste = array_filter($sessoes, fn($sessao) =>
                    $sessao->data === $dataSelecionada && $sessao->horaInicioFormatada === $horaSelecionada
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
        // ISTO CRIA UM ARRAY DE SESSÕES POR DATA (EX.: 2025-11-03 => [sessao_1, sessao_2])
        $datasSessoes = [];
        foreach ($sessoes as $sessao) {
            $datasSessoes[$sessao->data][] = $sessao;
        }

        // OBTER TODOS OS CINEMAS ATIVOS COM SESSÕES FUTURAS DESTE FILME
        $listaCinemas = ArrayHelper::map($model->getCinemasComSessoesFuturas(), 'id', 'nome');

        // LISTA DE DATAS
        // ISTO CRIA UM ARRAY DE DATAS FORMATADAS (EX.: [2025-11-03 => 11/03/2025, 2025-11-04 => 11/04/2025])
        $listaDatas = [];
        foreach ($datasSessoes as $data => $listaSessoes) {
            // [0] PORQUE TODAS AS SESSÕES DENTRO DA LISTA DE DATAS TÊM A MESMA DATA
            $listaDatas[$data] = $listaSessoes[0]->dataFormatada;
        }

        // LISTA DE HORAS PARA A DATA SELECIONADA
        $listaHoras = [];
        if ($dataSelecionada && isset($datasSessoes[$dataSelecionada])) {
            foreach ($datasSessoes[$dataSelecionada] as $sessao) {
                // ADICIONAR HORAS NA LISTA DE HORAS, CHAVE E VALOR SÃO IGUAIS
                $listaHoras[$sessao->horaInicioFormatada] = $sessao->horaInicioFormatada;
            }
        }

        // ENCONTRAR UMA SESSÃO SELECIONADA PARA BOTÃO COMPRAR BILHETES
        $sessaoSelecionada = null;

        // SE DATA E HORA SELECIONADA E DATA SELECIONADA EXITE NO ARRAY DE DATAS
        if ($dataSelecionada && $horaSelecionada && isset($datasSessoes[$dataSelecionada])) {
            // PARA CADA SESSÃO DO ARRAY DE DATA AGRUPADAS
            foreach ($datasSessoes[$dataSelecionada] as $sessao) {
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
