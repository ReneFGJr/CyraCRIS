<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTipoOrientacaoToOrientacoes extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('orientacoes', [
            'tipo_orientacao' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => false,
                'default'    => 'ORIENTADOR',
                'after'      => 'tipo',
                'comment'    => 'ORIENTADOR ou CO_ORIENTADOR',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('orientacoes', 'tipo_orientacao');
    }
}
