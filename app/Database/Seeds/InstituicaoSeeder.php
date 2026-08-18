<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class InstituicaoSeeder extends Seeder
{
    public function run(): void
    {
        $instituicao = [
            'codigo_externo' => 4358,
            'nome'          => 'UNIVERSIDADE FEDERAL DO RIO GRANDE DO SUL',
            'sigla'          => 'UFRGS',
            'telefone'      => '(51) 33085123',
            'email'         => 'ppgcin@ufrgs.br',
            'website'       => 'http://www.ufrgs.br/ppgcin',
            'updated_at'    => date('Y-m-d H:i:s'),
        ];

        $builder = $this->db->table('instituicao');
        $existente = $builder->where('codigo_externo', $instituicao['codigo_externo'])->get()->getRowArray();

        if ($existente === null) {
            $instituicao['created_at'] = date('Y-m-d H:i:s');
            $builder->insert($instituicao);
            return;
        }

        $builder->where('id', $existente['id'])->update($instituicao);
    }
}
