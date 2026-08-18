<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddLattesUpdatedAtToIndividuo extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('individuo', [
            'lattes_updated_at' => [
                'type'  => 'DATETIME',
                'null'  => true,
                'after' => 'lattes_id',
            ],
        ]);
        $this->db->query('ALTER TABLE `individuo` ADD INDEX `idx_individuo_lattes_updated_at` (`lattes_updated_at`)');
    }

    public function down(): void
    {
        $this->db->query('ALTER TABLE `individuo` DROP INDEX `idx_individuo_lattes_updated_at`');
        $this->forge->dropColumn('individuo', 'lattes_updated_at');
    }
}
