<?php
$title = $title ?? 'CyraCRIS';
$description = $description ?? 'Current Research Information System para programas de pós-graduação.';
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#071a36">
    <meta name="description" content="<?= esc($description, 'attr') ?>">
    <title><?= esc($title) ?> | CyraCRIS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --cyra-navy: #071a36;
            --cyra-blue: #1266b1;
            --cyra-cyan: #17bdc5;
            --cyra-ice: #edf7fb;
            --cyra-muted: #aac1d4;
        }

        body {
            min-width: 320px;
            color: var(--cyra-ice);
            background: var(--cyra-navy);
            font-family: "Trebuchet MS", "Segoe UI", sans-serif;
        }

        .cyra-page {
            min-height: 100vh;
            background: radial-gradient(circle at 90% 8%, rgba(23, 189, 197, .18), transparent 28rem), linear-gradient(135deg, #061328, #0b426b);
        }

        .cyra-navbar { background: rgba(6, 19, 40, .84); backdrop-filter: blur(14px); }
        .cyra-brand { display: inline-flex; align-items: center; padding: 5px 12px; border-radius: 3px; background: #f4fbff; }
        .cyra-logo { display: block; width: min(210px, 48vw); height: auto; }
        .cyra-link { color: var(--cyra-muted); }
        .cyra-link:hover, .cyra-link:focus { color: var(--cyra-cyan); }
        .cyra-heading { font-family: Georgia, "Times New Roman", serif; letter-spacing: 0; }
        .cyra-accent { color: var(--cyra-cyan); }
        .cyra-muted { color: var(--cyra-muted); }
        .cyra-panel { border: 1px solid rgba(151, 205, 225, .2); background: rgba(5, 19, 40, .42); }
        .cyra-footer { border-top: 1px solid rgba(151, 205, 225, .18); background: rgba(3, 12, 27, .3); }
    </style>
</head>
<body>
<div class="cyra-page d-flex flex-column">
<?= view('layout/navbar', ['fluid' => $fluid ?? false]) ?>
