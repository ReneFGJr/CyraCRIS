<?php

namespace App\Models;

use CodeIgniter\Model;

class ProgramaPosGraduacaoModel extends Model
{
    protected $table         = 'programas_pos_graduacao';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'sucupira_id',
        'codigo_capes',
        'nome',
        'instituicao_codigo',
        'instituicao_nome',
        'instituicao_sigla',
        'telefone',
        'email',
        'website',
        'ano_inicio',
        'situacao',
        'nota_capes',
        'modalidade',
        'graus',
        'areas_concentracao',
        'linhas_pesquisa',
        'instituicoes_associadas',
        'cursos',
        'atos_normativos',
        'fonte_url',
    ];
}
