# Indonesian Format Helper Guide

## 🎯 Overview

`IndonesianFormatHelper` adalah helper class khusus untuk format data yang sesuai dengan standar dan kebiasaan Indonesia. Helper ini dirancang untuk aplikasi KSP Lam Gabe Jaya yang membutuhkan format data lokal Indonesia.

## 📋 Fitur Utama

### **💰 Format Mata Uang**
- Format Rupiah dengan simbol dan pemisah Indonesia
- Parsing string Rupiah ke numeric
- Format sederhana tanpa desimal
- Support untuk berbagai jumlah desimal

### **📅 Format Tanggal & Waktu**
- Konversi tanggal ke bahasa Indonesia
- Format tanggal dan waktu lengkap
- Support nama hari dan bulan Indonesia
- Format singkatan dan panjang

### **🆔 Validasi & Format NIK**
- Validasi format NIK (Nomor Induk Kependudukan)
- Extract informasi dari NIK
- Format NIK dengan pemisah
- Deteksi jenis kelamin dari NIK

### **📞 Format Nomor Telepon**
- Format nomor telepon Indonesia
- Validasi nomor telepon
- Support format internasional (+62)
- Format pretty dengan pemisah

### **🏠 Format Alamat**
- Format alamat lengkap Indonesia
- Validasi kode pos
- Support RT/RW, Kelurahan, Kecamatan
- Format nama tempat Indonesia

### **👤 Format Nama**
- Format nama sesuai bahasa Indonesia
- Handle gelar akademik
- Support prefix nama khas Indonesia
- Title case yang tepat

### **🔢 Konversi Angka**
- Konversi angka ke terbilang
- Support mata uang dalam terbilang
- Format persentase Indonesia
- Format ukuran file

---

## 💰 **Format Mata Uang Rupiah**

### **Basic Usage**
```php
use App\Helpers\IndonesianFormatHelper;

// Format dengan simbol dan 2 desimal
echo IndonesianFormatHelper::formatRupiah(15000);
// Output: Rp 15.000,00

// Format tanpa simbol
echo IndonesianFormatHelper::formatRupiah(15000, false);
// Output: 15.000,00

// Format tanpa desimal
echo IndonesianFormatHelper::formatRupiah(15000, true, 0);
// Output: Rp 15.000
```

### **Shortcut Function**
```php
// Menggunakan shortcut function
echo format_rupiah(25000);
// Output: Rp 25.000,00

echo format_rupiah(25000, false, 0);
// Output: 25.000
```

### **Parse Rupiah String**
```php
$amount = IndonesianFormatHelper::parseRupiah('Rp 15.000,00');
// Output: 15000.00

$amount = IndonesianFormatHelper::parseRupiah('Rp 25.000');
// Output: 25000.00
```

### **Use Case: KSP Application**
```php
// Menampilkan saldo nasabah
$saldo = 15000000;
echo 'Saldo: ' . IndonesianFormatHelper::formatRupiah($saldo);
// Output: Saldo: Rp 15.000.000,00

// Menampilkan total pinjaman
$pinjaman = 2500000;
echo 'Pinjaman: ' . IndonesianFormatHelper::formatRupiahSimple($pinjaman);
// Output: Pinjaman: Rp 2.500.000
```

---

## 📅 **Format Tanggal & Waktu**

### **Format Tanggal**
```php
// Format tanggal standar
echo IndonesianFormatHelper::formatDate('2024-03-22');
// Output: 22 Maret 2024

// Format dengan nama hari
echo IndonesianFormatHelper::formatDate('2024-03-22', true);
// Output: Jumat, 22 Maret 2024

// Format singkatan bulan
echo IndonesianFormatHelper::formatDate('2024-03-22', false, false);
// Output: 22 Mar 2024
```

### **Format Tanggal & Waktu**
```php
// Format datetime lengkap
echo IndonesianFormatHelper::formatDateTime('2024-03-22 14:30:00');
// Output: 22 Maret 2024 pukul 14:30

// Dengan nama hari
echo IndonesianFormatHelper::formatDateTime('2024-03-22 14:30:00', true);
// Output: Jumat, 22 Maret 2024 pukul 14:30
```

### **Shortcut Function**
```php
echo format_tanggal('2024-03-22', true);
// Output: Jumat, 22 Maret 2024
```

### **Use Case: KSP Application**
```php
// Tanggal transaksi
$tanggalTransaksi = '2024-03-22';
echo 'Tanggal: ' . IndonesianFormatHelper::formatDate($tanggalTransaksi, true);
// Output: Tanggal: Jumat, 22 Maret 2024

// Waktu login
$loginTime = '2024-03-22 09:15:30';
echo 'Login: ' . IndonesianFormatHelper::formatDateTime($loginTime);
// Output: Login: 22 Maret 2024 pukul 09:15
```

---

## 🆔 **Validasi & Format NIK**

### **Validasi NIK**
```php
// Validasi NIK
$nik = '3201011234560001';
if (IndonesianFormatHelper::validateNIK($nik)) {
    echo 'NIK valid';
} else {
    echo 'NIK tidak valid';
}
```

### **Format NIK**
```php
// Format NIK dengan pemisah
echo IndonesianFormatHelper::formatNIK('3201011234560001');
// Output: 32 01 01 123456 0001

// Dengan custom separator
echo IndonesianFormatHelper::formatNIK('3201011234560001', '-');
// Output: 32-01-01-123456-0001
```

### **Extract Informasi NIK**
```php
$info = IndonesianFormatHelper::extractNIKInfo('3201011234560001');
print_r($info);
/*
Array (
    [province_code] => 32
    [regency_code] => 01
    [district_code] => 01
    [birth_day] => 12
    [birth_month] => 3
    [birth_year] => 1956
    [gender] => Laki-laki
    [sequence] => 0001
)
*/
```

### **Use Case: KSP Application**
```php
// Form pendaftaran nasabah
$nik = $_POST['nik'];

if (!IndonesianFormatHelper::validateNIK($nik)) {
    echo 'NIK tidak valid!';
    return;
}

// Extract informasi untuk verifikasi
$info = IndonesianFormatHelper::extractNIKInfo($nik);
echo 'Tanggal Lahir: ' . $info['birth_day'] . '/' . $info['birth_month'] . '/' . $info['birth_year'];
echo 'Jenis Kelamin: ' . $info['gender'];
```

---

## 📞 **Format Nomor Telepon**

### **Format Nomor Telepon**
```php
// Format nasional
echo IndonesianFormatHelper::formatPhoneNumber('08123456789', 'national');
// Output: 08123456789

// Format internasional
echo IndonesianFormatHelper::formatPhoneNumber('08123456789', 'international');
// Output: +62 8123456789

// Format pretty
echo IndonesianFormatHelper::formatPhoneNumber('08123456789', 'pretty');
// Output: 081-2345-6789
```

### **Validasi Nomor Telepon**
```php
if (IndonesianFormatHelper::validatePhoneNumber('08123456789')) {
    echo 'Nomor telepon valid';
} else {
    echo 'Nomor telepon tidak valid';
}
```

### **Use Case: KSP Application**
```php
// Kontak nasabah
$phone = '08123456789';
echo 'Telepon: ' . IndonesianFormatHelper::formatPhoneNumber($phone, 'pretty');
// Output: Telepon: 081-2345-6789

// WhatsApp format
$waPhone = IndonesianFormatHelper::formatPhoneNumber($phone, 'international');
echo 'WhatsApp: ' . $waPhone;
// Output: WhatsApp: +62 8123456789
```

---

## 🏠 **Format Alamat**

### **Format Alamat Lengkap**
```php
$addressData = [
    'street' => 'Jl. Merdeka No. 123',
    'rt' => '001',
    'rw' => '002',
    'village' => 'Mekar Jaya',
    'district' => 'Sukajadi',
    'city' => 'Bandung',
    'province' => 'Jawa Barat',
    'postal_code' => '40123'
];

echo IndonesianFormatHelper::formatAddress($addressData);
// Output: Jl. Merdeka No. 123, RT 001, RW 002, Kel. Mekar Jaya, Kec. Sukajadi, Bandung, Jawa Barat, 40123
```

### **Validasi Kode Pos**
```php
if (IndonesianFormatHelper::validatePostalCode('40123')) {
    echo 'Kode pos valid';
} else {
    echo 'Kode pos tidak valid';
}
```

### **Use Case: KSP Application**
```php
// Alamat nasabah
$alamatNasabah = [
    'street' => $nasabah['alamat'],
    'village' => $nasabah['kelurahan'],
    'district' => $nasabah['kecamatan'],
    'city' => $nasabah['kota'],
    'province' => $nasabah['provinsi'],
    'postal_code' => $nasabah['kode_pos']
];

echo IndonesianFormatHelper::formatAddress($alamatNasabah);
```

---

## 👤 **Format Nama**

### **Format Nama Indonesia**
```php
echo IndonesianFormatHelper::formatName('ahmad rizki');
// Output: Ahmad Rizki

echo IndonesianFormatHelper::formatName('muhammad yusuf');
// Output: Muhammad Yusuf

echo IndonesianFormatHelper::formatName('siti aisyah');
// Output: Siti Aisyah
```

### **Format Gelar Akademik**
```php
echo IndonesianFormatHelper::formatAcademicTitle('dr. Ahmad Rizki S.Kom.');
// Output: Doktor Ahmad Rizki Sarjana Komputer
```

### **Use Case: KSP Application**
```php
// Nama nasabah
$nama = 'budi santoso';
echo 'Nama: ' . IndonesianFormatHelper::formatName($nama);
// Output: Nama: Budi Santoso

// Nama staff dengan gelar
$namaStaff = 'ir. john doe S.T.';
echo 'Staff: ' . IndonesianFormatHelper::formatAcademicTitle($namaStaff);
// Output: Staff: Insinyur John Doe Sarjana Teknik
```

---

## 🔢 **Konversi Angka**

### **Angka ke Terbilang**
```php
echo IndonesianFormatHelper::numberToWords(1234);
// Output: seribu dua ratus tiga puluh empat

echo IndonesianFormatHelper::numberToWords(1234, true);
// Output: seribu dua ratus tiga puluh empat Rupiah
```

### **Format Persentase**
```php
echo IndonesianFormatHelper::formatPercentage(0.75);
// Output: 75,00%

echo IndonesianFormatHelper::formatPercentage(0.1234, 1);
// Output: 12,3%
```

### **Format Ukuran File**
```php
echo IndonesianFormatHelper::formatFileSize(1024);
// Output: 1 KB

echo IndonesianFormatHelper::formatFileSize(1048576);
// Output: 1 MB
```

### **Use Case: KSP Application**
```php
// Kwitansi
$jumlah = 1500000;
echo 'Terbilang: ' . IndonesianFormatHelper::numberToWords($jumlah, true);
// Output: Terbilang: satu juta lima ratus ribu Rupiah

// Bunga pinjaman
$bunga = 0.15;
echo 'Bunga: ' . IndonesianFormatHelper::formatPercentage($bunga * 100);
// Output: Bunga: 15,00%
```

---

## 🔧 **Validasi Data Lengkap**

### **Validasi Data Pengguna**
```php
$userData = [
    'nik' => '3201011234560001',
    'phone' => '08123456789',
    'email' => 'user@example.com',
    'postal_code' => '40123'
];

$validation = IndonesianFormatHelper::validateUserData($userData);

if (!$validation['valid']) {
    foreach ($validation['errors'] as $field => $error) {
        echo $field . ': ' . $error;
    }
}
```

### **Format Data Tabel**
```php
$tableData = [
    'name' => 'ahmad rizki',
    'amount' => 1500000,
    'date' => '2024-03-22',
    'phone' => '08123456789',
    'nik' => '3201011234560001'
];

$formattedData = IndonesianFormatHelper::formatTableData($tableData);
print_r($formattedData);
/*
Array (
    [name] => Ahmad Rizki
    [amount] => Rp 1.500.000,00
    [date] => 22 Maret 2024
    [phone] => 081-2345-6789
    [nik] => 32 01 01 123456 0001
)
*/
```

---

## 🚀 **Implementasi di KSP Lam Gabe Jaya**

### **1. Setup Autoload**
```php
// Di composer.json atau autoloader
require_once 'helpers/IndonesianFormatHelper.php';
```

### **2. Penggunaan di Controller**
```php
class NasabahController {
    public function showProfile($id) {
        $nasabah = $this->nasabahModel->find($id);
        
        // Format data untuk display
        $nasabah['nama_formatted'] = IndonesianFormatHelper::formatName($nasabah['nama']);
        $nasabah['saldo_formatted'] = IndonesianFormatHelper::formatRupiah($nasabah['saldo']);
        $nasabah['tanggal_lahir_formatted'] = IndonesianFormatHelper::formatDate($nasabah['tanggal_lahir'], true);
        $nasabah['telepon_formatted'] = IndonesianFormatHelper::formatPhoneNumber($nasabah['telepon'], 'pretty');
        $nasabah['alamat_formatted'] = IndonesianFormatHelper::formatAddress($nasabah);
        
        return view('nasabah.profile', compact('nasabah'));
    }
}
```

### **3. Form Validation**
```php
class NasabahController {
    public function store(Request $request) {
        $data = $request->all();
        
        // Validasi data Indonesia
        $validation = IndonesianFormatHelper::validateUserData($data);
        
        if (!$validation['valid']) {
            return back()->withErrors($validation['errors']);
        }
        
        // Format data sebelum save
        $data['nama'] = IndonesianFormatHelper::formatName($data['nama']);
        $data['telepon'] = IndonesianFormatHelper::formatPhoneNumber($data['telepon'], 'national');
        
        $this->nasabahModel->create($data);
        
        return redirect()->route('nasabah.index')->with('success', 'Nasabah berhasil ditambahkan');
    }
}
```

### **4. Laporan & Kwitansi**
```php
class LaporanController {
    public function kwitansi($id) {
        $transaksi = $this->transaksiModel->find($id);
        
        return view('laporan.kwitansi', [
            'transaksi' => $transaksi,
            'jumlah_terbilang' => IndonesianFormatHelper::numberToWords($transaksi['jumlah'], true),
            'tanggal_formatted' => IndonesianFormatHelper::formatDate($transaksi['tanggal'], true),
            'waktu_formatted' => IndonesianFormatHelper::formatDateTime($transaksi['created_at'])
        ]);
    }
}
```

---

## 📱 **Frontend Integration**

### **JavaScript Helper (Optional)**
```javascript
// frontend-format-helper.js
class IndonesianFormatHelper {
    static formatRupiah(amount) {
        return 'Rp ' + parseFloat(amount).toLocaleString('id-ID');
    }
    
    static formatDate(date) {
        const options = { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        };
        return new Date(date).toLocaleDateString('id-ID', options);
    }
    
    static formatPhoneNumber(phone) {
        // Remove non-digits
        const cleaned = phone.replace(/\D/g, '');
        
        // Format: 08xx-xxxx-xxxx
        if (cleaned.length === 11) {
            return cleaned.replace(/(\d{4})(\d{4})(\d{3})/, '$1-$2-$3');
        }
        return cleaned;
    }
}

// Usage in Blade template
<script>
    const amount = 1500000;
    console.log(IndonesianFormatHelper.formatRupiah(amount));
    // Output: Rp 1.500.000
</script>
```

### **Blade Template Example**
```blade
<!-- resources/views/nasabah/profile.blade.php -->
<div class="card">
    <div class="card-header">
        <h5>Profil Nasabah</h5>
    </div>
    <div class="card-body">
        <p><strong>Nama:</strong> {{ $nasabah['nama_formatted'] }}</p>
        <p><strong>NIK:</strong> {{ $nasabah['nik_formatted'] }}</p>
        <p><strong>Telepon:</strong> {{ $nasabah['telepon_formatted'] }}</p>
        <p><strong>Alamat:</strong> {{ $nasabah['alamat_formatted'] }}</p>
        <p><strong>Saldo:</strong> {{ $nasabah['saldo_formatted'] }}</p>
        <p><strong>Tanggal Lahir:</strong> {{ $nasabah['tanggal_lahir_formatted'] }}</p>
    </div>
</div>
```

---

## 🎯 **Best Practices**

### **1. Consistent Usage**
```php
// ✅ Good: Use helper consistently
$amount = IndonesianFormatHelper::formatRupiah($amount);
$date = IndonesianFormatHelper::formatDate($date);

// ❌ Bad: Mixed formatting approaches
$amount = 'Rp ' . number_format($amount, 2, ',', '.');
```

### **2. Validation First**
```php
// ✅ Good: Validate before format
if (IndonesianFormatHelper::validateNIK($nik)) {
    $formattedNik = IndonesianFormatHelper::formatNIK($nik);
}

// ❌ Bad: Format without validation
$formattedNik = IndonesianFormatHelper::formatNIK($nik);
```

### **3. Error Handling**
```php
// ✅ Good: Handle empty/invalid data
$formattedDate = !empty($date) ? IndonesianFormatHelper::formatDate($date) : '-';

// ❌ Bad: Assume data is always valid
$formattedDate = IndonesianFormatHelper::formatDate($date);
```

### **4. Performance**
```php
// ✅ Good: Use static methods
IndonesianFormatHelper::formatRupiah($amount);

// ❌ Bad: Create unnecessary instances
$helper = new IndonesianFormatHelper();
$helper->formatRupiah($amount);
```

---

## 🔧 **Troubleshooting**

### **Common Issues**

#### **1. NIK Validation Fails**
```php
// Check NIK format
$nik = '3201011234560001';
if (!IndonesianFormatHelper::validateNIK($nik)) {
    echo 'NIK harus 16 digit angka';
}
```

#### **2. Date Format Issues**
```php
// Ensure valid date format
$date = '2024-03-22'; // Y-m-d format
$formattedDate = IndonesianFormatHelper::formatDate($date);
```

#### **3. Phone Format Issues**
```php
// Clean phone number first
$phone = preg_replace('/[^0-9]/', '', $phone);
$formattedPhone = IndonesianFormatHelper::formatPhoneNumber($phone);
```

### **Debug Mode**
```php
// Enable debug for troubleshooting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Test individual functions
var_dump(IndonesianFormatHelper::validateNIK('3201011234560001'));
var_dump(IndonesianFormatHelper::formatRupiah(15000));
```

---

## 📚 **References**

### **Official Documentation**
- [PHP Number Formatting](https://www.php.net/manual/en/function.number-format.php)
- [PHP DateTime](https://www.php.net/manual/en/class.datetime.php)
- [Indonesian NIK Regulation](https://www.dukcapil.kemendagri.go.id/)

### **Related Resources**
- [Indonesian Phone Number Formats](https://en.wikipedia.org/wiki/Telephone_numbers_in_Indonesia)
- [Indonesian Postal Codes](https://kodepos.posindonesia.co.id/)
- [Indonesian Date Formats](https://www.w3.org/International/questions/qa/date-time-format)

---

**🎯 **IndonesianFormatHelper menyediakan solusi lengkap untuk formatting data Indonesia dengan validasi yang ketat dan implementasi yang mudah untuk aplikasi KSP Lam Gabe Jaya!**
