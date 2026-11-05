<?php
require_once __DIR__ . '/functions.php';
$pageTitle = $pageTitle ?? 'Meal Planner';
?>
<!DOCTYPE html>
<html lang="fr" data-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle) ?> — Meal Planner</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Lien CSS en dur pour être sûr qu'il soit trouvé -->
<link href="http://localhost/xamp/Mealplan/assets/css/style.css" rel="stylesheet">
<script src="https://unpkg.com/lucide@latest"></script>
<meta name="csrf-token" content="<?= csrf_token() ?>">
</head>
<body>
<div class="app-shell">