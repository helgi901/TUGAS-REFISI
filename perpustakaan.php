<?php
// =========================================================================
// --- KONFIGURASI NAMA FILE CSV ---
// Menetapkan nama file penyimpanan data agar mudah dipanggil di seluruh fungsi
// =========================================================================
define('FILE_BUKU', 'buku.csv');            // Nama file untuk menyimpan database buku
define('FILE_PEMINJAM', 'peminjam.csv');    // Nama file untuk menyimpan database anggota/peminjam
define('FILE_PEMINJAMAN', 'peminjaman.csv');// Nama file untuk menyimpan riwayat transaksi pinjam-kembali

// =========================================================================
// --- KODE WARNA TERMINAL VERSI KALEM & ADEM (PASTEL) ---
// Menggunakan ANSI Escape Code untuk menghias tampilan teks di terminal CLI
// =========================================================================
define('RESET', "\033[0m");     // Menormalkan kembali warna teks agar tidak bocor ke baris bawah
define('BOLD', "\033[1m");      // Membuat teks menjadi tebal (bold)
define('MERAH_SOFT', "\033[91m");     // Warna merah pastel untuk notifikasi error / status terlambat
define('HIJAU_SOFT', "\033[92m");     // Warna hijau pastel untuk notifikasi sukses
define('KUNING_SOFT', "\033[93m");    // Warna kuning pasir untuk judul menu proses transaksi
define('ABU_ABU', "\033[90m");         // Warna abu-abu untuk hiasan garis pembatas (border)
define('CYAN_SOFT', "\033[96m");       // Warna biru muda pastel khusus untuk teks penunjuk input data
define('PUTIH_MUTIARA', "\033[97m");   // Warna putih bersih untuk teks judul utama aplikasi
define('BG_ABU_GELAP', "\033[100m");   // Warna latar belakang (background) abu-abu untuk judul banner

// =========================================================================
// --- 1. INISIALISASI FILE CSV ---
// Fungsi untuk mengecek dan membuat file database baru jika filenya belum ada
// =========================================================================
function inisialisasi_file() {
    if (!file_exists(FILE_BUKU)) {
        $f = fopen(FILE_BUKU, 'w');
        fwrite($f, "isbn        ,judul                     ,stok \r\n");
        fclose($f);
    }
    if (!file_exists(FILE_PEMINJAM)) {
        $f = fopen(FILE_PEMINJAM, 'w');
        fwrite($f, "ktp               ,nama                ,email                \r\n");
        fclose($f);
    }
    if (!file_exists(FILE_PEMINJAMAN)) {
        $f = fopen(FILE_PEMINJAMAN, 'w');
        fwrite($f, "id_pinjam  ,ktp               ,isbn        ,tgl_pinjam  ,tgl_harus_kembali  ,tgl_kembali ,status         \r\n");
        fclose($f);
    }
}

// =========================================================================
// --- 2. FUNGSI BACA CSV ---
// Fungsi untuk mengambil data mentah dari file teks CSV lalu diubah menjadi array PHP
// =========================================================================
function baca_csv($nama_file) {
    $data = [];
    if (($f = fopen($nama_file, 'r')) !== FALSE) {
        $headers = fgetcsv($f);
        if (!$headers) return [];
        $headers = array_map('trim', $headers);
        
        while (($row = fgetcsv($f)) !== FALSE) {
            if (empty($row) || $row[0] === null) continue;
            $row = array_map('trim', $row);
            if (count($headers) == count($row)) {
                $data[] = array_combine($headers, $row);
            }
        }
        fclose($f);
    }
    return $data;
}

// =========================================================================
// --- 3. FUNGSI TULIS CSV MANUWAL ---
// Fungsi untuk menulis ulang seluruh data dari PHP kembali menjadi file teks CSV yang rapi
// =========================================================================
function tulis_csv($nama_file, $headers, $data) {
    $f = fopen($nama_file, 'w');
    $lebar_kolom = [
        'isbn'              => 12,
        'judul'             => 25,
        'stok'              => 5,
        'ktp'               => 18,
        'nama'              => 20,
        'email'             => 20,
        'id_pinjam'         => 10,
        'tgl_pinjam'        => 12,
        'tgl_harus_kembali' => 18,
        'tgl_kembali'       => 12,
        'status'            => 15
    ];

    $line_header = [];
    foreach ($headers as $key) {
        $lebar = $lebar_kolom[$key] ?? 12;
        $line_header[] = str_pad($key, $lebar, " ");
    }
    fwrite($f, implode(",", $line_header) . "\r\n");

    foreach ($data as $row) {
        $line = [];
        foreach ($headers as $key) {
            $val = $row[$key] ?? '';
            $lebar = $lebar_kolom[$key] ?? 12;
            $line[] = str_pad($val, $lebar, " ");
        }
        fwrite($f, implode(",", $line) . "\r\n");
    }
    fclose($f);
}

// =========================================================================
// --- 4. FUNGSI MENERIMA INPUT TERMINAL ---
// =========================================================================
function input($prompt) {
    echo CYAN_SOFT . $prompt . RESET;
    return trim(fgets(STDIN));
}

// =========================================================================
// --- 5. FITUR: TAMBAH BUKU BARU (MENU 1) ---
// =========================================================================
function tambah_buku() {
    echo "\n" . BOLD . CYAN_SOFT . "┌────────────────────────────────────────┐" . RESET . "\n";
    echo BOLD . CYAN_SOFT . "│           ➕ TAMBAH BUKU BARU            │" . RESET . "\n";
    echo BOLD . CYAN_SOFT . "└────────────────────────────────────────┘" . RESET . "\n";
    
    while (true) {
        $isbn = input(" ▹ Masukkan Nomor ISBN : "); 
        if ($isbn === "") {
            echo " " . MERAH_SOFT . "❌ ISBN tidak boleh kosong! Silakan isi kembali.\n" . RESET;
            continue;
        }
        break;
    }
    
    $semua_buku = baca_csv(FILE_BUKU);
    foreach ($semua_buku as $b) {
        if ($b['isbn'] == $isbn) {
            echo "\n" . MERAH_SOFT . " ❌ Gagal: Buku dengan ISBN tersebut sudah terdaftar!" . RESET . "\n";
            return;
        }
    }
    
    while (true) {
        $judul = input(" ▹ Masukkan Judul Buku : "); 
        if ($judul === "") {
            echo " " . MERAH_SOFT . "❌ Judul buku tidak boleh kosong! Silakan isi kembali.\n" . RESET;
            continue;
        }
        break;
    }
    
    $stok = 1;

    $semua_buku[] = ['isbn' => $isbn, 'judul' => $judul, 'stok' => $stok];
    tulis_csv(FILE_BUKU, ['isbn', 'judul', 'stok'], $semua_buku);
    echo "\n" . HIJAU_SOFT . " 🎉 Sukses: Buku '$judul' berhasil ditambahkan (Stok: 1)." . RESET . "\n";
}

// =========================================================================
// --- 6. FITUR: TAMBAH DATA PEMINJAM (MENU 2) ---
// =========================================================================
function tambah_peminjam() {
    echo "\n" . BOLD . CYAN_SOFT . "┌────────────────────────────────────────┐" . RESET . "\n";
    echo BOLD . CYAN_SOFT . "│        👥 TAMBAH DATA PEMINJAM         │" . RESET . "\n";
    echo BOLD . CYAN_SOFT . "└────────────────────────────────────────┘" . RESET . "\n";
    
    while (true) {
        $ktp = input(" ▹ Masukkan Nomor KTP : "); 
        if ($ktp === "") {
            echo " " . MERAH_SOFT . "❌ KTP tidak boleh kosong! Silakan isi kembali.\n" . RESET;
            continue;
        }
        break;
    }
    
    $semua_peminjam = baca_csv(FILE_PEMINJAM);
    foreach ($semua_peminjam as $p) {
        if ($p['ktp'] == $ktp) {
            echo "\n" . MERAH_SOFT . " ❌ Gagal: Nomor KTP sudah terdaftar!" . RESET . "\n";
            return;
        }
    }
    
    while (true) {
        $nama = input(" ▹ Masukkan Nama       : "); 
        if ($nama === "") {
            echo " " . MERAH_SOFT . "❌ Nama tidak boleh kosong! Silakan isi kembali.\n" . RESET;
            continue; 
        }
        break; 
    }
    
    // --- VALIDASI EMAIL DENGAN POLA REGEX KETAT (ANTI-TYPO) ---
    while (true) {
        $email = input(" ▹ Masukkan Email      : "); 
        
        if ($email === "") {
            echo " " . MERAH_SOFT . "❌ Email tidak boleh kosong! Silakan isi kembali.\n" . RESET;
            continue; 
        }
        
        // Pola Regex untuk memastikan domain diakhiri dengan ekstensi resmi (.com, .id, .co.id, .net, .org)
        // Dan mencegah tulisan typo seperti .kom, .gamil, dll.
        $pola_email = '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.(com|id|co\.id|net|org)$/i';
        
        if (!preg_match($pola_email, $email)) {
            echo " " . MERAH_SOFT . "❌ Format email salah atau typo! (Wajib akhiran resmi seperti .com, .id, .net)\n" . RESET;
            continue;
        }
        
        break; 
    }
    
    foreach ($semua_peminjam as $p) {
        if (strtolower($p['email']) == strtolower($email)) {
            echo "\n" . MERAH_SOFT . " ❌ Gagal: Email sudah digunakan oleh orang lain!" . RESET . "\n";
            return;
        }
    }

    $semua_peminjam[] = ['ktp' => $ktp, 'nama' => $nama, 'email' => $email];
    tulis_csv(FILE_PEMINJAM, ['ktp', 'nama', 'email'], $semua_peminjam);
    echo "\n" . HIJAU_SOFT . " 🎉 Sukses: Peminjam '$nama' berhasil didaftarkan." . RESET . "\n";
}

// =========================================================================
// --- 7. FITUR: PROSES PEMINJAMAN BUKU (MENU 3) ---
// =========================================================================
function pinjam_buku() {
    echo "\n" . BOLD . KUNING_SOFT . "┌────────────────────────────────────────┐" . RESET . "\n";
    echo BOLD . KUNING_SOFT . "│        📖 PROSES PEMINJAMAN BUKU       │" . RESET . "\n";
    echo BOLD . KUNING_SOFT . "└────────────────────────────────────────┘" . RESET . "\n";
    
    while (true) {
        $ktp = input(" ▹ Masukkan Nomor KTP Peminjam : "); 
        if ($ktp === "") {
            echo " " . MERAH_SOFT . "❌ KTP tidak boleh kosong! Silakan isi kembali.\n" . RESET;
            continue;
        }
        break;
    }
    
    $semua_peminjam = baca_csv(FILE_PEMINJAM);
    $peminjam_ada = false;
    foreach ($semua_peminjam as $p) {
        if ($p['ktp'] == $ktp) { $peminjam_ada = true; break; }
    }
    if (!$peminjam_ada) {
        echo "\n" . MERAH_SOFT . " ❌ Gagal: Peminjam belum terdaftar sebagai anggota!" . RESET . "\n";
        return;
    }

    $riwayat_pinjam = baca_csv(FILE_PEMINJAMAN);
    foreach ($riwayat_pinjam as $rp) {
        if (trim($rp['ktp']) == $ktp && $rp['tgl_kembali'] == '-') {
            echo "\n" . MERAH_SOFT . " ❌ Gagal: Orang ini masih meminjam buku lain (ISBN: {$rp['isbn']})!" . RESET . "\n";
            return;
        }
    }

    $semua_buku = baca_csv(FILE_BUKU);
    $indeks_buku = -1;
    $isbn = "";

    while (true) {
        echo "\n" . ABU_ABU . " (Ketik 'batal' jika ingin kembali ke menu utama)" . RESET . "\n";
        $isbn = input(" ▹ Masukkan ISBN Buku          : ");

        if ($isbn === "") {
            echo " " . MERAH_SOFT . "❌ ISBN tidak boleh kosong!\n" . RESET;
            continue;
        }

        if (strtolower($isbn) === 'batal') {
            echo "\n" . KUNING_SOFT . " ↩️  Peminjaman dibatalkan." . RESET . "\n";
            return;
        }

        $indeks_buku = -1;
        foreach ($semua_buku as $index => $b) {
            if ($b['isbn'] == $isbn) { $indeks_buku = $index; break; }
        }
            
        if ($indeks_buku == -1) {
            echo MERAH_SOFT . " ❌ Buku tidak ditemukan di database! Silakan coba lagi." . RESET . "\n";
            continue;
        }
            
        if ((int)$semua_buku[$indeks_buku]['stok'] <= 0) {
            echo MERAH_SOFT . " ❌ Buku ini sedang dipinjam orang lain! (Stok Kosong). Pilih buku lain." . RESET . "\n";
            continue;
        }

        break;
    }

    $lama_pinjam = (int)input(" ▹ Durasi Pinjam (Maks 30 hari): ");
    if ($lama_pinjam < 1 || $lama_pinjam > 30) {
        echo "\n" . MERAH_SOFT . " ❌ Gagal: Lama peminjaman harus antara 1 sampai 30 hari!" . RESET . "\n";
        return;
    }

    $tgl_pinjam = date('Y-m-d');
    $tgl_harus_kembali = date('Y-m-d', strtotime("+$lama_pinjam days"));

    $semua_buku[$indeks_buku]['stok'] = 0;
    tulis_csv(FILE_BUKU, ['isbn', 'judul', 'stok'], $semua_buku);

    $id_pinjam = "TRX" . str_pad(count($riwayat_pinjam) + 1, 3, "0", STR_PAD_LEFT);
    $riwayat_pinjam[] = [
        'id_pinjam'         => $id_pinjam,
        'ktp'               => $ktp,
        'isbn'              => $isbn,
        'tgl_pinjam'        => $tgl_pinjam,
        'tgl_harus_kembali' => $tgl_harus_kembali,
        'tgl_kembali'       => '-',
        'status'            => 'DIPINJAM'
    ];
    tulis_csv(FILE_PEMINJAMAN, ['id_pinjam', 'ktp', 'isbn', 'tgl_pinjam', 'tgl_harus_kembali', 'tgl_kembali', 'status'], $riwayat_pinjam);
    
    echo "\n" . HIJAU_SOFT . " 🎉 Sukses: Buku berhasil dipinjam!" . RESET . "\n";
    echo KUNING_SOFT . " 🗓️  Batas pengembalian: $tgl_harus_kembali" . RESET . "\n";
}

// =========================================================================
// --- 8. FITUR: PROSES PENGEMBALIAN BUKU (MENU 4) ---
// =========================================================================
function kembalikan_buku() {
    echo "\n" . BOLD . KUNING_SOFT . "┌────────────────────────────────────────┐" . RESET . "\n";
    echo BOLD . KUNING_SOFT . "│        ↩️  PROSES PENGEMBALIAN BUKU      │" . RESET . "\n";
    echo BOLD . KUNING_SOFT . "└────────────────────────────────────────┘" . RESET . "\n";
    
    while (true) {
        $ktp = input(" ▹ Masukkan Nomor KTP Peminjam: "); 
        if ($ktp === "") {
            echo " " . MERAH_SOFT . "❌ KTP tidak boleh kosong! Silakan isi kembali.\n" . RESET;
            continue;
        }
        break;
    }
    
    $riwayat_pinjam = baca_csv(FILE_PEMINJAMAN);
    $indeks_trx = -1;
    
    foreach ($riwayat_pinjam as $index => $rp) {
        if (trim($rp['ktp']) == $ktp && $rp['tgl_kembali'] == '-') {
            $indeks_trx = $index;
            break;
        }
    }
            
    if ($indeks_trx == -1) {
        echo "\n" . MERAH_SOFT . " ❌ Gagal: Tidak ada data peminjaman aktif untuk KTP ini." . RESET . "\n";
        return;
    }

    $tgl_kembali = date('Y-m-d');

    while (true) {
        $pilihan_tgl = strtolower(input(" ▹ Gunakan tanggal hari ini? (y/n): "));
        $tgl_kembali = date('Y-m-d');
        
        if ($pilihan_tgl != 'y') {
            echo "\n" . ABU_ABU . " (Ketik 'batal' jika ingin kembali ke menu utama)" . RESET . "\n";
            $tgl_kembali = input(" ▹ Masukkan tanggal (YYYY-MM-DD) : ");
            
            if ($tgl_kembali === "") {
                echo " " . MERAH_SOFT . "❌ Tanggal manual tidak boleh kosong!\n" . RESET;
                continue;
            }

            if (strtolower($tgl_kembali) === 'batal') {
                echo "\n" . KUNING_SOFT . " ↩️  Pengembalian dibatalkan." . RESET . "\n";
                return;
            }
        }

        $tgl_pinjam_asli = $riwayat_pinjam[$indeks_trx]['tgl_pinjam'];
        if (strtotime($tgl_kembali) < strtotime($tgl_pinjam_asli)) {
            echo "\n" . MERAH_SOFT . " ❌ Gagal: Tanggal pengembalian ($tgl_kembali) tidak valid karena mendahului tanggal pinjam ($tgl_pinjam_asli)!" . RESET . "\n";
            echo KUNING_SOFT . " 👉 Silakan masukkan ulang tanggal yang benar.\n" . RESET;
            continue;
        }

        break;
    }

    $batas_kembali = $riwayat_pinjam[$indeks_trx]['tgl_harus_kembali'];
    
    if (strtotime($tgl_kembali) <= strtotime($batas_kembali)) {
        $status_akhir = "TEPAT WAKTU";
        echo "\n" . HIJAU_SOFT . " ✅ Sukses: Buku dikembalikan TEPAT WAKTU. Terima kasih!" . RESET . "\n";
    } else {
        $selisih_detik = strtotime($tgl_kembali) - strtotime($batas_kembali);
        $selisih_hari = round($selisih_detik / 86400);
        $status_akhir = "TERLAMBAT ($selisih_hari Hari)";
        echo "\n" . MERAH_SOFT . " ⚠️ Perhatian: Pengembalian TERLAMBAT $selisih_hari hari!" . RESET . "\n";
    }

    $riwayat_pinjam[$indeks_trx]['tgl_kembali'] = $tgl_kembali;
    $riwayat_pinjam[$indeks_trx]['status'] = $status_akhir;
    tulis_csv(FILE_PEMINJAMAN, ['id_pinjam', 'ktp', 'isbn', 'tgl_pinjam', 'tgl_harus_kembali', 'tgl_kembali', 'status'], $riwayat_pinjam);

    $semua_buku = baca_csv(FILE_BUKU);
    foreach ($semua_buku as &$b) {
        if ($b['isbn'] == $riwayat_pinjam[$indeks_trx]['isbn']) {
            $b['stok'] = 1;
            break;
        }
    }
    tulis_csv(FILE_BUKU, ['isbn', 'judul', 'stok'], $semua_buku);
}

// =========================================================================
// --- 9. MENU UTAMA LOOP ---
// =========================================================================
inisialisasi_file();

while (true) {
    echo "\n" . ABU_ABU . "────────────────────────────────────────────────" . RESET . "\n";
    echo BOLD . BG_ABU_GELAP . PUTIH_MUTIARA . "   📚 PERPUSTAKAAN (HELGI) 📚   " . RESET . "\n";
    echo ABU_ABU . "────────────────────────────────────────────────" . RESET . "\n";
    echo " " . ABU_ABU . "1." . RESET . " 📋 Tambah Buku Baru\n";
    echo " " . ABU_ABU . "2." . RESET . " 👥 Tambah Data Peminjam\n";
    echo " " . ABU_ABU . "3." . RESET . " 📖 Proses Peminjaman Buku\n";
    echo " " . ABU_ABU . "4." . RESET . " ↩️  Proses Pengembalian Buku\n";
    echo " " . ABU_ABU . "5." . RESET . " 🚪 Keluar Aplikasi\n";
    echo ABU_ABU . "────────────────────────────────────────────────" . RESET . "\n";
    $pilihan = input("👉 Pilih menu (1-5): ");
    
    switch ($pilihan) {
        case '1': tambah_buku(); break;
        case '2': tambah_peminjam(); break;
        case '3': pinjam_buku(); break;
        case '4': kembalikan_buku(); break;
        case '5':
            echo "\n" . BOLD . KUNING_SOFT . " 👋 Keluar dari sistem. Terima kasih banyak!" . RESET . "\n\n"; 
            exit;
        default:
            echo "\n" . MERAH_SOFT . " ❌ Pilihan tidak valid, silakan coba lagi." . RESET . "\n";
    }
    
    input("\n Press [Enter] to continue...");
}