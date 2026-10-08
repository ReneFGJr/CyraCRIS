<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMarcacaoFieldsToTmpOrientacoes extends Migration
{
    public function up(): void
    {
        $fields = [
            'titulo_qualificacao' => ['type' => 'TEXT', 'null' => true],
            'titulo_defesa' => ['type' => 'TEXT', 'null' => true],
            'data_entrada_programa' => ['type' => 'DATE', 'null' => true],
            'data_qualificacao' => ['type' => 'DATE', 'null' => true],
            'data_defesa' => ['type' => 'DATE', 'null' => true],
            'membro_orientador' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
        ];

        foreach (['avalidor_externo', 'avalidor_interno', 'suplente'] as $prefix) {
            for ($number = 1; $number <= 3; $number++) {
                $fields[$prefix . '_' . $number] = ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true];
            }
        }

        $this->forge->addColumn('tmp_orientacoes', $fields);
    }

    public function down(): void
    {
        $fields = [
            'titulo_qualificacao', 'titulo_defesa', 'data_entrada_programa',
            'data_qualificacao', 'data_defesa', 'membro_orientador',
        ];

        foreach (['avalidor_externo', 'avalidor_interno', 'suplente'] as $prefix) {
            for ($number = 1; $number <= 3; $number++) {
                $fields[] = $prefix . '_' . $number;
            }
        }

        $this->forge->dropColumn('tmp_orientacoes', $fields);
    }
}
