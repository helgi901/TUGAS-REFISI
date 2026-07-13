<?php

// =========================================================================
// 1. FUNGSI UTAMA (MAIN MENU FUNCTION)
// Mengatur menu pilihan yang diinput oleh user (Sekarang sampai menu 6).
// =========================================================================
function jalankanAplikasi() {
    // Loop 'while' membuat program berputar terus-menerus tanpa henti
    while (true) {
        // Menampilkan teks pilihan menu terbaru di layar terminal user
        echo "\n=== MENU APLIKASI RAPORT ===\n";
        echo "1. Input Data Siswa\n";
        echo "2. Input Nilai\n";
        echo "3. Cetak Nilai Raport\n";
        echo "4. Ranking Siswa\n";   // Menu 4 diganti khusus Ranking
        echo "5. Analisa Tabel\n";   // Menu 5 diganti khusus Analisa
        echo "6. Keluar\n";          // Menu 6 diganti khusus Keluar
        echo "Pilih menu (1-6): ";
        
        // Membaca input pilihan menu dari user dan menghapus spasi/enter liar
        $pilihan = trim(fgets(STDIN));

        // Jika user memilih menu 1, jalankan fungsi input data siswa
        if ($pilihan == '1') {
            inputDataSiswa();
        // Jika user memilih menu 2, jalankan fungsi input nilai siswa
        } elseif ($pilihan == '2') {
            inputNilaiSiswa();
        // Jika user memilih menu 3, jalankan fungsi cetak raport individu
        } elseif ($pilihan == '3') {
            cetakRaportSiswa();
        // Jika user memilih menu 4, jalankan fungsi tampilkan ranking siswa
        } elseif ($pilihan == '4') {
            tampilkanRankingSiswa();
        // Jika user memilih menu 5, jalankan fungsi analisa tabel mapel
        } elseif ($pilihan == '5') {
            analisaTabelMapel();
        // Jika user memilih menu 6, hentikan perulangan dan matikan aplikasi
        } elseif ($pilihan == '6') {
            echo "Terima kasih! Aplikasi keluar.\n";
            break; // Keluar dari loop while (stop aplikasi)
        // Jika input selain angka 1 sampai 6, tampilkan pesan eror
        } else {
            echo "Pilihan tidak valid, coba lagi.\n";
        }
    }
}

// =========================================================================
// 2. MENU 1: INPUT DATA SISWA (Wajib Angka & Anti-Duplikat No Siswa)
// =========================================================================
function inputDataSiswa() {
    echo "\n--- INPUT DATA SISWA ---\n";
    echo "Masukkan No Siswa: ";
    $no_siswa = trim(fgets(STDIN));

    // VALIDASI: No Siswa wajib diisi angka, jika huruf langsung ditolak
    if (!is_numeric($no_siswa)) {
        echo "❌ Gagal! No Siswa harus berupa ANGKA, tidak boleh huruf atau simbol.\n";
        return; 
    }

    // VALIDASI ANTI-KEMBAR NO SISWA
    if (file_exists("data_siswa.csv")) {
        $file_baca = fopen("data_siswa.csv", "r");
        while (($line = fgets($file_baca)) !== FALSE) {
            $data = explode(";", trim($line));
            if (isset($data[0]) && $data[0] == $no_siswa) {
                echo "❌ Gagal! No Siswa [$no_siswa] sudah terdaftar dengan nama: $data[1].\n";
                fclose($file_baca); 
                return; 
            }
        }
        fclose($file_baca); 
    }

    echo "Masukkan Nama Siswa: ";
    $nama_siswa = trim(fgets(STDIN));

    $buat_header = !file_exists("data_siswa.csv");
    $file = fopen("data_siswa.csv", "a");
    if ($buat_header) {
        fwrite($file, "NO;NAMA\n");
    }
    
    // Menulis data siswa ke file CSV diikuti \n agar rapi turun ke bawah
    fwrite($file, $no_siswa . ";" . $nama_siswa . "\n");
    fclose($file);
    echo "✅ Data siswa berhasil disimpan!\n";
}

// =========================================================================
// 3. MENU 2: INPUT NILAI SISWA (Wajib Angka, Anti-Double, & Batas Nilai 0-100)
// =========================================================================
function inputNilaiSiswa() {
    echo "\n--- INPUT NILAI SISWA ---\n";
    echo "Masukkan No Siswa: ";
    $no_siswa = trim(fgets(STDIN));

    // VALIDASI 1: No Siswa harus angka murni
    if (!is_numeric($no_siswa)) {
        echo "❌ Gagal! No Siswa harus berupa ANGKA.\n";
        return; 
    }

    // VALIDASI 2: Memastikan No Siswa ini belum pernah diisi nilainya di data_nilai.csv
    if (file_exists("data_nilai.csv")) {
        $file_baca_nilai = fopen("data_nilai.csv", "r"); 
        while (($line = fgets($file_baca_nilai)) !== FALSE) {
            $data = explode(";", trim($line)); 
            if (isset($data[0]) && $data[0] == $no_siswa) {
                echo "❌ Gagal! Nilai untuk No Siswa [$no_siswa] sudah pernah diinput sebelumnya.\n";
                fclose($file_baca_nilai); 
                return; 
            }
        }
        fclose($file_baca_nilai); 
    }

    echo "Masukkan Nilai MTK: ";
    $mtk = trim(fgets(STDIN)); 
    echo "Masukkan Nilai IPA: ";
    $ipa = trim(fgets(STDIN)); 
    echo "Masukkan Nilai IPS: ";
    $ips = trim(fgets(STDIN)); 

    // VALIDASI 3: Memastikan semua nilai mapel wajib berupa angka
    if (!is_numeric($mtk) || !is_numeric($ipa) || !is_numeric($ips)) {
        echo "❌ Gagal! Nilai MAPEL harus berupa angka.\n";
        return; 
    }

    // VALIDASI 4: Mengunci batas nilai raport agar wajib berada di rentang 0 sampai 100
    if ($mtk < 0 || $mtk > 100 || $ipa < 0 || $ipa > 100 || $ips < 0 || $ips > 100) {
        echo "❌ Gagal! Input ditolak. Nilai sekolah harus berada di rentang 0 sampai 100!\n";
        return; 
    }

    $buat_header = !file_exists("data_nilai.csv");
    $file = fopen("data_nilai.csv", "a");
    if ($buat_header) {
        fwrite($file, "NO;MTK;IPA;IPS\n");
    }

    // Menulis data nilai siswa ke file CSV diikuti \n di ujungnya
    fwrite($file, $no_siswa . ";" . $mtk . ";" . $ipa . ";" . $ips . "\n");
    fclose($file);
    echo "✅ Data nilai berhasil disimpan!\n";
}

// =========================================================================
// 4. MENU 3: CETAK NILAI RAPORT PER SISWA (Wajib Angka Saat Cari Nomor)
// =========================================================================
function cetakRaportSiswa() {
    echo "\n--- CETAK NILAI RAPORT ---\n";
    echo "Masukkan No Siswa yang dicari: ";
    $cari_no = trim(fgets(STDIN));

    // VALIDASI: Cari nomor siswa WAJIB pakai angka, huruf langsung diblok
    if (!is_numeric($cari_no)) {
        echo "❌ Gagal! No Siswa yang dicari harus berupa ANGKA.\n";
        return; 
    }

    $mtk = 0; $ipa = 0; $ips = 0;
    $nama_siswa = "Siswa Tanpa Nama";
    $nilai_ditemukan = false; 

    // Tahap 1: Mencari data nama siswa berdasarkan nomornya di data_siswa.csv
    if (file_exists("data_siswa.csv")) {
        $file_siswa = fopen("data_siswa.csv", "r"); 
        while (($line = fgets($file_siswa)) !== FALSE) {
            $data = explode(";", trim($line)); 
            if (isset($data[0]) && $data[0] == $cari_no) {
                $nama_siswa = $data[1]; 
                break; 
            }
        }
        fclose($file_siswa); 
    }

    // Tahap 2: Mencari data nilai mata pelajaran di data_nilai.csv
    if (file_exists("data_nilai.csv")) {
        $file_nilai = fopen("data_nilai.csv", "r"); 
        fgets($file_nilai); 
        while (($line = fgets($file_nilai)) !== FALSE) {
            $data = explode(";", trim($line)); 
            if (isset($data[0]) && $data[0] == $cari_no) {
                $mtk = (int)$data[1]; 
                $ipa = (int)$data[2]; 
                $ips = (int)$data[3]; 
                $nilai_ditemukan = true; 
                break; 
            }
        }
        fclose($file_nilai); 
    }

    // Jika nomor siswa tersebut belum punya nilai, batalkan cetak
    if (!$nilai_ditemukan) {
        echo "❌ Maaf, No Siswa [$cari_no] nilainya belum diinput di Menu 2.\n";
        return; 
    }

    // Hitung statistik untuk cetakan raport personal siswa
    $nilai_array = [$mtk, $ipa, $ips];
    $terbesar   = max($nilai_array); 
    $terkecil   = min($nilai_array); 
    $jumlah     = array_sum($nilai_array); 
    $rata_rata  = number_format($jumlah / count($nilai_array), 2);

    // Mencetak output desain nota/lembar raport ke terminal
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
// 5. MENU 4: FUNGSI TAMPILKAN RANKING SISWA
// Menghitung total nilai seluruh siswa lalu diurutkan dari juara 1 terbesar.
// =========================================================================
function tampilkanRankingSiswa() {
    // Validasi awal: Jika file nilai belum ada, batalkan kalkulasi ranking
    if (!file_exists("data_nilai.csv")) {
        echo "\n❌ Belum ada data nilai untuk menghitung ranking.\n";
        return;
    }

    // Membuat kamus pencarian No Siswa -> Nama Siswa agar tabel ranking ada namanya
    $kamus_nama = [];
    if (file_exists("data_siswa.csv")) {
        $file_s = fopen("data_siswa.csv", "r");
        fgets($file_s); // Lewati header kolom data_siswa
        while (($line = fgets($file_s)) !== FALSE) {
            $data = explode(";", trim($line));
            if (count($data) >= 2) {
                $kamus_nama[$data[0]] = $data[1]; // Mengikat Nomor dengan Nama Siswa
            }
        }
        fclose($file_s);
    }

    // Menyiapkan array penampung utama ranking seluruh murid
    $daftar_ranking = [];

    $file = fopen("data_nilai.csv", "r");
    fgets($file); // Melewati header data_nilai

    // Mengambil dan menjumlahkan total skor semua murid di file
    while (($line = fgets($file)) !== FALSE) {
        $data = explode(";", trim($line)); 
        if (count($data) >= 4) {
            $no_s  = $data[0];
            $v_mtk = (int)$data[1];
            $v_ipa = (int)$data[2];
            $v_ips = (int)$data[3];

            // Menghitung total dan rata-rata skor per anak
            $total_skor = $v_mtk + $v_ipa + $v_ips;
            $rata_skor  = number_format($total_skor / 3, 2);
            $nama_s     = isset($kamus_nama[$no_s]) ? $kamus_nama[$no_s] : "Tanpa Nama";

            // Masukkan data paket siswa ke array ranking
            $daftar_ranking[] = [
                'no'    => $no_s,
                'nama'  => $nama_s,
                'total' => $total_skor,
                'rata'  => $rata_skor
            ];
        }
    }
    fclose($file); 

    // Jika file data_nilai ada tapi isinya kosong, tampilkan info
    if (empty($daftar_ranking)) {
        echo "\n❌ Data nilai masih kosong.\n";
        return;
    }

    // PROSES SORTING: Mengurutkan array $daftar_ranking berdasarkan skor total secara Descending (Besar ke Kecil)
    usort($daftar_ranking, function($a, $b) {
        return $b['total'] <=> $a['total'];
    });

    // MENCETAK TABEL RANKING DI TERMINAL (Format kotak presisi pakai sprintf)
    echo "\n========================================================\n";
    echo "                    TABEL RANKING SISWA                 \n";
    echo "========================================================\n";
    echo sprintf("| %-4s | %-5s | %-15s | %-10s | %-10s |\n", "RANK", "NO", "NAMA SISWA", "TOTAL", "RATA-RATA");
    echo "--------------------------------------------------------\n";
    
    $rank_counter = 1; // Variabel pembuat nomor urut juara (dimulai dari 1)
    foreach ($daftar_ranking as $siswa) {
        echo sprintf("| %-4s | %-5s | %-15s | %-10s | %-10s |\n", 
            $rank_counter, 
            $siswa['no'], 
            $siswa['nama'], 
            $siswa['total'], 
            $siswa['rata']
        );
        $rank_counter++; // Setiap dicetak, nomor juara bertambah 1 otomatis
    }
    echo "========================================================\n";

    // EKSPOR: Menyimpan data tabel ranking terurut ini ke file data_ranking.csv agar bisa dibuka di excel
    $file_rank_excel = fopen("data_ranking.csv", "w");
    fwrite($file_rank_excel, "Rank;No Siswa;Nama Siswa;Total Nilai;Rata-Rata\n");
    $excel_rank_num = 1;
    foreach ($daftar_ranking as $siswa) {
        fwrite($file_rank_excel, $excel_rank_num . ";" . $siswa['no'] . ";" . $siswa['nama'] . ";" . $siswa['total'] . ";" . $siswa['rata'] . "\n");
        $excel_rank_num++;
    }
    fclose($file_rank_excel);

    echo "\n📊 [SISTEM] Sukses! data_ranking.csv berhasil diperbarui otomatis.\n";
}

// =========================================================================
// 6. MENU 5: ANALISA TABEL MAPEL (Menyuntik Rumus Makro Excel)
// =========================================================================
function analisaTabelMapel() {
    if (!file_exists("data_nilai.csv")) {
        echo "\n❌ Belum ada data nilai untuk dianalisa.\n";
        return;
    }

    $all_mtk = []; $all_ipa = []; $all_ips = [];
    $jumlah_baris = 0;

    $file = fopen("data_nilai.csv", "r");
    fgets($file); // Melewati baris judul kolom

    // Menyedot seluruh isi data nilai mapel
    while (($line = fgets($file)) !== FALSE) {
        $data = explode(";", trim($line)); 
        if (count($data) >= 4) {
            $all_mtk[] = (int)$data[1]; 
            $all_ipa[] = (int)$data[2]; 
            $all_ips[] = (int)$data[3]; 
            $jumlah_baris++; 
        }
    }
    fclose($file); 

    if (empty($all_mtk)) {
        echo "\n❌ Data nilai masih kosong.\n";
        return;
    }

    // Fungsi penolong ringkas untuk kalkulasi min/max/average terminal
    $hitung_stats = function($mapel_array) {
        $jml = array_sum($mapel_array); 
        return [
            'max'  => max($mapel_array), 
            'min'  => min($mapel_array), 
            'avg'  => number_format($jml / count($mapel_array), 2), 
            'sum'  => $jml 
        ];
    };

    $stats_mtk = $hitung_stats($all_mtk);
    $stats_ipa = $hitung_stats($all_ipa);
    $stats_ips = $hitung_stats($all_ips);

    // Menampilkan cetakan visual TABEL ANALISA kotak rapi simetris di terminal
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

    // Menghitung jangkauan baris dinamis untuk penulisan rumus macro Excel
    $baris_akhir = $jumlah_baris + 1; 
    $file_excel = fopen("data_analisa.csv", "w");
    
    fwrite($file_excel, "Mapel;Nilai Terbesar;Nilai Terkecil;Nilai Rata-Rata;Jumlah Nilai\n");
    
    // Menyuntikkan string rumus otomatis asli bawaan Excel (=MAX, =MIN, =ROUND, =SUM)
    fwrite($file_excel, "MTK;=MAX(data_nilai!B2:B" . $baris_akhir . ");=MIN(data_nilai!B2:B" . $baris_akhir . ");=ROUND(AVERAGE(data_nilai!B2:B" . $baris_akhir . ");2);=SUM(data_nilai!B2:B" . $baris_akhir . ")\n");
    fwrite($file_excel, "IPA;=MAX(data_nilai!C2:C" . $baris_akhir . ");=MIN(data_nilai!C2:C" . $baris_akhir . ");=ROUND(AVERAGE(data_nilai!C2:C" . $baris_akhir . ");2);=SUM(data_nilai!C2:C" . $baris_akhir . ")\n");
    fwrite($file_excel, "IPS;=MAX(data_nilai!D2:D" . $baris_akhir . ");=MIN(data_nilai!D2:D" . $baris_akhir . ");=ROUND(AVERAGE(data_nilai!D2:D" . $baris_akhir . ");2);=SUM(data_nilai!D2:D" . $baris_akhir . ")\n");
    
    fclose($file_excel);
    echo "\n📊 [SISTEM] Sukses! data_analisa.csv berhasil diperbarui otomatis.\n";
}

// Menjalankan pemicu utama aplikasi
jalankanAplikasi();