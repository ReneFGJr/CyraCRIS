<?php

namespace App\Models;

use CodeIgniter\Model;

class RdfClassModel extends Model
{
    protected $table            = 'rdf_class';
    protected $primaryKey       = 'id_c';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $useTimestamps    = false;

    protected $allowedFields = [
        'c_class',
        'c_equivalent',
        'c_prefix',
        'c_class_main',
        'c_type',
        'c_description',
        'c_url',
        'c_url_update',
    ];

    protected $validationRules = [
        'c_class'       => 'required|max_length[200]',
        'c_equivalent'  => 'required|integer|greater_than_equal_to[0]',
        'c_prefix'      => 'required|integer|greater_than_equal_to[0]',
        'c_class_main'  => 'required|integer|greater_than_equal_to[0]',
        'c_type'        => 'required|in_list[C,P]',
        'c_description' => 'permit_empty',
        'c_url'         => 'permit_empty|max_length[100]',
        'c_url_update'  => 'required|valid_date[Y-m-d]',
    ];

    protected $validationMessages = [
        'c_class' => [
            'required'   => 'Informe o nome da classe.',
            'max_length' => 'O nome da classe deve ter no máximo 200 caracteres.',
        ],
        'c_type' => [
            'required'   => 'Informe o tipo.',
            'in_list'    => 'Selecione Classe ou Propriedade.',
        ],
        'c_url' => [
            'max_length' => 'A URL deve ter no máximo 100 caracteres.',
        ],
        'c_url_update' => [
            'required'   => 'Informe a data de atualização da URL.',
            'valid_date' => 'Informe uma data válida.',
        ],
    ];
}
