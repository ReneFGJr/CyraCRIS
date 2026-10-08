<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddExtensaoDiasToTmpOrientacoes extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('tmp_orientacoes', [
            'extensao_dias' => ['type' => 'INT', 'unsigned' => true, 'default' => 0, 'null' => false],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('tmp_orientacoes', 'extensao_dias');
    }
}
