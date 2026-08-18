<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateOrientacoes extends Migration
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
            'orientador_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => false,
            ],
            'estudante_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => false,
            ],
            'tipo' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => false,
            ],
            'status' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'unsigned'   => true,
                'default'    => 0,
                'null'       => false,
                'comment'    => '0=Em andamento; 1=Concluída',
            ],
            'ano_inicio' => [
                'type'       => 'SMALLINT',
                'constraint' => 4,
                'unsigned'   => true,
                'null'       => true,
            ],
            'ano_final' => [
                'type'       => 'SMALLINT',
                'constraint' => 4,
                'unsigned'   => true,
                'null'       => true,
            ],
            'titulo' => [
                'type'       => 'TEXT',
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
        $this->forge->addKey('orientador_id');
        $this->forge->addKey('estudante_id');
        $this->forge->addUniqueKey(['orientador_id', 'estudante_id', 'tipo']);
        $this->forge->createTable('orientacoes');

        $this->db->query(
            'ALTER TABLE `orientacoes`
             ADD CONSTRAINT `fk_orientacoes_orientador`
             FOREIGN KEY (`orientador_id`) REFERENCES `individuo` (`id`)
             ON UPDATE CASCADE ON DELETE CASCADE,
             ADD CONSTRAINT `fk_orientacoes_estudante`
             FOREIGN KEY (`estudante_id`) REFERENCES `individuo` (`id`)
             ON UPDATE CASCADE ON DELETE CASCADE,
             ADD CONSTRAINT `chk_orientacoes_status`
             CHECK (`status` IN (0, 1)),
             ADD CONSTRAINT `chk_orientacoes_tipo`
             CHECK (`tipo` IN (\'Pos-doc\', \'Doutorado\', \'Mestrado\', \'Iniciação científica\', \'TCC (Graduação)\', \'Especialização\', \'Outras\'))'
        );
    }

    public function down(): void
    {
        $this->forge->dropTable('orientacoes', true);
    }
}
