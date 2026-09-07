<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddInstituicaoToOrientacoes extends Migration
{
    public function up(): void
    {
        if (! $this->db->fieldExists('instituicao_id', 'orientacoes')) {
            $this->forge->addColumn('orientacoes', [
                'instituicao_id' => [
                    'type'       => 'BIGINT',
                    'constraint' => 20,
                    'unsigned'   => true,
                    'null'       => true,
                    'after'      => 'programa_id',
                ],
            ]);
        }

        $this->db->query('ALTER TABLE `instituicao` ENGINE=InnoDB');
        $this->db->query(
            'ALTER TABLE `orientacoes`
             ADD INDEX `idx_orientacoes_instituicao_id` (`instituicao_id`),
             ADD CONSTRAINT `fk_orientacoes_instituicao`
             FOREIGN KEY (`instituicao_id`) REFERENCES `instituicao` (`id`)
             ON UPDATE CASCADE ON DELETE SET NULL'
        );
    }

    public function down(): void
    {
        $this->db->query(
            'ALTER TABLE `orientacoes`
             DROP FOREIGN KEY `fk_orientacoes_instituicao`,
             DROP INDEX `idx_orientacoes_instituicao_id`'
        );

        $this->forge->dropColumn('orientacoes', 'instituicao_id');
    }
}
