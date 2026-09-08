<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTmpOrientacoes extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'nome' => ['type' => 'VARCHAR', 'constraint' => 255],
            'id_lattes' => ['type' => 'VARCHAR', 'constraint' => 16, 'default' => ''],
            'ano' => ['type' => 'SMALLINT', 'unsigned' => true],
            'cracha' => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => ''],
            'orientador' => ['type' => 'VARCHAR', 'constraint' => 255, 'default' => ''],
            'hash_registro' => ['type' => 'CHAR', 'constraint' => 64],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('hash_registro', 'uq_tmp_orientacoes_hash');
        $this->forge->addKey('id_lattes');
        $this->forge->addKey('cracha');
        $this->forge->createTable('tmp_orientacoes', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('tmp_orientacoes', true);
    }
}
