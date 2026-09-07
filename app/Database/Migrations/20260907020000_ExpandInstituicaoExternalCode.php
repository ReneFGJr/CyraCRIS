<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ExpandInstituicaoExternalCode extends Migration
{
    public function up(): void
    {
        $this->db->query(
            'ALTER TABLE `instituicao`
             MODIFY `codigo_externo` BIGINT UNSIGNED NULL'
        );
    }

    public function down(): void
    {
        $this->db->query(
            'ALTER TABLE `instituicao`
             MODIFY `codigo_externo` INT UNSIGNED NULL'
        );
    }
}
