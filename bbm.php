<?php

// ===================================================================
// DATA DAFTAR PILIHAN BBM PERTAMINA
// ===================================================================
$daftar_bbm = [
    1 => ["nama" => "Pertalite",       "harga" => 10000],
    2 => ["nama" => "Pertamax",        "harga" => 16250], 
    3 => ["nama" => "Pertamax Turbo",  "harga" => 20750], 
    4 => ["nama" => "Pertamina Dex",   "harga" => 24800]  
]; 

echo "=========================================\n";
echo "         APLIKASI KASIR PERTAMINA        \n";
echo "=========================================\n";
echo "[No]  Jenis BBM             Harga/Liter  \n";
echo "-----------------------------------------\n";

// Mengulang dan merapikan tampilan daftar BBM ke layar terminal
foreach ($daftar_bbm as $nomor => $bbm) {
    $nama_rapi  = str_pad($bbm['nama'], 22, " ");
    $harga_rapi = str_pad(number_format($bbm['harga'], 0, ',', '.'), 11, " ", STR_PAD_LEFT);
    echo "[$nomor]  " . $nama_rapi . "Rp " . $harga_rapi . "\n";
}
echo "=========================================\n";

echo "\n";

// ===================================================================
// PROSES 1: PILIHAN JENIS BBM (ALUR POS)
// ===================================================================
while (true) {
    echo "Pilihan BBM (1-4): ";
    $input_pilihan = trim(fgets(STDIN));

    // Cek apakah input kosong, bukan angka, atau nomor bbm-nya gak terdaftar di array (!isset)
    if ($input_pilihan === '' || !ctype_digit($input_pilihan) || !isset($daftar_bbm[intval($input_pilihan)])) {
        echo "\n❌ MAAF, NOMOR BBM TIDAK TERSEDIA! Silakan pilih angka 1 sampai 4.\n";
        continue; // Mengulang pertanyaan pilihan BBM
    }

    $pilihan = intval($input_pilihan);
    break; // Lolos validasi, keluar dari loop BBM
}

// ===================================================================
// PROSES 2: TENTUKAN NOMINAL BELI (ALUR POS)
// ===================================================================
$harga_per_liter = $daftar_bbm[$pilihan]['harga'];

while (true) {
    echo "Mau beli berapa (Rp)? ";
    $input_nominal = trim(fgets(STDIN));

    // Validasi dasar: Harus angka bulat positif dan gak boleh kosong
    if ($input_nominal === '' || !ctype_digit($input_nominal) || intval($input_nominal) <= 0) {
        echo "❌ Masukkan nominal yang valid.\n";
        continue;
    }

    $nominal = intval($input_nominal);
    
    // Hitung takaran liter yang didapat berdasarkan nominal beli
    $liter_didapat = $nominal / $harga_per_liter;
    $uang_terpakai = $nominal; // Nominal ini yang jadi total tagihan belanja bbm
    break; 
}

echo "\n-----------------------------------------\n";
echo "TOTAL YANG HARUS DIBAYAR: Rp " . number_format($uang_terpakai, 0, ',', '.') . "\n";
echo "-----------------------------------------\n";

// ===================================================================
// PROSES 3: INPUT UANG BAYAR & VALIDASI KECUKUPAN (ALUR POS)
// ===================================================================
while (true) {
    echo "Masukkan Uang Tunai / Bayar (Rp): ";
    $input_uang = trim(fgets(STDIN));

    // Filter anti-karakter aneh dan enter kosong
    if ($input_uang === '' || !ctype_digit($input_uang) || intval($input_uang) <= 0) {
        echo "❌ Input tidak valid. Masukkan angka bulat positif saja.\n\n";
        continue;
    }

    $uang = intval($input_uang);

    // LOGIKA PENGECEKAN UANG KURANG
    if ($uang < $uang_terpakai) {
        echo "\n❌ MAAF, UANG ANDA KURANG! Total tagihan adalah Rp " . number_format($uang_terpakai, 0, ',', '.') . "\n";
        echo "Silakan masukkan nominal uang bayar yang cukup.\n\n";
        continue; // Menggunakan continue agar program GAK MATI, tapi balik nanya ulang input uang lagi!
    }

    break; // Uang pas/lebih, lolos validasi dan keluar loop buat cetak struk
}

// Rumus mencari uang kembalian
$kembalian = $uang - $uang_terpakai;

// ===================================================================
// OUTPUT: CETAK STRUK NOTA BBM FINAL
// ===================================================================
echo "\n=========================================\n";
echo "             STRUK NOTA BBM              \n";
echo "=========================================\n";
echo "BBM Pilihan    : " . $daftar_bbm[$pilihan]['nama'] . "\n";
echo "Harga / Liter  : Rp " . number_format($harga_per_liter, 0, ',', '.') . "\n";
echo "-----------------------------------------\n";
echo "Uang Bayar     : Rp " . number_format($uang_terpakai, 0, ',', '.') . "\n";

// 🔥 LOGIKA PINTAR: Mendeteksi apakah hasil liter merupakan angka bulat atau pecahan desimal
if (fmod($liter_didapat, 1) == 0) {
    // Jika angkanya bulat (misal 100.00), dicetak tanpa angka desimal di belakang koma
    echo "Bensin Didapat : " . number_format($liter_didapat, 0, ',', '.') . " Liter\n"; 
} else {
    // Jika angkanya pecahan desimal, dipaksa konsisten 3 angka di belakang koma (khas SPBU)
    echo "Bensin Didapat : " . number_format($liter_didapat, 3, ',', '.') . " Liter\n"; 
}

echo "=========================================\n";
echo "Uang Diterima  : Rp " . number_format($uang, 0, ',', '.') . "\n";
echo "Kembalian      : Rp " . number_format($kembalian, 0, ',', '.') . "\n";
echo "=========================================\n";
?>