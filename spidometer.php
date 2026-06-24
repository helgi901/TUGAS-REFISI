<?php
// ===================================================================
// KONSTANTA & SETTING AWAL SPIDOMETER (FORMAT ANGKA MURNI)
// ===================================================================
$bensin_awal      = 4.0;              // Menentukan kapasitas maksimal bensin awal motor (4 Liter)
$total_bar        = 6;                // Menentukan jumlah tingkatan bar bensin pada layar speedometer
$file_spidometer  = "jarak_total.txt"; // Menentukan nama file eksternal untuk menyimpan data odometer

// LOGIKA LOAD DATA: Cek apakah file penyimpanan ada dan isinya tidak kosong?
if (file_exists($file_spidometer) && filesize($file_spidometer) > 0) {
    // Jika file ada, ambil isinya dan hapus spasi/karakter tak terlihat di awal/akhir
    $isi_file = trim(file_get_contents($file_spidometer));
    // Mengonversi teks dari file menjadi angka desimal (float) untuk jarak tempuh total
    $jarak_tempuh_total = floatval($isi_file); 
    
    // Menghitung konsumsi BBM: Diatur dengan asumsi hitungan 1 Liter = 1.000 Meter (1 KM)
    $bensin_terpakai = $jarak_tempuh_total / 1000;
    // Mengurangi bensin awal dengan bensin yang sudah terpakai
    $bensin_saat_ini = 4.0 - $bensin_terpakai;
    // Pengaman: Jika hasil perhitungan bensin minus, paksa set ke angka 0
    if ($bensin_saat_ini < 0) {
        $bensin_saat_ini = 0;
    }
} else {
    // Jika file tidak ditemukan atau kosong, set data perjalanan dari awal (0)
    $jarak_tempuh_total = 0.0; 
    // Set bensin penuh kembali ke 4 Liter
    $bensin_saat_ini    = 4.0; 
}

// ===================================================================
// DAFTAR FUNGSI-FUNGSI LOGIKA UTAMA SISTEM
// ===================================================================

// Fungsi untuk menulis dan memperbarui data odometer terbaru ke dalam file txt
function simpanDataKeFile($file, $odometer) {
    // Menyimpan data angka desimal dengan format 2 angka di belakang koma (.2f)
    file_put_contents($file, sprintf("%.2f", $odometer));
}

// Fungsi untuk menghitung pertambahan jarak tempuh berdasarkan kecepatan meter per detik
function hitungJarakTempuh($jarak_sekarang, $kecepatan_mps) {
    // Jarak saat ini ditambah dengan jarak baru yang ditempuh dalam 1 detik
    return $jarak_sekarang + $kecepatan_mps;
}

// Fungsi untuk menghitung pengurangan bensin berdasarkan kecepatan real-time
function hitungSisaBensin($bensin_sekarang, $kecepatan_mps) {
    // Mengonversi jarak yang ditempuh (mps) menjadi bensin yang terpakai
    $bensin_terpakai = $kecepatan_mps / 1000; 
    // Mengurangi bensin yang ada dengan bensin yang baru saja terpakai
    $sisa = $bensin_sekarang - $bensin_terpakai;
    // Mengembalikan nilai sisa bensin, jika di bawah 0 maka otomatis mengembalikan nilai 0
    return ($sisa < 0) ? 0 : $sisa;
}

// Fungsi untuk menghitung berapa jumlah bar bensin yang harus menyala di speedometer
function getJumlahBar($sisa_bensin, $bensin_awal, $total_bar) {
    // Jika bensin habis atau 0, maka tidak ada bar yang menyala (0 bar)
    if ($sisa_bensin <= 0) return 0;
    // Menghitung rasio bensin, dikali total bar, lalu dibulatkan ke bawah dengan floor()
    return (int) floor(($sisa_bensin / $bensin_awal) * $total_bar);
}

// Fungsi dengan teknik Pass-by-Reference (tanda &) untuk mengubah variabel asli secara langsung
function dapatkanKecepatan(&$kmjam, &$mps, $input_user_kmjam) {
    // Mengisi variabel kmjam asli dengan nilai input dari user
    $kmjam = $input_user_kmjam; 
    // Mengonversi satuan km/jam menjadi Meter per Detik (mps) dengan membaginya 3.6
    $mps   = $kmjam / 3.6; 
}

// ===================================================================
// LOOP BESAR UTAMA PROGRAM (Akan terus berjalan sampai user keluar)
// ===================================================================
while (true) {
    
    // Cek kondisi di awal loop: Apakah bensin sudah habis?
    if ($bensin_saat_ini <= 0) {
        echo "\n❌ TIDAK BISA JALAN! Bensin sudah habis total.\n";
        echo "Mau isi bensin full lagi (Odometer bakal di-reset ke 0)? (y/n): ";
        // Mengambil input konfirmasi dari user melalui terminal
        $isi_bensin = strtolower(trim(fgets(STDIN)));
        // Jika user setuju (mengetik 'y')
        if ($isi_bensin === 'y') {
            $jarak_tempuh_total = 0.0; // Reset odometer ke 0
            $bensin_saat_ini = 4.0;    // Isi bensin full lagi ke 4 Liter
            simpanDataKeFile($file_spidometer, $jarak_tempuh_total); // Simpan perubahan ke file txt
            echo "✅ Bensin diisi penuh! Silakan masukkan jarak kembali.\n";
            continue; // Lompat kembali ke awal loop utama untuk meminta input jarak baru
        } else {
            // Jika user menolak ('n'), program dihentikan
            echo "Motor diparkir dalam kondisi mogok. Perjalanan selesai!\n";
            break; // Keluar dari loop besar utama (selesai)
        }
    }
    
    // SUB-LOOP VALIDASI INPUT (Memastikan input user murni angka dan tidak kosong)
    while (true) {
        // Mengonversi meter ke kilometer untuk tampilan status awal
        $odometer_km = $jarak_tempuh_total / 1000;
        echo "\n================================================\n";
        echo "Posisi Odometer Saat Ini    : " . number_format($odometer_km, 2, ',', '.') . " KM\n";
        echo "Sisa Bensin Saat Ini        : " . number_format($bensin_saat_ini, 2, ',', '.') . " Liter\n";
        echo "------------------------------------------------\n";
        
        echo "Masukkan Jarak Target Perjalanan (Meter) : ";
        $input_jarak_baru = trim(fgets(STDIN)); // Mengambil input jarak target dari terminal

        echo "Masukkan Batas Kecepatan Motor (km/jam)  : ";
        $input_kecepatan = trim(fgets(STDIN)); // Mengambil input kecepatan motor dari terminal

        // PENGAMAN: Validasi jika salah satu atau kedua input dibiarkan kosong oleh user
        if ($input_jarak_baru === '' || $input_kecepatan === '') {
            echo "❌ ERROR: Input tidak boleh kosong oii!\n";
            continue; // Ulangi sub-loop untuk meminta input kembali
        }

        // REGEX FILTER: Memastikan string input hanya berisi karakter angka murni 0 sampai 9
        if (!preg_match('/^[0-9]+$/', $input_jarak_baru) || !preg_match('/^[0-9]+$/', $input_kecepatan)) {
            echo "❌ ERROR: INPUT DITOLAK! Hanya boleh diketik ANGKA MURNI saja!\n";
            echo "          (Dilarang pakai huruf, spasi, minus '-', atau simbol lainnya)\n";
            continue; // Ulangi sub-loop jika terdeteksi karakter ilegal
        }

        // Validasi nilai: Memastikan angka yang dimasukkan harus di atas 0 (tidak boleh 0 atau minus)
        if (intval($input_jarak_baru) <= 0 || intval($input_kecepatan) <= 0) {
            echo "❌ ERROR: Nilai angka harus lebih besar dari 0!\n";
            continue; // Ulangi sub-loop jika input bernilai 0
        }

        // Jika lolos semua validasi, konversi string input menjadi tipe data Integer (angka bulat)
        $jarak_target_user = intval($input_jarak_baru);
        $kecepatan_user    = intval($input_kecepatan);
        break; // Keluar dari sub-loop validasi dan lanjut ke proses simulasi
    }

    echo "\n=========================================\n";
    echo "       MOTOR KEMBALI MELAJU...           \n";
    echo "=========================================\n";
    echo "Target Jarak Pas        : " . number_format($jarak_target_user, 0, ',', '.') . " Meter\n";
    echo "Kecepatan Motor (Kunci) : " . $kecepatan_user . " km/jam\n";
    echo "=========================================\n";
    sleep(2); // Menahan proses selama 2 detik untuk memberikan jeda visual sebelum layar dibersihkan

    // Perintah untuk membersihkan layar terminal secara total berdasarkan jenis Sistem Operasi (OS)
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') { system('cls'); } else { system('clear'); }

    $jarak_tertempuh_sesi_ini = 0; // Mengeset hitungan jarak sesi baru dari 0 meter
    $target_akhir_odometer = $jarak_tempuh_total + $jarak_target_user; // Menghitung target angka akhir odometer

    // ===============================================================
    // SIMULASI PERJALANAN LIVE & REAL-TIME (Looping per detik)
    // ===============================================================
    while (true) {
        $kecepatan_kmjam_display = 0; // Inisialisasi variabel penampung display kecepatan km/jam
        $kecepatan_mps_hitung = 0;    // Inisialisasi variabel penampung hitungan kecepatan meter/detik
        
        // Memanggil fungsi pass-by-reference untuk memproses nilai kecepatan dari user
        dapatkanKecepatan($kecepatan_kmjam_display, $kecepatan_mps_hitung, $kecepatan_user);

        // Memperbarui total data odometer dan akumulasi jarak tempuh di sesi berjalan saat ini
        $jarak_tempuh_total       = hitungJarakTempuh($jarak_tempuh_total, $kecepatan_mps_hitung);
        $jarak_tertempuh_sesi_ini = hitungJarakTempuh($jarak_tertempuh_sesi_ini, $kecepatan_mps_hitung);

        // Rem otomatis: Jika pergerakan melampaui target, paksa nilainya agar pas presisi di angka target
        if ($jarak_tertempuh_sesi_ini >= $jarak_target_user) {
            $jarak_tertempuh_sesi_ini = $jarak_target_user;
            $jarak_tempuh_total       = $target_akhir_odometer;
        }

        // Mengurangi kapasitas bensin secara berkala sesuai jarak per detik yang sudah dilalui
        $bensin_saat_ini = hitungSisaBensin($bensin_saat_ini, $kecepatan_mps_hitung);

        // Jika bensin habis di tengah simulasi, paksa semua sistem kecepatan dan hitungan menjadi 0 (berhenti)
        if ($bensin_saat_ini <= 0) {
            $bensin_saat_ini = 0;
            $kecepatan_kmjam_display = 0;
            $kecepatan_mps_hitung = 0;
        }

        // Mendapatkan jumlah indikator kotak/bar bensin aktif untuk dicetak ke panel visual
        $bar_aktif = getJumlahBar($bensin_saat_ini, $bensin_awal, $total_bar);
        
        // REFRESH PANEL TERMINAL (ANTI-KEDIP & ANTI-NUMPUK)
        // \e[H = Memindahkan kursor ke pojok kiri atas | \e[J = Menghapus sisa karakter ke bawah
        echo "\e[H\e[J"; 
        
        // Perhitungan sisa waktu perjalanan: Sisa Jarak (meter) dibagi Kecepatan (meter per detik)
        $sisa_jarak_sesi_ini = $jarak_target_user - $jarak_tertempuh_sesi_ini;
        $sisa_waktu_detik = ($kecepatan_mps_hitung > 0) ? round($sisa_jarak_sesi_ini / $kecepatan_mps_hitung) : 0;
        
        // --- LOGIKA PERBAIKAN WAKTU OLEH GURU ---
        // Memformat hitungan total detik menjadi satuan Jam, Menit, dan Detik jika > 60 menit
        $tampilan_menit_total = floor($sisa_waktu_detik / 60);
        $tampilan_detik = $sisa_waktu_detik % 60;

        if ($tampilan_menit_total >= 60) {
            $tampilan_jam = floor($tampilan_menit_total / 60);
            $tampilan_menit = $tampilan_menit_total % 60;
            $waktu_format = sprintf("%d Jam %02d Mnt", $tampilan_jam, $tampilan_menit);
        } else {
            $waktu_format = sprintf("%02d:%02d Mnt", $tampilan_menit_total, $tampilan_detik);
        }

        // LOGIKA ALARM NOTIFIKASI HAMPIR TIBA (Aktif otomatis saat sisa jarak berada di bawah atau sama dengan 10%)
        $notifikasi_status = ""; 
        if ($sisa_jarak_sesi_ini > 0 && $sisa_jarak_sesi_ini <= ($jarak_target_user * 0.1)) {
            $notifikasi_status = "⚠️  [NOTIFIKASI: ANDA HAMPIR TIBA DI TUJUAN!] ⚠️\n------------------------------------------------\n";
        }

        // Mengonversi data tampilan meter ke kilometer (KM) agar mudah dibaca manusia
        $odometer_display_km = $jarak_tempuh_total / 1000;
        $sisa_jarak_display_km = $sisa_jarak_sesi_ini / 1000;

        // ===============================================================
        // VISUALISASI PANEL SPEEDOMETER YAMAHA LEXI CUSTOM (Cetak Tampilan)
        // ===============================================================
        echo "================================================\n";
        echo "                 PANEL SPEEDOMETER              \n";
        echo "================================================\n";
        echo $notifikasi_status; // Mencetak teks peringatan jika status hampir tiba terpenuhi
        
        // Logika Ternary bertingkat untuk mencetak bar bensin ("◢◤" jika aktif, atau spasi jika kosong)
        echo "  [F] " . ($bar_aktif >= 6 ? "◢◤" : "  ") . "      | \n";
        echo "      " . ($bar_aktif >= 5 ? "◢◤" : "  ") . "      |    Sisa Waktu : " . $waktu_format . "\n";
        echo "      " . ($bar_aktif >= 4 ? "◢◤" : "  ") . "      |    Sisa Jarak : " . number_format($sisa_jarak_display_km, 2, ',', '.') . " KM lagi\n";
        echo "      " . ($bar_aktif >= 3 ? "◢◤" : "  ") . "      |    Target Sesi: " . number_format($jarak_target_user / 1000, 2, ',', '.') . " KM\n";
        echo "      " . ($bar_aktif >= 2 ? "◢◤" : "  ") . "      |    Kecepatan  : " . $kecepatan_kmjam_display . " km/h\n"; // Mengganti Limit User jadi Kecepatan
        echo "  [E] " . ($bar_aktif >= 1 ? "◢◤" : "  ") . "      |    \n"; // Menghapus baris nilai terbawah sesuai instruksi guru
        
        echo "------------------------------------------------\n";
        echo " Odometer Total : " . number_format($odometer_display_km, 2, ',', '.') . " KM  |  Bensin: " . number_format($bensin_saat_ini, 2, ',', '.') . " L (" . $bar_aktif . "/6)\n"; 
        echo "================================================\n";

        // Menyimpan status kilometer terbaru ke dalam file setiap detiknya (Auto-save)
        simpanDataKeFile($file_spidometer, $jarak_tempuh_total);

        // Jika bensin bernilai 0, hentikan perjalanan seketika dan paksa keluar dari 2 tingkatan loop (break 2)
        if ($bensin_saat_ini <= 0) {
            echo "\n❌ MOGOK! Bensin habis di jalan.\n";
            break 2; 
        }

        // Jika jarak yang ditempuh sudah menyamai atau melewati target perjalanan, keluar dari loop live-simulasi
        if ($jarak_tertempuh_sesi_ini >= $jarak_target_user) {
            break; 
        }

        sleep(1); // Menahan simulasi selama 1 detik sebelum melakukan iterasi update berikutnya
    }

    echo "\n🏁 SAMPAI! Motor sudah sampai di tujuan sesi ini.\n";

    // MENU INTERAKTIF JALAN LAGI (Validasi respon user untuk lanjut atau tidak)
    while (true) {
        echo "\nMau lanjut berkendara lagi? (y/n): ";
        $tanya = strtolower(trim(fgets(STDIN))); // Mengambil opsi huruf input dari user
        if ($tanya !== 'y' && $tanya !== 'n') {
            echo "❌ INPUT SALAH! Ketik 'y' atau 'n'.\n";
            continue; // Ulangi pertanyaan jika input bukan 'y' atau 'n'
        }
        break; // Keluar dari loop konfirmasi jika input sudah valid
    }

    // Jika user memilih 'n' (tidak ingin lanjut), putus loop besar utama untuk mengakhiri program
    if ($tanya === 'n') {
        echo "\nMotor diparkir. Perjalanan selesai!\n";
        break; 
    } else {
        // [PERBAIKAN]: Clear screen total sebelum masuk ke input data sesi baru
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') { 
            system('cls'); 
        } else { 
            system('clear'); 
        }
    }
}

// ===================================================================
// REKAPAN AKUMULATIF AKHIR (Ditampilkan saat program selesai/keluar)
// ===================================================================
$odometer_akhir_km = $jarak_tempuh_total / 1000; // Konversi total akhir odometer ke KM
echo "\n=========================================\n";
echo "           REKAPAN AKHIR SPEEDOMETER          \n";
echo "=========================================\n";
echo "Total Jarak Speedometer : " . number_format($odometer_akhir_km, 2, ',', '.') . " KM\n";
echo "Sisa Bensin Akhir       : " . number_format($bensin_saat_ini, 2, ',', '.') . " Liter\n";
echo "=========================================\n";
?>