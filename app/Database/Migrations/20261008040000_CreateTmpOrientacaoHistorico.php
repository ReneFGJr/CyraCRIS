<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTmpOrientacaoHistorico extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'orientacao_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'tipo' => ['type' => 'ENUM', 'constraint' => ['Movimentação', 'Solicitação']],
            'evento' => ['type' => 'VARCHAR', 'constraint' => 100],
            'descricao' => ['type' => 'TEXT', 'null' => true],
            'situacao_anterior' => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
            'situacao_nova' => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
            'dados_anteriores' => ['type' => 'LONGTEXT', 'null' => true, 'comment' => 'Dados anteriores em JSON'],
            'dados_novos' => ['type' => 'LONGTEXT', 'null' => true, 'comment' => 'Dados novos em JSON'],
            'usuario' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at' => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['orientacao_id', 'created_at']);
        $this->forge->addKey('tipo');
        $this->forge->addForeignKey('orientacao_id', 'tmp_orientacoes', 'id', 'CASCADE', 'RESTRICT', 'fk_tmp_historico_orientacao');
        $this->forge->createTable('tmp_orientacao_historico');
    }

    public function down(): void
    {
        $this->forge->dropTable('tmp_orientacao_historico');
    }
}
