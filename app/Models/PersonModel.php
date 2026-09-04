<?php

namespace App\Models;

use CodeIgniter\Model;

class PersonModel extends Model
{
    protected $table            = 'individuo';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $allowedFields    = [
        'use',
        'nome',
        'email',
        'lattes_url',
        'lattes_id',
        'orcid',
        'cpf',
        'cracha',
        'genero',
        'instituicao',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'use'        => 'permit_empty|integer',
        'nome'       => 'required|max_length[255]',
        'email'      => 'permit_empty|valid_email|max_length[190]',
        'lattes_url' => 'permit_empty|valid_url_strict|max_length[255]',
        'lattes_id'  => 'permit_empty|max_length[40]',
        'orcid'      => 'permit_empty|max_length[19]',
        'cpf'        => 'permit_empty|max_length[14]',
        'cracha'     => 'permit_empty|max_length[50]',
    ];

    protected $validationMessages = [
        'nome' => [
            'required'   => 'O nome é obrigatório.',
            'max_length' => 'O nome deve ter no máximo 255 caracteres.',
        ],
        'email' => [
            'valid_email' => 'Informe um endereço de e-mail válido.',
            'max_length'  => 'O e-mail deve ter no máximo 255 caracteres.',
            'is_unique'   => 'Este e-mail já está cadastrado.',
        ],
        'lattes_url' => [
            'valid_url_strict' => 'Informe uma URL válida para o currículo Lattes.',
            'max_length'       => 'A URL do currículo Lattes deve ter no máximo 500 caracteres.',
        ],
        'lattes_id' => [
            'max_length' => 'O ID Lattes deve ter no máximo 40 caracteres.',
        ],
        'orcid' => [
            'max_length' => 'O ORCID deve ter no máximo 19 caracteres.',
        ],
        'cpf' => [
            'max_length' => 'O CPF deve ter no máximo 14 caracteres.',
        ],
        'cracha' => [
            'max_length' => 'O crachá deve ter no máximo 50 caracteres.',
        ],
    ];
}
