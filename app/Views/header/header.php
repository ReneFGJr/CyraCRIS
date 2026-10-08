<?php
$title = $title ?? 'CyraCRIS';
$description = $description ?? 'Current Research Information System para programas de pós-graduação.';
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f7f3ea">
    <meta name="description" content="<?= esc($description, 'attr') ?>">
    <link rel="icon" href="<?= base_url('favicon.ico') ?>?v=<?= filemtime(FCPATH . 'favicon.ico') ?>" type="image/png">
    <title><?= esc($title) ?> | CyraCRIS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700&display=swap">
    <link rel="stylesheet" href="<?= base_url('assets/style.css') ?>?v=<?= filemtime(FCPATH . 'assets/style.css') ?>">
</head>
<body>
<div class="cyra-page d-flex flex-column">
<?= view('layout/navbar', ['fluid' => $fluid ?? false]) ?>
