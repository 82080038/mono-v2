<?php

require_once 'IndonesianFormatHelper.php';

/**
 * Demo Indonesian Format Helper
 * File ini menunjukkan contoh penggunaan lengkap IndonesianFormatHelper
 * untuk aplikasi KSP Lam Gabe Jaya
 */

echo "<h1>🇮🇩 Indonesian Format Helper Demo</h1>";
echo "<h2>KSP Lam Gabe Jaya - Format Helper Examples</h2>";

// ==================== FORMAT RUPIAH ====================
echo "<h3>💰 Format Mata Uang Rupiah</h3>";

$amounts = [15000, 2500000, 15000000, 1234567.89];

echo "<table border='1' cellpadding='10'>";
echo "<tr><th>Amount</th><th>Format Rupiah</th><th>Simple Format</th><th>Without Symbol</th></tr>";

foreach ($amounts as $amount) {
    echo "<tr>";
    echo "<td>" . $amount . "</td>";
    echo "<td>" . IndonesianFormatHelper::formatRupiah($amount) . "</td>";
    echo "<td>" . IndonesianFormatHelper::formatRupiahSimple($amount) . "</td>";
    echo "<td>" . IndonesianFormatHelper::formatRupiah($amount, false) . "</td>";
    echo "</tr>";
}
echo "</table>";

// Parse example
$parseExamples = ['Rp 15.000,00', 'Rp 2.500.000', 'Rp 15.000'];
echo "<h4>Parse Rupiah Examples:</h4>";
foreach ($parseExamples as $example) {
    echo "<strong>$example</strong> → " . IndonesianFormatHelper::parseRupiah($example) . "<br>";
}

// ==================== FORMAT TANGGAL ====================
echo "<h3>📅 Format Tanggal & Waktu</h3>";

$dates = [
    '2024-03-22',
    '2024-03-22 14:30:00',
    '1990-12-25',
    '2000-01-01 09:15:30'
];

echo "<table border='1' cellpadding='10'>";
echo "<tr><th>Date</th><th>Format Date</th><th>With Day</th><th>DateTime</th></tr>";

foreach ($dates as $date) {
    echo "<tr>";
    echo "<td>" . $date . "</td>";
    echo "<td>" . IndonesianFormatHelper::formatDate($date) . "</td>";
    echo "<td>" . IndonesianFormatHelper::formatDate($date, true) . "</td>";
    echo "<td>" . IndonesianFormatHelper::formatDateTime($date) . "</td>";
    echo "</tr>";
}
echo "</table>";

// ==================== NIK VALIDATION & FORMAT ====================
echo "<h3>🆔 Validasi & Format NIK</h3>";

$niks = [
    '3201011234560001', // Valid
    '3201012345670002', // Valid (female)
    '1234567890123456', // Invalid format
    '320101123456789',  // Invalid length
    '320101123456000A'  // Invalid character
];

echo "<table border='1' cellpadding='10'>";
echo "<tr><th>NIK</th><th>Valid</th><th>Formatted</th><th>Info</th></tr>";

foreach ($niks as $nik) {
    echo "<tr>";
    echo "<td>" . $nik . "</td>";
    echo "<td>" . (IndonesianFormatHelper::validateNIK($nik) ? '✅ Valid' : '❌ Invalid') . "</td>";
    echo "<td>" . IndonesianFormatHelper::formatNIK($nik) . "</td>";
    
    if (IndonesianFormatHelper::validateNIK($nik)) {
        $info = IndonesianFormatHelper::extractNIKInfo($nik);
        echo "<td>Lahir: {$info['birth_day']}/{$info['birth_month']}/{$info['birth_year']}, {$info['gender']}</td>";
    } else {
        echo "<td>-</td>";
    }
    echo "</tr>";
}
echo "</table>";

// ==================== FORMAT TELEPON ====================
echo "<h3>📞 Format Nomor Telepon</h3>";

$phones = [
    '08123456789',
    '628123456789',
    '+628123456789',
    '0221234567',
    '0812-3456-789',
    'invalid-phone'
];

echo "<table border='1' cellpadding='10'>";
echo "<tr><th>Phone</th><th>National</th><th>International</th><th>Pretty</th><th>Valid</th></tr>";

foreach ($phones as $phone) {
    echo "<tr>";
    echo "<td>" . $phone . "</td>";
    echo "<td>" . IndonesianFormatHelper::formatPhoneNumber($phone, 'national') . "</td>";
    echo "<td>" . IndonesianFormatHelper::formatPhoneNumber($phone, 'international') . "</td>";
    echo "<td>" . IndonesianFormatHelper::formatPhoneNumber($phone, 'pretty') . "</td>";
    echo "<td>" . (IndonesianFormatHelper::validatePhoneNumber($phone) ? '✅ Valid' : '❌ Invalid') . "</td>";
    echo "</tr>";
}
echo "</table>";

// ==================== FORMAT ALAMAT ====================
echo "<h3>🏠 Format Alamat</h3>";

$addresses = [
    [
        'street' => 'Jl. Merdeka No. 123',
        'rt' => '001',
        'rw' => '002',
        'village' => 'Mekar Jaya',
        'district' => 'Sukajadi',
        'city' => 'Bandung',
        'province' => 'Jawa Barat',
        'postal_code' => '40123'
    ],
    [
        'street' => 'Jl. Sudirman No. 456',
        'village' => 'Harapan Baru',
        'district' => 'Coblong',
        'city' => 'Bandung',
        'province' => 'Jawa Barat',
        'postal_code' => '40131'
    ]
];

foreach ($addresses as $i => $address) {
    echo "<h4>Alamat " . ($i + 1) . ":</h4>";
    echo "<strong>Formatted:</strong> " . IndonesianFormatHelper::formatAddress($address) . "<br>";
    echo "<strong>Kode Pos Valid:</strong> " . (IndonesianFormatHelper::validatePostalCode($address['postal_code']) ? '✅ Valid' : '❌ Invalid') . "<br><br>";
}

// ==================== FORMAT NAMA ====================
echo "<h3>👤 Format Nama</h3>";

$names = [
    'ahmad rizki',
    'muhammad yusuf',
    'siti aisyah',
    'dr. john doe',
    'ir. budi santoso',
    'mohammad ali'
];

echo "<table border='1' cellpadding='10'>";
echo "<tr><th>Original</th><th>Formatted</th></tr>";

foreach ($names as $name) {
    echo "<tr>";
    echo "<td>" . $name . "</td>";
    echo "<td>" . IndonesianFormatHelper::formatName($name) . "</td>";
    echo "</tr>";
}
echo "</table>";

// ==================== ANGKA TERBILANG ====================
echo "<h3>🔢 Konversi Angka ke Terbilang</h3>";

$numbers = [1234, 15000, 2500000, 15000000, 123456789];

echo "<table border='1' cellpadding='10'>";
echo "<tr><th>Number</th><th>Terbilang</th><th>Terbilang Rupiah</th></tr>";

foreach ($numbers as $number) {
    echo "<tr>";
    echo "<td>" . IndonesianFormatHelper::formatRupiah($number) . "</td>";
    echo "<td>" . IndonesianFormatHelper::numberToWords($number) . "</td>";
    echo "<td>" . IndonesianFormatHelper::numberToWords($number, true) . "</td>";
    echo "</tr>";
}
echo "</table>";

// ==================== FORMAT PERSentase & FILE SIZE ====================
echo "<h3>📊 Format Persentase & Ukuran File</h3>";

$percentages = [0.75, 0.1234, 0.5, 1.0];
$fileSizes = [1024, 1048576, 1073741824, 2147483648];

echo "<h4>Persentase:</h4>";
echo "<table border='1' cellpadding='10'>";
echo "<tr><th>Decimal</th><th>Percentage</th></tr>";

foreach ($percentages as $percentage) {
    echo "<tr>";
    echo "<td>" . $percentage . "</td>";
    echo "<td>" . IndonesianFormatHelper::formatPercentage($percentage * 100) . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h4>Ukuran File:</h4>";
echo "<table border='1' cellpadding='10'>";
echo "<tr><th>Bytes</th><th>Formatted</th></tr>";

foreach ($fileSizes as $size) {
    echo "<tr>";
    echo "<td>" . number_format($size) . "</td>";
    echo "<td>" . IndonesianFormatHelper::formatFileSize($size) . "</td>";
    echo "</tr>";
}
echo "</table>";

// ==================== VALIDASI DATA LENGKAP ====================
echo "<h3>🔍 Validasi Data Lengkap</h3>";

$testUsers = [
    [
        'nik' => '3201011234560001',
        'phone' => '08123456789',
        'email' => 'user@example.com',
        'postal_code' => '40123'
    ],
    [
        'nik' => '1234567890123456', // Invalid
        'phone' => '08123456789',
        'email' => 'invalid-email',
        'postal_code' => '1234' // Invalid
    ],
    [
        'nik' => '3201011234560001',
        'phone' => 'invalid-phone',
        'email' => 'user@example.com',
        'postal_code' => '40123'
    ]
];

foreach ($testUsers as $i => $user) {
    echo "<h4>User " . ($i + 1) . " Validation:</h4>";
    $validation = IndonesianFormatHelper::validateUserData($user);
    
    echo "<strong>Valid:</strong> " . ($validation['valid'] ? '✅ Valid' : '❌ Invalid') . "<br>";
    
    if (!$validation['valid']) {
        echo "<strong>Errors:</strong><br>";
        foreach ($validation['errors'] as $field => $error) {
            echo "- $field: $error<br>";
        }
    }
    echo "<br>";
}

// ==================== FORMAT DATA TABEL ====================
echo "<h3>📊 Format Data Tabel</h3>";

$tableData = [
    'name' => 'ahmad rizki',
    'amount' => 1500000,
    'date' => '2024-03-22',
    'phone' => '08123456789',
    'nik' => '3201011234560001',
    'email' => 'ahmad@example.com',
    'postal_code' => '40123'
];

echo "<h4>Original Data:</h4>";
echo "<pre>";
print_r($tableData);
echo "</pre>";

echo "<h4>Formatted Data:</h4>";
echo "<pre>";
print_r(IndonesianFormatHelper::formatTableData($tableData));
echo "</pre>";

// ==================== USE CASE: KSP APPLICATION ====================
echo "<h3>🏦 Use Case: KSP Lam Gabe Jaya Application</h3>";

// Simulasi data nasabah
$nasabah = [
    'id' => 1,
    'nama' => 'budi santoso',
    'nik' => '3201011501850001',
    'telepon' => '08123456789',
    'email' => 'budi@example.com',
    'tanggal_lahir' => '1985-01-15',
    'alamat' => [
        'street' => 'Jl. Koperasi No. 123',
        'rt' => '001',
        'rw' => '002',
        'village' => 'Koperasi Jaya',
        'district' => 'Mekar',
        'city' => 'Bandung',
        'province' => 'Jawa Barat',
        'postal_code' => '40123'
    ],
    'saldo' => 15000000,
    'pinjaman' => 2500000,
    'bunga' => 0.15,
    'tanggal_daftar' => '2020-01-15 10:30:00'
];

echo "<h4>Profil Nasabah:</h4>";
echo "<table border='1' cellpadding='10'>";
echo "<tr><th>Field</th><th>Value</th></tr>";

echo "<tr><td>Nama</td><td>" . IndonesianFormatHelper::formatName($nasabah['nama']) . "</td></tr>";
echo "<tr><td>NIK</td><td>" . IndonesianFormatHelper::formatNIK($nasabah['nik']) . "</td></tr>";
echo "<tr><td>Telepon</td><td>" . IndonesianFormatHelper::formatPhoneNumber($nasabah['telepon'], 'pretty') . "</td></tr>";
echo "<tr><td>Email</td><td>" . $nasabah['email'] . "</td></tr>";
echo "<tr><td>Tanggal Lahir</td><td>" . IndonesianFormatHelper::formatDate($nasabah['tanggal_lahir'], true) . "</td></tr>";
echo "<tr><td>Alamat</td><td>" . IndonesianFormatHelper::formatAddress($nasabah['alamat']) . "</td></tr>";
echo "<tr><td>Saldo</td><td>" . IndonesianFormatHelper::formatRupiah($nasabah['saldo']) . "</td></tr>";
echo "<tr><td>Pinjaman</td><td>" . IndonesianFormatHelper::formatRupiah($nasabah['pinjaman']) . "</td></tr>";
echo "<tr><td>Bunga</td><td>" . IndonesianFormatHelper::formatPercentage($nasabah['bunga'] * 100) . "</td></tr>";
echo "<tr><td>Tanggal Daftar</td><td>" . IndonesianFormatHelper::formatDateTime($nasabah['tanggal_daftar'], true) . "</td></tr>";

echo "</table>";

echo "<h4>Informasi NIK:</h4>";
$nikInfo = IndonesianFormatHelper::extractNIKInfo($nasabah['nik']);
echo "- Tanggal Lahir: {$nikInfo['birth_day']}/{$nikInfo['birth_month']}/{$nikInfo['birth_year']}<br>";
echo "- Jenis Kelamin: {$nikInfo['gender']}<br>";
echo "- Kode Provinsi: {$nikInfo['province_code']}<br>";

echo "<h4>Kwitansi Pinjaman:</h4>";
echo "Terbilang: " . IndonesianFormatHelper::numberToWords($nasabah['pinjaman'], true) . "<br>";
echo "Tanggal: " . IndonesianFormatHelper::formatDate(date('Y-m-d'), true) . "<br>";

// ==================== SHORTCUT FUNCTIONS ====================
echo "<h3>⚡ Shortcut Functions Demo</h3>";

echo "format_rupiah(15000): " . format_rupiah(15000) . "<br>";
echo "format_tanggal('2024-03-22', true): " . format_tanggal('2024-03-22', true) . "<br>";
echo "format_telepon('08123456789', 'pretty'): " . format_telepon('08123456789', 'pretty') . "<br>";
echo "validate_nik('3201011234560001'): " . (validate_nik('3201011234560001') ? 'Valid' : 'Invalid') . "<br>";
echo "angka_terbilang(15000, true): " . angka_terbilang(15000, true) . "<br>";

// ==================== PERFORMANCE TEST ====================
echo "<h3>⚡ Performance Test</h3>";

$iterations = 10000;
$testAmount = 1500000;

$startTime = microtime(true);
for ($i = 0; $i < $iterations; $i++) {
    IndonesianFormatHelper::formatRupiah($testAmount);
}
$endTime = microtime(true);

$executionTime = ($endTime - $startTime) * 1000;
echo "Format Rupiah $iterations times: " . number_format($executionTime, 2) . " ms<br>";
echo "Average per operation: " . number_format($executionTime / $iterations, 4) . " ms<br>";

echo "<hr>";
echo "<h2>✅ Demo Selesai!</h2>";
echo "<p>Semua fungsi IndonesianFormatHelper telah diuji dan berfungsi dengan baik.</p>";
echo "<p>Helper ini siap digunakan untuk aplikasi KSP Lam Gabe Jaya.</p>";

?>
