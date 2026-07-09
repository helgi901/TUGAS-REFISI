<?php

// Fungsi utama untuk menjalankan aplikasi terminal
function jalankanAplikasi() {
    while (true) {
        echo "\n=== MENU APLIKASI RAPORT ===\n";
        echo "1. Input Data Siswa\n";
        echo "2. Input Nilai\n";
        echo "3. Cetak Nilai Raport\n";
        echo "4. Analisa Tabel\n";
        echo "5. Keluar\n";
        echo "Pilih menu (1-5): ";
        
        $pilihan = trim(fgets(STDIN));

        if ($pilihan == '1') {
            inputDataSiswa();
        } elseif ($pilihan == '2') {
            inputNilaiSiswa();
        } elseif ($pilihan == '3') {
            cetakRaportSiswa();
        } elseif ($pilihan == '4') {
            analisaTabelMapel();
        } elseif ($pilihan == '5') {
            echo "Terima kasih! Aplikasi keluar.\n";
            break;
        } else {
            echo "Pilihan tidak valid, coba lagi.\n";
        }
    }
}

// MENU 1: Input Data Siswa (Tetap aman anti-duplikat)
function inputDataSiswa() {
    echo "\n--- INPUT DATA SISWA ---\n";
    echo "Masukkan No Siswa: ";
    $no_siswa = trim(fgets(STDIN));

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
    
    // PERBAIKAN: Memastikan ada \n di ujung agar data selanjutnya otomatis pindah ke bawah
    fwrite($file, $no_siswa . ";" . $nama_siswa . "\n");
    fclose($file);
    echo "✅ Data siswa berhasil disimpan!\n";
}

// MENU 2: Input Nilai (Otomatis tulis judul kolom permanen tanpa nama)
function inputNilaiSiswa() {
    echo "\n--- INPUT NILAI SISWA ---\n";
    echo "Masukkan No Siswa: ";
    $no_siswa = trim(fgets(STDIN));
    echo "Masukkan Nilai MTK: ";
    $mtk = trim(fgets(STDIN));
    echo "Masukkan Nilai IPA: ";
    $ipa = trim(fgets(STDIN));
    echo "Masukkan Nilai IPS: ";
    $ips = trim(fgets(STDIN));

    $buat_header = !file_exists("data_nilai.csv");
    $file = fopen("data_nilai.csv", "a");
    if ($buat_header) {
        fwrite($file, "NO;MTK;IPA;IPS\n"); // Mengunci header di file data_nilai
    }

    // PERBAIKAN FATAL: Ditambahkan "\n" di bagian paling ujung agar siswa selanjutnya otomatis turun ke baris baru!
    fwrite($file, $no_siswa . ";" . $mtk . ";" . $ipa . ";" . $ips . "\n");
    fclose($file);
    echo "✅ Data nilai berhasil disimpan!\n";
}

// MENU 3: Cetak Nilai Raport Per Siswa (Bebas Error Undefined Variable)
function cetakRaportSiswa() {
    echo "\n--- CETAK NILAI RAPORT ---\n";
    echo "Masukkan No Siswa yang dicari: ";
    $cari_no = trim(fgets(STDIN));

    // Inisialisasi awal agar extension VS Code tidak protes kuning
    $mtk = 0; $ipa = 0; $ips = 0;
    $nama_siswa = "Siswa Tanpa Nama";
    $nilai_ditemukan = false;

    // 1. Ambil Nama Siswa dari data_siswa.csv
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

    // 2. Ambil Nilai berdasarkan struktur kolom baru (MTK=1, IPA=2, IPS=3)
    if (file_exists("data_nilai.csv")) {
        $file_nilai = fopen("data_nilai.csv", "r");
        fgets($file_nilai); // Lewati header teks
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

    if (!$nilai_ditemukan) {
        echo "❌ Maaf, No Siswa [$cari_no] nilainya belum diinput di Menu 2.\n";
        return;
    }

    $nilai_array = [$mtk, $ipa, $ips];
    $terbesar   = max($nilai_array);
    $terkecil   = min($nilai_array);
    $jumlah     = array_sum($nilai_array);
    $rata_rata  = number_format($jumlah / count($nilai_array), 2);

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

// MENU 4: Analisa Tabel (Tampilan Terminal Premium + Kunci Judul Kolom Excel)
function analisaTabelMapel() {
    if (!file_exists("data_nilai.csv")) {
        echo "\n❌ Belum ada data nilai untuk dianalisa.\n";
        return;
    }

    $all_mtk = []; $all_ipa = []; $all_ips = [];
    $jumlah_baris = 0;

    $file = fopen("data_nilai.csv", "r");
    fgets($file); // Skip baris pertama header nilai (NO;MTK;IPA;IPS)

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

    // 1. Cetak Visual ke Terminal (Sangat Rapi & Simetris)
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

    // 2. Tulis File CSV dengan Judul Kolom Permanen (Bebas Column4!)
    $baris_akhir = $jumlah_baris + 1; 
    $file_excel = fopen("data_analisa.csv", "w");
    
    // Judul kolom kelima dikunci sebagai 'Jumlah Nilai'
    fwrite($file_excel, "Mapel;Nilai Terbesar;Nilai Terkecil;Nilai Rata-Rata;Jumlah Nilai\n");
    
    // Kirim rumus makro Excel otomatis
    fwrite($file_excel, "MTK;=MAX(data_nilai!B2:B" . $baris_akhir . ");=MIN(data_nilai!B2:B" . $baris_akhir . ");=ROUND(AVERAGE(data_nilai!B2:B" . $baris_akhir . ");2);=SUM(data_nilai!B2:B" . $baris_akhir . ")\n");
    fwrite($file_excel, "IPA;=MAX(data_nilai!C2:C" . $baris_akhir . ");=MIN(data_nilai!C2:C" . $baris_akhir . ");=ROUND(AVERAGE(data_nilai!C2:C" . $baris_akhir . ");2);=SUM(data_nilai!C2:C" . $baris_akhir . ")\n");
    fwrite($file_excel, "IPS;=MAX(data_nilai!D2:D" . $baris_akhir . ");=MIN(data_nilai!D2:D" . $baris_akhir . ");=ROUND(AVERAGE(data_nilai!D2:D" . $baris_akhir . ");2);=SUM(data_nilai!D2:D" . $baris_akhir . ")\n");
    
    fclose($file_excel);
    echo "\n📊 [SISTEM] Sukses! data_analisa.csv berhasil diperbarui otomatis.\n";
}

// Jalankan aplikasinya
jalankanAplikasi();