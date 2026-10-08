<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSituacaoToTmpOrientacoes extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('tmp_orientacoes', [
            'situacao' => [
                'type' => 'ENUM',
                'constraint' => ['Cursando', 'Desistiu', 'Qualificado', 'Defendido', 'Diplomando', 'Desligado'],
                'default' => 'Cursando',
                'null' => false,
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('tmp_orientacoes', 'situacao');
    }
}
