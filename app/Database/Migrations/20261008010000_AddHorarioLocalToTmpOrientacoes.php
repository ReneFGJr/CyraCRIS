<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddHorarioLocalToTmpOrientacoes extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('tmp_orientacoes', [
            'hora_qualificacao' => ['type' => 'TIME', 'null' => true],
            'hora_defesa' => ['type' => 'TIME', 'null' => true],
            'local' => ['type' => 'TEXT', 'null' => true],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('tmp_orientacoes', ['hora_qualificacao', 'hora_defesa', 'local']);
    }
}
