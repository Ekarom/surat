<?php
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST = [
    'nip' => '123456789',
    'nrk' => '987654321',
    'nama' => 'Test Employee',
    'tempat_lahir' => 'Jakarta',
    'tgl_lahir' => '1990-01-01',
    'jenis_kelamin' => 'L',
    'pendidikan' => 'S1',
    'tgl_lulus' => '2012-05-20',
    'jabatan' => 'Guru',
    'pangkat' => 'Penata',
    'golongan' => 'III/c',
    'tmt_golongan' => '2020-10-01',
    'tmt_pangkat' => '2021-01-01',
    'gelar_depan' => 'Drs.',
    'gelar_belakang' => 'M.Pd',
    'unit_kerja' => 'SMP Test',
    'status_pegawai' => 'PNS',
    'nuptk' => '1122334455',
    'agama' => 'Islam',
    'alamat' => 'Jl. Test No. 1',
    'rt' => '01',
    'rw' => '02',
    'kelurahan' => 'Test Kel',
    'kecamatan' => 'Test Kec',
    'no_hp' => '0812345678',
    'email' => 'test@example.com'
];

// Include the process file
// We need to handle the ob_start() and exit() in the file
ob_start();
try {
    include __DIR__ . "/../proses_import_excel.php";
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage();
}
$output = ob_get_clean();
echo "Output: " . $output . PHP_EOL;
?>
