<?php
session_start();

// Konfigurasi Header JSON
header('Content-Type: application/json');
$response = array();

// Cek Koneksi Database
if (file_exists("dbconn.php")) {
    include "dbconn.php";
} else {
    echo json_encode(['status' => 'error', 'message' => 'File database tidak ditemukan.']);
    exit;
}

// Cek Login User (PENTING: Jangan default ke ID 1)
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Sesi habis, silakan login kembali.']);
    exit;
}

$userId = $_SESSION['user_id'];

// Konfigurasi Folder
// PERBAIKAN: Tambahkan '/' di akhir path
$target_dir = "file/profil/"; 

// Buat folder jika belum ada (Permission 0755 lebih aman daripada 0777)
if (!file_exists($target_dir)) {
    mkdir($target_dir, 0755, true);
}

// Cek Input File
if (isset($_FILES['photo1'])) {
    $file = $_FILES['photo1'];
    
    $fileName = $file['name'];
    $fileTmpName = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileError = $file['error'];
    
    // Ambil ekstensi
    $fileExt = explode('.', $fileName);
    $fileActualExt = strtolower(end($fileExt));

    // Validasi Tipe File
    $allowed = array('jpg', 'jpeg', 'png', 'gif');

    if (in_array($fileActualExt, $allowed)) {
        if ($fileError === 0) {
            if ($fileSize < 2097152) { // Max 2MB
                
                // Cek apakah file benar-benar gambar (Security extra)
                $checkImage = getimagesize($fileTmpName);
                if($checkImage === false) {
                     echo json_encode(['status' => 'error', 'message' => 'File bukan gambar valid.']);
                     exit;
                }

                // Generate Nama Baru
                $fileNameNew = uniqid('', true) . "." . $fileActualExt;
                $fileDestination = $target_dir . $fileNameNew;

                // Pindahkan File
                if (move_uploaded_file($fileTmpName, $fileDestination)) {
                    
                    // --- LOGIKA BARU: Hapus Foto Lama ---
                    // 1. Ambil nama foto lama dari database
                    $queryGetOld = "SELECT poto FROM tb_user WHERE id = ?";
                    $stmtGetOld = mysqli_prepare($conn, $queryGetOld);
                    
                    if ($stmtGetOld) {
                        mysqli_stmt_bind_param($stmtGetOld, "i", $userId);
                        mysqli_stmt_execute($stmtGetOld);
                        $resultOld = mysqli_stmt_get_result($stmtGetOld);
                        $rowOld = mysqli_fetch_assoc($resultOld);
                        
                        // 2. Cek dan hapus file fisik
                        if ($rowOld) {
                            $oldPhotoName = $rowOld['poto'];
                            $oldPhotoPath = $target_dir . $oldPhotoName;
                            
                            // Pastikan file ada, namanya tidak kosong, dan BUKAN file default (sesuaikan 'default.png' jika ada)
                            if (!empty($oldPhotoName) && file_exists($oldPhotoPath) && $oldPhotoName != 'default.png') {
                                unlink($oldPhotoPath); // Hapus file lama
                            }
                        }
                        mysqli_stmt_close($stmtGetOld);
                    }
                    // ------------------------------------

                    // PERBAIKAN: Gunakan Prepared Statement untuk mencegah SQL Injection
                    $query = "UPDATE tb_user SET poto = ? WHERE id = ?";
                    $stmt = mysqli_prepare($conn, $query);
                    
                    if ($stmt) {
                        mysqli_stmt_bind_param($stmt, "si", $fileNameNew, $userId);
                        
                        if (mysqli_stmt_execute($stmt)) {
                            $response['status'] = 'success';
                            $response['message'] = 'Upload berhasil!';
                            $response['url'] = $fileDestination;
                        } else {
                            $response['status'] = 'error';
                            $response['message'] = 'Gagal update database: ' . mysqli_error($conn);
                        }
                        mysqli_stmt_close($stmt);
                    } else {
                         $response['status'] = 'error';
                         $response['message'] = 'Query error.';
                    }

                } else {
                    $response['status'] = 'error';
                    $response['message'] = 'Gagal memindahkan file (Permission issue?).';
                }

            } else {
                $response['status'] = 'error';
                $response['message'] = 'Ukuran file terlalu besar! (Maks 2MB)';
            }
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Error upload code: ' . $fileError;
        }
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Ekstensi file tidak diizinkan.';
    }
} else {
    $response['status'] = 'error';
    $response['message'] = 'Tidak ada file yang dipilih.';
}

echo json_encode($response);
?>