<?php

use yii\db\Migration;

class m251022_130422_init_cinelive extends Migration
{
    public function safeUp()
    {
        // ====== TABELA CINEMA ======
        $this->createTable('cinema', [
            'id' => $this->primaryKey(),
            'nome' => $this->string(80)->notNull(),
            'rua' => $this->string(100)->notNull(),
            'codigo_postal' => $this->string(8)->notNull(),
            'cidade' => $this->string(50)->notNull(),
            'latitude' => $this->decimal(10, 6)->notNull(),
            'longitude' => $this->decimal(10, 6)->notNull(),
            'email' => $this->string(255)->notNull(),
            'telefone' => $this->integer()->notNull(),
            'horario_abertura' => $this->time()->notNull(),
            'horario_fecho' => $this->time()->notNull(),
            'estado' => "ENUM('ativo','encerrado') NOT NULL",
            'gerente_id' => $this->integer(),
        ]);

        $this->createIndex('idx-cinema-gerente_id', 'cinema', 'gerente_id', true);
        $this->addForeignKey('fk-cinema-gerente_id', 'cinema', 'gerente_id', 'user', 'id', 'RESTRICT', 'RESTRICT');

        // ====== TABELA USER_PROFILE ======
        $this->createTable('user_profile', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'cinema_id' => $this->integer(),
            'nome' => $this->string(100)->notNull(),
            'telemovel' => $this->string(9)->notNull(),
        ]);

        $this->createIndex('idx-user_profile-user_id', 'user_profile', 'user_id', true);
        $this->createIndex('idx-user_profile-cinema_id', 'user_profile', 'cinema_id');
        $this->addForeignKey('fk-user_profile-user_id', 'user_profile', 'user_id', 'user', 'id', 'CASCADE', 'RESTRICT');
        $this->addForeignKey('fk-user_profile-cinema_id', 'user_profile', 'cinema_id', 'cinema', 'id', 'RESTRICT', 'RESTRICT');

        // ====== TABELA GENERO ======
        $this->createTable('genero', [
            'id' => $this->primaryKey(),
            'nome' => $this->string(80)->notNull(),
        ]);

        // ====== TABELA FILME ======
        $this->createTable('filme', [
            'id' => $this->primaryKey(),
            'titulo' => $this->string(255)->notNull(),
            'sinopse' => $this->text()->notNull(),
            'duracao' => $this->integer()->notNull(),
            'rating' => "ENUM('Todos','M3','M6','M12','M14','M16','M18') NOT NULL",
            'estreia' => $this->date()->notNull(),
            'idioma' => $this->string(50)->notNull(),
            'realizacao' => $this->string(80)->notNull(),
            'trailer_url' => $this->string(255)->notNull(),
            'poster_path' => $this->string(255)->notNull(),
            'estado' => "ENUM('brevemente','em_exibicao','terminado') NOT NULL",
        ]);

        // ====== TABELA FILME_GENERO ======
        $this->createTable('filme_genero', [
            'filme_id' => $this->integer()->notNull(),
            'genero_id' => $this->integer()->notNull(),
        ]);

        $this->createIndex('idx-filme_genero-filme_id', 'filme_genero', 'filme_id');
        $this->createIndex('idx-filme_genero-genero_id', 'filme_genero', 'genero_id');
        $this->addForeignKey('fk-filme_genero-filme_id', 'filme_genero', 'filme_id', 'filme', 'id', 'CASCADE', 'RESTRICT');
        $this->addForeignKey('fk-filme_genero-genero_id', 'filme_genero', 'genero_id', 'genero', 'id', 'CASCADE', 'RESTRICT');

        // ====== TABELA SALA ======
        $this->createTable('sala', [
            'id' => $this->primaryKey(),
            'cinema_id' => $this->integer()->notNull(),
            'numero' => $this->integer()->notNull(),
            'num_filas' => $this->integer()->notNull(),
            'num_colunas' => $this->integer()->notNull(),
            'preco_bilhete' => $this->decimal(5, 2)->notNull(),
            'estado' => "ENUM('ativa','encerrada') NOT NULL",
        ]);

        $this->createIndex('idx-sala-cinema_id', 'sala', 'cinema_id');
        $this->addForeignKey('fk-sala-cinema_id', 'sala', 'cinema_id', 'cinema', 'id', 'RESTRICT', 'RESTRICT');

        // ====== TABELA SESSAO ======
        $this->createTable('sessao', [
            'id' => $this->primaryKey(),
            'data' => $this->date()->notNull(),
            'hora_inicio' => $this->time()->notNull(),
            'hora_fim' => $this->time()->notNull(),
            'filme_id' => $this->integer()->notNull(),
            'sala_id' => $this->integer()->notNull(),
            'cinema_id' => $this->integer()->notNull(),
        ]);

        $this->createIndex('idx-sessao-filme_id', 'sessao', 'filme_id');
        $this->createIndex('idx-sessao-sala_id', 'sessao', 'sala_id');
        $this->createIndex('idx-sessao-cinema_id', 'sessao', 'cinema_id');
        $this->addForeignKey('fk-sessao-filme_id', 'sessao', 'filme_id', 'filme', 'id', 'CASCADE', 'RESTRICT');
        $this->addForeignKey('fk-sessao-sala_id', 'sessao', 'sala_id', 'sala', 'id', 'RESTRICT', 'RESTRICT');
        $this->addForeignKey('fk-sessao-cinema_id', 'sessao', 'cinema_id', 'cinema', 'id', 'RESTRICT', 'RESTRICT');

        // ====== TABELA COMPRA ======
        $this->createTable('compra', [
            'id' => $this->primaryKey(),
            'cliente_id' => $this->integer()->notNull(),
            'data' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'pagamento' => "ENUM('mbway','cartao','multibanco') NOT NULL",
            'estado' => "ENUM('pendente','confirmada','cancelada') NOT NULL",
        ]);

        $this->createIndex('idx-compra-cliente_id', 'compra', 'cliente_id');
        $this->addForeignKey('fk-compra-cliente_id', 'compra', 'cliente_id', 'user', 'id', 'CASCADE', 'RESTRICT');

        // ====== TABELA BILHETE ======
        $this->createTable('bilhete', [
            'id' => $this->primaryKey(),
            'compra_id' => $this->integer()->notNull(),
            'sessao_id' => $this->integer()->notNull(),
            'lugar' => $this->string(3)->notNull(),
            'preco' => $this->decimal(5,2)->notNull(),
            'codigo' => $this->string(45)->notNull()->unique(),
            'estado' => "ENUM('pendente','confirmado','cancelado') NOT NULL",
        ]);

        $this->createIndex('idx-bilhete-compra_id', 'bilhete', 'compra_id');
        $this->createIndex('idx-bilhete-sessao_id', 'bilhete', 'sessao_id');
        $this->addForeignKey('fk-bilhete-compra_id', 'bilhete', 'compra_id', 'compra', 'id', 'CASCADE', 'RESTRICT');
        $this->addForeignKey('fk-bilhete-sessao_id', 'bilhete', 'sessao_id', 'sessao', 'id', 'RESTRICT', 'RESTRICT');

        // ====== TABELA ALUGUER_SALA ======
        $this->createTable('aluguer_sala', [
            'id' => $this->primaryKey(),
            'cliente_id' => $this->integer()->notNull(),
            'sala_id' => $this->integer()->notNull(),
            'data' => $this->date()->notNull(),
            'hora_inicio' => $this->time()->notNull(),
            'hora_fim' => $this->time()->notNull(),
            'estado' => "ENUM('pendente','confirmado','cancelado') NOT NULL",
            'tipo_evento' => $this->string(100)->notNull(),
            'observacoes' => $this->text()->notNull(),
        ]);

        $this->createIndex('idx-aluguer_sala-cliente_id', 'aluguer_sala', 'cliente_id');
        $this->createIndex('idx-aluguer_sala-sala_id', 'aluguer_sala', 'sala_id');
        $this->addForeignKey('fk-aluguer_sala-cliente_id', 'aluguer_sala', 'cliente_id', 'user', 'id', 'CASCADE', 'RESTRICT');
        $this->addForeignKey('fk-aluguer_sala-sala_id', 'aluguer_sala', 'sala_id', 'sala', 'id', 'RESTRICT', 'RESTRICT');
    }

    public function safeDown()
    {
        $this->dropTable('aluguer_sala');
        $this->dropTable('bilhete');
        $this->dropTable('compra');
        $this->dropTable('sessao');
        $this->dropTable('sala');
        $this->dropTable('filme_genero');
        $this->dropTable('filme');
        $this->dropTable('genero');
        $this->dropTable('user_profile');
        $this->dropTable('cinema');
    }
}
