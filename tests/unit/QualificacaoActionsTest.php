<?php

use App\Controllers\Admin;
use CodeIgniter\Test\CIUnitTestCase;

final class QualificacaoActionsTest extends CIUnitTestCase
{
    private function renderStudent(?string $situacao, string $modo = ''): string
    {
        return view('admin/qualificacao', [
            'title' => 'Marcação de qualificação', 'query' => '', 'alunos' => [], 'hasMore' => false,
            'aluno' => ['id' => 1, 'nome' => 'Aluno de teste', 'id_lattes' => '', 'cracha' => '',
                'ano' => 2026, 'orientador' => '', 'situacao' => $situacao],
            'modo' => $modo,
            'situacoes' => ['Cursando', 'Desistiu', 'Qualificado', 'Defendido', 'Diplomando', 'Desligado'],
        ]);
    }

    public function testCursandoAndNullAllowOnlyQualification(): void
    {
        foreach ([null, 'Cursando'] as $situacao) {
            $html = $this->renderStudent($situacao);
            $this->assertStringContainsString('Marcar qualificação', $html);
            $this->assertStringNotContainsString('Marcar defesa', $html);
            $this->assertStringContainsString('Alterar status da situação', $html);
        }
    }

    public function testQualifiedAllowsOnlyDefense(): void
    {
        $html = $this->renderStudent('Qualificado');
        $this->assertStringContainsString('Marcar defesa', $html);
        $this->assertStringNotContainsString('Marcar qualificação', $html);
        $this->assertStringNotContainsString('id="student-edit"', $this->renderStudent('Qualificado', 'qualificacao'));
    }

    public function testOtherStatusesAllowEditingWithoutScheduling(): void
    {
        foreach (['Desistiu', 'Defendido', 'Diplomando', 'Desligado'] as $situacao) {
            $html = $this->renderStudent($situacao);
            $this->assertStringNotContainsString('Marcar defesa', $html);
            $this->assertStringNotContainsString('Marcar qualificação', $html);
            $this->assertStringContainsString('Alterar status da situação', $html);
        }
    }

    public function testStatusEditorHasIndependentEventFields(): void
    {
        $html = $this->renderStudent('Cursando', 'situacao');
        foreach (['entrada_programa', 'qualificacao', 'defesa'] as $group) {
            foreach ($group === 'entrada_programa' ? ['data_'] : ['data_', 'hora_', 'local_'] as $prefix) {
                $this->assertStringContainsString('name="' . $prefix . $group . '"', $html);
            }
        }
        $this->assertStringNotContainsString('name="hora_entrada_programa"', $html);
        $this->assertStringNotContainsString('name="local_entrada_programa"', $html);
        $this->assertStringContainsString('name="titulo_qualificacao"', $html);
        $this->assertStringContainsString('name="titulo_defesa"', $html);
        $this->assertStringContainsString('name="situacao"', $html);
    }

    public function testSaveRequiresAuthentication(): void
    {
        session()->remove('auth_logged_in');
        $controller = new Admin();
        $controller->initController(service('request'), service('response'), service('logger'));
        $response = $controller->salvarSituacao(130);
        $this->assertStringContainsString('login', $response->getHeaderLine('Location'));
    }
}
