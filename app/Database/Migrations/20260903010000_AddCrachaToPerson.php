<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCrachaToPerson extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('person', [
            'cracha' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'after'      => 'email',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('person', 'cracha');
    }
}
