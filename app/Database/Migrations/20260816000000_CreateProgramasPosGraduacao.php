<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProgramasPosGraduacao extends Migration
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
            'sucupira_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => true,
            ],
            'codigo_capes' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => false,
            ],
            'nome' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'instituicao_codigo' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => true,
            ],
            'instituicao_nome' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'instituicao_sigla' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
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
            'ano_inicio' => [
                'type'       => 'YEAR',
                'null'       => true,
            ],
            'situacao' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'nota_capes' => [
                'type'       => 'TINYINT',
                'constraint' => 2,
                'unsigned'   => true,
                'null'       => true,
            ],
            'modalidade' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
            ],
            'graus' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'areas_concentracao' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'linhas_pesquisa' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'instituicoes_associadas' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'cursos' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'atos_normativos' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'fonte_url' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
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
        $this->forge->addUniqueKey('codigo_capes');
        $this->forge->addKey('instituicao_codigo');
        $this->forge->addKey('situacao');
        $this->forge->createTable('programas_pos_graduacao');
    }

    public function down(): void
    {
        $this->forge->dropTable('programas_pos_graduacao', true);
    }
}
