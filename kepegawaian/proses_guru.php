<?php
session_start();
include "../dbconn.php";

header('Content-Type: application/json');

if (!isset($_SESSION['authenticated']) || $_SESSION['level'] != '4') {
    echo json_encode(['status' => 'error', 'message' => 'Akses ditolak.']);
    exit;
}

$action = $_REQUEST['action'] ?? '';
$nik_user = $_SESSION['nik'] ?? '';

if (empty($nik_user)) {
    echo json_encode(['status' => 'error', 'message' => 'Data NIK tidak ditemukan di sesi.']);
    exit;
}

// Get pegawai ID based on NIK
$stmt_peg = $conn->prepare("SELECT id FROM pegawai WHERE nip = ?");
$stmt_peg->bind_param("s", $nik_user);
$stmt_peg->execute();
$peg_res = $stmt_peg->get_result()->fetch_assoc();
if (!$peg_res) {
    echo json_encode(['status' => 'error', 'message' => 'Data profil pegawai tidak ditemukan.']);
    exit;
}
$pegawai_id = $peg_res['id'];

// --- UPDATE DATA PRIBADI ---
if ($action == 'update_profil') {
    $nm_pegawai = $_POST['nm_pegawai'];
    $tempat_lahir = $_POST['tempat_lahir'];
    $tgl_lahir = $_POST['tgl_lahir'];
    $jenis_kelamin = $_POST['jenis_kelamin'];
    $pendidikan = $_POST['pendidikan'];
    $no_hp = $_POST['no_hp'];
    $email = $_POST['email'];

    $stmt = $conn->prepare("UPDATE pegawai SET nm_pegawai=?, tempat_lahir=?, tgl_lahir=?, jenis_kelamin=?, pendidikan=?, no_hp=?, email=? WHERE id=?");
    $stmt->bind_param("sssssssi", $nm_pegawai, $tempat_lahir, $tgl_lahir, $jenis_kelamin, $pendidikan, $no_hp, $email, $pegawai_id);

    if ($stmt->execute()) {
        // Update session name if changed
        $_SESSION['nama'] = $nm_pegawai;
        echo json_encode(['status' => 'success', 'message' => 'Profil berhasil diperbarui!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal memperbarui profil: ' . $stmt->error]);
    }
    exit;
}

// --- UPLOAD RIWAYAT ---
if ($action == 'simpan_riwayat') {
    $kategori = $_POST['kategori'];
    $deskripsi = $_POST['deskripsi'];
    $tmt = !empty($_POST['tmt']) ? $_POST['tmt'] : null;
    $no_sk = $_POST['no_sk'];
    $tgl_sk = !empty($_POST['tgl_sk']) ? $_POST['tgl_sk'] : null;

    $file_lampiran = null;
    if (isset($_FILES['file_lampiran']) && $_FILES['file_lampiran']['error'] == 0) {
        $target_dir = "../file/datakepegawaian/";
        if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
        
        $ext = pathinfo($_FILES['file_lampiran']['name'], PATHINFO_EXTENSION);
        $new_name = 'sk_guru_' . time() . '_' . rand(100, 999) . '.' . $ext;
        if (move_uploaded_file($_FILES['file_lampiran']['tmp_name'], $target_dir . $new_name)) {
            $file_lampiran = $new_name;
        }
    }

    $stmt = $conn->prepare("INSERT INTO riwayat_kepegawaian (pegawai_id, kategori, deskripsi, tmt, no_sk, tgl_sk, file_lampiran) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssss", $pegawai_id, $kategori, $deskripsi, $tmt, $no_sk, $tgl_sk, $file_lampiran);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Riwayat berhasil ditambahkan!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan riwayat.']);
    }
    exit;
}

// --- MUAT RIWAYAT ---
if ($action == 'muat_riwayat') {
    $stmt = $conn->prepare("SELECT * FROM riwayat_kepegawaian WHERE pegawai_id = ? ORDER BY tmt ASC");
    $stmt->bind_param("i", $pegawai_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    echo json_encode(['status' => 'success', 'data' => $data]);
    exit;
}
?>
