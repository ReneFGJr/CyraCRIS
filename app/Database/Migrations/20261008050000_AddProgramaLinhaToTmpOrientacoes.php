<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddProgramaLinhaToTmpOrientacoes extends Migration
{
    public function up(): void
    {
        $fields = [
            'programa_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'linha_pesquisa_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
        ];
        foreach ($fields as $name => $definition) {
            if (! $this->db->fieldExists($name, 'tmp_orientacoes')) {
                $this->forge->addColumn('tmp_orientacoes', [$name => $definition]);
            }
        }
        // Legacy research-line tables may use MyISAM, which does not support foreign keys.
        $this->db->query('ALTER TABLE `linhas_pesquisa` ENGINE=InnoDB');
        $this->db->query(
            'ALTER TABLE `tmp_orientacoes`
             ADD INDEX `idx_tmp_orientacoes_programa` (`programa_id`),
             ADD INDEX `idx_tmp_orientacoes_linha` (`linha_pesquisa_id`),
             ADD CONSTRAINT `fk_tmp_orientacoes_programa`
                FOREIGN KEY (`programa_id`) REFERENCES `programas_pos_graduacao` (`id`)
                ON UPDATE CASCADE ON DELETE SET NULL,
             ADD CONSTRAINT `fk_tmp_orientacoes_linha`
                FOREIGN KEY (`linha_pesquisa_id`) REFERENCES `linhas_pesquisa` (`id`)
                ON UPDATE CASCADE ON DELETE SET NULL'
        );
    }

    public function down(): void
    {
        $this->forge->dropForeignKey('tmp_orientacoes', 'fk_tmp_orientacoes_programa');
        $this->forge->dropForeignKey('tmp_orientacoes', 'fk_tmp_orientacoes_linha');
        $this->forge->dropColumn('tmp_orientacoes', ['programa_id', 'linha_pesquisa_id']);
    }
}
