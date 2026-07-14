<?php

// =========================================================================
// 1. FUNGSI UTAMA (MAIN MENU FUNCTION)
// Mengatur perulangan menu utama agar aplikasi tetap berjalan di terminal.
// =========================================================================
function jalankanAplikasi() {
    // Loop 'while' membuat menu terus berputar dan tidak langsung menutup terminal
    while (true) {
        // Menampilkan teks header menu aplikasi ke layar terminal
        echo "\n=== MENU APLIKASI RAPORT ===\n";
        // Menampilkan pilihan menu 1 untuk input data siswa
        echo "1. Input Data Siswa\n";
        // Menampilkan pilihan menu 2 untuk input nilai
        echo "2. Input Nilai\n";
        // Menampilkan pilihan menu 3 untuk mencetak raport personal
        echo "3. Cetak Nilai Raport\n";
        // Menampilkan pilihan menu 4 untuk melihat ranking kelas
        echo "4. Ranking Siswa\n";   
        // Menampilkan pilihan menu 5 untuk melihat analisa mata pelajaran
        echo "5. Analisa Tabel\n";   
        // Menampilkan pilihan menu 6 untuk keluar dari program
        echo "6. Keluar\n";          
        // Menampilkan teks petunjuk input untuk user
        echo "Pilih menu (1-6): ";
        
        // Membaca ketikan user di terminal dan membersihkan spasi/enter liar di ujungnya
        $pilihan = trim(fgets(STDIN));

        // Jika user mengetik angka 1, jalankan modul input data siswa
        if ($pilihan == '1') {
            // Memanggil fungsi inputDataSiswa
            inputDataSiswa();
        // Jika user mengetik angka 2, jalankan modul input nilai siswa
        } elseif ($pilihan == '2') {
            // Memanggil fungsi inputNilaiSiswa
            inputNilaiSiswa();
        // Jika user mengetik angka 3, jalankan modul cetak raport individu
        } elseif ($pilihan == '3') {
            // Memanggil fungsi cetakRaportSiswa
            cetakRaportSiswa();
        // Jika user mengetik angka 4, jalankan modul visualisasi ranking kelas
        } elseif ($pilihan == '4') {
            // Memanggil fungsi tampilkanRankingSiswa
            tampilkanRankingSiswa(); 
        // Jika user mengetik angka 5, jalankan modul analisa statistik kelas
        } elseif ($pilihan == '5') {
            // Memanggil fungsi analisaTabelMapel
            analisaTabelMapel();    
        // Jika user mengetik angka 6, matikan perulangan program
        } elseif ($pilihan == '6') {
            // Mencetak teks perpisahan ke layar terminal
            echo "Terima kasih! Aplikasi keluar.\n";
            // Memecah loop 'while(true)' untuk menghentikan total eksekusi script
            break; 
        // Jika user mengetik selain angka 1 sampai 6, jalankan blok pertahanan ini
        } else {
            // Menampilkan peringatan bahwa input yang dimasukkan salah
            echo "Pilihan tidak valid, coba lagi.\n";
        // Penutup blok kondisi else
        }
    // Penutup perulangan while
    }
// Penutup fungsi jalankanAplikasi
}

// =========================================================================
// 2. MENU 1: INPUT DATA SISWA (Wajib Angka & Anti-Duplikat No Siswa)
// =========================================================================
function inputDataSiswa() {
    // Menampilkan judul menu input data siswa
    echo "\n--- INPUT DATA SISWA ---\n";
    // Menampilkan perintah input nomor identitas siswa
    echo "Masukkan No Siswa: ";
    // Menangkap input nomor siswa dari terminal dan menghapus karakter spasi/enter
    $no_siswa = trim(fgets(STDIN));

    // Validasi penolak huruf: Memeriksa apakah input nomor siswa BUKAN berupa angka
    if (!is_numeric($no_siswa)) {
        // Menampilkan pesan eror jika terdeteksi ada huruf atau simbol yang masuk
        echo "❌ Gagal! No Siswa harus berupa ANGKA, tidak boleh huruf atau simbol.\n";
        // Menghentikan paksa fungsi ini dan melempar user kembali ke menu utama
        return; 
    // Penutup blok kondisi validasi angka
    }

    // Memeriksa apakah file database data_siswa.csv sudah ada di folder project
    if (file_exists("data_siswa.csv")) {
        // Membuka file data_siswa.csv dengan hak akses "r" (Read-Only / Hanya Baca)
        $file_baca = fopen("data_siswa.csv", "r");
        // Loop untuk menyisir file CSV baris demi baris sampai data habis
        while (($line = fgets($file_baca)) !== FALSE) {
            // Memecah baris teks menjadi array berdasarkan tanda pembatas titik koma (;)
            $data = explode(";", trim($line));
            // Memeriksa apakah indeks 0 (No Siswa di file) bernilai sama dengan nomor yang baru diinput
            if (isset($data[0]) && $data[0] == $no_siswa) {
                // Menampilkan pesan eror karena nomor siswa terbukti sudah terdaftar sebelumnya
                echo "❌ Gagal! No Siswa [$no_siswa] sudah terdaftar dengan nama: $data[1].\n";
                // Menutup koneksi file pembacaan agar memori komputer bersih kembali
                fclose($file_baca); 
                // Keluar dari fungsi untuk membatalkan proses pendaftaran data kembar
                return; 
            // Penutup blok pemeriksaan nomor kembar
            }
        // Penutup perulangan while membaca file
        }
        // Menutup file siswa setelah selesai disisir seluruh barisnya dan dinyatakan aman
        fclose($file_baca); 
    // Penutup blok pemeriksaan file_exists data siswa
    }

    // Menampilkan perintah input nama lengkap siswa
    echo "Masukkan Nama Siswa: ";
    // Menangkap ketikan nama siswa dari terminal (di sini huruf diizinkan bebas)
    $nama_siswa = trim(fgets(STDIN));

    // Mengecek apakah file data_siswa.csv belum ada (sebagai penanda butuh baris judul kolom)
    $buat_header = !file_exists("data_siswa.csv");
    // Membuka file data_siswa.csv dengan mode "a" (Append / Menulis data baru diselipkan di baris terbawah)
    $file = fopen("data_siswa.csv", "a");
    // Jika variabel buat_header bernilai true, suntikkan judul kolom ke baris pertama
    if ($buat_header) {
        // Menuliskan baris teks judul "NO;NAMA" ke dalam file csv baru
        fwrite($file, "NO;NAMA\n");
    // Penutup blok kondisi buat header kolom
    }
    
    // Menuliskan data siswa baru dengan format No_Siswa;Nama_Siswa dan diakhiri enter (\n)
    fwrite($file, $no_siswa . ";" . $nama_siswa . "\n");
    // Menutup koneksi file untuk mengunci data secara permanen di harddisk
    fclose($file);
    // Menampilkan tanda sukses bahwa pendaftaran murid baru berhasil
    echo "✅ Data siswa berhasil disimpan!\n";
// Penutup fungsi inputDataSiswa
}

// =========================================================================
// 3. MENU 2: INPUT NILAI SISWA (Wajib Angka, Anti-Double, & Batas Nilai 0-100)
// =========================================================================
function inputNilaiSiswa() {
    // Menampilkan judul menu input nilai raport
    echo "\n--- INPUT NILAI SISWA ---\n";
    // Menampilkan perintah untuk memasukkan nomor target siswa
    echo "Masukkan No Siswa: ";
    // Menangkap nomor siswa target dari ketikan terminal user
    $no_siswa = trim(fgets(STDIN));

    // Validasi tipe data: Memastikan nomor target wajib berupa angka tulen
    if (!is_numeric($no_siswa)) {
        // Menampilkan pesan eror jika nomor yang dimasukkan mengandung huruf
        echo "❌ Gagal! No Siswa harus berupa ANGKA.\n";
        // Menghentikan fungsi dan menolak proses pengisian nilai
        return; 
    // Penutup blok validasi angka nomor siswa
    }

    // Memeriksa apakah file data_nilai.csv sudah pernah dibuat sebelumnya
    if (file_exists("data_nilai.csv")) {
        // Membuka file nilai dengan mode baca ("r") untuk menyensor data ganda
        $file_baca_nilai = fopen("data_nilai.csv", "r"); 
        // Membaca file nilai baris demi baris dari atas sampai bawah
        while (($line = fgets($file_baca_nilai)) !== FALSE) {
            // Memotong teks baris menjadi array terpisah berdasarkan semicolon (;)
            $data = explode(";", trim($line)); 
            // Memeriksa apakah nomor siswa ini sudah terekam di dalam file nilai
            if (isset($data[0]) && $data[0] == $no_siswa) {
                // Memberi tahu user bahwa siswa ini nilainya sudah ada dan tidak boleh diisi dua kali
                echo "❌ Gagal! Nilai untuk No Siswa [$no_siswa] sudah pernah diinput sebelumnya.\n";
                // Menutup file nilai pembacaan
                fclose($file_baca_nilai); 
                // Menghentikan fungsi untuk memblokir manipulasi data ganda
                return; 
            // Penutup blok cek nomor siswa ganda di file nilai
            }
        // Penutup perulangan while membaca file nilai
        }
        // Menutup file nilai setelah proses saringan selesai terlewati dengan aman
        fclose($file_baca_nilai); 
    // Penutup blok pemeriksaan file_exists data nilai
    }

    // Menampilkan perintah input nilai mata pelajaran Matematika
    echo "Masukkan Nilai MTK: ";
    // Menangkap input nilai Matematika dari terminal
    $mtk = trim(fgets(STDIN)); 
    // Menampilkan perintah input nilai mata pelajaran IPA
    echo "Masukkan Nilai IPA: ";
    // Menangkap input nilai IPA dari terminal
    $ipa = trim(fgets(STDIN)); 
    // Menampilkan perintah input nilai mata pelajaran IPS
    echo "Masukkan Nilai IPS: ";
    // Menangkap input nilai IPS dari terminal
    $ips = trim(fgets(STDIN)); 

    // Validasi isi mapel: Memastikan ketiga kolom nilai wajib diisi angka murni tanpa huruf
    if (!is_numeric($mtk) || !is_numeric($ipa) || !is_numeric($ips)) {
        // Menampilkan pesan kegagalan jika ada kolom nilai yang disisipi huruf
        echo "❌ Gagal! Nilai MAPEL harus berupa angka.\n";
        // Memotong fungsi untuk membatalkan penyimpanan data cacat
        return; 
    // Penutup blok validasi angka mata pelajaran
    }

    // Validasi batas logis: Mengunci rentang standar nilai sekolah wajib antara 0 sampai 100
    if ($mtk < 0 || $mtk > 100 || $ipa < 0 || $ipa > 100 || $ips < 0 || $ips > 100) {
        // Menolak input jika user memasukkan angka minus atau angka bonus di atas 100
        echo "❌ Gagal! Input ditolak. Nilai sekolah harus berada di rentang 0 sampai 100!\n";
        // Menghentikan fungsi seketika
        return; 
    // Penutup blok validasi rentang nilai 0-100
    }

    // Mengecek apakah file data_nilai.csv belum ada di folder project
    $buat_header = !file_exists("data_nilai.csv");
    // Membuka file data_nilai.csv dengan hak akses append ("a") untuk menulis di baris paling bawah
    $file = fopen("data_nilai.csv", "a");
    // Jika file terdeteksi baru pertama kali dibuat, suntikkan judul kolomnya
    if ($buat_header) {
        // Menuliskan teks header "NO;MTK;IPA;IPS" di baris pertama file csv nilai
        fwrite($file, "NO;MTK;IPA;IPS\n");
    // Penutup blok kondisi buat header kolom nilai
    }

    // Merekam baris data nilai baru dengan format: No_Siswa;Nilai_MTK;Nilai_IPA;Nilai_IPS
    fwrite($file, $no_siswa . ";" . $mtk . ";" . $ipa . ";" . $ips . "\n");
    // Menutup koneksi file nilai agar perubahan tersimpan aman ke disk drive
    fclose($file);
    // Menampilkan pesan sukses ke layar terminal user
    echo "✅ Data nilai berhasil disimpan!\n";
// Penutup fungsi inputNilaiSiswa
}

// =========================================================================
// 4. MENU 3: CETAK NILAI RAPORT PER SISWA (Wajib Angka Saat Cari Nomor)
// =========================================================================
function cetakRaportSiswa() {
    // Menampilkan judul menu cetak laporan nilai personal
    echo "\n--- CETAK NILAI RAPORT ---\n";
    // Menampilkan perintah input kata kunci pencarian nomor siswa
    echo "Masukkan No Siswa yang dicari: ";
    // Menangkap nomor target siswa yang mau dicari dari terminal
    $cari_no = trim(fgets(STDIN));

    // Validasi pencarian: Memastikan nomor yang dicari wajib berupa angka murni
    if (!is_numeric($cari_no)) {
        // Menampilkan pesan eror jika user iseng mengetik huruf saat mencari nomor siswa
        echo "❌ Gagal! No Siswa yang dicari harus berupa ANGKA.\n";
        // Menghentikan pencarian dan melempar user ke menu utama
        return; 
    // Penutup blok kondisi validasi angka pencarian
    }

    // Menyiapkan variabel default untuk nilai Matematika
    $mtk = 0; 
    // Menyiapkan variabel default untuk nilai IPA
    $ipa = 0; 
    // Menyiapkan variabel default untuk nilai IPS
    $ips = 0;
    // Menyiapkan nama cadangan jika data nama siswa tidak sinkron/tidak ketemu
    $nama_siswa = "Siswa Tanpa Nama";
    // Membuat saklar boolean penanda status pencarian nilai (diinisialisasi dengan false)
    $nilai_ditemukan = false; 

    // --- TAHAP 1: MENCARI DATA NAMA SISWA DI FILE SISWA ---
    if (file_exists("data_siswa.csv")) {
        // Membuka file data_siswa.csv dengan akses membaca ("r")
        $file_siswa = fopen("data_siswa.csv", "r"); 
        // Menyisir file nama siswa baris demi baris sampai ke ujung bawah
        while (($line = fgets($file_siswa)) !== FALSE) {
            // Membelah baris teks menjadi array berdasarkan pembatas titik koma (;)
            $data = explode(";", trim($line)); 
            // Memeriksa jika nomor siswa di file cocok dengan nomor siswa yang dicari user
            if (isset($data[0]) && $data[0] == $cari_no) {
                // Mengambil string nama di index 1 dan memindahkannya ke variabel nama_siswa
                $nama_siswa = $data[1]; 
                // Menghentikan perulangan while karena target nama sudah sukses ditemukan
                break; 
            // Penutup blok pemeriksaan kecocokan nomor siswa
            }
        // Penutup perulangan while membaca file siswa
        }
        // Menutup koneksi file data siswa
        fclose($file_siswa); 
    // Penutup blok kondisi file_exists data siswa
    }

    // --- TAHAP 2: MENCARI DATA ANGKA NILAI DI FILE NILAI ---
    if (file_exists("data_nilai.csv")) {
        // Membuka file data_nilai.csv dengan akses membaca ("r")
        $file_nilai = fopen("data_nilai.csv", "r"); 
        // Sengaja membaca baris 1 tanpa diproses untuk melompati teks judul header kolom
        fgets($file_nilai); 
        // Menyisir baris data nilai satu per satu dari baris nomor 2 dst
        while (($line = fgets($file_nilai)) !== FALSE) {
            // Memecah teks baris nilai menjadi susunan array komponen angka
            $data = explode(";", trim($line)); 
            // Memeriksa jika nomor siswa di file nilai cocok dengan target yang dicari
            if (isset($data[0]) && $data[0] == $cari_no) {
                // Mengonversi string nilai MTK kolom index 1 menjadi tipe Integer (Angka murni)
                $mtk = (int)$data[1]; 
                // Mengonversi string nilai IPA kolom index 2 menjadi tipe Integer (Angka murni)
                $ipa = (int)$data[2]; 
                // Mengonversi string nilai IPS kolom index 3 menjadi tipe Integer (Angka murni)
                $ips = (int)$data[3]; 
                // Mengubah posisi saklar status temuan nilai menjadi TRUE (Sukses!)
                $nilai_ditemukan = true; 
                // Menghentikan perulangan while karena seluruh nilai target sudah dikunci
                break; 
            // Penutup blok pemeriksaan nomor siswa di file nilai
            }
        // Penutup perulangan while membaca file nilai
        }
        // Menutup koneksi file data nilai
        fclose($file_nilai); 
    // Penutup blok kondisi file_exists data nilai
    }

    // Pemeriksaan akhir: Jika saklar nilai_ditemukan terbukti masih berstatus FALSE (gagal)
    if (!$nilai_ditemukan) {
        // Menampilkan informasi bahwa murid tersebut nilainya memang belum pernah diinput
        echo "❌ Maaf, No Siswa [$cari_no] nilainya belum diinput di Menu 2.\n";
        // Menggagalkan total pencetakan lembar raport individu
        return; 
    // Penutup blok kondisi kegagalan temuan nilai
    }

    // --- TAHAP 3: KALKULASI MATEMATIKA TERMINAL PHP ---
    // Menyatukan ketiga nilai pelajaran ke dalam satu wadah Array kelompok
    $nilai_array = [$mtk, $ipa, $ips];
    // Fungsi max() menyaring otomatis untuk mengambil angka nilai tertinggi di array
    $terbesar   = max($nilai_array); 
    // Fungsi min() menyaring otomatis untuk mengambil angka nilai terendah di array
    $terkecil   = min($nilai_array); 
    // Fungsi array_sum() menjumlahkan total pertambahan isi angka di dalam array
    $jumlah     = array_sum($nilai_array); 
    // Menghitung rata-rata (total / jumlah mapel) dan dikunci manis 2 angka desimal belakang koma
    $rata_rata  = number_format($jumlah / count($nilai_array), 2);

    // --- TAHAP 4: MENCETAK DISPLAY NOTA RAPORT KE LAYAR TERMINAL ---
    // Mencetak garis pembatas atas raport
    echo "\n=========================================\n";
    // Mencetak judul kertas raport hasil belajar
    echo "RAPORT HASIL BELAJAR SISWA\n";
    // Mencetak garis pembatas tengah
    echo "=========================================\n";
    // Menampilkan nomor induk siswa yang dicari
    echo "No Siswa   : $cari_no\n";
    // Menampilkan nama lengkap siswa hasil temuan
    echo "Nama       : $nama_siswa\n";
    // Mencetak garis pembatas tipis
    echo "-----------------------------------------\n";
    // Menampilkan nilai Matematika asli siswa
    echo "Nilai MTK  : $mtk\n";
    // Menampilkan nilai IPA asli siswa
    echo "Nilai IPA  : $ipa\n";
    // Menampilkan nilai IPS asli siswa
    echo "Nilai IPS  : $ips\n";
    // Mencetak garis pembatas tipis bawah
    echo "-----------------------------------------\n";
    // Menampilkan judul sub-menu hasil perhitungan statistik
    echo "Hasil Perhitungan:\n";
    // Menampilkan angka nilai tertinggi
    echo "- Nilai Terbesar  : $terbesar\n";
    // Menampilkan angka nilai terendah
    echo "- Nilai Terkecil  : $terkecil\n";
    // Menampilkan hasil kalkulasi rata-rata kelas personal
    echo "- Nilai Rata-Rata : $rata_rata\n";
    // Menampilkan hasil akumulasi jumlah total skor
    echo "- Jumlah Nilai    : $jumlah\n";
    // Mencetak garis pembatas penutup bawah raport
    echo "=========================================\n";
// Penutup fungsi cetakRaportSiswa
}

// =========================================================================
// 5. MENU 4: FUNGSI RANKING SISWA (KOLOM "NO SISWA" TELAH DIHAPUS)
// =========================================================================
function tampilkanRankingSiswa() {
    // Validasi awal: Jika database file nilai belum tercipta, batalkan hitungan ranking kelas
    if (!file_exists("data_nilai.csv")) {
        // Menampilkan pesan bahwa ranking tidak bisa diproses karena data kosong
        echo "\n❌ Belum ada data nilai untuk menghitung ranking.\n";
        // Menghentikan fungsi ranking
        return;
    // Penutup blok validasi keberadaan file nilai
    }

    // Menyiapkan array kosong sebagai kamus bantuan pemetaan No Siswa -> Nama Siswa
    $kamus_nama = [];
    // Memeriksa jika data_siswa.csv ada agar tabel ranking bisa memuat nama orang
    if (file_exists("data_siswa.csv")) {
        // Membuka file data siswa dengan izin akses baca ("r")
        $file_s = fopen("data_siswa.csv", "r");
        // Melompati baris pertama (judul header kolom) agar tidak merusak data string
        fgets($file_s); 
        // Menggulung file siswa baris demi baris sampai data habis
        while (($line = fgets($file_s)) !== FALSE) {
            // Memecah baris teks siswa berdasarkan titik koma (;)
            $data = explode(";", trim($line));
            // Memastikan data baris valid memiliki kolom nomor dan kolom nama lengkap
            if (count($data) >= 2) {
                // Mengikat kata kunci nomor siswa dengan nilai nama siswa ke dalam array asosiatif
                $kamus_nama[$data[0]] = $data[1]; 
            // Penutup blok pengecekan jumlah kolom data siswa
            }
        // Penutup perulangan while membaca file siswa
        }
        // Menutup file database siswa
        fclose($file_s);
    // Penutup blok pemeriksaan file_exists data siswa
    }

    // Menyiapkan struktur array master kosong untuk menampung seluruh paket skor siswa
    $daftar_ranking = [];

    // Membuka database data_nilai.csv dengan mode baca ("r")
    $file = fopen("data_nilai.csv", "r");
    // Melompati baris pertama (judul header kolom nilai)
    fgets($file); 

    // Perulangan while untuk menyedot seluruh angka nilai siswa se-kelas
    while (($line = fgets($file)) !== FALSE) {
        // Memecah teks baris nilai menjadi komponen array mandiri
        $data = explode(";", trim($line)); 
        // Memastikan isi baris data komplit memiliki minimal 4 kolom (No, Mtk, Ipa, Ips)
        if (count($data) >= 4) {
            // Menyimpan nomor siswa baris ini ke dalam variabel no_s
            $no_s  = $data[0];
            // Mengubah nilai MTK string menjadi tipe Integer angka murni
            $v_mtk = (int)$data[1];
            // Mengubah nilai IPA string menjadi tipe Integer angka murni
            $v_ipa = (int)$data[2];
            // Mengubah nilai IPS string menjadi tipe Integer angka murni
            $v_ips = (int)$data[3];

            // Menghitung akumulasi total pertambahan nilai tiga mapel siswa ini
            $total_skor = $v_mtk + $v_ipa + $v_ips;
            // Menghitung rata-rata skor milik siswa ini dan dikunci 2 digit desimal
            $rata_skor  = number_format($total_skor / 3, 2);
            // Mencari nama siswa di kamus, jika tidak ketemu set nama menjadi "Tanpa Nama"
            $nama_s     = isset($kamus_nama[$no_s]) ? $kamus_nama[$no_s] : "Tanpa Nama";

            // Memasukkan satu paket data lengkap siswa ke dalam antrean array besar daftar_ranking
            $daftar_ranking[] = [
                'no'    => $no_s,
                'nama'  => $nama_s,
                'total' => $total_skor,
                'rata'  => $rata_skor
            ];
        // Penutup blok pengecekan jumlah kolom data nilai
        }
    // Penutup perulangan while menyedot data file nilai
    }
    // Menutup file database nilai
    fclose($file); 

    // Validasi isi: Jika data nilai terbukti ada tapi tidak berisi catatan baris murid
    if (empty($daftar_ranking)) {
        // Memberi tahu user bahwa data kelas masih nihil
        echo "\n❌ Data nilai masih kosong.\n";
        // Menghentikan fungsi ranking
        return;
    // Penutup blok validasi array ranking kosong
    }

    // --- PROSES SORTING (PENGURUTAN JUARA KELAS) ---
    // usort() membedah array daftar_ranking dan mengurutkannya memakai rumus custom landing comparison
    usort($daftar_ranking, function($a, $b) {
        // Spaceship Operator (<=>) membandingkan skor total b dengan a untuk urutan Descending (Besar ke Kecil)
        return $b['total'] <=> $a['total'];
    // Penutup closure usort
    });

    // --- MENCETAK DISPLAY TABEL RANKING DINAMIS GAYA KOTAK (+---+) ---
    // Kolom 'NO' (No Siswa) resmi dibuang dari format layout string di bawah ini
    echo "\n============================================================\n";
    echo "                     TABEL RANKING SISWA                    \n";
    echo "============================================================\n";
    // Mengubah parameter sprintf dan lebarnya agar simetris tanpa kolom NO
    echo sprintf("| %-4s | %-18s | %-9s | %-10s |\n", "RANK", "NAMA SISWA", "TOTAL", "RATA-RATA");
    echo "+------+--------------------+-----------+------------+\n";
    
    // Inisialisasi variabel penghitung peringkat otomatis dimulai dari nomor juara 1
    $rank_counter = 1; 
    // Perulangan foreach untuk membongkar array ranking terurut dan mencetaknya baris demi baris
    foreach ($daftar_ranking as $siswa) {
        // Mencetak baris data murid tanpa variabel $siswa['no']
        echo sprintf("| %-4s | %-18s | %-9s | %-10s |\n", 
            $rank_counter, 
            $siswa['nama'], 
            $siswa['total'], 
            $siswa['rata']
        );
        // Menaikkan urutan peringkat sebesar +1 secara otomatis untuk murid di baris berikutnya
        $rank_counter++;
    // Penutup perulangan foreach cetak ranking terminal
    }
    // Mencetak garis pembatas persimpangan tabel penutup bawah yang sudah disingkat ukurannya
    echo "+------+--------------------+-----------+------------+\n";

    // --- EKSPOR STRUKTUR DATA RANKING TERURUT KE EXCEL ---
    // File CSV Excel tetap menyimpan data lengkap termasuk nomor siswa untuk dokumentasi admin
    $file_rank_excel = fopen("data_ranking.csv", "w");
    fwrite($file_rank_excel, "Rank;No Siswa;Nama Siswa;Total Nilai;Rata-Rata\n");
    $excel_rank_num = 1;
    foreach ($daftar_ranking as $siswa) {
        fwrite($file_rank_excel, $excel_rank_num . ";" . $siswa['no'] . ";" . $siswa['nama'] . ";" . $siswa['total'] . ";" . $siswa['rata'] . "\n");
        $excel_rank_num++;
    }
    fclose($file_rank_excel);

    // Menampilkan notifikasi bahwa file data_ranking.csv sukses disinkronisasikan
    echo "[SISTEM] data_ranking.csv berhasil diperbarui otomatis.\n";
    // Mencetak garis pembatas penutup terbawah menu ranking
    echo "============================================================\n";
// Penutup fungsi tampilkanRankingSiswa
}

// =========================================================================
// 6. MENU 5: ANALISA TABEL MAPEL (Menyuntik Rumus Makro Asli Excel)
// =========================================================================
function analisaTabelMapel() {
    // Validasi awal: Jika file database data_nilai.csv tidak ditemukan, gagalkan analisa
    if (!file_exists("data_nilai.csv")) {
        // Menampilkan pesan penolakan karena bahan hitungan rata-rata kelas belum diinput
        echo "\n❌ Belum ada data nilai untuk dianalisa.\n";
        // Menghentikan fungsi analisa
        return;
    // Penutup blok kondisi validasi keberadaan file nilai
    }

    // Menyiapkan kumpulan array kosong sebagai wadah sedotan seluruh nilai Matematika se-kelas
    $all_mtk = []; 
    // Menyiapkan kumpulan array kosong sebagai wadah sedotan seluruh nilai IPA se-kelas
    $all_ipa = []; 
    // Menyiapkan kumpulan array kosong sebagai wadah sedotan seluruh nilai IPS se-kelas
    $all_ips = [];
    // Menyiapkan variabel pembaca total baris terisi dinamis milik jumlah murid kelas
    $jumlah_baris = 0;

    // Membuka database data_nilai.csv dengan mode read ("r")
    $file = fopen("data_nilai.csv", "r");
    // Melompati teks baris header judul kolom di paling atas file
    fgets($file); 

    // Perulangan untuk menyedot habis data nilai dari baris awal sampai baris akhir file
    while (($line = fgets($file)) !== FALSE) {
        // Membelah string baris menjadi susunan array per nilai mapel
        $data = explode(";", trim($line)); 
        // Memastikan isi array baris kokoh memuat minimal 4 kolom mata pelajaran
        if (count($data) >= 4) {
            // Memasukkan angka nilai Matematika baris ini ke dalam penampung master all_mtk
            $all_mtk[] = (int)$data[1]; 
            // Memasukkan angka nilai IPA baris ini ke dalam penampung master all_ipa
            $all_ipa[] = (int)$data[2]; 
            // Memasukkan angka nilai IPS baris ini ke dalam penampung master all_ips
            $all_ips[] = (int)$data[3]; 
            // Menaikkan angka hitungan pencatat jumlah baris data sebesar +1
            $jumlah_baris++; 
        // Penutup blok pengecekan kelengkapan kolom data baris nilai
        }
    // Penutup perulangan while menyedot data nilai kelas
    }
    // Menutup file database nilai setelah selesai disedot seluruh isinya
    fclose($file); 

    // Validasi isi: Jika isi array terbukti kosong melompong karena file hanya berisi header
    if (empty($all_mtk)) {
        // Memberi tahu user di terminal bahwa hitungan statistik belum memiliki record
        echo "\n❌ Data nilai masih kosong.\n";
        // Menghentikan fungsi analisa
        return;
    // Penutup blok kondisi validasi array mapel kosong
    }

    // --- MEMBUAT FUNGSI LOGIKA PEMBANTU INTERNAL ---
    $hitung_stats = function($mapel_array) {
        // Akumulasi penjumlahan pertambahan seluruh skor anak se-kelas khusus mapel ini
        $jml = array_sum($mapel_array); 
        // Mengembalikan paket array hasil kalkulasi terstruktur
        return [
            // max() mencari rekor nilai paling tinggi di kelas untuk mapel ini
            'max'  => max($mapel_array), 
            // min() mencari rekor nilai paling rendah di kelas untuk mapel ini
            'min'  => min($mapel_array), 
            // Menghitung rata-rata kelas (total / jumlah anak) dikunci format 2 angka desimal
            'avg'  => number_format($jml / count($mapel_array), 2), 
            // Menyimpan total skor kumulatif kelas
            'sum'  => $jml 
        ];
    // Penutup blok closure hitung_stats dan diakhiri tanda semicolon (;)
    };

    // Mengeksekusi fungsi pembantu di atas untuk menyaring data statistik Matematika kelas
    $stats_mtk = $hitung_stats($all_mtk);
    // Mengeksekusi fungsi pembantu di atas untuk menyaring data statistik IPA kelas
    $stats_ipa = $hitung_stats($all_ipa);
    // Mengeksekusi fungsi pembantu di atas untuk menyaring data statistik IPS kelas
    $stats_ips = $hitung_stats($all_ips);

    // --- MENCETAK DISPLAY TABEL ANALISA KELAS GAYA KOTAK TERMINAL (+---+) ---
    // Mencetak garis pembatas atas tabel analisa angkatan
    echo "\n=============================================================================\n";
    // Mencetak judul tabel pusat kendali analisa mata pelajaran
    echo "                                TABEL ANALISA                                \n";
    // Mencetak garis ganda tengah tabel
    echo "=============================================================================\n";
    // sprintf() mematok lebar spasi kolom agar presisi tegak lurus: Mapel(7), Max(14), Min(14), Rata(17), Total(10)
    echo sprintf("| %-7s | %-14s | %-14s | %-17s | %-10s |\n", "MAPEL", "Nilai Terbesar", "Nilai Terkecil", "Rata-Rata Kelas", "Total Skor");
    // Mencetak garis pembatas persimpangan tabel berciri khas tanda tambah (+)
    echo "+---------+----------------+----------------+-------------------+------------+\n";
    // Memasukkan hasil kalkulasi MTK ke susunan baris kolom tabel terminal
    echo sprintf("| %-7s | %-14s | %-14s | %-17s | %-10s |\n", "MTK", $stats_mtk['max'], $stats_mtk['min'], $stats_mtk['avg'], $stats_mtk['sum']);
    // Memasukkan hasil kalkulasi IPA ke susunan baris kolom tabel terminal
    echo sprintf("| %-7s | %-14s | %-14s | %-17s | %-10s |\n", "IPA", $stats_ipa['max'], $stats_ipa['min'], $stats_ipa['avg'], $stats_ipa['sum']);
    // Memasukkan hasil kalkulasi IPS ke susunan baris kolom tabel terminal
    echo sprintf("| %-7s | %-14s | %-14s | %-17s | %-10s |\n", "IPS", $stats_ips['max'], $stats_ips['min'], $stats_ips['avg'], $stats_ips['sum']);
    // Mencetak garis pembatas persimpangan tabel penutup bawah
    echo "+---------+----------------+----------------+-------------------+------------+\n";

    // --- SINKRONISASI & SUNTIK STRING RUMUS MACRO DINAMIS KE MICROSOFT EXCEL ---
    // Menghitung jangkauan baris cell akhir di Excel dinamis (Jumlah murid terbaca + 1 baris header)
    $baris_akhir = $jumlah_baris + 1; 
    // Membuka/Membuat dokumen spreadsheet data_analisa.csv dengan hak akses tulis ulang ("w")
    $file_excel = fopen("data_analisa.csv", "w");
    
    // Menuliskan teks header kolom baris pertama untuk dokumen spreadsheet Excel kamu
    fwrite($file_excel, "Mapel;Nilai Terbesar;Nilai Terkecil;Nilai Rata-Rata;Jumlah Nilai\n");
    
    // MENYUNTIKKAN RUMUS EXCEL ASLI MURNI BERBENTUK STRINGS:
    fwrite($file_excel, "MTK;=MAX(data_nilai!B2:B" . $baris_akhir . ");=MIN(data_nilai!B2:B" . $baris_akhir . ");=ROUND(AVERAGE(data_nilai!B2:B" . $baris_akhir . ");2);=SUM(data_nilai!B2:B" . $baris_akhir . ")\n");
    // Menyuntik string formula makro Excel otomatis untuk jangkauan kolom C (Nilai pelajaran IPA)
    fwrite($file_excel, "IPA;=MAX(data_nilai!C2:C" . $baris_akhir . ");=MIN(data_nilai!C2:C" . $baris_akhir . ");=ROUND(AVERAGE(data_nilai!C2:C" . $baris_akhir . ");2);=SUM(data_nilai!C2:C" . $baris_akhir . ")\n");
    // Menyuntik string formula makro Excel otomatis untuk jangkauan kolom D (Nilai pelajaran IPS)
    fwrite($file_excel, "IPS;=MAX(data_nilai!D2:D" . $baris_akhir . ");=MIN(data_nilai!D2:D" . $baris_akhir . ");=ROUND(AVERAGE(data_nilai!D2:D" . $baris_akhir . ");2);=SUM(data_nilai!D2:D" . $baris_akhir . ")\n");
    
    // Menutup file analisa excel untuk mengamankan suntikan string formula makro
    fclose($file_excel);
    // Menampilkan notifikasi sukses ke layar terminal user bahwa database spreadsheet siap dibuka
    echo "[SISTEM] data_analisa.csv berhasil diperbarui otomatis.\n";
    // Mencetak garis pembatas penutup terbawah menu analisa tabel
    echo "=============================================================================\n";
// Penutup fungsi analisaTabelMapel
}

// Menjalankan pemicu utama aplikasi
jalankanAplikasi();
?>