<?php

namespace App\Database\Seeds;

use App\Models\ProgramaPosGraduacaoModel;
use CodeIgniter\Database\Seeder;

class LinhasEDocentesSeeder extends Seeder
{
    public function run(): void
    {
        $programa = (new ProgramaPosGraduacaoModel())
            ->where('codigo_capes', '42001013176P0')
            ->first();

        if ($programa === null) {
            return;
        }

        $linhas = [
            [
                'nome'              => 'PRODUÇÃO E ORGANIZAÇÃO DA INFORMAÇÃO',
                'area_concentracao' => 'INFORMAÇÃO, CIÊNCIA E SOCIEDADE',
                'inicio'            => '2019-04-02',
            ],
            [
                'nome'              => 'GESTÃO, PRESERVAÇÃO E USO DA INFORMAÇÃO',
                'area_concentracao' => 'INFORMAÇÃO, CIÊNCIA E SOCIEDADE',
                'inicio'            => '2019-04-02',
            ],
        ];

        $builder = $this->db->table('linhas_pesquisa');

        foreach ($linhas as $linha) {
            $dados = array_merge($linha, [
                'programa_id' => $programa['id'],
                'updated_at'  => date('Y-m-d H:i:s'),
            ]);
            $existente = $builder
                ->where('programa_id', $programa['id'])
                ->where('nome', $linha['nome'])
                ->get()
                ->getRowArray();

            if ($existente === null) {
                $dados['created_at'] = date('Y-m-d H:i:s');
                $builder->insert($dados);
                continue;
            }

            $builder->where('id', $existente['id'])->update($dados);
        }
    }
}
