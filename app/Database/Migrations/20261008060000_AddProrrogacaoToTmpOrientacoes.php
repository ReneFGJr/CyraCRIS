<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddProrrogacaoToTmpOrientacoes extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('tmp_orientacoes', [
            'data_prorrogacao_qualificacao' => ['type' => 'DATE', 'null' => true],
            'data_prorrogacao_defesa' => ['type' => 'DATE', 'null' => true],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('tmp_orientacoes', [
            'data_prorrogacao_qualificacao', 'data_prorrogacao_defesa',
        ]);
    }
}
