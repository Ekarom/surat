<?php
include "../dbconn.php";
$cols = ['nrk', 'nm_pegawai', 'tempat_lahir', 'tgl_lahir', 'jenis_kelamin', 'pendidikan', 'tgl_lulus', 'jabatan', 'pangkat', 'golongan', 'tmt_golongan', 'tmt_pangkat', 'gelar_depan', 'gelar_belakang', 'unit_kerja', 'status_pegawai', 'no_hp', 'email', 'rt', 'rw', 'kelurahan', 'kecamatan', 'status', 'nuptk', 'agama', 'alamat'];
foreach ($cols as $col) {
    $r = mysqli_query($conn, "SHOW COLUMNS FROM pegawai LIKE '$col'");
    echo "$col: " . (mysqli_num_rows($r) > 0 ? 'EXIST' : 'NOT_EXIST') . PHP_EOL;
}
?>
