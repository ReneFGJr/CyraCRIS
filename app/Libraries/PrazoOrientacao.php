<?php

namespace App\Libraries;

use DateTimeImmutable;
use DateTimeZone;

class PrazoOrientacao
{
    public static function prever(array $aluno, int $meses): ?DateTimeImmutable
    {
        $ingresso = self::data($aluno['data_entrada_programa'] ?? null);
        if ($ingresso === null) {
            return null;
        }
        $mesDestino = $ingresso->modify('first day of this month')->modify('+' . $meses . ' months');
        $previsao = $mesDestino->setDate((int) $mesDestino->format('Y'), (int) $mesDestino->format('m'), min((int) $ingresso->format('d'), (int) $mesDestino->format('t')));
        $dias = max(0, (int) ($aluno['extensao_dias'] ?? 0));
        return $dias > 0 ? $previsao->modify('+' . $dias . ' days') : $previsao;
    }

    public static function calcular(array $aluno, ?DateTimeImmutable $hoje = null): array
    {
        $hoje = ($hoje ?? new DateTimeImmutable('today', new DateTimeZone('America/Sao_Paulo')))->setTime(0, 0);
        $situacao = $aluno['situacao'] ?? 'Cursando';
        if (in_array($situacao, ['Desistiu', 'Desligado'], true)) {
            return ['rotulo' => '-', 'classe' => 'sem-data', 'detalhe' => 'Sem acompanhamento de prazo nesta situação.'];
        }
        $defesa = self::data($aluno['data_defesa'] ?? null);
        if (in_array($situacao, ['Defendido', 'Diplomando'], true) || ($defesa !== null && $defesa <= $hoje)) {
            return ['rotulo' => 'Normal', 'classe' => 'normal', 'detalhe' => 'Defesa concluída.'];
        }
        $qualificacao = self::data($aluno['data_qualificacao'] ?? null);
        $qualificado = $situacao === 'Qualificado' || ($qualificacao !== null && $qualificacao <= $hoje);
        $evento = $qualificado ? 'defesa' : 'qualificacao';
        $meses = $qualificado ? 24 : 18;
        $limite = self::prever(array_replace($aluno, ['extensao_dias' => 0]), $meses);
        $prorrogacao = self::data($aluno['data_prorrogacao_' . $evento] ?? null);
        if ($prorrogacao !== null && ($limite === null || $prorrogacao > $limite)) {
            $limite = $prorrogacao;
        }
        if ($limite === null) {
            return ['rotulo' => '-', 'classe' => 'sem-data', 'detalhe' => 'Informe a data de ingresso para calcular o prazo.'];
        }
        $extensaoDias = max(0, (int) ($aluno['extensao_dias'] ?? 0));
        if ($extensaoDias > 0) {
            $limite = $limite->modify('+' . $extensaoDias . ' days');
        }
        $atrasado = $hoje > $limite;
        return [
            'rotulo' => $atrasado ? 'Atrasado' : 'Normal',
            'classe' => $atrasado ? 'atrasado' : 'normal',
            'detalhe' => 'Prazo de ' . $evento . ': ' . $limite->format('d/m/Y') . '.',
        ];
    }

    private static function data(mixed $valor): ?DateTimeImmutable
    {
        if (! is_string($valor) || $valor === '') {
            return null;
        }
        $data = DateTimeImmutable::createFromFormat('!Y-m-d', $valor, new DateTimeZone('America/Sao_Paulo'));
        return $data !== false && $data->format('Y-m-d') === $valor ? $data : null;
    }
}
