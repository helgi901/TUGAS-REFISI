<?php
// ===================================================================
// KONSTANTA & SETTING AWAL SPIDOMETER (FORMAT ANGKA MURNI)
// ===================================================================
$bensin_awal      = 4.0;               // Kapasitas maksimal tanki bensin awal (4.0 Liter)
$total_bar        = 6;                 // Jumlah balok/tingkatan bar bensin di layar Yamaha Lexi
$file_spidometer  = "jarak_total.txt"; // Nama file teks untuk menyimpan data odometer (harddisk)

// LOGIKA LOAD DATA: Cek apakah file ada di komputer dan isinya tidak kosong?
if (file_exists($file_spidometer) && filesize($file_spidometer) > 0) {
    // Jika file ada, baca isinya dan hapus spasi/enter yang gak penting dengan trim()
    $isi_file = trim(file_get_contents($file_spidometer));
    // Ubah teks dari file tadi menjadi angka pecahan/desimal (float)
    $jarak_tempuh_total = floatval($isi_file); 
    
    // Konsumsi BBM: 1 Liter = 1.000 Meter (1 KM). Hitung bensin yang sudah terpakai sebelumnya
    $bensin_terpakai = $jarak_tempuh_total / 1000;
    // Hitung sisa bensin saat ini (Kapasitas awal dikurangi yang sudah terpakai)
    $bensin_saat_ini = 4.0 - $bensin_terpakai;
    // Pengaman: Jika hasil hitungan bensinnya minus, paksa set ke angka 0
    if ($bensin_saat_ini < 0) {
        $bensin_saat_ini = 0;
    }
} else {
    // Jika file data tidak ditemukan (motor baru/file kehapus), set semua dari awal
    $jarak_tempuh_total = 0.0; // Odometer mulai dari 0.0 Meter
    $bensin_saat_ini    = 4.0; // Bensin full kembali 4.0 Liter
}

// ===================================================================
// DAFTAR FUNGSI-FUNGSI LOGIKA UTAMA SISTEM (MODULAR CODE)
// ===================================================================

// Fungsi untuk menulis/menyimpan angka odometer terbaru ke dalam file teks
function simpanDataKeFile($file, $odometer) {
    // sprintf("%.2f") memaksa angka odometer ditulis rapi dengan 2 angka di belakang koma
    file_put_contents($file, sprintf("%.2f", $odometer));
}

// Fungsi untuk menghitung total jarak tempuh baru (jarak lama + kecepatan per detik)
function hitungJarakTempuh($jarak_sekarang, $kecepatan_mps) {
    return $jarak_sekarang + $kecepatan_mps;
}

// Fungsi untuk menghitung sisa bensin yang berkurang setiap detiknya
function hitungSisaBensin($bensin_sekarang, $kecepatan_mps) {
    $bensin_terpakai = $kecepatan_mps / 1000; // Rumus konsumsi: kecepatan per detik dibagi 1000
    $sisa = $bensin_sekarang - $bensin_terpakai;
    // Jika sisa bensin kurang dari 0, kembalikan nilai 0. Jika tidak, kembalikan nilai $sisa
    return ($sisa < 0) ? 0 : $sisa;
}

// Fungsi untuk menghitung berapa balok bar bensin yang harus menyala di spidometer
function getJumlahBar($sisa_bensin, $bensin_awal, $total_bar) {
    if ($sisa_bensin <= 0) return 0; // Kalau bensin habis, bar yang menyala otomatis 0
    // Menghitung rasio bensin lalu dibulatkan ke bawah menggunakan floor() menjadi angka bulat
    return (int) floor(($sisa_bensin / $bensin_awal) * $total_bar);
}

// Fungsi konversi kecepatan dari Km/Jam ke Meter/Detik menggunakan teknik Pass-by-Reference (simbol &)
function dapatkanKecepatan(&$kmjam, &$mps, $input_user_kmjam) {
    $kmjam = $input_user_kmjam;    // Mengisi variabel display dengan input user
    $mps   = $kmjam / 3.6;         // Rumus fisika konversi Km/Jam ke Meter/Detik (dibagi 3.6)
}

// ===================================================================
// LOOP BESAR UTAMA PROGRAM (Roda Motor Mulai Berputar)
// ===================================================================
while (true) {
    
    // CEK KONDISI MOGOK: Jika bensin saat ini 0 atau habis
    if ($bensin_saat_ini <= 0) {
        echo "\n❌ TIDAK BISA JALAN! Bensin sudah habis total.\n";
        echo "Mau isi bensin full lagi? (y/n): ";
        $isi_bensin = strtolower(trim(fgets(STDIN))); // Mengambil input ketikan user di terminal
        
        if ($isi_bensin === 'y') {
            // Jarak tidak di-reset! Cukup bensinnya aja yang digasin full lagi
            $bensin_saat_ini = 4.0;    // Isi bensin full lagi 4 Liter
            $jarak_tempuh_total = 0.0; // Reset odometer karena bensinnya dihitung dari 0 lagi berdasarkan logika file lu
            simpanDataKeFile($file_spidometer, $jarak_tempuh_total); // Amankan data odometer lama ke file
            echo "✅ Bensin diisi penuh! Odometer di-reset kembali. Silakan masukkan jarak kembali.\n";
            continue; // Lompat ke awal loop utama untuk meminta input perjalanan baru
        } else {
            echo "Motor diparkir dalam kondisi mogok. Perjalanan selesai!\n";
            break; // Keluar dari loop utama dan menghentikan program
        }
    }
    // SUB-LOOP VALIDASI INPUT (Kunci mati: User wajib input angka murni 0-9)
    while (true) {
        $odometer_km = $jarak_tempuh_total / 1000; // Konversi meter ke KM untuk tampilan info awal
        echo "\n================================================\n";
        echo "Posisi Odometer Saat Ini    : " . number_format($odometer_km, 2, ',', '.') . " KM\n";
        echo "Sisa Bensin Saat Ini        : " . number_format($bensin_saat_ini, 2, ',', '.') . " Liter\n";
        echo "------------------------------------------------\n";
        
        echo "Masukkan Jarak Target Perjalanan (Meter) : ";
        $input_jarak_baru = trim(fgets(STDIN)); // Ambil input target jarak dari user

        echo "Masukkan Batas Kecepatan Motor (km/jam)  : ";
        $input_kecepatan = trim(fgets(STDIN)); // Ambil input kecepatan dari user

        // PENGAMAN 1: Cek apakah ada input yang dikosongkan (langsung enter)?
        if ($input_jarak_baru === '' || $input_kecepatan === '') {
            echo "❌ ERROR: Input tidak boleh kosong oii!\n";
            continue; // Minta input ulang dari atas sub-loop
        }

        // PENGAMAN 2 (REGEX FILTER): Memastikan string HARUS berisi karakter angka 0-9 dari awal sampai akhir.
        // Gak boleh disisipi huruf, spasi, minus (-), titik (.), koma (,), atau simbol lainnya!
        if (!preg_match('/^[0-9]+$/', $input_jarak_baru) || !preg_match('/^[0-9]+$/', $input_kecepatan)) {
            echo "❌ ERROR: INPUT DITOLAK! Hanya boleh diketik ANGKA MURNI saja!\n";
            echo "          (Dilarang pakai huruf, spasi, minus '-', atau simbol lainnya)\n";
            continue; // Minta input ulang dari atas sub-loop
        }

        // PENGAMAN 3: Memastikan nilainya tidak boleh angka 0 atau minus
        if (intval($input_jarak_baru) <= 0 || intval($input_kecepatan) <= 0) {
            echo "❌ ERROR: Nilai angka harus lebih besar dari 0!\n";
            continue; // Minta input ulang dari atas sub-loop
        }

        // Jika lolos semua pengaman, ubah string input tadi menjadi tipe data Integer (Angka bulat)
        $jarak_target_user = intval($input_jarak_baru);
        $kecepatan_user    = intval($input_kecepatan);
        break; // Keluar dari sub-loop validasi input karena data sudah aman
    }

    echo "\n=========================================\n";
    echo "       MOTOR KEMBALI MELAJU...           \n";
    echo "=========================================\n";
    echo "Target Jarak Pas        : " . number_format($jarak_target_user, 0, ',', '.') . " Meter\n";
    echo "Kecepatan Motor (Kunci) : " . $kecepatan_user . " km/jam\n";
    echo "=========================================\n";
    sleep(2); // Menahan program selama 2 detik untuk efek dramatis/loading sebelum melaju

    // Bersihkan layar sekali tepat sebelum spidometer live dimulai biar bersih
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') { popen('cls', 'w'); } else { system('clear'); }

    $jarak_tertempuh_sesi_ini = 0; // Reset hitungan jarak untuk sesi perjalanan saat ini
    $target_akhir_odometer = $jarak_tempuh_total + $jarak_target_user; // Kunci target akhir odometer

    // ===============================================================
    // SIMULASI PERJALANAN LIVE & REAL-TIME (BERJALAN PER DETIK)
    // ===============================================================
    while (true) {
        $kecepatan_kmjam_display = 0; // Wadah kosong untuk menampung display km/jam dari fungsi
        $kecepatan_mps_hitung = 0;   // Wadah kosong untuk menampung mps hasil hitungan fungsi
        
        // Memanggil fungsi konversi kecepatan. Nilai wadah di atas akan terisi otomatis (Pass-by-reference)
        dapatkanKecepatan($kecepatan_kmjam_display, $kecepatan_mps_hitung, $kecepatan_user);

        // Update hitungan Jarak Total Odometer dan Jarak Sesi ini secara real-time
        $jarak_tempuh_total       = hitungJarakTempuh($jarak_tempuh_total, $kecepatan_mps_hitung);
        $jarak_tertempuh_sesi_ini = hitungJarakTempuh($jarak_tertempuh_sesi_ini, $kecepatan_mps_hitung);

        // REM OTOMATIS: Logika agar motor berhenti presisi pas di target, tidak bablas/overshoot di detik terakhir
        $sudah_sampai = false;
        if ($jarak_tertempuh_sesi_ini >= $jarak_target_user) {
            $jarak_tertempuh_sesi_ini = $jarak_target_user;
            $jarak_tempuh_total       = $target_akhir_odometer;
            $sudah_sampai             = true;
        }

        // Potong jumlah bensin berdasarkan jarak yang sudah dilewati dalam 1 detik ini
        $bensin_saat_ini = hitungSisaBensin($bensin_saat_ini, $kecepatan_mps_hitung);

        // Jika bensin habis di detik ini, paksa semua angka kecepatan drop ke angka 0
        if ($bensin_saat_ini <= 0) {
            $bensin_saat_ini = 0;
            $kecepatan_kmjam_display = 0;
            $kecepatan_mps_hitung = 0;
        }

        // Hitung ulang sisa balok bar bensin yang berhak menyala di layar
        $bar_aktif = getJumlahBar($bensin_saat_ini, $bensin_awal, $total_bar);
        
        // Hitung sisa jarak sesi ini dan hitung estimasi sisa waktu tiba: (Jarak / Kecepatan)
        $sisa_jarak_sesi_ini = $jarak_target_user - $jarak_tertempuh_sesi_ini;
        $sisa_waktu_detik = ($kecepatan_mps_hitung > 0) ? round($sisa_jarak_sesi_ini / $kecepatan_mps_hitung) : 0;
        
        // Mengubah format detik menjadi hitungan Menit dan Detik (Contoh: 90 detik jadi 01:30)
        $tampilan_menit = floor($sisa_waktu_detik / 60);
        $tampilan_detik = $sisa_waktu_detik % 60;
        $waktu_format   = sprintf("%02d:%02d", $tampilan_menit, $tampilan_detik);

        // LOGIKA ALARM NOTIFIKASI HAMPIR TIBA (Memicu teks peringatan saat sisa jarak <= 10% dari target)
        $notifikasi_status = "";
        if ($sisa_jarak_sesi_ini > 0 && $sisa_jarak_sesi_ini <= ($jarak_target_user * 0.1)) {
            $notifikasi_status = "⚠️  [NOTIFIKASI: ANDA HAMPIR TIBA DI TUJUAN!] ⚠️\n------------------------------------------------\n";
        }

        // Konversi angka murni Meter ke dalam satuan Kilometer (KM) untuk display spidometer
        $odometer_display_km = $jarak_tempuh_total / 1000;
        $sisa_jarak_display_km = $sisa_jarak_sesi_ini / 1000;

        // KUNCI UTAMA (ANTI-KEDIP): Menaikkan kursor terminal kembali ke pojok kiri atas.
        // Menggantikan perintah cls/clear bawaan agar teks lama langsung tertimpa dengan mulus.
        echo "\e[H";

        // ===============================================================
        // VISUALISASI PANEL SPEEDOMETER YAMAHA LEXI CUSTOM (GUI TERMINAL)
        // ===============================================================
        echo "================================================\n";
        echo "               PANEL SPEEDOMETER                \n";
        echo "================================================\n";
        echo $notifikasi_status; // Menampilkan teks alarm jika kondisi 10% terpenuhi
        
        // Logika cetak bar bensin vertikal: Jika $bar_aktif memenuhi syarat tingkatan, cetak karaktek "◢◤"
        echo "  [F] " . ($bar_aktif >= 6 ? "◢◤" : "  ") . "      | \n";
        echo "      " . ($bar_aktif >= 5 ? "◢◤" : "  ") . "      |    Sisa Waktu : " . $waktu_format . " Mnt\n";
        echo "      " . ($bar_aktif >= 4 ? "◢◤" : "  ") . "      |    Sisa Jarak : " . number_format($sisa_jarak_display_km, 2, ',', '.') . " KM lagi\n";
        echo "      " . ($bar_aktif >= 3 ? "◢◤" : "  ") . "      |    Target Sesi: " . number_format($jarak_target_user / 1000, 2, ',', '.') . " KM\n";
        echo "      " . ($bar_aktif >= 2 ? "◢◤" : "  ") . "      |    Limit User : " . $kecepatan_user . " km/h\n";
        echo "  [E] " . ($bar_aktif >= 1 ? "◢◤" : "  ") . "      |    " . ($sudah_sampai ? 0 : $kecepatan_kmjam_display) . " km/h\n";
        
        echo "------------------------------------------------\n";
        echo " Odometer Total : " . number_format($odometer_display_km, 2, ',', '.') . " KM  |  Bensin: " . number_format($bensin_saat_ini, 2, ',', '.') . " L (" . $bar_aktif . "/6)\n"; 
        echo "================================================\n";

        // Amankan data Odometer terbaru ke file teks secara real-time setiap detiknya
        simpanDataKeFile($file_spidometer, $jarak_tempuh_total);

        // Jika bensin habis total saat berjalan, hentikan paksa 2 tingkatan loop sekaligus (Mogok di jalan)
        if ($bensin_saat_ini <= 0) {
            echo "\n❌ MOGOK! Bensin habis di jalan.\n";
            break 2; 
        }

        // Jika jarak yang ditempuh sudah menyamai target, kunci jeda sebentar biar render 00:00 terbaca lalu break
        if ($sudah_sampai) {
            sleep(1);
            break; 
        }

        sleep(1); // Menahan jalannya code selama 1 detik sebelum melakukan perulangan berikutnya
    }

    echo "\n🏁 SAMPAI! Motor sudah sampai di tujuan sesi ini.\n";

    // MENU INTERAKTIF JALAN LAGI (Validasi agar user hanya menekan tombol y atau n)
    while (true) {
        echo "\nMau lanjut berkendara lagi? (y/n): ";
        $tanya = strtolower(trim(fgets(STDIN))); // Membaca jawaban user
        if ($tanya !== 'y' && $tanya !== 'n') {
            echo "❌ INPUT SALAH! Ketik 'y' atau 'n'.\n";
            continue; // Ulangi pertanyaan jika input salah
        }
        break; // Keluar dari loop validasi menu jika input benar
    }

    // Jika user memilih tidak ('n'), hancurkan loop utama untuk mengakhiri petualangan
    if ($tanya === 'n') {
        echo "\nMotor diparkir. Perjalanan selesai!\n";
        break; 
    }
}

// ===================================================================
// REKAPAN AKUMULATIF AKHIR (Halaman terakhir saat program ditutup)
// ===================================================================
$odometer_akhir_km = $jarak_tempuh_total / 1000; // Konversi hasil final ke satuan KM
echo "\n=========================================\n";
echo "           REKAPAN AKHIR SPEEDOMETER          \n";
echo "=========================================\n";
echo "Total Jarak Speedometer : " . number_format($odometer_akhir_km, 2, ',', '.') . " KM\n";
echo "Sisa Bensin Akhir       : " . number_format($bensin_saat_ini, 2, ',', '.') . " Liter\n";
echo "=========================================\n";
?>