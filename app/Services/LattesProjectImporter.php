<?php

namespace App\Services;

use SimpleXMLElement;

class LattesProjectImporter
{
    public function importar(int $pesquisadorId, SimpleXMLElement $xml): int
    {
        $registros = [];
        $agora = date('Y-m-d H:i:s');
        foreach ($xml->xpath('//PROJETO-DE-PESQUISA') ?: [] as $projeto) {
            $titulo = trim((string) $projeto['NOME-DO-PROJETO']);
            if ($titulo === '') continue;
            $atributos = [];
            foreach ($projeto->attributes() as $nome => $valor) $atributos[(string) $nome] = trim((string) $valor);
            $integrantes = [];
            foreach ($projeto->xpath('.//INTEGRANTES-DO-PROJETO') ?: [] as $integrante) {
                $nome = trim((string) $integrante['NOME-COMPLETO']);
                if ($nome !== '') $integrantes[] = $nome;
            }
            $anoInicio = $this->ano($atributos['ANO-INICIO'] ?? '');
            $anoFim = $this->ano($atributos['ANO-FIM'] ?? '');
            $situacao = $this->normalizarSituacao($atributos['SITUACAO'] ?? $atributos['SITUACAO-DO-PROJETO'] ?? '', $anoFim);
            $hash = hash('sha256', mb_strtolower($titulo) . '|' . ($anoInicio ?? '') . '|' . ($anoFim ?? ''));
            $registros[$hash] = [
                'pesquisador_id' => $pesquisadorId, 'titulo' => $titulo,
                'descricao' => ($atributos['DESCRICAO-DO-PROJETO'] ?? '') ?: null,
                'situacao' => $situacao, 'natureza' => ($atributos['NATUREZA'] ?? '') ?: null,
                'ano_inicio' => $anoInicio, 'ano_fim' => $anoFim,
                'integrantes' => $integrantes === [] ? null : implode('; ', array_values(array_unique($integrantes))),
                'dados_json' => json_encode(['atributos' => $atributos, 'integrantes' => $integrantes], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'chave_hash' => $hash, 'created_at' => $agora, 'updated_at' => $agora,
            ];
        }
        $db = db_connect();
        $db->table('projetos')->where('pesquisador_id', $pesquisadorId)->delete();
        if ($registros !== []) $db->table('projetos')->insertBatch(array_values($registros));
        return count($registros);
    }

    private function ano(string $valor): ?int
    {
        return preg_match('/^(19|20)\d{2}$/', $valor) === 1 ? (int) $valor : null;
    }

    private function normalizarSituacao(string $situacao, ?int $anoFim): string
    {
        $situacao = mb_strtoupper(str_replace([' ', '-'], '_', trim($situacao)));
        if (str_contains($situacao, 'CONCLUID') || str_contains($situacao, 'DESATIVAD')) return 'CONCLUIDO';
        if (str_contains($situacao, 'ANDAMENTO')) return 'EM_ANDAMENTO';
        return $anoFim === null ? 'EM_ANDAMENTO' : 'CONCLUIDO';
    }
}
