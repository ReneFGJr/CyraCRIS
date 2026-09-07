<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddLogoToProgramasPosGraduacao extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('programas_pos_graduacao', [
            'logo' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'website',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('programas_pos_graduacao', 'logo');
    }
}
