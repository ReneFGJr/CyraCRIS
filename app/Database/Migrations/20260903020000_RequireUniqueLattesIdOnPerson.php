<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use RuntimeException;

class RequireUniqueLattesIdOnPerson extends Migration
{
    public function up(): void
    {
        $missing = $this->db->query(
            "SELECT COUNT(*) AS total FROM `person` WHERE `lattes_id` IS NULL OR TRIM(`lattes_id`) = ''",
        )->getRowArray();

        if ((int) ($missing['total'] ?? 0) > 0) {
            throw new RuntimeException(
                'Não é possível tornar lattes_id obrigatório: existem registros sem ID Lattes.',
            );
        }

        $duplicates = $this->db->query(
            "SELECT COUNT(*) AS total FROM (
                SELECT `lattes_id`
                FROM `person`
                GROUP BY `lattes_id`
                HAVING COUNT(*) > 1
            ) AS `duplicate_lattes_ids`",
        )->getRowArray();

        if ((int) ($duplicates['total'] ?? 0) > 0) {
            throw new RuntimeException(
                'Não é possível criar a restrição única: existem IDs Lattes duplicados.',
            );
        }

        $this->db->query(
            'ALTER TABLE `person`
                MODIFY `lattes_id` VARCHAR(100) NOT NULL,
                ADD UNIQUE INDEX `uq_person_lattes_id` (`lattes_id`)',
        );
    }

    public function down(): void
    {
        $this->db->query(
            'ALTER TABLE `person`
                DROP INDEX `uq_person_lattes_id`,
                MODIFY `lattes_id` VARCHAR(100) NULL',
        );
    }
}
