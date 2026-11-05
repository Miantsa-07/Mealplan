<?php
// --- TEST DE SÉCURITÉ ---
if (!file_exists(__DIR__ . '/../includes/functions.php')) {
    die("ERREUR : Le fichier functions.php est introuvable dans le dossier includes. Vérifie le nom du fichier.");
}
require_once __DIR__ . '/../includes/functions.php';

if (!function_exists('current_user_id')) {
    die("ERREUR : Le fichier functions.php est lu, mais la fonction current_user_id() n'existe pas. Vérifie que tu as bien copié tout le code dans functions.php.");
}
// ------------------------

$pageTitle = 'Dashboard';
$uid = current_user_id();
$today = date('Y-m-d');
$weekStart = date('Y-m-d', strtotime('monday this week'));
$dayNum = (int)date('N'); // 1=lundi

// Récupérer le plan de la semaine
$stmt = db()->prepare("SELECT id FROM meal_plans WHERE user_id=? AND week_start=?");
$stmt->execute([$uid, $weekStart]);
$planId = $stmt->fetchColumn();

$todayMeals = [];
$weekMealCount = 0;

if ($planId) {
  // Repas du jour
  $stmt = db()->prepare("
    SELECT pm.slot, r.name, r.image_path, r.servings AS recipe_servings, pm.servings
    FROM planned_meals pm
    JOIN recipes r ON r.id = pm.recipe_id
    WHERE pm.meal_plan_id = ? AND pm.day_of_week = ?
  ");
  $stmt->execute([$planId, $dayNum]);
  $todayMeals = $stmt->fetchAll();

  // Nombre de repas dans la semaine
  $stmt = db()->prepare("SELECT COUNT(*) FROM planned_meals WHERE meal_plan_id=?");
  $stmt->execute([$planId]);
  $weekMealCount = (int)$stmt->fetchColumn();
}

// Total calories du jour (recalcul propre)
$todayCalories = 0;
if ($planId) {
  $stmt = db()->prepare("
    SELECT pm.recipe_id, pm.servings AS plan_servings, r.servings AS recipe_servings
    FROM planned_meals pm 
    JOIN recipes r ON r.id = pm.recipe_id
    WHERE pm.meal_plan_id = ? AND pm.day_of_week = ?
  ");
  $stmt->execute([$planId, $dayNum]);
  
  foreach ($stmt->fetchAll() as $row) {
    $n = recipe_nutrition((int)$row['recipe_id']);
    $todayCalories += $n['per_serving']['calories'] * $row['plan_servings'];
  }
}

// Objectif calorique
$goal = db()->prepare("SELECT calories_goal FROM nutrition_goals WHERE user_id=?");
$goal->execute([$uid]);
$calGoal = (int)($goal->fetchColumn() ?: 2000);

require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/sidebar.php';
?>

<h1>Bonjour 👋</h1>
<p class="muted"><?= date('l j F Y') ?></p>

<div class="grid grid-3" style="margin-top:24px">
  <div class="card">
    <p class="muted">Calories aujourd'hui</p>
    <h2><?= round($todayCalories) ?> / <?= $calGoal ?> kcal</h2>
  </div>
  <div class="card">
    <p class="muted">Repas planifiés cette semaine</p>
    <h2><?= $weekMealCount ?> repas</h2>
  </div>
  <div class="card">
    <p class="muted">Repas aujourd'hui</p>
    <h2><?= count($todayMeals) ?> repas</h2>
  </div>
</div>

<div class="card" style="margin-top:24px">
  <h3>Repas du jour</h3>
  <?php if (empty($todayMeals)): ?>
    <p class="muted">Aucun repas planifié. Va dans <a href="planner.php">Weekly Planner</a>.</p>
  <?php else: ?>
    <ul style="list-style:none; padding:0; margin:0;">
      <?php foreach ($todayMeals as $m): ?>
        <li style="padding: 10px 0; border-bottom: 1px solid var(--beige);">
          <strong style="text-transform: capitalize;"><?= e($m['slot']) ?></strong> 
          — <?= e($m['name']) ?>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>