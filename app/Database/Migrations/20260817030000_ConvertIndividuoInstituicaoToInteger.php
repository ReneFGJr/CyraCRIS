<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ConvertIndividuoInstituicaoToInteger extends Migration
{
    public function up(): void
    {
        $this->db->query(
            "UPDATE `individuo` i
             INNER JOIN `instituicao` inst
                     ON CONVERT(i.`instituicao` USING utf8mb4) COLLATE utf8mb4_general_ci = CONVERT(CAST(inst.`id` AS CHAR) USING utf8mb4) COLLATE utf8mb4_general_ci
                     OR CONVERT(i.`instituicao` USING utf8mb4) COLLATE utf8mb4_general_ci = CONVERT(inst.`nome` USING utf8mb4) COLLATE utf8mb4_general_ci
                     OR CONVERT(i.`instituicao` USING utf8mb4) COLLATE utf8mb4_general_ci = CONVERT(inst.`sigla` USING utf8mb4) COLLATE utf8mb4_general_ci
             SET i.`instituicao` = CAST(inst.`id` AS CHAR)"
        );

        $this->db->query(
            "UPDATE `individuo`
             SET `instituicao` = NULL
             WHERE `instituicao` IS NOT NULL
               AND `instituicao` NOT REGEXP '^[0-9]+$'"
        );

        $this->db->query(
            'ALTER TABLE `individuo`
             MODIFY `instituicao` INT UNSIGNED NULL'
        );

        $this->db->query(
            'ALTER TABLE `individuo`
             ADD INDEX `idx_individuo_instituicao` (`instituicao`)'
        );

        $this->db->query(
            'ALTER TABLE `individuo`
             ADD CONSTRAINT `fk_individuo_instituicao_id`
             FOREIGN KEY (`instituicao`) REFERENCES `instituicao` (`id`)
             ON UPDATE CASCADE ON DELETE SET NULL'
        );
    }

    public function down(): void
    {
        $this->db->query(
            'ALTER TABLE `individuo`
             DROP FOREIGN KEY `fk_individuo_instituicao_id`'
        );

        $this->db->query(
            'ALTER TABLE `individuo`
             DROP INDEX `idx_individuo_instituicao`'
        );

        $this->db->query(
            'ALTER TABLE `individuo`
             MODIFY `instituicao` VARCHAR(255) NULL'
        );
    }
}
