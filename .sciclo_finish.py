exec(Path('D:/www/CyraCRIS/.sciclo_workflow.py').read_text(encoding='utf-8').split("put('app/Database")[0]) if False else None
from pathlib import Path
root = Path('D:/www/Sciclo')
def edit(path, old, new):
    p = root / path
    t = p.read_text(encoding='utf-8')
    assert old in t, path
    p.write_text(t.replace(old, new, 1), encoding='utf-8')
edit('app/Controllers/Repositories.php', "            'submitted' => ['title'", "            'revisions_required' => ['title' => 'Revisões necessárias', 'badge' => 'bg-warning text-dark', 'items' => []],\n            'accepted' => ['title' => 'Aceitos para avaliação', 'badge' => 'bg-info text-dark', 'items' => []],\n            'submitted' => ['title'")
edit('app/Controllers/Repositories.php', "->select('id, repository_name, base_url, repository_type, created_at, updated_at, submitted_at, seal_data_avaliation')", "->select('*')")
edit('app/Controllers/Repositories.php', "            $groups[$key]['items'][] = $repository;", "            if (!$evaluated && in_array($repository['workflow_status'] ?? '', ['revisions_required', 'accepted'], true)) $key = $repository['workflow_status'];\n            $groups[$key]['items'][] = $repository;")
edit('app/Controllers/Application.php', "        return view('application/application_select_questionnaire', $data);", "        $data['revision'] = ($data['repo']['workflow_status'] ?? '') === 'revisions_required' ? (new \\App\\Models\\RepositoryMovementModel())->where('oai_pmh_id', (int) $id)->where('to_status', 'revisions_required')->orderBy('id', 'DESC')->first() : null;\n        return view('application/application_select_questionnaire', $data);")
edit('app/Views/application/application_select_questionnaire.php', '<div class="container py-5">', '<div class="container py-5">\n    <?php if (!empty($revision)): ?><div class="alert alert-warning"><h2 class="h5">Revisões necessárias</h2><p class="mb-2"><?= nl2br(esc($revision[\'details\'])) ?></p><p class="mb-0">Complete as informações e confirme o reenvio para avaliação ao concluir.</p></div><?php endif ?>')
# Fix a misleading comment and retain completed status in the profile.
edit('app/Controllers/Auth.php', '                // Workflow decisions take precedence over individual evaluator assignments.', '            // Workflow decisions take precedence over individual evaluator assignments.')
edit('app/Controllers/Auth.php', "            foreach ($repositories as &$repository) {\n                if (($repository['workflow_status']", "            foreach ($repositories as &$repository) {\n                if (!empty($repository['seal_data_avaliation']) && $repository['seal_data_avaliation'] !== '0000-00-00 00:00:00') continue;\n                if (($repository['workflow_status']")
(root / 'tests/repository_workflow_smoke.php').write_text(r'''<?php
ob_start();
require __DIR__ . '/repositories_smoke.php';
require APPPATH . 'Database/Migrations/20261026120000_create_repository_movements.php';
$migration = new App\Database\Migrations\CreateRepositoryMovements($forge);
$migration->up();
use App\Libraries\RepositoryWorkflow as Workflow;
$repos = new App\Models\Oai_pmh\OaiPmhModel();
$history = new App\Models\RepositoryMovementModel();
$repo = $repos->where('repository_name', 'Submitted marker')->first();
$original = $repo;
$id = Workflow::move($repo, 'accepted', $admin);
check(Workflow::status($repos->find($repo['id'])) === 'accepted', 'Acceptance saved');
check($history->find($id)['actor_id'] == $admin, 'Actor recorded');
$blocked = false;
try { Workflow::move($original, 'accepted', $admin); } catch (DomainException $e) { $blocked = true; }
check($blocked && $history->countAllResults() === 1, 'Stale transition leaves no history');
$repo = $repos->find($repo['id']);
$id = Workflow::move($repo, 'revisions_required', $admin, '<script>unsafe</script>' . "\nComplete os dados", 'origin@example.test');
check($history->find($id)['email_status'] === 'pending', 'Notification pending in audit');
$repo = $repos->find($repo['id']);
check($repo['submitted_at'] === $original['submitted_at'], 'Original submission preserved on return');
$app = controller(App\Controllers\Application::class, 'POST', ['question_id' => 0]);
$guard = new ReflectionMethod($app, 'editingDenied');
check(!$guard->invoke($app, (int) $repo['id']), 'Revisions reopen editing');
Workflow::move($repo, 'submitted', $admin);
check($guard->invoke($app, (int) $repo['id']), 'Resubmission locks editing');
check($history->countAllResults() === 3, 'All transitions recorded');
$html = view('emails/repository_revision', ['repository' => $repo, 'movement' => $history->find($id), 'cid' => 'sciclo-logo', 'url' => 'http://sciclo/application/form/select/' . $repo['id']]);
check(str_contains($html, 'cid:sciclo-logo') && !str_contains($html, '<script>unsafe</script>') && str_contains($html, '&lt;script&gt;'), 'Embedded logo and escaped details');
check(str_contains($html, 'Completar dados do repositório'), 'Email contains action');
$render = view('repositories/workflow', ['repository' => $repos->find($repo['id']), 'movements' => $history->findAll()]);
check(str_contains($render, 'Aceitar para avaliação') && str_contains($render, 'revision-details'), 'Actions and textbox render');
config('Email')->fromEmail = '';
check(!Workflow::notify($id), 'Missing mail configuration fails safely without sending');
check($history->find($id)['email_status'] === 'failed', 'Email failure auditable');
check($history->countAllResults() === 3, 'Failed mail does not duplicate movement');
$migration->down();
check(!$db->tableExists('repository_movements'), 'History migration rollback');
ob_end_clean();
echo "PASS: workflow transitions, stale requests, reopening, resubmission, audit, safe email failure and HTML escaping (SQLite memory).\n";
''', encoding='utf-8')
print('Workflow refinements and test written.')
