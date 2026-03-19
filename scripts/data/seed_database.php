<?php
/**
 * Database Seeder - Populate database with sample data
 */

require_once __DIR__ . '/config/Config.php';

$db = Config::getDatabase();

echo "🌱 Starting Database seeding...\n";

// Sample members data
$members = [
    [
        'uuid' => 'sample-Anggota-1',
        'nik' => '1234567890123456',
        'Nama' => 'Ahmad Wijaya',
        'Email' => 'ahmad@Anggota.coop',
        'Telepon' => '08123456789',
        'Alamat' => 'Jl. Merdeka Tidak. 123, Jakarta',
        'birth_date' => '1985-05-15',
        'gender' => 'L',
        'Status' => 'Aktif',
        'created_by' => 1,
        'created_at' => date('Y-m-d H:i:s')
    ],
    [
        'uuid' => 'sample-Anggota-2',
        'nik' => '2345678901234567',
        'Nama' => 'Siti Nurhaliza',
        'Email' => 'siti@Anggota.coop',
        'Telepon' => '08234567890',
        'Alamat' => 'Jl. Sudirman Tidak. 456, Jakarta',
        'birth_date' => '1990-08-22',
        'gender' => 'P',
        'Status' => 'Aktif',
        'created_by' => 1,
        'created_at' => date('Y-m-d H:i:s')
    ]
];

// Insert sample members
foreach ($members as $member) {
    try {
        $stmt = $db->prepare("
            INSERT INTO Anggota (uuid, nik, Nama, Email, Telepon, Alamat, birth_date, gender, Status, created_by, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $member['uuid'],
            $member['nik'],
            $member['Nama'],
            $member['Email'],
            $member['Telepon'],
            $member['Alamat'],
            $member['birth_date'],
            $member['gender'],
            $member['Status'],
            $member['created_by'],
            $member['created_at']
        ]);
        echo "✅ Anggota inserted: {$Anggota["Nama']}\n";
    } catch (Exception $e) {
        echo "⚠️ Anggota Sudah Ada: {$Anggota["Nama']}\n";
    }
}

// Sample users data
$users = [
    [
        'uuid' => 'sample-Pengguna-1',
        'Nama' => 'Administrator',
        'Email' => 'Admin@lamabejaya.coop',
        'Kata Sandi' => password_hash('admin123', PASSWORD_DEFAULT),
        'role' => 'Admin',
        'Status' => 'Aktif',
        'created_by' => 1,
        'created_at' => date('Y-m-d H:i:s')
    ],
    [
        'uuid' => 'sample-Pengguna-2',
        'Nama' => 'Budi Santoso',
        'Email' => 'budi@lamabejaya.coop',
        'Kata Sandi' => password_hash('password123', PASSWORD_DEFAULT),
        'role' => 'Teller',
        'Status' => 'Aktif',
        'created_by' => 1,
        'created_at' => date('Y-m-d H:i:s')
    ]
];

// Insert sample users
foreach ($users as $user) {
    try {
        $stmt = $db->prepare("
            INSERT INTO Pengguna (uuid, Nama, Email, Kata Sandi, role, Status, created_by, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $user['uuid'],
            $user['Nama'],
            $user['Email'],
            $user['Kata Sandi'],
            $user['role'],
            $user['Status'],
            $user['created_by'],
            $user['created_at']
        ]);
        echo "✅ Pengguna inserted: {$Pengguna["Nama']}\n";
    } catch (Exception $e) {
        echo "⚠️ Pengguna Sudah Ada: {$Pengguna["Nama']}\n";
    }
}

// Sample loans data
$loans = [
    [
        'uuid' => 'sample-Pinjaman-1',
        'member_id' => 1,
        'product_id' => 1,
        'loan_number' => 'L' . date('YmdHis') . '001',
        'Jumlah' => 5000000,
        'interest_rate' => 12,
        'term_months' => 12,
        'admin_fee' => 50000,
        'disbursement_amount' => 4950000,
        'application_date' => date('Y-m-d'),
        'disbursement_date' => date('Y-m-d'),
        'due_date' => date('Y-m-d', strtotime('+12 months')),
        'unit_id' => 1,
        'Status' => 'Aktif',
        'created_by' => 1,
        'created_at' => date('Y-m-d H:i:s')
    ]
];

// Insert sample loans
foreach ($loans as $loan) {
    try {
        $stmt = $db->prepare("
            INSERT INTO Pinjaman (uuid, member_id, product_id, loan_number, Jumlah, interest_rate, term_months, admin_fee, disbursement_amount, application_date, disbursement_date, due_date, unit_id, Status, created_by, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $loan['uuid'],
            $loan['member_id'],
            $loan['product_id'],
            $loan['loan_number'],
            $loan['Jumlah'],
            $loan['interest_rate'],
            $loan['term_months'],
            $loan['admin_fee'],
            $loan['disbursement_amount'],
            $loan['application_date'],
            $loan['disbursement_date'],
            $loan['due_date'],
            $loan['unit_id'],
            $loan['Status'],
            $loan['created_by'],
            $loan['created_at']
        ]);
        echo "✅ Pinjaman inserted: {$Pinjaman["loan_number']}\n";
    } catch (Exception $e) {
        echo "⚠️ Pinjaman Sudah Ada\n";
    }
}

// Sample savings data
$savings = [
    [
        'uuid' => 'sample-Simpanan-1',
        'member_id' => 1,
        'account_number' => 'SA' . date('YmdHis') . '001',
        'account_type' => 'regular',
        'Saldo' => 1000000,
        'Status' => 'Aktif',
        'created_by' => 1,
        'created_at' => date('Y-m-d H:i:s')
    ]
];

// Insert sample savings
foreach ($savings as $saving) {
    try {
        $stmt = $db->prepare("
            INSERT INTO savings_accounts (uuid, member_id, account_number, account_type, Saldo, Status, created_by, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $saving['uuid'],
            $saving['member_id'],
            $saving['account_number'],
            $saving['account_type'],
            $saving['Saldo'],
            $saving['Status'],
            $saving['created_by'],
            $saving['created_at']
        ]);
        echo "✅ Simpanan account inserted: {$saving["account_number']}\n";
    } catch (Exception $e) {
        echo "⚠️ Simpanan account Sudah Ada\n";
    }
}

echo "\n🎉 Database seeding Selesai!\n";
echo "📊 Sample Data has been populated for testing.\n";
?>
