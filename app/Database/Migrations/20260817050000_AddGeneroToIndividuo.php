<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddGeneroToIndividuo extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('individuo', [
            'genero' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'unsigned'   => true,
                'default'    => 0,
                'null'       => false,
                'after'      => 'nome',
            ],
        ]);

        $this->db->query(
            'ALTER TABLE `individuo`
             ADD CONSTRAINT `chk_individuo_genero`
             CHECK (`genero` IN (0, 1, 2))'
        );
    }

    public function down(): void
    {
        $this->db->query(
            'ALTER TABLE `individuo`
             DROP CONSTRAINT `chk_individuo_genero`'
        );

        $this->forge->dropColumn('individuo', 'genero');
    }
}
