<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddProgramaToOrientacoes extends Migration
{
    public function up(): void
    {
        if (! $this->db->fieldExists('programa_id', 'orientacoes')) {
            $this->forge->addColumn('orientacoes', [
                'programa_id' => [
                    'type'       => 'BIGINT',
                    'constraint' => 20,
                    'unsigned'   => true,
                    'null'       => true,
                    'after'      => 'estudante_id',
                ],
            ]);
        }

        // A tabela foi criada originalmente como MyISAM, que não aceita chaves estrangeiras.
        $this->db->query('ALTER TABLE `programas_pos_graduacao` ENGINE=InnoDB');

        $this->db->query(
            'ALTER TABLE `orientacoes`
             ADD INDEX `idx_orientacoes_programa_id` (`programa_id`),
             ADD CONSTRAINT `fk_orientacoes_programa`
             FOREIGN KEY (`programa_id`) REFERENCES `programas_pos_graduacao` (`id`)
             ON UPDATE CASCADE ON DELETE SET NULL'
        );
    }

    public function down(): void
    {
        $this->db->query(
            'ALTER TABLE `orientacoes`
             DROP FOREIGN KEY `fk_orientacoes_programa`,
             DROP INDEX `idx_orientacoes_programa_id`'
        );

        $this->forge->dropColumn('orientacoes', 'programa_id');
    }
}
