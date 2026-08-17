<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ProgramasPosGraduacaoSeeder extends Seeder
{
    public function run(): void
    {
        $programa = [
            'sucupira_id'            => 207794,
            'codigo_capes'           => '42001013176P0',
            'nome'                   => 'CIÊNCIA DA INFORMAÇÃO',
            'instituicao_codigo'     => 4358,
            'instituicao_nome'       => 'UNIVERSIDADE FEDERAL DO RIO GRANDE DO SUL',
            'instituicao_sigla'      => 'UFRGS',
            'telefone'               => '(51) 33085123',
            'email'                  => 'ppgcin@ufrgs.br',
            'website'                => 'http://www.ufrgs.br/ppgcin',
            'ano_inicio'             => 2019,
            'situacao'               => 'EM FUNCIONAMENTO',
            'nota_capes'             => 5,
            'modalidade'             => 'ACADÊMICO',
            'graus'                  => json_encode(['Mestrado', 'Doutorado'], JSON_UNESCAPED_UNICODE),
            'areas_concentracao'     => json_encode([], JSON_UNESCAPED_UNICODE),
            'linhas_pesquisa'        => json_encode([], JSON_UNESCAPED_UNICODE),
            'instituicoes_associadas'=> json_encode([], JSON_UNESCAPED_UNICODE),
            'cursos'                 => json_encode(['Mestrado', 'Doutorado'], JSON_UNESCAPED_UNICODE),
            'atos_normativos'        => json_encode([], JSON_UNESCAPED_UNICODE),
            'fonte_url'              => 'https://sucupira.capes.gov.br/programas/detalhamento/207794?page=0&size=20&regiao=Sul&uf=RS&grande-area-conhecimento=6&conceito=5&grau=ME/DO',
            'updated_at'             => date('Y-m-d H:i:s'),
        ];

        $builder = $this->db->table('programas_pos_graduacao');
        $existente = $builder->where('codigo_capes', $programa['codigo_capes'])->get()->getRowArray();

        if ($existente === null) {
            $programa['created_at'] = date('Y-m-d H:i:s');
            $builder->insert($programa);
            return;
        }

        $builder->where('id', $existente['id'])->update($programa);
    }
}
