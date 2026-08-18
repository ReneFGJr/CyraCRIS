<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RemoveLattesUrlFromIndividuo extends Migration
{
    public function up(): void
    {
        $this->forge->dropColumn('individuo', 'lattes_url');
    }

    public function down(): void
    {
        $this->forge->addColumn('individuo', [
            'lattes_url' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'email',
            ],
        ]);
    }
}
