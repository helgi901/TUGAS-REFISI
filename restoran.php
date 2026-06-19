<?php
// ===================================================================
// DATA DAFTAR MENU RESTORAN
// ===================================================================
$daftar_menu = [
    1 => ["nama" => "Nasi Goreng", "harga" => 15000],
    2 => ["nama" => "Nasi Uduk",    "harga" => 12000],
    3 => ["nama" => "Ayam Bakar",   "harga" => 18000]
]; 

echo "\n=========================================\n";
echo "              DAFTAR MENU                \n";
echo "=========================================\n";
echo "[No]  Nama Menu             Harga        \n";
echo "-----------------------------------------\n";

foreach ($daftar_menu as $nomor => $menu) {
    // Merapikan kolom nama (rata kiri, lebar 22 karakter)
    $nama_rapi  = str_pad($menu['nama'], 22, " ");
    
    // Merapikan kolom harga (rata kanan, lebar 11 karakter, tanpa desimal pecahan)
    $harga_rapi = str_pad(number_format($menu['harga'], 0, ',', '.'), 11, " ", STR_PAD_LEFT);
    
    echo "[$nomor]  " . $nama_rapi . "Rp " . $harga_rapi . "\n";
}
echo "=========================================\n";

// ===================================================================
// PROSES INPUT NAMA CUSTOMER (ANTI-ANGKA & ANTI-KOSONG)
// ===================================================================
while (true) {
    echo "Masukkan Nama Customer: ";
    $input_nama = trim(fgets(STDIN));

    // Menghapus spasi sementara agar ctype_alpha tidak menganggap spasi sebagai karakter haram
    $cek_nama = str_replace(' ', '', $input_nama);

    // Filter: Gak boleh kosong, dan wajib berisi huruf murni
    if ($input_nama === '' || !ctype_alpha($cek_nama)) {
        echo "❌ ERROR: Nama customer harus berupa huruf saja (Tanpa angka / simbol)!\n\n";
        continue; 
    }

    $nama_customer = $input_nama;
    break; 
}

$keranjang = [];

// ===================================================================
// PROSES BELANJA (PERULANGAN UTAMA)
// ===================================================================
while (true) {
    
    // Loop kecil 1: Mengunci validasi pemilihan nomor menu
    while (true) {
        echo "\nPilih Nomor Menu (1-3): ";
        $input_pilihan = trim(fgets(STDIN));

        if ($input_pilihan === '' || !ctype_digit($input_pilihan) || !isset($daftar_menu[intval($input_pilihan)])) {
            echo "❌ MAAF, NOMOR MENU TIDAK ADA! Silakan pilih angka 1 sampai 3.\n";
            continue; 
        }

        $pilihan = intval($input_pilihan);
        break; 
    }

    // Loop kecil 2: Mengunci validasi jumlah beli (anti-huruf, anti-nol/minus)
    while (true) {
        echo "Jumlah Beli (pcs/bungkus): ";
        $input_jumlah = trim(fgets(STDIN));

        if ($input_jumlah === '' || !ctype_digit($input_jumlah) || intval($input_jumlah) <= 0) {
            echo "❌ Input jumlah tidak valid. Masukkan angka bulat positif saja (Min. 1).\n\n";
            continue; 
        }

        $jumlah = intval($input_jumlah);
        break; 
    }

    // Memasukkan data pesanan yang lolos sensor ke dalam laci keranjang belanja
    $keranjang[] = [
        'nama'     => $daftar_menu[$pilihan]['nama'],
        'harga'    => $daftar_menu[$pilihan]['harga'],
        'qty'      => $jumlah,
        'subtotal' => $daftar_menu[$pilihan]['harga'] * $jumlah 
    ];

    echo "Mau tambah menu lain? (y/n): ";
    $tanya = strtolower(trim(fgets(STDIN)));

    // Jika kasir tidak menekan tombol 'y', maka perulangan belanja berhenti
    if ($tanya !== 'y') {
        break; 
    }

    // Menampilkan kembali papan menu agar kasir tidak usah scroll layar ke atas
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
// HITUNG TOTAL BELANJAAN (AKUMULASI)
// ===================================================================
$grand_total = 0;
foreach ($keranjang as $item) {
    $grand_total += $item['subtotal']; 
}

echo "\n-----------------------------------------\n";
echo "TOTAL YANG HARUS DIBAYAR: Rp " . number_format($grand_total, 0, ',', '.') . "\n";
echo "-----------------------------------------\n";

// ===================================================================
// PROSES INPUT UANG BAYAR & VALIDASI KECUKUPAN
// ===================================================================
while (true) {
    echo "Masukkan Uang Bayar (Rp): ";
    $input_bayar = trim(fgets(STDIN));

    // Filter: Anti-kosong, anti-huruf, dan anti-minus
    if ($input_bayar === '' || !ctype_digit($input_bayar) || intval($input_bayar) <= 0) {
        echo "❌ Input salah! Mohon masukkan angka bulat saja (Tanpa huruf / simbol).\n\n";
        continue; 
    }

    $uang_bayar = intval($input_bayar);

    // Cek apakah uangnya kurang dari total belanja
    if ($uang_bayar < $grand_total) {
        echo "❌ MAAF, UANG ANDA KURANG! Total tagihan adalah Rp " . number_format($grand_total, 0, ',', '.') . "\n";
        echo "Silakan masukkan uang bayar yang cukup.\n\n";
        continue; // Mengulang input uang, program tidak mati bawaan
    }

    break; 
}

// Menghitung sisa uang kembalian pembeli
$kembalian = $uang_bayar - $grand_total;

// ===================================================================
// OUTPUT STRUK NOTA FINAL RESTORAN
// ===================================================================
echo "\n=========================================\n";
echo "         ISI KERANJANG BELANJA           \n";
echo "=========================================\n";
// ucwords() otomatis merapikan huruf kapital di awal kata nama customer
echo "Nama Customer: " . ucwords($nama_customer) . "\n";
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