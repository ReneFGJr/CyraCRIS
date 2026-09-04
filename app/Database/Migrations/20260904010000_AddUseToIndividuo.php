<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUseToIndividuo extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('individuo', [
            'use' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => false,
                'default'    => 0,
                'after'      => 'id',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('individuo', 'use');
    }
}
