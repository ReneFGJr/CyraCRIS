<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProducoes extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'pesquisador_id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true],
            'categoria' => ['type' => 'VARCHAR', 'constraint' => 20],
            'grupo' => ['type' => 'VARCHAR', 'constraint' => 20],
            'tipo' => ['type' => 'VARCHAR', 'constraint' => 100],
            'titulo' => ['type' => 'TEXT'],
            'ano' => ['type' => 'SMALLINT', 'constraint' => 4, 'unsigned' => true, 'null' => true],
            'doi' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'autores' => ['type' => 'TEXT', 'null' => true],
            'dados_json' => ['type' => 'MEDIUMTEXT', 'null' => true],
            'chave_hash' => ['type' => 'CHAR', 'constraint' => 64],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['pesquisador_id', 'categoria']);
        $this->forge->addKey(['pesquisador_id', 'grupo']);
        $this->forge->addUniqueKey(['pesquisador_id', 'chave_hash']);
        $this->forge->createTable('producoes');
        $this->db->query('ALTER TABLE `producoes` ADD CONSTRAINT `fk_producoes_pesquisador` FOREIGN KEY (`pesquisador_id`) REFERENCES `individuo` (`id`) ON UPDATE CASCADE ON DELETE CASCADE');
    }

    public function down(): void
    {
        $this->forge->dropTable('producoes', true);
    }
}
