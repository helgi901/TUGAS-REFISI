<?php
// ===================================================================
// KONSTANTA & SETTING AWAL SPIDOMETER (DITARUH DI LUAR LOOP UTAMA)
// ===================================================================
$bensin_awal        = 4.0;    // Konstanta bensin awal (4 Liter)
$jarak_tempuh_total = 0.0;    // Mulai dari 0 meter dan akan terus bertambah (Odometer)
$total_bar          = 6;      // Indikator bensin 6 baris

// ===================================================================
// LOOP BESAR UTAMA: AGAR BISA INPUT JARAK BERULANG-ULANG TANPA RESET
// ===================================================================
while (true) {
    
    // Cek dulu, kalau bensin sudah habis dari perjalanan sebelumnya, langsung tamat
    if (($bensin_awal - ($jarak_tempuh_total / 1000)) <= 0) {
        echo "\n❌ TIDAK BISA JALAN! Bensin sudah habis total. Silakan isi bensin dulu.\n";
        break;
    }

    // Input target jarak tambahan untuk sesi perjalanan kali ini
    while (true) {
        echo "\nPosisi Odometer Saat Ini: " . number_format($jarak_tempuh_total, 1, ',', '.') . " Meter\n";
        echo "Masukkan Jarak Perjalanan Baru (Meter): ";
        $input_jarak_baru = trim(fgets(STDIN));

        if ($input_jarak_baru === '' || !ctype_digit($input_jarak_baru) || intval($input_jarak_baru) <= 0) {
            echo "❌ Input salah! Masukkan angka bulat positif saja.\n";
            continue;
        }

        $jarak_baru = intval($input_jarak_baru);
        break;
    }

    // Menghitung titik target akhir untuk sesi jalan kali ini
    // Misal odometer sekarang 1000, input baru 500, maka target finish adalah 1500
    $target_finish = $jarak_tempuh_total + $jarak_baru;

    echo "\n=========================================\n";
    echo "       MOTOR KEMBALI MELAJU...           \n";
    echo "=========================================\n";
    echo "Target Odometer Akhir: " . number_format($target_finish, 0, ',', '.') . " Meter\n";
    echo "=========================================\n";
    sleep(2);

    // ===============================================================
    // SIMULASI PERJALANAN REAL-TIME UNTUK SESI INI
    // ===============================================================
    while (true) {
        // 1. Simulasi Kecepatan acak (40 - 80 km/jam), diubah ke meter/detik
        $kecepatan_kmjam = rand(40, 80);
        $kecepatan_mps   = $kecepatan_kmjam / 3.6; 

        // 2. Jarak odometer bertambah terus dari posisi terakhirnya
        $jarak_tempuh_total += $kecepatan_mps;

        // KUNCI AUTO-STOP SESI: Jika sudah menyentuh target finish sesi ini
        if ($jarak_tempuh_total >= $target_finish) {
            $jarak_tempuh_total = $target_finish; // Pas-in angkanya
        }

        // 3. Hitung Sisa Bensin kumulatif (dari total seluruh jarak yang sudah ditempuh)
        $bensin_terpakai = $jarak_tempuh_total / 1000;
        $sisa_bensin     = $bensin_awal - $bensin_terpakai;

        if ($sisa_bensin <= 0) {
            $sisa_bensin = 0;
            $kecepatan_kmjam = 0;
        }

        // 4. Hitung visualisasi 6 Bar Bensin
        $bar_menyala = round(($sisa_bensin / $bensin_awal) * $total_bar);
        $tampilan_bar = str_repeat("█", $bar_menyala) . str_repeat("░", $total_bar - $bar_menyala);

        // ===========================================================
        // REFRESH PANEL INDIKATOR DI TERMINAL
        // ===========================================================
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') { popen('cls', 'w'); } else { system('clear'); }

        echo "=========================================\n";
        echo "            PANEL INDIKATOR              \n";
        echo "=========================================\n";
        echo "Kecepatan       : " . $kecepatan_kmjam . " km/jam\n";
        echo "Odometer (Total): " . number_format($jarak_tempuh_total, 1, ',', '.') . " Meter\n";
        echo "Target Sesi Ini : " . number_format($target_finish, 0, ',', '.') . " Meter\n";
        echo "-----------------------------------------\n";
        echo "Sisa Bensin     : " . number_format($sisa_bensin, 2, ',', '.') . " Liter\n";
        echo "Indikator BBM   : [" . $tampilan_bar . "] (" . $bar_menyala . "/6 Bar)\n";
        echo "=========================================\n";

        // MOGOK: Bensin habis ditengah jalan
        if ($sisa_bensin <= 0) {
            echo "\n❌ MOGOK! Bensin habis di jalan.\n";
            break 2; // Keluar dari KEDUA perulangan (Simulasi beneran selesai/mati)
        }

        // SAMPAI TUJUAN SESI INI
        if ($jarak_tempuh_total >= $target_finish) {
            echo "\n🏁 SAMPAI! Motor sudah sampai di tujuan sesi ini.\n";
            break; // Keluar dari loop jalan sesi ini, kembali ke loop besar buat nanya jarak baru
        }

        sleep(1); 
    }

    // ===============================================================
    // PERTANYAAN INTERAKTIF: MAU JALAN LAGI ATAU SELESAI?
    // ===============================================================
    while (true) {
        echo "\nMau lanjut berkendara lagi? (y/n): ";
        $tanya = strtolower(trim(fgets(STDIN)));

        if ($tanya !== 'y' && $tanya !== 'n') {
            echo "❌ INPUT SALAH! Ketik 'y' untuk lanjut atau 'n' untuk parkir beneran.\n";
            continue;
        }
        break;
    }

    if ($tanya === 'n') {
        echo "\nMotor diparkir. Perjalanan selesai!\n";
        break; // Keluar dari loop besar utama
    }
}

// ===================================================================
// OUTPUT DATA AKHIR DARI SELURUH PERJALANAN
// ===================================================================
echo "\n=========================================\n";
echo "         REKAPAN AKHIR ODOMETER          \n";
echo "=========================================\n";
echo "Total Jarak Odometer : " . number_format($jarak_tempuh_total, 1, ',', '.') . " Meter\n";
echo "Sisa Bensin Akhir    : " . number_format($sisa_bensin, 2, ',', '.') . " Liter\n";
echo "=========================================\n";
?>