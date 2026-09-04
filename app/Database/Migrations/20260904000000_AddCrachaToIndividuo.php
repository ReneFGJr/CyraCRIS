<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCrachaToIndividuo extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('individuo', [
            'cracha' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'after'      => 'cpf',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('individuo', 'cracha');
    }
}
