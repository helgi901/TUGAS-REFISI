<?php
// ===================================================================
// KONSTANTA & SETTING AWAL SPIDOMETER (DITARUH DI LUAR LOOP UTAMA)
// ===================================================================
$bensin_awal        = 4.0;    // Batas maksimal kapasitas tangki bensin awal (Set 4 Liter)
$jarak_tempuh_total = 0.0;    // Angka awal Odometer. Mulai dari 0 meter dan akan terus bertambah (Akumulasi)
$total_bar          = 6;      // Jumlah total baris indikator balok bensin fisik di layar LCD (6 Bar)

// ===================================================================
// DAFTAR FUNGSI-FUNGSI BERPARAMETER (LOGIKA UTAMA SISTEM)
// ===================================================================

/**
 * 1. FUNGSI HITUNG JARAK TEMPUH
 * Parameter: Odometer sekarang (meter), Kecepatan saat ini (meter per detik)
 * Fungsi: Menambahkan jarak tempuh yang baru saja dilalui ke angka total odometer.
 */
function hitungJarakTempuh($jarak_sekarang, $kecepatan_mps) {
    return $jarak_sekarang + $kecepatan_mps; // Angka odometer bertambah setiap detik sesuai kecepatan mps
}

/**
 * 2. FUNGSI HITUNG SISA BENSIN
 * Parameter: Kapasitas bensin awal (4L), Total jarak odometer saat ini (meter)
 * Logika: Konversi rasio konsumsi dibuat 1 Liter = 1 KM (1.000 Meter).
 */
function hitungSisaBensin($bensin_awal, $total_jarak) {
    $bensin_terpakai = $total_jarak / 1000;      // Ubah jarak meter ke KM untuk tahu berapa liter yang habis
    $sisa = $bensin_awal - $bensin_terpakai;     // Kapasitas tangki dikurangi bensin yang sudah hangus dijalan
    return ($sisa < 0) ? 0 : $sisa;              // Ternary Operator: Kalau sisa bensin minus, paksa jadi angka 0 (biar gak aneh)
}

/**
 * 3. FUNGSI MENDAPATKAN JUMLAH BAR AKTIF
 * Parameter: Angka sisa bensin desimal (liter), Bensin awal (4L), Jumlah bar fisik (6)
 * Fungsi: Mengonversi sisa bensin desimal menjadi angka bulat skala 0-6 untuk lampu balok LCD.
 */
function getJumlahBar($sisa_bensin, $bensin_awal, $total_bar) {
    if ($sisa_bensin <= 0) return 0; // Kalau bensin habis, otomatis lampu bar yang menyala ada 0
    // Rumus persentase: (Sisa Bensin / Bensin Awal) * 6 Baris. Hasilnya dibulatkan ke angka terdekat (round)
    return (int) round(($sisa_bensin / $bensin_awal) * $total_bar);
}

/**
 * 4. FUNGSI CETAK INDIKATOR BBM VERTIKAL (REPLIKA HONDA SUPRA GTR / SONIC)
 * Parameter: Bensin awal, Total bar, Jumlah bar yang harus menyala
 * Logika: Print spasi manual bertingkat agar posisinya miring/diagonal mirip kluster motor real.
 */
function cetakBarVertikal($bensin_awal, $total_bar, $bar_menyala) {
    echo "     [F]\n"; // Cetak label Full di bagian atas agak ke tengah
    
    // BARIS 6: Diperiksa apakah bar aktif minimal 6? Cetak balok tebal, kalau kurang cetak balok redup
    $b6 = ($bar_menyala >= 6) ? "████" : "░░░░"; 
    echo "    | " . $b6 . " | 6\n"; // 4 Spasi awal -> Efek paling kiri atas
    
    // BARIS 5: Diperiksa apakah bar aktif minimal 5?
    $b5 = ($bar_menyala >= 5) ? "████" : "░░░░";
    echo "     | " . $b5 . " | 5\n"; // 5 Spasi awal -> Mulai geser ke kanan
    
    // BARIS 4: Diperiksa apakah bar aktif minimal 4?
    $b4 = ($bar_menyala >= 4) ? "████" : "░░░░";
    echo "      | " . $b4 . " | 4\n"; // 6 Spasi awal
    
    // BARIS 3: Diperiksa apakah bar aktif minimal 3?
    $b3 = ($bar_menyala >= 3) ? "████" : "░░░░";
    echo "       | " . $b3 . " | 3\n"; // 7 Spasi awal
    
    // BARIS 2: Diperiksa apakah bar aktif minimal 2?
    $b2 = ($bar_menyala >= 2) ? "████" : "░░░░";
    echo "        | " . $b2 . " | 2\n"; // 8 Spasi awal
    
    // BARIS 1: Diperiksa apakah bar aktif minimal 1?
    $b1 = ($bar_menyala >= 1) ? "████" : "░░░░";
    echo "         | " . $b1 . " | 1\n"; // 9 Spasi awal -> Paling kanan bawah (Membentuk tangga miring)
    
    echo "          [E]\n"; // Cetak label Empty di ujung kanan bawah mengikuti lekukan tangga
}

/**
 * 5. FUNGSI SIMULASI KECEPATAN (REAL-TIME)
 * Parameter: Variabel kecepatan kmjam dan mps (dipanggil pakai teknik '&' / reference agar nilai aslinya berubah)
 * Fungsi: Mengacak kecepatan layaknya motor asli yang gasnya naik turun dinamis.
 */
function dapatkanKecepatan(&$kmjam, &$mps) {
    $kmjam = rand(40, 60); // Acak kecepatan motor antara 40 km/jam sampai 60 km/jam setiap detiknya
    $mps   = $kmjam / 3.6; // Rumus Fisika: Konversi satuan dari KM/Jam ke Meter/Detik (mps) untuk hitungan odometer
}


// ===================================================================
// LOOP BESAR UTAMA: KENDALI UTAMA AGAR MOTOR BISA DI-INPUT BERULANG-ULANG
// ===================================================================
while (true) {
    
    // Cek kondisi bensin sebelum perjalanan dimulai. Kalau kosong langsung blokir program
    if (hitungSisaBensin($bensin_awal, $jarak_tempuh_total) <= 0) {
        echo "\n❌ TIDAK BISA JALAN! Bensin sudah habis total. Silakan isi bensin dulu.\n";
        break; // Keluar dari loop besar utama (Program berhenti)
    }

    // SUB-LOOP VALIDASI INPUT USER (Mengunci input agar tidak bisa dimasuki huruf/simbol/minus)
    while (true) {
        echo "\nPosisi Odometer Saat Ini: " . number_format($jarak_tempuh_total, 0, ',', '.') . " Meter\n";
        echo "Masukkan Jarak Perjalanan Baru (Meter): ";
        
        $input_jarak_baru = trim(fgets(STDIN)); // Mengambil input ketikan user dari terminal/konsol

        if ($input_jarak_baru === '') {
            echo "❌ ERROR: Jarak tidak boleh kosong!\n";
            continue; // Ulangi minta input
        }

        if (!ctype_digit($input_jarak_baru)) {
            echo "❌ ERROR: Hanya boleh angka bulat positif! (Tanpa huruf, spasi, simbol, atau minus)\n";
            continue; // Ulangi minta input
        }

        if (intval($input_jarak_baru) <= 0) {
            echo "❌ ERROR: Jarak harus lebih besar dari 0 meter!\n";
            continue; // Ulangi minta input
        }

        $jarak_baru = intval($input_jarak_baru); // Lolos validasi, ubah string input jadi tipe data Integer asli
        break; // Keluar dari sub-loop input, lanjut ke simulasi jalan
    }

    // Hitung target odometer akhir untuk sesi perjalanan kali ini
    $target_finish = $jarak_tempuh_total + $jarak_baru;

    echo "\n=========================================\n";
    echo "       MOTOR KEMBALI MELAJU...           \n";
    echo "=========================================\n";
    echo "Target Odometer Akhir: " . number_format($target_finish, 0, ',', '.') . " Meter\n";
    echo "=========================================\n";
    sleep(2); // Kasih jeda dramatis 2 detik sebelum masuk ke mode visualisasi layar spidometer

    // SUB-LOOP SIMULASI REAL-TIME (Efek animasi berjalan setiap 1 detik)
    while (true) {
        $kecepatan_kmjam = 0; $kecepatan_mps = 0; // Siapkan wadah angka kecepatan
        dapatkanKecepatan($kecepatan_kmjam, $kecepatan_mps); // Dapatkan angka acak kecepatan km/jam & mps baru

        // Update total jarak odometer utama dengan menambahkan jarak meter/detik barusan
        $jarak_tempuh_total = hitungJarakTempuh($jarak_tempuh_total, $kecepatan_mps);

        // Pengunci: Kalau pergerakan meteran sedetik tadi kelewatan dari target, paksa paskan di angka target
        if ($jarak_tempuh_total >= $target_finish) {
            $jarak_tempuh_total = $target_finish;
        }

        // Hitung sisa bensin real-time berdasarkan akumulasi odometer terbaru
        $sisa_bensin = hitungSisaBensin($bensin_awal, $jarak_tempuh_total);

        // Jika di tengah jalan bensinnya habis total
        if ($sisa_bensin <= 0) {
            $sisa_bensin = 0;      // Kunci di angka 0
            $kecepatan_kmjam = 0;  // Kecepatan motor otomatis drop ke 0 km/jam (Mogok)
        }

        // Cari tahu berapa balok bar yang harus menyala saat ini berdasarkan sisa bensin desimal
        $bar_aktif = getJumlahBar($sisa_bensin, $bensin_awal, $total_bar);

        // PERINTAH REFRESH LAYAR TERMINAL (Biar teks angka terlihat berjalan statis di tempat)
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') { 
            popen('cls', 'w'); // Kalau OS laptop Windows, jalankan perintah 'cls'
        } else { 
            system('clear');   // Kalau OS laptop Linux / macOS, jalankan perintah 'clear'
        }

        // PROSES CETAK DASHBOARD SPIDOMETER LIVE KE LAYAR
        echo "=========================================\n";
        echo "            PANEL INDIKATOR              \n";
        echo "=========================================\n";
        echo "Kecepatan       : " . $kecepatan_kmjam . " km/jam\n";
        echo "Odometer (Total): " . number_format($jarak_tempuh_total, 0, ',', '.') . " Meter\n"; 
        echo "Target Sesi Ini : " . number_format($target_finish, 0, ',', '.') . " Meter\n";
        echo "-----------------------------------------\n";
        echo "Sisa Bensin     : " . number_format($sisa_bensin, 2, ',', '.') . " Liter\n";
        echo "Indikator BBM   :\n";
        
        // Panggil fungsi nomor 4 untuk mencetak grafik tangga miring trapesium bensin
        cetakBarVertikal($bensin_awal, $total_bar, $bar_aktif); 
        
        echo "=========================================\n";

        // Cek darurat: Jika bensin menyentuh angka 0, lempar status Mogok dan matikan paksa seluruh program
        if ($sisa_bensin <= 0) {
            echo "\n❌ MOGOK! Bensin habis di jalan.\n";
            break 2; // Keluar dari 2 tingkat loop sekaligus (Sub-loop jalan & Loop besar utama)
        }

        // Cek target: Jika odometer total sudah berhasil menyentuh target perjalanan sesi ini
        if ($jarak_tempuh_total >= $target_finish) {
            echo "\n🏁 SAMPAI! Motor sudah sampai di tujuan sesi ini.\n";
            break; // Keluar dari sub-loop jalan, lanjut ke menu pertanyaan interaktif bawah
        }

        sleep(1); // Hentikan program selama 1 detik sebelum melakukan perulangan detik berikutnya
    }

    // SUB-LOOP MENU INTERAKTIF (Pilihan untuk lanjut berkendara atau sudahan)
    while (true) {
        echo "\nMau lanjut berkendara lagi? (y/n): ";
        $tanya = strtolower(trim(fgets(STDIN))); // Baca input ketikan huruf user

        if ($tanya !== 'y' && $tanya !== 'n') {
            echo "❌ INPUT SALAH! Ketik 'y' untuk lanjut atau 'n' untuk parkir beneran.\n";
            continue; // Balik nanya lagi
        }
        break; // Input valid ('y' atau 'n'), keluar dari sub-loop menu
    }

    // Jika user memilih 'n' (No / Gak mau lanjut)
    if ($tanya === 'n') {
        echo "\nMotor diparkir. Perjalanan selesai!\n";
        break; // Keluar dari loop besar utama, pergi ke nota rekapan akhir
    }
}

// ===================================================================
// OUTPUT REKAPAN AKHIR (Hanya tercetak kalau user keluar/selesai dengan selamat)
// ===================================================================
echo "\n=========================================\n";
echo "         REKAPAN AKHIR ODOMETER          \n";
echo "=========================================\n";
echo "Total Jarak Odometer : " . number_format($jarak_tempuh_total, 0, ',', '.') . " Meter\n";
echo "Sisa Bensin Akhir    : " . number_format($sisa_bensin, 2, ',', '.') . " Liter\n";
echo "=========================================\n";
?>