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
    // Jika file buku.csv tidak ditemukan di dalam folder...
    if (!file_exists(FILE_BUKU)) {
        $f = fopen(FILE_BUKU, 'w'); // Buat file baru dengan izin menulis ('w')
        // Tulis baris pertama sebagai nama kolom (header) dengan format spasi yang rapi
        fwrite($f, "isbn        ,judul                     ,stok \r\n");
        fclose($f); // Tutup file untuk mengunci perubahan dan menghemat memori
    }
    // Jika file peminjam.csv tidak ditemukan di dalam folder...
    if (!file_exists(FILE_PEMINJAM)) {
        $f = fopen(FILE_PEMINJAM, 'w'); // Buat file baru
        // Tulis struktur kolom untuk database data anggota
        fwrite($f, "ktp               ,nama                ,email               \r\n");
        fclose($f); // Tutup file
    }
    // Jika file peminjaman.csv tidak ditemukan di dalam folder...
    if (!file_exists(FILE_PEMINJAMAN)) {
        $f = fopen(FILE_PEMINJAMAN, 'w'); // Buat file baru
        // Tulis struktur kolom untuk mencatat seluruh riwayat transaksi pinjam buku
        fwrite($f, "id_pinjam  ,ktp               ,isbn        ,tgl_pinjam  ,tgl_harus_kembali  ,tgl_kembali ,status         \r\n");
        fclose($f); // Tutup file
    }
}

// =========================================================================
// --- 2. FUNGSI BACA CSV ---
// Fungsi untuk mengambil data mentah dari file teks CSV lalu diubah menjadi array PHP
// =========================================================================
function baca_csv($nama_file) {
    $data = []; // Wadah kosong untuk menampung seluruh baris data hasil ekstraksi
    // Buka file target dengan status 'r' (Read/Hanya Baca). Jika berhasil, jalankan logika...
    if (($f = fopen($nama_file, 'r')) !== FALSE) {
        $headers = fgetcsv($f); // Ambil baris pertama sebagai nama kolom (header)
        if (!$headers) return []; // Jika filenya kosong melompong, langsung batalkan dan balikkan array kosong []
        $headers = array_map('trim', $headers); // Bersihkan spasi-spasi hantu di sekeliling nama kolom
        
        // Lakukan perulangan untuk membaca baris-baris data di bawah kolom satu per satu
        while (($row = fgetcsv($f)) !== FALSE) {
            // Jika mendapati baris kosong atau datanya rusak, lewati (skip) baris tersebut
            if (empty($row) || $row[0] === null) continue;
            $row = array_map('trim', $row); // Bersihkan spasi hantu pada isi data di baris tersebut
            // SATPAM DATA: Cek apakah jumlah kolom isi sama dengan jumlah kolom judul (mencegah crash)
            if (count($headers) == count($row)) {
                // Jodohkan nama kolom dengan isinya, lalu dorong ([]=) ke dalam kumpulan array data utama
                $data[] = array_combine($headers, $row);
            }
        }
        fclose($f); // Selesai membaca, putuskan koneksi file menggunakan fclose
    }
    return $data; // Kembalikan data yang sudah rapi dalam bentuk Array Asosiatif ke pemanggil fungsi
}

// =========================================================================
// --- 3. FUNGSI TULIS CSV MANUWAL ---
// Fungsi untuk menulis ulang seluruh data dari PHP kembali menjadi file teks CSV yang rapi
// =========================================================================
function tulis_csv($nama_file, $headers, $data) {
    $f = fopen($nama_file, 'w'); // Buka/buat ulang file dengan mode 'w' (menimpa isi file lama dari nol)
    // Aturan standar batasan jumlah karakter tiap kolom agar lurus rapi saat dibuka di Notepad
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

    $line_header = []; // Wadah baris header yang mau dirapikan
    // Susun baris judul kolom terlebih dahulu
    foreach ($headers as $key) {
        $lebar = $lebar_kolom[$key] ?? 12; // Ambil patokan lebar kolom, kalau tidak terdaftar defaultnya 12
        // Tambahkan spasi di ujung kanan teks menggunakan str_pad agar panjang karakternya pas sejajar
        $line_header[] = str_pad($key, $lebar, " ");
    }
    // Gabungkan array judul kolom dengan tanda koma (,), lalu ketikkan ke file dan beri tombol enter (\r\n)
    fwrite($f, implode(",", $line_header) . "\r\n");

    // Susun seluruh data baris demi baris di bawah judul kolom
    foreach ($data as $row) {
        $line = []; // Wadah untuk baris data yang sedang diproses
        foreach ($headers as $key) {
            $val = $row[$key] ?? ''; // Ambil isi datanya, jika kosong beri string kosong ''
            $lebar = $lebar_kolom[$key] ?? 12; // Ambil patokan lebar kolom
            $line[] = str_pad($val, $lebar, " "); // Beri spasi tambahan di kanan kata agar rata tabel
        }
        // Gabungkan dengan koma, ketik ke file CSV, lalu tekan enter untuk baris berikutnya
        fwrite($f, implode(",", $line) . "\r\n");
    }
    fclose($f); // Kunci file dengan menutup koneksinya
}

// =========================================================================
// --- 4. FUNGSI MENERIMA INPUT TERMINAL ---
// Fungsi pembantu untuk menampilkan pertanyaan sekaligus menangkap ketikan dari user
// =========================================================================
function input($prompt) {
    echo CYAN_SOFT . $prompt . RESET; // Tampilkan teks pertanyaan dengan warna biru muda pastel
    return trim(fgets(STDIN)); // Ambil hasil ketikan keyboard (STDIN) dan potong spasi/enter di ujungnya
}

// =========================================================================
// --- 5. FITUR: TAMBAH BUKU BARU (MENU 1) ---
// =========================================================================
function tambah_buku() {
    // Tampilkan hiasan box judul menu tambah buku
    echo "\n" . BOLD . CYAN_SOFT . "┌────────────────────────────────────────┐" . RESET . "\n";
    echo BOLD . CYAN_SOFT . "│          ➕ TAMBAH BUKU BARU           │" . RESET . "\n";
    echo BOLD . CYAN_SOFT . "└────────────────────────────────────────┘" . RESET . "\n";
    $isbn = input(" ▹ Masukkan Nomor ISBN : "); // Minta input nomor ISBN buku baru
    
    $semua_buku = baca_csv(FILE_BUKU); // Ambil daftar buku yang sudah tersimpan saat ini
    // VALIDASI DUPLIKASI: Sisir semua buku lama, pastikan ISBN baru tidak boleh sama dengan yang lama
    foreach ($semua_buku as $b) {
        if ($b['isbn'] == $isbn) {
            echo "\n" . MERAH_SOFT . " ❌ Gagal: Buku dengan ISBN tersebut sudah terdaftar!" . RESET . "\n";
            return; // Hentikan fungsi saat ini juga dan balik ke menu utama
        }
    }
    
    $judul = input(" ▹ Masukkan Judul Buku : "); // Minta input judul buku baru
    $stok = 1; // Sesuai aturan sistem, setiap buku baru otomatis memiliki stok berangka 1

    // Gabungkan data baru ke baris paling akhir dari tumpukan array data lama
    $semua_buku[] = ['isbn' => $isbn, 'judul' => $judul, 'stok' => $stok];
    // Tulis ulang seluruh isi array yang sudah diperbarui ke file buku.csv
    tulis_csv(FILE_BUKU, ['isbn', 'judul', 'stok'], $semua_buku);
    echo "\n" . HIJAU_SOFT . " 🎉 Sukses: Buku '$judul' berhasil ditambahkan (Stok: 1)." . RESET . "\n";
}

// =========================================================================
// --- 6. FITUR: TAMBAH DATA PEMINJAM (MENU 2) ---
// =========================================================================
function tambah_peminjam() {
    // Tampilkan hiasan box judul menu tambah peminjam
    echo "\n" . BOLD . CYAN_SOFT . "┌────────────────────────────────────────┐" . RESET . "\n";
    echo BOLD . CYAN_SOFT . "│        👥 TAMBAH DATA PEMINJAM         │" . RESET . "\n";
    echo BOLD . CYAN_SOFT . "└────────────────────────────────────────┘" . RESET . "\n";
    $ktp = input(" ▹ Masukkan Nomor KTP : "); // Minta input nomor KTP pendaftar baru
    
    $semua_peminjam = baca_csv(FILE_PEMINJAM); // Ambil daftar peminjam yang sudah terdaftar
    // VALIDASI KTP: Cek apakah nomor KTP sudah pernah dipakai atau belum
    foreach ($semua_peminjam as $p) {
        if ($p['ktp'] == $ktp) {
            echo "\n" . MERAH_SOFT . " ❌ Gagal: Nomor KTP sudah terdaftar!" . RESET . "\n";
            return; // Batalkan dan kembali ke menu utama
        }
    }
    
    $nama = input(" ▹ Masukkan Nama       : "); // Minta data nama lengkap
    $email = input(" ▹ Masukkan Email      : "); // Minta data alamat email
    
    // VALIDASI EMAIL: Sisir semua data, abaikan perbedaan huruf besar/kecil (strtolower)
    foreach ($semua_peminjam as $p) {
        if (strtolower($p['email']) == strtolower($email)) {
            echo "\n" . MERAH_SOFT . " ❌ Gagal: Email sudah digunakan oleh orang lain!" . RESET . "\n";
            return; // Batalkan dan kembali ke menu utama
        }
    }

    // Masukkan data anggota baru ke dalam array, lalu simpan permanen ke file peminjam.csv
    $semua_peminjam[] = ['ktp' => $ktp, 'nama' => $nama, 'email' => $email];
    tulis_csv(FILE_PEMINJAM, ['ktp', 'nama', 'email'], $semua_peminjam);
    echo "\n" . HIJAU_SOFT . " 🎉 Sukses: Peminjam '$nama' berhasil didaftarkan." . RESET . "\n";
}

// =========================================================================
// --- 7. FITUR: PROSES PEMINJAMAN BUKU (MENU 3) ---
// =========================================================================
function pinjam_buku() {
    // Tampilkan box hiasan judul menu peminjaman buku
    echo "\n" . BOLD . KUNING_SOFT . "┌────────────────────────────────────────┐" . RESET . "\n";
    echo BOLD . KUNING_SOFT . "│        📖 PROSES PEMINJAMAN BUKU       │" . RESET . "\n";
    echo BOLD . KUNING_SOFT . "└────────────────────────────────────────┘" . RESET . "\n";
    $ktp = input(" ▹ Masukkan Nomor KTP Peminjam : "); // Minta nomor KTP peminjam
    
    $semua_peminjam = baca_csv(FILE_PEMINJAM); // Baca data semua anggota
    $peminjam_ada = false; // Indikator penanda awal apakah KTP terdaftar atau tidak
    // Cari KTP di database anggota
    foreach ($semua_peminjam as $p) {
        if ($p['ktp'] == $ktp) { $peminjam_ada = true; break; } // Ketemu! Ubah indikator jadi true dan stop looping
    }
    // Jika indikator tetap false (artinya KTP asing tidak terdaftar)...
    if (!$peminjam_ada) {
        echo "\n" . MERAH_SOFT . " ❌ Gagal: Peminjam belum terdaftar sebagai anggota!" . RESET . "\n";
        return; // Tolak transaksi dan keluar dari fungsi
    }

    $riwayat_pinjam = baca_csv(FILE_PEMINJAMAN); // Ambil database riwayat transaksi pinjam buku
    // ATURAN PERPUSTAKAAN: Satu orang hanya boleh meminjam maksimal 1 buku aktif dalam satu waktu
    foreach ($riwayat_pinjam as $rp) {
        // Cek jika KTP tersebut terdaftar dalam transaksi dan kolom tanggal pengembaliannya masih strip '-' (artinya belum dikembalikan)
        if ($rp['ktp'] == $ktp && $rp['tgl_kembali'] == '-') {
            echo "\n" . MERAH_SOFT . " ❌ Gagal: Orang ini masih meminjam buku lain (ISBN: {$rp['isbn']})!" . RESET . "\n";
            return; // Tolak transaksi peminjaman baru
        }
    }

    $semua_buku = baca_csv(FILE_BUKU); // Ambil database ketersediaan buku
    $indeks_buku = -1; // Penanda untuk melacak posisi urutan baris buku yang mau dipinjam
    $isbn = "";

    // --- LOOPING MINTA ISBN BUKU SAMPAI READY (PERANGKAP INPUT VALIDASI) ---
    while (true) {
        echo "\n" . ABU_ABU . " (Ketik 'batal' jika ingin kembali ke menu utama)" . RESET . "\n";
        $isbn = input(" ▹ Masukkan ISBN Buku          : ");

        // Jika user menyerah/salah menu dan mengetik tombol darurat 'batal'...
        if (strtolower($isbn) === 'batal') {
            echo "\n" . KUNING_SOFT . " ↩️  Peminjaman dibatalkan." . RESET . "\n";
            return; // Hancurkan fungsi saat ini juga dan balik aman ke menu utama
        }

        $indeks_buku = -1; // Reset ulang nomor indeks pelacak di setiap perulangan baru
        // Cari kesesuaian nomor ISBN yang diinput dengan data buku di file
        foreach ($semua_buku as $index => $b) {
            if ($b['isbn'] == $isbn) { $indeks_buku = $index; break; } // Jika ketemu, catat nomor barisnya (index)
        }
            
        // Validasi jika nomor baris tetap -1 artinya ISBN tidak ada di database file buku
        if ($indeks_buku == -1) {
            echo MERAH_SOFT . " ❌ Buku tidak ditemukan di database! Silakan coba lagi." . RESET . "\n";
            continue; // Paksa perulangan berputar lagi dari atas menanyakan nomor ISBN
        }
            
        // Validasi stok: Jika buku ada tapi angka stoknya tertulis 0 atau kurang...
        if ((int)$semua_buku[$indeks_buku]['stok'] <= 0) {
            echo MERAH_SOFT . " ❌ Buku ini sedang dipinjam orang lain! (Stok Kosong). Pilih buku lain." . RESET . "\n";
            continue; // Paksa perulangan berputar lagi dari atas untuk mencari judul buku lain
        }

        break; // Jika lolos semua validasi di atas, hancurkan perangkap while dan lanjut ke durasi
    }
    // -----------------------------------------------------------------------

    $lama_pinjam = (int)input(" ▹ Durasi Pinjam (Maks 30 hari): "); // Minta jumlah hari durasi peminjaman
    // Batasi durasi pinjam minimal harus 1 hari dan maksimal hanya boleh 30 hari
    if ($lama_pinjam < 1 || $lama_pinjam > 30) {
        echo "\n" . MERAH_SOFT . " ❌ Gagal: Lama peminjaman harus antara 1 sampai 30 hari!" . RESET . "\n";
        return; // Batalkan transaksi
    }

    $tgl_pinjam = date('Y-m-d'); // Catat tanggal peminjaman otomatis berdasarkan waktu hari ini (Format: TAHUN-BULAN-HARI)
    // Hitung rumus tanggal jatuh tempo pengembalian berdasarkan durasi hari menggunakan strtotime
    $tgl_harus_kembali = date('Y-m-d', strtotime("+$lama_pinjam days"));

    // UPDATE DATA 1: Ubah stok buku pada baris index tersebut menjadi angka 0 karena dipinjam
    $semua_buku[$indeks_buku]['stok'] = 0;
    tulis_csv(FILE_BUKU, ['isbn', 'judul', 'stok'], $semua_buku); // Simpan perubahan stok ke buku.csv

    // Buat kode nota transaksi otomatis (Contoh: TRX001, TRX002) berdasarkan total baris riwayat peminjaman
    $id_pinjam = "TRX" . str_pad(count($riwayat_pinjam) + 1, 3, "0", STR_PAD_LEFT);
    // Masukkan susunan data log transaksi baru ke dalam tumpukan array riwayat
    $riwayat_pinjam[] = [
        'id_pinjam'         => $id_pinjam,
        'ktp'               => $ktp,
        'isbn'              => $isbn,
        'tgl_pinjam'        => $tgl_pinjam,
        'tgl_harus_kembali' => $tgl_harus_kembali,
        'tgl_kembali'       => '-', // Diisi tanda strip karena status bukunya belum dibalikkan
        'status'            => 'DIPINJAM'
    ];
    // UPDATE DATA 2: Simpan baris transaksi pinjam baru ini ke dalam file peminjaman.csv
    tulis_csv(FILE_PEMINJAMAN, ['id_pinjam', 'ktp', 'isbn', 'tgl_pinjam', 'tgl_harus_kembali', 'tgl_kembali', 'status'], $riwayat_pinjam);
    
    echo "\n" . HIJAU_SOFT . " 🎉 Sukses: Buku berhasil dipinjam!" . RESET . "\n";
    echo KUNING_SOFT . " 🗓️  Batas pengembalian: $tgl_harus_kembali" . RESET . "\n";
}

// =========================================================================
// --- 8. FITUR: PROSES PENGEMBALIAN BUKU (MENU 4) ---
// =========================================================================
function kembalikan_buku() {
    // Tampilkan box hiasan judul menu pengembalian buku
    echo "\n" . BOLD . KUNING_SOFT . "┌────────────────────────────────────────┐" . RESET . "\n";
    echo BOLD . KUNING_SOFT . "│       ↩️  PROSES PENGEMBALIAN BUKU       │" . RESET . "\n";
    echo BOLD . KUNING_SOFT . "└────────────────────────────────────────┘" . RESET . "\n";
    $ktp = input(" ▹ Masukkan Nomor KTP Peminjam: "); // Minta nomor KTP peminjam untuk dicari datanya
    
    $riwayat_pinjam = baca_csv(FILE_PEMINJAMAN); // Baca database riwayat peminjaman
    $indeks_trx = -1; // Variabel untuk mencatat posisi baris ditemukannya transaksi peminjaman aktif
    // Sisir data transaksi untuk mencari baris peminjaman aktif milik KTP tersebut
    foreach ($riwayat_pinjam as $index => $rp) {
        if ($rp['ktp'] == $ktp && $rp['tgl_kembali'] == '-') {
            $indeks_trx = $index; // Simpan posisi baris indeksnya
            break; // Berhenti mencari karena data transaksi aktifnya sudah ketemu
        }
    }
            
    // Validasi jika indeks_trx tetap berangka -1 (artinya KTP tersebut tidak sedang meminjam buku apa pun)
    if ($indeks_trx == -1) {
        echo "\n" . MERAH_SOFT . " ❌ Gagal: Tidak ada data peminjaman aktif untuk KTP ini." . RESET . "\n";
        return; // Keluar dari fungsi pengembalian
    }

    // Minta konfirmasi pengisian tanggal pengembalian buku
    $pilihan_tgl = strtolower(input(" ▹ Gunakan tanggal hari ini? (y/n): "));
    $tgl_kembali = date('Y-m-d'); // Defaultnya otomatis disiapkan tanggal hari ini
    
    // Jika user menolak memilih 'y' (artinya ingin mengetik tanggal secara manual untuk simulasi terlambat/denda)
    if ($pilihan_tgl != 'y') {
        $tgl_kembali = input(" ▹ Masukkan tanggal (YYYY-MM-DD) : "); // Minta ketikan format tanggal manual
    }

    $batas_kembali = $riwayat_pinjam[$indeks_trx]['tgl_harus_kembali']; // Ambil data tanggal tenggat waktu pinjam
    
    // LOGIKA HITUNG TENGGAT: Ubah teks tanggal menjadi angka hitungan detik mutlak lewat fungsi strtotime()
    if (strtotime($tgl_kembali) <= strtotime($batas_kembali)) {
        // Skenario aman: Tanggal membalikkan buku lebih cepat atau pas dengan tanggal jatuh tempo
        $status_akhir = "TEPAT WAKTU";
        echo "\n" . HIJAU_SOFT . " ✅ Sukses: Buku dikembalikan TEPAT WAKTU. Terima kasih!" . RESET . "\n";
    } else {
        // Skenario apes: Tanggal membalikkan buku sudah melewati batas tanggal jatuh tempo
        $selisih_detik = strtotime($tgl_kembali) - strtotime($batas_kembali); // Hitung berapa detik selisih keterlambatannya
        $selisih_hari = round($selisih_detik / 86400); // Angka detik dibagi 86.400 (jumlah detik dalam 24 jam) untuk tahu total hari keterlambatan
        $status_akhir = "TERLAMBAT ($selisih_hari Hari)"; // Siapkan teks status keterlambatan
        echo "\n" . MERAH_SOFT . " ⚠️ Perhatian: Pengembalian TERLAMBAT $selisih_hari hari!" . RESET . "\n";
    }

    // UPDATE DATA 1: Perbarui status data di memori array riwayat peminjaman sesuai hasil pengecekan di atas
    $riwayat_pinjam[$indeks_trx]['tgl_kembali'] = $tgl_kembali; // Ganti tanda '-' dengan tanggal asli buku dibalikkan
    $riwayat_pinjam[$indeks_trx]['status'] = $status_akhir; // Ganti status 'DIPINJAM' menjadi status akhir kalkulasi waktu
    // Simpan semua pembaruan log transaksi ini kembali ke dalam file peminjaman.csv
    tulis_csv(FILE_PEMINJAMAN, ['id_pinjam', 'ktp', 'isbn', 'tgl_pinjam', 'tgl_harus_kembali', 'tgl_kembali', 'status'], $riwayat_pinjam);

    $semua_buku = baca_csv(FILE_BUKU); // Ambil database ketersediaan buku
    // UPDATE DATA 2: Gunakan tanda ampersand (&) alias pointer/reference agar modifikasi array di dalam loop bisa tersimpan permanen
    foreach ($semua_buku as &$b) {
        // Cari buku yang nomor ISBN-nya cocok dengan buku yang baru saja dipulangkan oleh transaksi ini
        if ($b['isbn'] == $riwayat_pinjam[$indeks_trx]['isbn']) {
            $b['stok'] = 1; // Pulihkan status angka stok buku tersebut kembali berangka 1 (Buku siap dipinjam orang lain lagi)
            break; // Keluar dari looping pencarian buku
        }
    }
    // Simpan pemulihan angka stok buku tersebut secara permanen ke dalam file buku.csv
    tulis_csv(FILE_BUKU, ['isbn', 'judul', 'stok'], $semua_buku);
}

// =========================================================================
// --- 9. MENU UTAMA LOOP (MESIN NYAWA APLIKASI) ---
// =========================================================================
inisialisasi_file(); // Panggil fungsi inisialisasi di awal program untuk menjamin ketersediaan 3 file CSV utama

// Mengunci sistem ke dalam perulangan tak terbatas (while true) agar aplikasi terus hidup dan tidak menutup otomatis
while (true) {
    // Cetak hiasan banner menu utama aplikasi perpustakaan CLI
    echo "\n" . ABU_ABU . "────────────────────────────────────────────────" . RESET . "\n";
    echo BOLD . BG_ABU_GELAP . PUTIH_MUTIARA . "  📚 PERPUSTAKAAN (HELGI) 📚  " . RESET . "\n";
    echo ABU_ABU . "────────────────────────────────────────────────" . RESET . "\n";
    echo " " . ABU_ABU . "1." . RESET . " Tambah Buku Baru\n";
    echo " " . ABU_ABU . "2." . RESET . " Tambah Data Peminjam\n";
    echo " " . ABU_ABU . "3." . RESET . " Proses Peminjaman Buku\n";
    echo " " . ABU_ABU . "4." . RESET . " Proses Pengembalian Buku\n";
    echo " " . ABU_ABU . "5." . RESET . " Keluar Aplikasi\n";
    echo ABU_ABU . "────────────────────────────────────────────────" . RESET . "\n";
    $pilihan = input("👉 Pilih menu (1-5): "); // Tangkap instruksi angka pilihan menu dari user
    
    // Polisi lalu lintas menu: Mengarahkan program untuk memicu fungsi yang sesuai berdasarkan angka ketikan user
    switch ($pilihan) {
        case '1': tambah_buku(); break;       // Jika ketik 1, panggil dan jalankan fungsi tambah_buku
        case '2': tambah_peminjam(); break;   // Jika ketik 2, panggil dan jalankan fungsi tambah_peminjam
        case '3': pinjam_buku(); break;       // Jika ketik 3, panggil dan jalankan fungsi pinjam_buku
        case '4': kembalikan_buku(); break;   // Jika ketik 4, panggil dan jalankan fungsi kembalikan_buku
        case '5':                             // Jika ketik 5, cetak kalimat perpisahan lalu matikan program
            echo "\n" . BOLD . KUNING_SOFT . " 👋 Keluar dari sistem. Terima kasih banyak!" . RESET . "\n\n"; 
            exit;                             // Perintah absolut php untuk mematikan paksa aplikasi terminal saat itu juga
        default:                              // Skenario proteksi jika user iseng mengetik huruf atau angka di luar rentang 1-5
            echo "\n" . MERAH_SOFT . " ❌ Pilihan tidak valid, silakan coba lagi." . RESET . "\n";
    }
    
    // Perangkap jeda: Menahan tampilan layar terminal agar user sempat membaca info sukses/gagal transaksi sebelum layar ter-refresh menu utama
    input("\n Press [Enter] to continue...");
}