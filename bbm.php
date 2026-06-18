<?php
// ===================================================================
// DATA DAFTAR BBM PERTAMINA
// ===================================================================
$daftar_bbm = [
    1 => ["nama" => "Pertalite",       "harga" => 10000],
    2 => ["nama" => "Pertamax",        "harga" => 12500],
    3 => ["nama" => "Pertamax Turbo",  "harga" => 14400],
    4 => ["nama" => "Pertamina Dex",   "harga" => 15100]
]; 

echo "=========================================\n";
echo "         APLIKASI KASIR PERTAMINA        \n";
echo "=========================================\n";
echo "[No]  Jenis BBM             Harga/Liter  \n";
echo "-----------------------------------------\n";

foreach ($daftar_bbm as $nomor => $bbm) {
    $nama_rapi  = str_pad($bbm['nama'], 22, " ");
    $harga_rapi = str_pad(number_format($bbm['harga'], 0, ',', '.'), 11, " ", STR_PAD_LEFT);
    echo "[$nomor]  " . $nama_rapi . "Rp " . $harga_rapi . "\n";
}
echo "=========================================\n";

echo "\n";

// ===================================================================
// PERULANGAN UNTUK PILIHAN BBM (BIAR ENGGAK RESTART DARI AWAL)
// ===================================================================
while (true) {
    // 1. INPUT PILIHAN BENSIN
    echo "Pilihan BBM (1-4): ";
    $pilihan = intval(trim(fgets(STDIN)));

    // VALIDASI: Cek jika nomor bbm tidak ada
    if (!isset($daftar_bbm[$pilihan])) {
        // Jika salah, komputer memunculkan pesan ini lalu 'continue' (mengulang pertanyaan di atas)
        echo "❌ MAAF, NOMOR BBM TIDAK TERSEDIA! Silakan pilih lagi.\n\n";
        continue; 
    }
    
    // Kalau nomornya BENAR, komputer akan menembus baris 'break' ini untuk keluar dari perulangan
    break; 
}

// 2. INPUT MAU BELI BENSIN BERAPA (Misal: 20000)
echo "Mau Beli Bensin Berapa (Rp): ";
$nominal_beli = intval(trim(fgets(STDIN))); 

// 3. INPUT UANG YANG DIBAYARKAN (Misal: 50000)
echo "Masukkan Uang Bayar (Rp): ";
$uang_bayar = intval(trim(fgets(STDIN))); 

// VALIDASI: Cek apakah uangnya cukup atau kurang
if ($uang_bayar < $nominal_beli) {
    echo "\n❌ MAAF, UANG ANDA KURANG!\n";
    exit;
}

// LOGIKA HITUNGAN BARU:
// Hitung liter bensin yang didapat
$liter_didapat = round($nominal_beli / $daftar_bbm[$pilihan]['harga'], 4);

// Hitung uang kembalian
$kembalian = $uang_bayar - $nominal_beli;

// ===================================================================
// OUTPUT STRUK NOTA PERTAMINA BARU
// ===================================================================
echo "\n=========================================\n";
echo "             STRUK NOTA BBM              \n";
echo "=========================================\n";
echo "BBM Pilihan    : " . $daftar_bbm[$pilihan]['nama'] . "\n";
echo "Harga / Liter  : Rp " . number_format($daftar_bbm[$pilihan]['harga'], 0, ',', '.') . "\n";
echo "-----------------------------------------\n";
echo "Total Beli     : Rp " . number_format($nominal_beli, 0, ',', '.') . "\n";
echo "Bensin Didapat : " . $liter_didapat . " Liter\n"; 
echo "-----------------------------------------\n";
echo "Uang Bayar     : Rp " . number_format($uang_bayar, 0, ',', '.') . "\n";
echo "Kembalian      : Rp " . number_format($kembalian, 0, ',', '.') . "\n";
echo "=========================================\n";
?>