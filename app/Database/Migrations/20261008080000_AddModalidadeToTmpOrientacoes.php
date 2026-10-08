<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddModalidadeToTmpOrientacoes extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('tmp_orientacoes', [
            'modalidade' => [
                'type' => 'ENUM',
                'constraint' => ['D', 'M'],
                'null' => true,
                'after' => 'id',
                'comment' => 'D = Doutorado; M = Mestrado',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('tmp_orientacoes', 'modalidade');
    }
}
