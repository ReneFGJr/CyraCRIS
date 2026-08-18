<?php

namespace App\Services;

use SimpleXMLElement;

class LattesProductionImporter
{
    public function importar(int $pesquisadorId, SimpleXMLElement $xml): int
    {
        $db = db_connect();
        $registros = [];
        foreach ([
            'BIBLIOGRAFICA' => '//PRODUCAO-BIBLIOGRAFICA//*[starts-with(name(*[1]), "DADOS-BASICOS-")]',
            'TECNICA' => '//PRODUCAO-TECNICA//*[starts-with(name(*[1]), "DADOS-BASICOS-")]',
            'ARTISTICA' => '//PRODUCAO-ARTISTICA-CULTURAL//*[starts-with(name(*[1]), "DADOS-BASICOS-")]',
        ] as $categoria => $xpath) {
            foreach ($xml->xpath($xpath) ?: [] as $item) {
                $basicos = $item->children()[0] ?? null;
                if (! $basicos instanceof SimpleXMLElement) {
                    continue;
                }
                $titulo = $this->titulo($basicos);
                if ($titulo === '') {
                    continue;
                }
                $tipoXml = $item->getName();
                $autores = [];
                foreach ($item->xpath('.//AUTORES') ?: [] as $autor) {
                    $nome = trim((string) $autor['NOME-COMPLETO-DO-AUTOR']);
                    if ($nome !== '') {
                        $autores[] = $nome;
                    }
                }
                $ano = $this->ano($basicos);
                $sourceId = $this->localizarOuCriarSource($item);
                $hash = hash('sha256', $categoria . '|' . $tipoXml . '|' . $titulo . '|' . $ano . '|' . implode('|', $autores));
                $registros[$hash] = [
                    'pesquisador_id' => $pesquisadorId,
                    'source_id' => $sourceId,
                    'categoria' => $categoria,
                    'grupo' => $this->grupo($tipoXml),
                    'tipo' => $this->rotuloTipo($tipoXml),
                    'titulo' => $titulo,
                    'ano' => $ano > 0 ? $ano : null,
                    'doi' => $this->primeiroAtributo($basicos, ['DOI']) ?: null,
                    'autores' => $autores === [] ? null : implode('; ', $autores),
                    'dados_json' => json_encode($this->atributos($item), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'chave_hash' => $hash,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ];
            }
        }

        $db->table('producoes')->where('pesquisador_id', $pesquisadorId)->delete();
        if ($registros !== []) {
            $db->table('producoes')->insertBatch(array_values($registros));
        }
        return count($registros);
    }

    private function grupo(string $tipo): string
    {
        return match (true) {
            str_contains($tipo, 'ARTIGO') => 'ARTIGO',
            str_contains($tipo, 'LIVRO'), str_contains($tipo, 'CAPITULO') => 'LIVRO',
            str_contains($tipo, 'EVENTO'), str_contains($tipo, 'APRESENTACAO-DE-TRABALHO') => 'EVENTO',
            default => 'OUTROS',
        };
    }

    private function rotuloTipo(string $tipo): string
    {
        return mb_convert_case(str_replace('-', ' ', $tipo), MB_CASE_TITLE, 'UTF-8');
    }

    private function ano(SimpleXMLElement $xml): int
    {
        foreach ($xml->attributes() as $nome => $valor) {
            if (str_starts_with((string) $nome, 'ANO') && preg_match('/^(19|20)\d{2}$/', (string) $valor) === 1) {
                return (int) $valor;
            }
        }
        return 0;
    }

    private function titulo(SimpleXMLElement $xml): string
    {
        foreach ($xml->attributes() as $nome => $valor) {
            $nome = (string) $nome;
            if ((str_starts_with($nome, 'TITULO') && ! str_contains($nome, 'INGLES')) || in_array($nome, ['NOME-DO-PROJETO', 'DESCRICAO'], true)) {
                $titulo = trim((string) $valor);
                if ($titulo !== '') {
                    return $titulo;
                }
            }
        }
        return '';
    }

    private function localizarOuCriarSource(SimpleXMLElement $item): ?int
    {
        $detalhamento = null;
        foreach ($item->children() as $elemento) {
            if (str_starts_with($elemento->getName(), 'DETALHAMENTO-')) {
                $detalhamento = $elemento;
                break;
            }
        }
        if (! $detalhamento instanceof SimpleXMLElement) {
            return null;
        }

        $atributos = [];
        foreach ($detalhamento->attributes() as $nome => $valor) {
            $atributos[(string) $nome] = trim((string) $valor);
        }
        $issn = $atributos['ISSN'] ?? '';
        $isbn = $atributos['ISBN'] ?? '';
        $periodico = $atributos['TITULO-DO-PERIODICO-OU-REVISTA'] ?? '';
        $evento = $atributos['NOME-DO-EVENTO'] ?? '';
        $livro = $atributos['TITULO-DO-LIVRO'] ?? '';
        $editora = $atributos['NOME-DA-EDITORA'] ?? '';

        if ($periodico !== '' || $issn !== '') {
            $tipo = 'PERIODICO';
            $nome = $periodico !== '' ? $periodico : 'Periódico não informado';
            $cidade = $atributos['LOCAL-DE-PUBLICACAO'] ?? '';
        } elseif ($evento !== '') {
            $tipo = 'EVENTO';
            $nome = $evento;
            $cidade = $atributos['CIDADE-DO-EVENTO'] ?? '';
        } elseif ($livro !== '' || $isbn !== '' || $editora !== '') {
            $tipo = 'LIVRO';
            $nome = $livro !== '' ? $livro : ($editora !== '' ? $editora : 'Livro não informado');
            $cidade = $atributos['CIDADE-DA-EDITORA'] ?? '';
        } else {
            return null;
        }

        $dados = [
            'tipo' => $tipo,
            'nome' => $nome,
            'issn' => $issn !== '' ? $issn : null,
            'isbn' => $isbn !== '' ? $isbn : null,
            'editora' => $editora !== '' ? $editora : null,
            'cidade' => $cidade !== '' ? $cidade : null,
            'pais' => ($atributos['PAIS'] ?? '') !== '' ? $atributos['PAIS'] : null,
            'dados_json' => json_encode($atributos, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ];
        $hash = hash('sha256', implode('|', [$tipo, mb_strtolower($nome), $issn, $isbn, mb_strtolower($editora)]));
        $db = db_connect();
        $source = $db->table('source')->select('id')->where('chave_hash', $hash)->get()->getRowArray();
        if ($source !== null) {
            $db->table('source')->where('id', $source['id'])->update(array_merge($dados, ['updated_at' => date('Y-m-d H:i:s')]));
            return (int) $source['id'];
        }

        $agora = date('Y-m-d H:i:s');
        $db->table('source')->insert(array_merge($dados, ['chave_hash' => $hash, 'created_at' => $agora, 'updated_at' => $agora]));
        return (int) $db->insertID();
    }

    /** @param list<string> $nomes */
    private function primeiroAtributo(SimpleXMLElement $xml, array $nomes): string
    {
        foreach ($nomes as $nome) {
            $valor = trim((string) $xml[$nome]);
            if ($valor !== '') {
                return $valor;
            }
        }
        return '';
    }

    /** @return array<string, mixed> */
    private function atributos(SimpleXMLElement $item): array
    {
        $dados = ['tipo_xml' => $item->getName(), 'elementos' => []];
        foreach ($item->children() as $filho) {
            $atributos = [];
            foreach ($filho->attributes() as $nome => $valor) {
                $atributos[$nome] = (string) $valor;
            }
            if ($atributos !== []) {
                $dados['elementos'][$filho->getName()][] = $atributos;
            }
        }
        return $dados;
    }
}
