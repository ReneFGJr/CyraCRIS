<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use RuntimeException;

class StoreCapesConceptText extends Migration
{
    public function up(): void
    {
        $this->forge->modifyColumn('programas_pos_graduacao', [
            'nota_capes' => [
                'name'       => 'nota_capes',
                'type'       => 'VARCHAR',
                'constraint' => 2,
                'null'       => true,
            ],
        ]);
    }

    public function down(): void
    {
        if ($this->db->table('programas_pos_graduacao')->where('nota_capes', 'A')->countAllResults() > 0) {
            throw new RuntimeException('Não é possível reverter a coluna enquanto houver conceitos CAPES "A" cadastrados.');
        }

        $this->forge->modifyColumn('programas_pos_graduacao', [
            'nota_capes' => [
                'name'       => 'nota_capes',
                'type'       => 'TINYINT',
                'constraint' => 2,
                'unsigned'   => true,
                'null'       => true,
            ],
        ]);
    }
}
