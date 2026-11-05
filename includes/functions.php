<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

// --- FONCTIONS DE BASE ---

function e($str): string {
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}

function current_user_id(): int {
    // Utilisateur unique en mono-user. Prêt pour multi plus tard.
    return 1;
}

function csrf_token(): string {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function verify_csrf(): void {
    $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_POST['csrf'] ?? '');
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (!hash_equals($_SESSION['csrf'] ?? '', $token)) {
        json_error('Token CSRF invalide', 403);
    }
}

function json_ok($data = null): void {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => true, 'data' => $data], JSON_UNESCAPED_UNICODE);
    exit;
}

function json_error(string $msg, int $code = 400): void {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'error' => $msg], JSON_UNESCAPED_UNICODE);
    exit;
}

function read_json_body(): array {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

// --- CALCUL NUTRITIONNEL ---

function recipe_nutrition(int $recipeId): array {
    $stmt = db()->prepare("
        SELECT r.servings,
               COALESCE(SUM(CASE WHEN ri.unit = i.reference_unit THEN ri.quantity * i.calories_per_100/100 ELSE 0 END),0) AS cal,
               COALESCE(SUM(CASE WHEN ri.unit = i.reference_unit THEN ri.quantity * i.protein_per_100/100 ELSE 0 END),0) AS pro,
               COALESCE(SUM(CASE WHEN ri.unit = i.reference_unit THEN ri.quantity * i.carbs_per_100/100 ELSE 0 END),0) AS carb,
               COALESCE(SUM(CASE WHEN ri.unit = i.reference_unit THEN ri.quantity * i.fat_per_100/100 ELSE 0 END),0) AS fat
        FROM recipes r
        LEFT JOIN recipe_ingredients ri ON ri.recipe_id = r.id
        LEFT JOIN ingredients i ON i.id = ri.ingredient_id
        WHERE r.id = ?
        GROUP BY r.id, r.servings
    ");
    $stmt->execute([$recipeId]);
    $row = $stmt->fetch();
    if (!$row) return ['calories'=>0,'protein'=>0,'carbs'=>0,'fat'=>0,'per_serving'=>['calories'=>0,'protein'=>0,'carbs'=>0,'fat'=>0]];
    $s = max(1, (int)$row['servings']);
    return [
        'calories' => round((float)$row['cal'],1),
        'protein'  => round((float)$row['pro'],1),
        'carbs'    => round((float)$row['carb'],1),
        'fat'      => round((float)$row['fat'],1),
        'servings' => $s,
        'per_serving' => [
            'calories' => round((float)$row['cal']/$s,1),
            'protein'  => round((float)$row['pro']/$s,1),
            'carbs'    => round((float)$row['carb']/$s,1),
            'fat'      => round((float)$row['fat']/$s,1),
        ],
    ];
}