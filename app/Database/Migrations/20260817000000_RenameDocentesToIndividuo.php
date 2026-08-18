<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RenameDocentesToIndividuo extends Migration
{
    public function up(): void
    {
        $this->db->query('RENAME TABLE `docentes` TO `individuo`');

        $this->forge->addColumn('individuo', [
            'lattes_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 40,
                'null'       => true,
                'after'      => 'lattes_url',
            ],
            'orcid' => [
                'type'       => 'VARCHAR',
                'constraint' => 19,
                'null'       => true,
                'after'      => 'lattes_id',
            ],
        ]);

        $this->forge->addKey('lattes_id', false, false, 'idx_individuo_lattes_id');
        $this->forge->addKey('orcid', false, false, 'idx_individuo_orcid');
    }

    public function down(): void
    {
        $this->db->query('ALTER TABLE `individuo` DROP INDEX `idx_individuo_lattes_id`');
        $this->db->query('ALTER TABLE `individuo` DROP INDEX `idx_individuo_orcid`');
        $this->forge->dropColumn('individuo', ['lattes_id', 'orcid']);
        $this->db->query('RENAME TABLE `individuo` TO `docentes`');
    }
}
