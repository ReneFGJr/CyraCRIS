<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateInstituicaoEIndividuoInstituicao extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'codigo_externo' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => true,
            ],
            'nome' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
                'null'       => false,
            ],
            'sigla' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
            ],
            'cnpj' => [
                'type'       => 'VARCHAR',
                'constraint' => 18,
                'null'       => true,
            ],
            'telefone' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 190,
                'null'       => true,
            ],
            'website' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('codigo_externo');
        $this->forge->addKey('nome');
        $this->forge->createTable('instituicao');

        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'individuo_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => false,
            ],
            'instituicao_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => false,
            ],
            'tipo_vinculo' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'default'    => 'VINCULADO',
                'null'       => false,
            ],
            'principal' => [
                'type'    => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'null'    => false,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('individuo_id');
        $this->forge->addKey('instituicao_id');
        $this->forge->addUniqueKey(['individuo_id', 'instituicao_id']);
        $this->forge->createTable('individuo_instituicao');
    }

    public function down(): void
    {
        $this->forge->dropTable('individuo_instituicao', true);
        $this->forge->dropTable('instituicao', true);
    }
}
