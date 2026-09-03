<?php

namespace App\Models;

use CodeIgniter\Model;

class PersonModel extends Model
{
    protected $table            = 'person';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $allowedFields    = [
        'name',
        'email',
        'cracha',
        'lattes_url',
        'lattes_id',
        'cpf',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'name'       => 'required|max_length[255]',
        'email'      => 'permit_empty|valid_email|max_length[255]|is_unique[person.email,id,{id}]',
        'cracha'     => 'permit_empty|max_length[50]',
        'lattes_url' => 'permit_empty|valid_url_strict|max_length[500]',
        'lattes_id'  => 'required|max_length[100]|is_unique[person.lattes_id,id,{id}]',
        'cpf'        => 'permit_empty|max_length[20]',
    ];

    protected $validationMessages = [
        'name' => [
            'required'   => 'O nome é obrigatório.',
            'max_length' => 'O nome deve ter no máximo 255 caracteres.',
        ],
        'email' => [
            'valid_email' => 'Informe um endereço de e-mail válido.',
            'max_length'  => 'O e-mail deve ter no máximo 255 caracteres.',
            'is_unique'   => 'Este e-mail já está cadastrado.',
        ],
        'cracha' => [
            'max_length' => 'O crachá deve ter no máximo 50 caracteres.',
        ],
        'lattes_url' => [
            'valid_url_strict' => 'Informe uma URL válida para o currículo Lattes.',
            'max_length'       => 'A URL do currículo Lattes deve ter no máximo 500 caracteres.',
        ],
        'lattes_id' => [
            'required'   => 'O ID Lattes é obrigatório.',
            'max_length' => 'O ID Lattes deve ter no máximo 100 caracteres.',
            'is_unique'  => 'Este ID Lattes já está cadastrado.',
        ],
        'cpf' => [
            'max_length' => 'O CPF deve ter no máximo 20 caracteres.',
        ],
    ];
}
