<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSourceAndLinkProducoes extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'tipo' => ['type' => 'VARCHAR', 'constraint' => 20],
            'nome' => ['type' => 'VARCHAR', 'constraint' => 500],
            'issn' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'isbn' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'editora' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'cidade' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'pais' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'dados_json' => ['type' => 'TEXT', 'null' => true],
            'chave_hash' => ['type' => 'CHAR', 'constraint' => 64],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('issn');
        $this->forge->addKey('isbn');
        $this->forge->addUniqueKey('chave_hash');
        $this->forge->createTable('source');

        $this->forge->addColumn('producoes', [
            'source_id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'null' => true, 'after' => 'pesquisador_id'],
        ]);
        $this->db->query('ALTER TABLE `producoes` ADD INDEX `idx_producoes_source_id` (`source_id`), ADD CONSTRAINT `fk_producoes_source` FOREIGN KEY (`source_id`) REFERENCES `source` (`id`) ON UPDATE CASCADE ON DELETE SET NULL');
    }

    public function down(): void
    {
        $this->db->query('ALTER TABLE `producoes` DROP FOREIGN KEY `fk_producoes_source`, DROP INDEX `idx_producoes_source_id`');
        $this->forge->dropColumn('producoes', 'source_id');
        $this->forge->dropTable('source', true);
    }
}
