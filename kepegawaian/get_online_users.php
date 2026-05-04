<?php
/**
 * AJAX Handler for Online Users
 * Returns JSON data for real-time dashboard updates
 */

$db_path = file_exists('../dbconn.php') ? '../dbconn.php' : 'dbconn.php';
include_once $db_path;

if (!isset($conn) || !$conn) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

$limit = 5;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

$online_users = [];

// 1. Fetch from tb_user (Admin/Staff)
$res_admin = $conn->query(
    "SELECT nama as nm_user, last_activity, level
     FROM tb_user
     WHERE last_activity > DATE_SUB(NOW(), INTERVAL 5 MINUTE)
     AND (status = '1' OR status = 'Aktif')
     ORDER BY last_activity DESC"
);

$level_map = [
    '1' => ['label' => 'ADMIN', 'color' => 'bg-success'],
    '2' => ['label' => 'STAFF', 'color' => 'bg-success'],
    '4' => ['label' => 'GURU', 'color' => 'bg-success'],
];

if ($res_admin) {
    while ($row = $res_admin->fetch_assoc()) {
        $lvl = $level_map[$row['level']] ?? ['label' => 'USER', 'color' => 'bg-secondary'];
        $online_users[] = [
            'nama' => $row['nm_user'],
            'last_time' => date('H:i', strtotime($row['last_activity'])),
            'role_label' => $lvl['label'],
            'badge_color' => $lvl['color'],
            'timestamp' => strtotime($row['last_activity'])
        ];
    }
}

// 2. Fetch from pegawai (Guru)
$res_guru = $conn->query(
    "SELECT nm_pegawai, last_activity
     FROM pegawai
     WHERE last_activity > DATE_SUB(NOW(), INTERVAL 5 MINUTE)
     AND (status = '1' OR status = 'Aktif')
     ORDER BY last_activity DESC"
);

if ($res_guru) {
    while ($row = $res_guru->fetch_assoc()) {
        $online_users[] = [
            'nama' => $row['nm_pegawai'],
            'last_time' => date('H:i', strtotime($row['last_activity'])),
            'role_label' => 'GURU',
            'badge_color' => 'bg-success',
            'timestamp' => strtotime($row['last_activity'])
        ];
    }
}

// Sort and Paginate
usort($online_users, fn($a, $b) => $b['timestamp'] - $a['timestamp']);
$total_count = count($online_users);
$total_pages = max(1, ceil($total_count / $limit));
$paginated_users = array_slice($online_users, $offset, $limit);

header('Content-Type: application/json');
echo json_encode([
    'total' => $total_count,
    'users' => $paginated_users,
    'pages' => $total_pages,
    'current' => (int)$page
]);
