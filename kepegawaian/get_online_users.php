<?php
/**
 * AJAX Handler for fetching online users
 * Optimized for Dashboard Kepegawaian
 * Managed by Antigravity AI
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');

if (!isset($conn) || !$conn) {
    $db_path = file_exists('../dbconn.php') ? '../dbconn.php' : 'dbconn.php';
    include_once $db_path;
}

if (!isset($conn) || !$conn) {
    echo json_encode(['error' => 'Koneksi database tidak tersedia.']);
    exit;
}

// Configuration
$online_limit_minutes = 15; // Consider users active in the last 15 mins as "online"
$limit = 5;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

/**
 * 1. Build Query
 * We UNION users from tb_user and pegawai (PTK) who have recent activity
 */

// User Admin / Staff
$sql_users = "SELECT 
    nama, 
    level, 
    last_activity,
    'admin' as source
FROM tb_user 
WHERE last_activity > (NOW() - INTERVAL $online_limit_minutes MINUTE)";

// PTK / Employee
$sql_pegawai = "SELECT 
    nm_pegawai as nama, 
    '4' as level, 
    last_activity,
    'pegawai' as source
FROM pegawai 
WHERE last_activity > (NOW() - INTERVAL $online_limit_minutes MINUTE)";

$combined_sql = "($sql_users) UNION ($sql_pegawai) ORDER BY last_activity DESC";

// Get Total for Pagination
$count_res = $conn->query("SELECT COUNT(*) FROM ($combined_sql) as total_tbl");
$total_online = $count_res ? $count_res->fetch_row()[0] : 0;
$total_pages = max(1, ceil($total_online / $limit));

// Fetch Paginated Data
$final_sql = "$combined_sql LIMIT $limit OFFSET $offset";
$res = $conn->query($final_sql);

$users = [];
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $role_label = 'User';
        $badge_color = 'bg-secondary';

        if ($row['source'] === 'admin') {
            if ($row['level'] == '1') {
                $role_label = 'Administrator';
                $badge_color = 'bg-danger';
            } elseif ($row['level'] == '2') {
                $role_label = 'Staff Admin';
                $badge_color = 'bg-warning';
            } else {
                $role_label = 'Staff';
                $badge_color = 'bg-info';
            }
        } else {
            $role_label = 'Guru / PTK';
            $badge_color = 'bg-success';
        }

        // Format relative time or simple time
        $last_time = "Baru saja";
        if ($row['last_activity']) {
            $last_time = date('H:i', strtotime($row['last_activity']));
        }

        $users[] = [
            'nama' => htmlspecialchars($row['nama']),
            'role_label' => $role_label,
            'badge_color' => $badge_color,
            'last_time' => $last_time
        ];
    }
}

// Response
echo json_encode([
    'total' => $total_online,
    'users' => $users,
    'pages' => $total_pages,
    'current' => $page,
    'status' => 'success'
]);
