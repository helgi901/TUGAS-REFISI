<?php
// ===================================================================
// DATA DAFTAR MENU RESTORAN
// ===================================================================
$daftar_menu = [
    1 => ["nama" => "Nasi Goreng", "harga" => 15000],
    2 => ["nama" => "Nasi Uduk",    "harga" => 12000],
    3 => ["nama" => "Ayam Bakar",   "harga" => 18000]
]; 

// --- SEKARANG DAFTAR MENU LANGSUNG DIPAJANG DI SINI ---
echo "\n=========================================\n";
echo "              DAFTAR MENU                \n";
echo "=========================================\n";
echo "[No]  Nama Menu             Harga        \n";
echo "-----------------------------------------\n";

foreach ($daftar_menu as $nomor => $menu) {
    $nama_rapi  = str_pad($menu['nama'], 22, " ");
    $harga_rapi = str_pad(number_format($menu['harga'], 0, ',', '.'), 11, " ", STR_PAD_LEFT);
    echo "[$nomor]  " . $nama_rapi . "Rp " . $harga_rapi . "\n";
}
echo "=========================================\n";

// BARU SETELAH MENU MUNCUL, MINTA NAMA CUSTOMER
echo "Masukkan Nama Customer: ";
$nama_customer = trim(fgets(STDIN));

$keranjang = [];

// ===================================================================
// PROSES BELANJA (PERULANGAN)
// ===================================================================
while (true) {
    echo "\nPilih Nomor Menu (1-3): ";
    $pilihan = intval(trim(fgets(STDIN)));

    if (!isset($daftar_menu[$pilihan])) {
        echo "❌ MAAF, NOMOR MENU TIDAK ADA! Silakan pilih angka 1 sampai 3.\n";
        continue; 
    }

    // --- VALIDASI JUMLAH BELI (WAJIB ANGKA & > 0) ---
    while (true) {
        echo "Jumlah Beli: ";
        $input_jumlah = trim(fgets(STDIN));

        if (ctype_digit($input_jumlah) && intval($input_jumlah) > 0) {
            $jumlah = intval($input_jumlah);
            break; 
        } else {
            echo "❌ EROR: Jumlah beli harus berupa angka bulat dan lebih dari 0! Coba lagi.\n";
        }
    }

    // Masukkan data ke keranjang
    $keranjang[] = [
        'nama'     => $daftar_menu[$pilihan]['nama'],
        'harga'    => $daftar_menu[$pilihan]['harga'],
        'qty'      => $jumlah,
        'subtotal' => $daftar_menu[$pilihan]['harga'] * $jumlah
    ];

    echo "Mau tambah menu lain? (y/n): ";
    $tanya = strtolower(trim(fgets(STDIN)));

    if ($tanya !== 'y') {
        break; // Keluar dari loop jika tidak pilih 'y'
    }

    // JIKA MAU TAMBAH MENU (Looping lagi), PAJANG LAGI MENUNYA DI SINI
    echo "\n=========================================\n";
    echo "              DAFTAR MENU                \n";
    echo "=========================================\n";
    foreach ($daftar_menu as $nomor => $menu) {
        $nama_rapi  = str_pad($menu['nama'], 22, " ");
        $harga_rapi = str_pad(number_format($menu['harga'], 0, ',', '.'), 11, " ", STR_PAD_LEFT);
        echo "[$nomor]  " . $nama_rapi . "Rp " . $harga_rapi . "\n";
    }
    echo "=========================================\n";
}

// ===================================================================
// HITUNG TOTAL BELANJAAN
// ===================================================================
$grand_total = 0;
foreach ($keranjang as $item) {
    $grand_total += $item['subtotal'];
}

echo "\n-----------------------------------------\n";
echo "TOTAL YANG HARUS DIBAYAR: Rp " . number_format($grand_total, 0, ',', '.') . "\n";

// --- VALIDASI UANG BAYAR (WAJIB ANGKA & HARUS CUKUP) ---
while (true) {
    echo "Masukkan Uang Bayar (Rp): ";
    $input_bayar = trim(fgets(STDIN));

    if (!ctype_digit($input_bayar)) {
        echo "❌ EROR: Nominal pembayaran harus berupa angka! Coba lagi.\n";
        continue;
    }

    $uang_bayar = intval($input_bayar);

    if ($uang_bayar < $grand_total) {
        echo "❌ MAAF, UANG ANDA KURANG! Totalnya Rp " . number_format($grand_total, 0, ',', '.') . ". Coba lagi.\n";
    } else {
        break; // Uang pas/lebih, keluar dari loop pembayaran
    }
}

$kembalian = $uang_bayar - $grand_total;

// ===================================================================
// OUTPUT STRUK NOTA FINAL
// ===================================================================
echo "\n=========================================\n";
echo "         ISI KERANJANG BELANJA           \n";
echo "=========================================\n";
echo "Nama Customer: " . $nama_customer . "\n";
echo "-----------------------------------------\n";

foreach ($keranjang as $item) {
    echo "- " . str_pad($item['nama'], 15, " ") . " (" . $item['qty'] . "x) = Rp " . number_format($item['subtotal'], 0, ',', '.') . "\n";
}

echo "-----------------------------------------\n";
echo "Total Belanja  : Rp " . number_format($grand_total, 0, ',', '.') . "\n";
echo "Uang Bayar     : Rp " . number_format($uang_bayar, 0, ',', '.') . "\n";
echo "Kembalian      : Rp " . number_format($kembalian, 0, ',', '.') . "\n";
echo "=========================================\n";
?>