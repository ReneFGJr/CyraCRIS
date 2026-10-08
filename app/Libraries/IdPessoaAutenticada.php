<?php

namespace App\Libraries;

class IdPessoaAutenticada
{
    public static function extrair(array $dados): ?string
    {
        foreach (['user', 'person', 'pessoa', 'profile', 'data'] as $campo) {
            if (isset($dados[$campo]) && is_array($dados[$campo])) {
                $id = self::extrair($dados[$campo]);
                if ($id !== null) {
                    return $id;
                }
            }
        }
        foreach (['person_id', 'pessoa_id', 'id_person', 'id_pessoa', 'user_id', 'id_user', 'id'] as $campo) {
            $id = $dados[$campo] ?? null;
            if ((is_int($id) || is_string($id)) && preg_match('/^[1-9][0-9]*$/D', (string) $id) === 1) {
                return (string) $id;
            }
        }
        return null;
    }
}
