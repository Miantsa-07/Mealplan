<?php
$current = basename($_SERVER['PHP_SELF'], '.php');
$nav = [
  'dashboard'   => ['Dashboard', 'home'],
  'planner'     => ['Weekly Planner', 'calendar'],
  'recipes'     => ['My Recipes', 'book-open'],
  'ingredients' => ['Ingredients', 'carrot'],
  'shopping'    => ['Shopping List', 'shopping-bag'],
  'nutrition'   => ['Nutrition', 'activity'],
  'pantry'      => ['My Pantry', 'package'],
  'budget'      => ['Budget', 'wallet'],
  'history'     => ['History', 'history'],
  'settings'    => ['Settings', 'settings'],
];
?>
<aside class="sidebar">
  <div class="brand">
    <span class="brand-dot"></span>
    <span class="brand-name">Meal Planner</span>
  </div>
  <nav class="nav">
    <?php foreach ($nav as $slug => [$label, $icon]): ?>
      <a href="http://localhost/xamp/Mealplan/pages/<?= $slug ?>.php"
         class="nav-link <?= $current === $slug ? 'active' : '' ?>">
        <i data-lucide="<?= $icon ?>"></i>
        <span><?= e($label) ?></span>
      </a>
    <?php endforeach; ?>
  </nav>
</aside>
<main class="main-content">