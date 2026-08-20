<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProjetos extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'pesquisador_id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true],
            'titulo' => ['type' => 'VARCHAR', 'constraint' => 500],
            'descricao' => ['type' => 'TEXT', 'null' => true],
            'situacao' => ['type' => 'VARCHAR', 'constraint' => 30],
            'natureza' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'ano_inicio' => ['type' => 'SMALLINT', 'constraint' => 4, 'unsigned' => true, 'null' => true],
            'ano_fim' => ['type' => 'SMALLINT', 'constraint' => 4, 'unsigned' => true, 'null' => true],
            'integrantes' => ['type' => 'TEXT', 'null' => true],
            'dados_json' => ['type' => 'MEDIUMTEXT', 'null' => true],
            'chave_hash' => ['type' => 'CHAR', 'constraint' => 64],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['pesquisador_id', 'situacao']);
        $this->forge->addUniqueKey(['pesquisador_id', 'chave_hash']);
        $this->forge->createTable('projetos');
        $this->db->query('ALTER TABLE `projetos` ADD CONSTRAINT `fk_projetos_pesquisador` FOREIGN KEY (`pesquisador_id`) REFERENCES `individuo` (`id`) ON UPDATE CASCADE ON DELETE CASCADE');
    }

    public function down(): void
    {
        $this->forge->dropTable('projetos', true);
    }
}
