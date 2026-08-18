<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddForeignKeysIndividuoInstituicao extends Migration
{
    public function up(): void
    {
        $this->db->query(
            'ALTER TABLE `individuo_instituicao`
             ADD CONSTRAINT `fk_individuo_instituicao_individuo`
             FOREIGN KEY (`individuo_id`) REFERENCES `individuo` (`id`)
             ON UPDATE CASCADE ON DELETE CASCADE'
        );

        $this->db->query(
            'ALTER TABLE `individuo_instituicao`
             ADD CONSTRAINT `fk_individuo_instituicao_instituicao`
             FOREIGN KEY (`instituicao_id`) REFERENCES `instituicao` (`id`)
             ON UPDATE CASCADE ON DELETE CASCADE'
        );
    }

    public function down(): void
    {
        $this->db->query(
            'ALTER TABLE `individuo_instituicao`
             DROP FOREIGN KEY `fk_individuo_instituicao_instituicao`'
        );

        $this->db->query(
            'ALTER TABLE `individuo_instituicao`
             DROP FOREIGN KEY `fk_individuo_instituicao_individuo`'
        );
    }
}
