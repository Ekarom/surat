<?php
session_start();
header('Content-Type: application/json');
include "cfg/konek.php";

$response = ['status' => 'error', 'msg' => 'Terjadi kesalahan sistem.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['file_upload']) && isset($_POST['target_column'])) {
        $file = $_FILES['file_upload'];
        $column = $_POST['target_column'];
        $rotation = isset($_POST['rotation']) ? (int)$_POST['rotation'] : 0;

        // Validasi kolom yang diizinkan untuk keamanan
        $allowed_columns = ['logo_sekolah', 'logo_pemda', 'background_login'];
        if (!in_array($column, $allowed_columns)) {
            echo json_encode(['status' => 'error', 'msg' => 'Kolom tidak valid!']);
            exit;
        }

        $target_dir = "images/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0755, true);
        }

        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $new_file_name = $column . "_" . time() . "." . $file_ext;
        $target_file = $target_dir . $new_file_name;

        // Validasi format
        $allowed_ext = ['jpg', 'jpeg', 'png'];
        if (!in_array($file_ext, $allowed_ext)) {
            echo json_encode(['status' => 'error', 'msg' => 'Format file tidak didukung! (Hanya JPG/PNG)']);
            exit;
        }

        // Proses rotasi jika perlu
        if ($rotation !== 0) {
            $source = null;
            if ($file_ext == 'png') {
                $source = imagecreatefrompng($file['tmp_name']);
            } else {
                $source = imagecreatefromjpeg($file['tmp_name']);
            }

            if ($source) {
                // GD rotate is counter-clockwise, JS degree is clockwise
                $rotated = imagerotate($source, -$rotation, 0);
                
                if ($file_ext == 'png') {
                    imagealphablending($rotated, false);
                    imagesavealpha($rotated, true);
                    imagepng($rotated, $target_file);
                } else {
                    imagejpeg($rotated, $target_file, 90);
                }
                imagedestroy($source);
                imagedestroy($rotated);
            } else {
                move_uploaded_file($file['tmp_name'], $target_file);
            }
        } else {
            if (!move_uploaded_file($file['tmp_name'], $target_file)) {
                echo json_encode(['status' => 'error', 'msg' => 'Gagal memindahkan file upload.']);
                exit;
            }
        }

        // Update Database
        $stmt = $sqlconn->prepare("UPDATE profils SET `$column` = ? WHERE id = 1");
        $stmt->bind_param("s", $new_file_name);
        if ($stmt->execute()) {
            $response = ['status' => 'success', 'msg' => 'Gambar berhasil diupload dan diperbarui!'];
        } else {
            $response = ['status' => 'error', 'msg' => 'Gagal memperbarui database: ' . $sqlconn->error];
        }
    } else {
        $response = ['status' => 'error', 'msg' => 'Data upload tidak lengkap.'];
    }
}

echo json_encode($response);
?>
