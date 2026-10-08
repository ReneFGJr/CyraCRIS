<?php

namespace App\Models;

use CodeIgniter\Model;

class TmpOrientacaoHistoricoModel extends Model
{
    protected $table = 'tmp_orientacao_historico';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'orientacao_id', 'tipo', 'evento', 'descricao', 'situacao_anterior',
        'situacao_nova', 'dados_anteriores', 'dados_novos', 'usuario',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = '';
    protected $validationRules = [
        'orientacao_id' => 'required|is_natural_no_zero',
        'tipo' => 'required|in_list[Movimentação,Solicitação]',
        'evento' => 'required|max_length[100]',
        'usuario' => 'required|is_natural_no_zero|max_length[255]',
        'situacao_anterior' => 'permit_empty|max_length[30]',
        'situacao_nova' => 'permit_empty|max_length[30]',
    ];
}
