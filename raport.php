<?php

// =========================================================================
// 1. FUNGSI UTAMA (MAIN MENU FUNCTION)
// Fungsi ini berguna untuk menjaga aplikasi tetap menyala di terminal dan
// mengatur menu pilihan yang diinput oleh user.
// =========================================================================
function jalankanAplikasi() {
    // Loop 'while (true)' membuat program berputar terus-menerus tanpa henti,
    // sehingga terminal tidak akan langsung otomatis menutup setelah satu perintah selesai.
    while (true) {
        // 'echo' digunakan untuk mencetak/menampilkan teks pilihan menu ke layar terminal user.
        echo "\n=== MENU APLIKASI RAPORT ===\n";
        echo "1. Input Data Siswa\n";
        echo "2. Input Nilai\n";
        echo "3. Cetak Nilai Raport\n";
        echo "4. Analisa Tabel\n";
        echo "5. Keluar\n";
        echo "Pilih menu (1-5): ";
        
        // 'fgets(STDIN)' = Perintah untuk menunggu dan mengambil apa pun teks yang diketik user di terminal.
        // 'trim(...)' = Fungsi untuk membersihkan spasi gaib atau karakter "Enter" bawaan terminal di ujung inputan.
        $pilihan = trim(fgets(STDIN));

        // Struktur 'if-elseif-else' memeriksa isi variabel $pilihan:
        // Jika variabel $pilihan berisi karakter '1', jalankan fungsi inputDataSiswa()
        if ($pilihan == '1') {
            inputDataSiswa();
        // Jika variabel $pilihan berisi karakter '2', jalankan fungsi inputNilaiSiswa()
        } elseif ($pilihan == '2') {
            inputNilaiSiswa();
        // Jika variabel $pilihan berisi karakter '3', jalankan fungsi cetakRaportSiswa()
        } elseif ($pilihan == '3') {
            cetakRaportSiswa();
        // Jika variabel $pilihan berisi karakter '4', jalankan fungsi analisaTabelMapel()
        } elseif ($pilihan == '4') {
            analisaTabelMapel();
        // Jika variabel $pilihan berisi karakter '5', tampilkan pesan pamit dan matikan perulangan menu
        } elseif ($pilihan == '5') {
            echo "Terima kasih! Aplikasi keluar.\n";
            break; // Keyword 'break' bertugas memaksa keluar dari loop 'while(true)', sehingga aplikasi resmi selesai/berhenti.
        // Jika user mengetik selain angka 1 sampai 5, bagian 'else' ini yang akan memprotes.
        } else {
            echo "Pilihan tidak valid, coba lagi.\n";
        }
    }
}

// =========================================================================
// 2. MENU 1: INPUT DATA SISWA
// Fungsi ini mengurus pendaftaran No Siswa dan Nama Siswa ke dalam file 'data_siswa.csv'.
// =========================================================================
function inputDataSiswa() {
    echo "\n--- INPUT DATA SISWA ---\n";
    echo "Masukkan No Siswa: ";
    // Menangkap input nomor siswa dari ketikan terminal user.
    $no_siswa = trim(fgets(STDIN));

    // [VALIDASI WAJIB ANGKA]
    // '!is_numeric($no_siswa)' membaca: "Jika variabel $no_siswa BUKAN berisi angka..."
    // Maka, jika user mengetik huruf (seperti 'f', 'abc'), kondisi ini akan langsung aktif terpicu.
    if (!is_numeric($no_siswa)) {
        echo "❌ Gagal! No Siswa harus berupa ANGKA, tidak boleh huruf atau simbol.\n";
        return; // Keyword 'return' langsung memotong fungsi di sini dan melempar user kembali ke menu utama. Input diblokir!
    }

    // [VALIDASI ANTI-KEMBAR / ANTI-DUPLIKAT NO SISWA]
    // 'file_exists(...)' memeriksa apakah file 'data_siswa.csv' sudah pernah tercipta di dalam folder project.
    if (file_exists("data_siswa.csv")) {
        // 'fopen(..., "r")' membuka file csv siswa dengan hak akses "r" (Read-Only / Hanya untuk dibaca, dilarang mengubah).
        $file_baca = fopen("data_siswa.csv", "r");
        
        // Loop 'while' ini membaca file CSV baris demi baris dari paling atas sampai paling bawah.
        // 'fgets($file_baca)' bertugas mengambil satu baris teks dalam file tersebut pada setiap putaran loop.
        while (($line = fgets($file_baca)) !== FALSE) {
            // 'explode(";", ...)' bertugas membelah satu baris teks menjadi potongan array terpisah berdasarkan tanda pembatas titik koma (;).
            // Contoh teks "1;Rafka" akan dipecah menjadi: $data[0] = "1" dan $data[1] = "Rafka".
            $data = explode(";", trim($line));
            
            // Memeriksa: Apakah slot $data[0] (No Siswa di file) bernilai sama persis dengan $no_siswa yang baru saja diketik user?
            if (isset($data[0]) && $data[0] == $no_siswa) {
                echo "❌ Gagal! No Siswa [$no_siswa] sudah terdaftar dengan nama: $data[1].\n";
                fclose($file_baca); // Wajib menutup koneksi file sebelum keluar dari fungsi agar memory komputer bersih.
                return; // Menghentikan proses pendaftaran karena nomor siswa sudah dipakai orang lain!
            }
        }
        fclose($file_baca); // Menutup file jika proses pencarian duplikat di seluruh baris selesai dan aman tidak ada yang kembar.
    }

    echo "Masukkan Nama Siswa: ";
    // Mengambil nama siswa. Di sini user BEBAS memasukkan huruf karena ini adalah nama orang.
    $nama_siswa = trim(fgets(STDIN));

    // '!file_exists(...)' mengecek: "Jika file data_siswa.csv BELUM ada di folder..."
    // Maka variabel $buat_header akan bernilai TRUE (artinya ini adalah pertama kalinya file dibuat, butuh judul kolom).
    $buat_header = !file_exists("data_siswa.csv");
    
    // 'fopen(..., "a")' membuka file csv siswa dengan mode "a" (Append / Menulis data baru diselipkan di baris paling bawah/akhir file).
    $file = fopen("data_siswa.csv", "a");
    
    // Jika variabel $buat_header bernilai TRUE, tulis judul kolom "NO;NAMA" terlebih dahulu sebagai baris paling atas (baris 1).
    if ($buat_header) {
        fwrite($file, "NO;NAMA\n");
    }
    
    // 'fwrite(...)' menuliskan data ke dalam file dengan format: No_Siswa;Nama_Siswa diikuti tanda "\n" di ujungnya.
    // Karakter "\n" (wajib petik dua) beroperasi sebagai tombol "Enter" otomatis, memaksa kursor turun ke baris baru di bawahnya.
    fwrite($file, $no_siswa . ";" . $nama_siswa . "\n");
    
    // Selesai menulis, tutup file agar perubahan tersimpan permanen di harddisk komputer.
    fclose($file);
    echo "✅ Data siswa berhasil disimpan!\n";
}

// =========================================================================
// 3. MENU 2: INPUT NILAI SISWA
// Fungsi ini mencatat nilai pelajaran MTK, IPA, dan IPS siswa ke 'data_nilai.csv'.
// =========================================================================
function inputNilaiSiswa() {
    echo "\n--- INPUT NILAI SISWA ---\n";
    echo "Masukkan No Siswa: ";
    // Menangkap input nomor siswa yang mau diberi nilai.
    $no_siswa = trim(fgets(STDIN));

    // [VALIDASI WAJIB ANGKA]
    // Memastikan nomor siswa yang mau diinput nilainya adalah angka tulen.
    if (!is_numeric($no_siswa)) {
        echo "❌ Gagal! No Siswa harus berupa ANGKA.\n";
        return; // Blokir aksi jika user memasukkan huruf.
    }

    // [VALIDASI ANTI-DOUBLE NILAI SISWA]
    // Berfungsi agar satu siswa tidak bisa memiliki 2 baris nilai yang berbeda di database CSV.
    if (file_exists("data_nilai.csv")) {
        $file_baca_nilai = fopen("data_nilai.csv", "r"); // Buka file dengan akses baca (read)
        // Membaca file nilai baris demi baris
        while (($line = fgets($file_baca_nilai)) !== FALSE) {
            $data = explode(";", trim($line)); // Potong string baris menjadi susunan array nilai berdasarkan semicolon (;)
            // Jika nomor siswa ditemukan sudah nongkrong di file nilai, batalkan aksi!
            if (isset($data[0]) && $data[0] == $no_siswa) {
                echo "❌ Gagal! Nilai untuk No Siswa [$no_siswa] sudah pernah diinput sebelumnya.\n";
                fclose($file_baca_nilai); // Tutup file pembacaan
                return; // Tendang user kembali ke menu utama. Nilai ganda berhasil dicegah!
            }
        }
        fclose($file_baca_nilai); // Tutup file setelah lolos sensor cek duplikat.
    }

    echo "Masukkan Nilai MTK: ";
    $mtk = trim(fgets(STDIN)); // Ambil ketikan nilai Matematika
    echo "Masukkan Nilai IPA: ";
    $ipa = trim(fgets(STDIN)); // Ambil ketikan nilai IPA
    echo "Masukkan Nilai IPS: ";
    $ips = trim(fgets(STDIN)); // Ambil ketikan nilai IPS

    // [VALIDASI NILAI WAJIB ANGKA]
    // Menggunakan operator OR (||). Dibaca: "Jika $mtk BUKAN angka, ATAU $ipa BUKAN angka, ATAU $ips BUKAN angka..."
    if (!is_numeric($mtk) || !is_numeric($ipa) || !is_numeric($ips)) {
        echo "❌ Gagal! Nilai MAPEL harus berupa angka.\n";
        return; // Hentikan fungsi jika ada huruf terselip di kolom nilai mapel.
    }

    // [VALIDASI AMAN RENTANG NILAI 0 - 100]
    // Dibaca: "Jika nilai mtk di bawah 0 ATAU nilai mtk di atas 100, ATAU ipa di bawah 0 ATAU ipa di atas 100..."
    // Jika ada satu saja nilai mapel yang melanggar batas (misal minus atau di atas 100), blokir total!
    if ($mtk < 0 || $mtk > 100 || $ipa < 0 || $ipa > 100 || $ips < 0 || $ips > 100) {
        echo "❌ Gagal! Input ditolak. Nilai sekolah harus berada di rentang 0 sampai 100!\n";
        return; // Gagalkan penyimpanan data ke CSV.
    }

    // Cek jika file data_nilai.csv belum pernah dibuat di folder project
    $buat_header = !file_exists("data_nilai.csv");
    // Buka file data_nilai.csv dengan mode "a" (Append / menulis lanjut di baris paling bawah)
    $file = fopen("data_nilai.csv", "a");
    // Tuliskan header kolom di baris pertama jika file baru pertama kali menetas
    if ($buat_header) {
        fwrite($file, "NO;MTK;IPA;IPS\n");
    }

    // Rekam data nilai ke dalam file dengan susunan: No_Siswa;MTK;IPA;IPS
    // Tanda "\n" di ujung berfungsi sangat vital untuk memindahkan kursor ke baris baru agar data berikutnya tidak menempel ke samping!
    fwrite($file, $no_siswa . ";" . $mtk . ";" . $ipa . ";" . $ips . "\n");
    // Tutup file untuk mengunci dan menyelamatkan data yang ditulis ke sistem penyimpanan komputer.
    fclose($file);
    echo "✅ Data nilai berhasil disimpan!\n";
}

// =========================================================================
// 4. MENU 3: CETAK NILAI RAPORT PER SISWA
// Fungsi ini bertugas mencari nama di file siswa, mencari nilai di file nilai,
// lalu menghitung statistiknya untuk ditampilkan layaknya selembar raport di terminal.
// =========================================================================
function cetakRaportSiswa() {
    echo "\n--- CETAK NILAI RAPORT ---\n";
    echo "Masukkan No Siswa yang dicari: ";
    // Menangkap nomor target siswa yang mau dicari laporannya.
    $cari_no = trim(fgets(STDIN));

    // [VALIDASI WAJIB ANGKA SAAT PENCARIAN]
    // Memastikan kata kunci pencarian wajib berupa karakter angka tulen, huruf (seperti 'daa') langsung diblok telak!
    if (!is_numeric($cari_no)) {
        echo "❌ Gagal! No Siswa yang dicari harus berupa ANGKA.\n";
        return; // Stop pencarian, usir user kembali ke menu utama.
    }

    // Menyiapkan variabel-variabel penampung cadangan (default) sebelum pencarian dimulai
    $mtk = 0; $ipa = 0; $ips = 0;
    $nama_siswa = "Siswa Tanpa Nama";
    $nilai_ditemukan = false; // Variabel boolean (saklar penanda). Awalnya diset FALSE (artinya belum ketemu).

    // --- TAHAP 1: BERBURU NAMA SISWA DI FILE DATA_SISWA.CSV ---
    if (file_exists("data_siswa.csv")) {
        $file_siswa = fopen("data_siswa.csv", "r"); // Buka file dengan izin baca (Read)
        // Menyisir file baris demi baris
        while (($line = fgets($file_siswa)) !== FALSE) {
            $data = explode(";", trim($line)); // Pecah pembatas titik koma (;)
            // Jika nomor di kolom index 0 cocok dengan nomor yang dicari user...
            if (isset($data[0]) && $data[0] == $cari_no) {
                $nama_siswa = $data[1]; // Ambil string namanya di index 1, pindahkan ke variabel $nama_siswa
                break; // Misi pencarian nama sukses, hentikan perulangan 'while' ini demi menghemat performa komputer.
            }
        }
        fclose($file_siswa); // Tutup file siswa.
    }

    // --- TAHAP 2: BERBURU DATA NILAI MAPEL DI FILE DATA_NILAI.CSV ---
    if (file_exists("data_nilai.csv")) {
        $file_nilai = fopen("data_nilai.csv", "r"); // Buka file nilai dengan akses baca
        fgets($file_nilai); // Teknik khusus: Sengaja membaca baris pertama tanpa diproses untuk melompati baris teks header judul kolom.
        
        // Menyisir data nilai baris demi baris
        while (($line = fgets($file_nilai)) !== FALSE) {
            $data = explode(";", trim($line)); // Potong data
            // Jika nomor siswa di index 0 terbukti cocok dengan target...
            if (isset($data[0]) && $data[0] == $cari_no) {
                // Konversi data string dari CSV menjadi Tipe Integer/Angka murni menggunakan perintah '(int)'
                $mtk = (int)$data[1]; // Nilai MTK ada di kolom index 1
                $ipa = (int)$data[2]; // Nilai IPA ada di kolom index 2
                $ips = (int)$data[3]; // Nilai IPS ada di kolom index 3
                $nilai_ditemukan = true; // Nyalakan saklar penanda menjadi TRUE (artinya data nilai siswa resmi ditemukan!).
                break; // Hentikan loop pencarian nilai karena target sudah tertangkap.
            }
        }
        fclose($file_nilai); // Tutup file nilai.
    }

    // Jika setelah berkelana di file nilai variabel $nilai_ditemukan tetap berstatus FALSE,
    // artinya nomor tersebut memang belum pernah diinput nilainya di Menu 2.
    if (!$nilai_ditemukan) {
        echo "❌ Maaf, No Siswa [$cari_no] nilainya belum diinput di Menu 2.\n";
        return; // Batalkan proses cetak lembar raport.
    }

    // --- TAHAP 3: PERHITUNGAN STATISTIK TERMINAL PHP ---
    // Menggabungkan ketiga variabel nilai ke dalam satu wadah Array berkumpul bersamasama.
    $nilai_array = [$mtk, $ipa, $ips];
    $terbesar   = max($nilai_array); // Fungsi 'max()' otomatis menyaring dan mengambil angka tertinggi di dalam array.
    $terkecil   = min($nilai_array); // Fungsi 'min()' otomatis menyaring dan mengambil angka terendah di dalam array.
    $jumlah     = array_sum($nilai_array); // Fungsi 'array_sum()' otomatis menjumlahkan total pertambahan seluruh angka array.
    
    // 'count($nilai_array)' menghitung total jumlah anggota array (dalam hal ini ada 3 mata pelajaran).
    // 'number_format(..., 2)' bertugas memotong hasil pembagian rata-rata agar pas dikunci hanya menampilkan 2 digit angka di belakang koma desimal.
    $rata_rata  = number_format($jumlah / count($nilai_array), 2);

    // --- TAHAP 4: MENCETAK DESAIN NOTA RAPORT KE LAYAR TERMINAL ---
    echo "\n=========================================\n";
    echo "RAPORT HASIL BELAJAR SISWA\n";
    echo "=========================================\n";
    echo "No Siswa   : $cari_no\n";
    echo "Nama       : $nama_siswa\n";
    echo "-----------------------------------------\n";
    echo "Nilai MTK  : $mtk\n";
    echo "Nilai IPA  : $ipa\n";
    echo "Nilai IPS  : $ips\n";
    echo "-----------------------------------------\n";
    echo "Hasil Perhitungan:\n";
    echo "- Nilai Terbesar  : $terbesar\n";
    echo "- Nilai Terkecil  : $terkecil\n";
    echo "- Nilai Rata-Rata : $rata_rata\n";
    echo "- Jumlah Nilai    : $jumlah\n";
    echo "=========================================\n";
}

// =========================================================================
// 5. MENU 4: ANALISA TABEL & SINKRONISASI EXCEL
// Fungsi tercanggih: Menampilkan tabel kotak rapi di terminal, mendeteksi jumlah baris secara dinamis,
// dan menyuntikkan string rumus macro bawaan Excel ke file 'data_analisa.csv'.
// =========================================================================
function analisaTabelMapel() {
    // Validasi awal: Jika file data_nilai.csv belum pernah tercipta, tidak ada data yang bisa dihitung statistiknya.
    if (!file_exists("data_nilai.csv")) {
        echo "\n❌ Belum ada data nilai untuk dianalisa.\n";
        return;
    }

    // Membuat array kosong untuk menampung sekumpulan nilai dari seluruh siswa di kelas
    $all_mtk = []; $all_ipa = []; $all_ips = [];
    $jumlah_baris = 0; // Variabel counter (penghitung), mendeteksi ada berapa jumlah murid yang terdaftar di file.

    $file = fopen("data_nilai.csv", "r");
    fgets($file); // Melompati baris pertama (teks judul kolom) agar tidak merusak perhitungan matematika.

    // Menyedot seluruh isi file nilai
    while (($line = fgets($file)) !== FALSE) {
        $data = explode(";", trim($line)); // Potong pembatas semicolon (;)
        // Memastikan isi baris data komplit memiliki minimal 4 kolom (No, Mtk, Ipa, Ips)
        if (count($data) >= 4) {
            $all_mtk[] = (int)$data[1]; // Lempar nilai mtk baris ini ke array penampung master MTK
            $all_ipa[] = (int)$data[2]; // Lempar nilai ipa baris ini ke array penampung master IPA
            $all_ips[] = (int)$data[3]; // Lempar nilai ips baris ini ke array penampung master IPS
            $jumlah_baris++; // Setiap satu baris data murid terbaca, naikkan angka hitungan counter sebesar +1.
        }
    }
    fclose($file); // Selesai menyedot data kelas, tutup filenya.

    // Jika file ada tapi isinya kosong (hanya header saja), hentikan fungsi.
    if (empty($all_mtk)) {
        echo "\n❌ Data nilai masih kosong.\n";
        return;
    }

    // --- MEMBUAT FUNGSI LOGIKA PENOLONG (CLOSURE / ANONYMOUS FUNCTION) ---
    // Fungsi '$hitung_stats' ini sengaja dibuat ringkas untuk menghitung data min, max, rata-rata, dan total jumlah
    // khusus untuk ditampilkan di visualisasi terminal internal PHP.
    $hitung_stats = function($mapel_array) {
        $jml = array_sum($mapel_array); // Hitung total penjumlahan isi array mapel
        return [
            'max'  => max($mapel_array), // Ambil angka tertinggi di kelas untuk mapel ini
            'min'  => min($mapel_array), // Ambil angka terendah di kelas untuk mapel ini
            'avg'  => number_format($jml / count($mapel_array), 2), // Rata-rata kelas format 2 desimal
            'sum'  => $jml // Total jumlah nilai kelas
        ];
    };

    // Eksekusi fungsi penolong di atas untuk masing-masing bidang studi
    $stats_mtk = $hitung_stats($all_mtk);
    $stats_ipa = $hitung_stats($all_ipa);
    $stats_ips = $hitung_stats($all_ips);

    // --- MENCETAK TABEL KOTAK PRESISI SIMETRIS DI TERMINAL ---
    // 'sprintf' berfungsi mengunci jarak lebar kolom tabel agar tidak bergeser berantakan.
    // Arti dari '%-7s' = Siapkan slot kosong bertipe String (s), Rata Kiri (-), sebesar pas 7 karakter lebar spasinya.
    echo "\n=========================================================================\n";
    echo "                             TABEL ANALISA                               \n";
    echo "=========================================================================\n";
    echo sprintf("| %-7s | %-14s | %-14s | %-15s | %-12s |\n", "MAPEL", "Nilai Terbesar", "Nilai Terkecil", "Nilai Rata-Rata", "Jumlah Nilai");
    echo "-------------------------------------------------------------------------\n";
    echo sprintf("| %-7s | %-14s | %-14s | %-15s | %-12s |\n", "MTK", $stats_mtk['max'], $stats_mtk['min'], $stats_mtk['avg'], $stats_mtk['sum']);
    echo "-------------------------------------------------------------------------\n";
    echo sprintf("| %-7s | %-14s | %-14s | %-15s | %-12s |\n", "IPA", $stats_ipa['max'], $stats_ipa['min'], $stats_ipa['avg'], $stats_ipa['sum']);
    echo "-------------------------------------------------------------------------\n";
    echo sprintf("| %-7s | %-14s | %-14s | %-15s | %-12s |\n", "IPS", $stats_ips['max'], $stats_ips['min'], $stats_ips['avg'], $stats_ips['sum']);
    echo "=========================================================================\n";

    // --- SINKRONISASI MATRIX RUMUS DYNAMIC KE EXCEL BERSAMA ---
    // Menghitung batas jangkauan baris terakhir data nilai secara dinamis di Excel.
    // Jika di terminal kita punya 4 murid ($jumlah_baris = 4), ditambah 1 baris header, maka baris akhir tabel di Excel adalah baris nomor 5.
    $baris_akhir = $jumlah_baris + 1; 
    
    // 'fopen(..., "w")' membuka/membuat file 'data_analisa.csv' dengan hak akses "w" (Write / Overwrite total).
    // Artinya isi file yang lama akan dihapus bersih dibuang, lalu diganti dengan tulisan rumus kalkulasi barunya dari awal.
    $file_excel = fopen("data_analisa.csv", "w");
    
    // Menulis judul header kolom file csv data_analisa
    fwrite($file_excel, "Mapel;Nilai Terbesar;Nilai Terkecil;Nilai Rata-Rata;Jumlah Nilai\n");
    
    // MENYUNTIKKAN RUMUS EXCEL ASING BERBENTUK STRING TEKS:
    // PHP tidak menghitung matematika di sini. PHP murni hanya menulis string teks rumus mentah seperti "=MAX(data_nilai!B2:B5)".
    // Ketika file csv ini diimpor atau direfresh oleh Microsoft Excel kamu, Excel akan mendeteksi lambang tanda sama dengan (=) 
    // dan secara otomatis mengaktifkan mesin kalkulator internal Excel untuk menghitung hasilnya secara realtime dan akurat!
    // '=ROUND(..., 2)' digunakan di rumus rata-rata Excel agar hitungan rata-rata di cell lembar Excel kamu dikunci manis hanya 2 angka desimal di belakang koma.
    fwrite($file_excel, "MTK;=MAX(data_nilai!B2:B" . $baris_akhir . ");=MIN(data_nilai!B2:B" . $baris_akhir . ");=ROUND(AVERAGE(data_nilai!B2:B" . $baris_akhir . ");2);=SUM(data_nilai!B2:B" . $baris_akhir . ")\n");
    fwrite($file_excel, "IPA;=MAX(data_nilai!C2:C" . $baris_akhir . ");=MIN(data_nilai!C2:C" . $baris_akhir . ");=ROUND(AVERAGE(data_nilai!C2:C" . $baris_akhir . ");2);=SUM(data_nilai!C2:C" . $baris_akhir . ")\n");
    fwrite($file_excel, "IPS;=MAX(data_nilai!D2:D" . $baris_akhir . ");=MIN(data_nilai!D2:D" . $baris_akhir . ");=ROUND(AVERAGE(data_nilai!D2:D" . $baris_akhir . ");2);=SUM(data_nilai!D2:D" . $baris_akhir . ")\n");
    
    // Tutup koneksi penulisan file analisa excel.
    fclose($file_excel);
    echo "\n📊 [SISTEM] Sukses! data_analisa.csv berhasil diperbarui otomatis.\n";
}

// =========================================================================
// TRIGGER UTAMA JALANNYA PROGRAM
// Baris perintah tunggal di bawah ini bertugas sebagai tombol starter utama untuk memicu
// berjalannya fungsi jalankanAplikasi() di atas saat file php dieksekusi via terminal 'php raport.php'.
// =========================================================================
jalankanAplikasi();