<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddEventLocationsToTmpOrientacoes extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('tmp_orientacoes', [
            'hora_entrada_programa' => ['type' => 'TIME', 'null' => true],
            'local_entrada_programa' => ['type' => 'TEXT', 'null' => true],
            'local_qualificacao' => ['type' => 'TEXT', 'null' => true],
            'local_defesa' => ['type' => 'TEXT', 'null' => true],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('tmp_orientacoes', [
            'hora_entrada_programa', 'local_entrada_programa', 'local_qualificacao', 'local_defesa',
        ]);
    }
}
