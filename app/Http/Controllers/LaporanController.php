<?php

namespace App\Http\Controllers;

use App\Models\Aset;
use App\Models\Biaya;
use App\Models\LogPekerjaan;
use App\Models\PemakaianChemical;
use App\Models\PenerimaanLinen;
use App\Models\BeratLinenHarian;
use App\Models\StokChemical;
use App\Models\TransaksiLinen;
use App\Models\Tugas;
use App\Models\SuhuRuangan;
use Illuminate\Http\Request;
use Inertia\Inertia;

// Library Excel (setara openpyxl di Python). Install: sail composer require phpoffice/phpspreadsheet
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use PhpOffice\PhpSpreadsheet\Chart\Legend;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Title;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

// Controller untuk modul Rekap Laporan.
// Pola umum: method "...Export()" untuk tiap modul mengambil data, susun jadi array baris,
// lalu serahkan ke helper privat exportSimpleExcel() supaya kode pembuatan file Excel
// TIDAK ditulis berulang-ulang di 9 method berbeda (DRY -- Don't Repeat Yourself),
// setara pola BaseExportView / BaseSnapshotExportView di Django asli.
class LaporanController extends Controller
{
    // GET /laporan -> halaman dashboard: 1 filter tanggal bersama + tombol export tiap modul
    public function index(Request $request)
    {
        // Default rentang tanggal: 30 hari terakhir, dipakai sebagai nilai awal filter di halaman
        $startDate = $request->date('start_date') ?? now()->subDays(30)->startOfDay();
        $endDate = $request->date('end_date') ?? now()->endOfDay();

        return Inertia::render('Laporan/Index', [
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
        ]);
    }

    // ==========================================================================
    // SUHU (sudah dibuat di tahap sebelumnya -- halaman dedicated dengan grafik)
    // ==========================================================================

    public function suhu(Request $request)
    {
        $startDate = $request->date('start_date') ?? now()->subDays(30)->startOfDay();
        $endDate = $request->date('end_date') ?? now()->endOfDay();

        $queryDasar = SuhuRuangan::whereBetween('tanggal', [$startDate, $endDate]);

        $dataTabel = (clone $queryDasar)->with('petugas')->orderBy('tanggal')->orderBy('jam')
            ->paginate(15)->withQueryString();

        $semuaData = (clone $queryDasar)->orderBy('tanggal')->get(['tanggal', 'ruangan', 'suhu', 'kelembaban']);

        [$chartLabels, $datasetSuhu, $datasetKelembaban] = $this->susunDataGrafikSuhu($semuaData);

        return Inertia::render('Laporan/Suhu', [
            'dataTabel' => $dataTabel,
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
            'chartLabels' => $chartLabels,
            'datasetSuhu' => $datasetSuhu,
            'datasetKelembaban' => $datasetKelembaban,
        ]);
    }

    public function suhuExport(Request $request)
    {
        $request->validate(['start_date' => ['required', 'date'], 'end_date' => ['required', 'date']]);
        $startDate = $request->date('start_date');
        $endDate = $request->date('end_date');

        $semuaData = SuhuRuangan::with('petugas')->whereBetween('tanggal', [$startDate, $endDate])
            ->orderBy('tanggal')->orderBy('jam')->get();

        [$chartLabels, $datasetSuhu] = $this->susunDataGrafikSuhu($semuaData);

        $spreadsheet = new Spreadsheet();
        $sheetData = $spreadsheet->getActiveSheet();
        $sheetData->setTitle('Data Suhu');
        $sheetData->fromArray(['Tanggal', 'Jam', 'Ruangan', 'Waktu Ukur', 'Suhu (°C)', 'Kelembaban (%)', 'Petugas', 'Keterangan'], null, 'A1');
        $baris = 2;
        foreach ($semuaData as $item) {
            $sheetData->fromArray([
                $item->tanggal->format('Y-m-d'), $item->jam, $item->ruangan, $item->waktu_ukur,
                (float) $item->suhu, $item->kelembaban, $item->petugas?->name, $item->keterangan,
            ], null, "A{$baris}");
            $baris++;
        }
        foreach (range('A', 'H') as $kolom) {
            $sheetData->getColumnDimension($kolom)->setAutoSize(true);
        }

        $sheetGrafik = $spreadsheet->createSheet();
        $sheetGrafik->setTitle('Grafik Suhu');
        $this->tulisPivotDanChart($sheetGrafik, $chartLabels, $datasetSuhu, 'Tren Suhu Ruangan (°C)');

        $spreadsheet->setActiveSheetIndex(0);

        $namaFile = "laporan_suhu_{$startDate->format('Y-m-d')}_sd_{$endDate->format('Y-m-d')}.xlsx";

        return $this->streamSpreadsheet($spreadsheet, $namaFile);
    }

    // ==========================================================================
    // CHECKLIST PENERIMAAN LINEN (date range) -- 1 baris Excel per ITEM linen, bukan per header
    // ==========================================================================

    public function checklistExport(Request $request)
    {
        [$startDate, $endDate] = $this->validasiRentangTanggal($request);

        // with('items', 'petugas') -> eager load item-item linen DAN petugas sekaligus,
        // supaya tidak query berulang saat looping nested di bawah
        $semuaPenerimaan = PenerimaanLinen::with(['items', 'petugas'])
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->orderBy('tanggal')->orderBy('jam')->get();

        $headers = ['Tanggal', 'Jam', 'Ruangan', 'Nama Item', 'Jumlah', 'Kondisi', 'Keterangan Item', 'Petugas'];

        // Baris Excel-nya di-generate lewat sebuah generator PHP (pakai 'yield'), supaya tidak perlu
        // bikin array besar di memory dulu sebelum ditulis -- efisien kalau datanya banyak.
        // Loop LUAR = tiap header penerimaan, loop DALAM = tiap item di header itu (flatten jadi 1 baris/item)
        $rows = (function () use ($semuaPenerimaan) {
            foreach ($semuaPenerimaan as $penerimaan) {
                foreach ($penerimaan->items as $item) {
                    yield [
                        $penerimaan->tanggal->format('Y-m-d'),
                        $penerimaan->jam,
                        $penerimaan->ruangan,
                        $item->nama_item,
                        $item->jumlah,
                        $item->kondisi,
                        $item->keterangan,
                        $penerimaan->petugas?->name,
                    ];
                }
            }
        })();

        return $this->exportSimpleExcel($headers, $rows, 'Checklist Linen', $this->namaFileRange('checklist_penerimaan_linen', $startDate, $endDate));
    }

    // ==========================================================================
    // PEMAKAIAN CHEMICAL (date range)
    // ==========================================================================

    public function pemakaianChemicalExport(Request $request)
    {
        [$startDate, $endDate] = $this->validasiRentangTanggal($request);

        $data = PemakaianChemical::with(['chemical', 'petugas'])
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->orderBy('tanggal')->get();

        $headers = ['Tanggal', 'Nama Chemical', 'Jumlah', 'Satuan', 'Petugas', 'Keterangan'];
        $rows = $data->map(fn ($item) => [
            $item->tanggal->format('Y-m-d'),
            $item->chemical?->nama_chemical,
            (float) $item->jumlah,
            $item->chemical?->unit,
            $item->petugas?->name,
            $item->keterangan,
        ]);

        return $this->exportSimpleExcel($headers, $rows, 'Pemakaian Chemical', $this->namaFileRange('pemakaian_chemical', $startDate, $endDate));
    }

    // ==========================================================================
    // TRANSAKSI LINEN (date range)
    // ==========================================================================

    public function transaksiLinenExport(Request $request)
    {
        [$startDate, $endDate] = $this->validasiRentangTanggal($request);

        $data = TransaksiLinen::with(['stokLinen', 'petugas'])
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->orderBy('tanggal')->get();

        $headers = ['Tanggal', 'Nama Linen', 'Ruangan', 'Jenis Transaksi', 'Jumlah', 'Petugas', 'Keterangan'];
        $rows = $data->map(fn ($item) => [
            $item->tanggal->format('Y-m-d'),
            $item->stokLinen?->nama_linen,
            $item->stokLinen?->ruangan,
            $item->jenis_transaksi === 'MASUK' ? 'Linen Masuk' : 'Linen Keluar',
            $item->jumlah,
            $item->petugas?->name,
            $item->keterangan,
        ]);

        return $this->exportSimpleExcel($headers, $rows, 'Transaksi Linen', $this->namaFileRange('transaksi_linen', $startDate, $endDate));
    }

    // ==========================================================================
    // BERAT LINEN HARIAN (date range)
    // ==========================================================================

    public function beratLinenExport(Request $request)
    {
        [$startDate, $endDate] = $this->validasiRentangTanggal($request);

        $data = BeratLinenHarian::with('petugas')
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->orderBy('tanggal')->get();

        $headers = ['Tanggal', 'Ruangan', 'Shift', 'Total Berat (Kg)', 'Petugas'];
        $rows = $data->map(fn ($item) => [
            $item->tanggal->format('Y-m-d'),
            $item->ruangan,
            $item->shift,
            (float) $item->total_berat,
            $item->petugas?->name,
        ]);

        return $this->exportSimpleExcel($headers, $rows, 'Berat Linen Harian', $this->namaFileRange('berat_linen_harian', $startDate, $endDate));
    }

    // ==========================================================================
    // LOG PEKERJAAN (date range)
    // ==========================================================================

    public function logPekerjaanExport(Request $request)
    {
        [$startDate, $endDate] = $this->validasiRentangTanggal($request);

        $data = LogPekerjaan::with('pj')
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->orderBy('tanggal')->get();

        $headers = ['Tanggal', 'Keterangan', 'PJ'];
        $rows = $data->map(fn ($item) => [
            $item->tanggal->format('Y-m-d'),
            $item->keterangan,
            $item->pj?->name,
        ]);

        return $this->exportSimpleExcel($headers, $rows, 'Log Pekerjaan', $this->namaFileRange('log_pekerjaan', $startDate, $endDate));
    }

    // ==========================================================================
    // BIAYA / PENGELUARAN (date range) -- MODUL BARU, ikut disediakan export-nya juga
    // ==========================================================================

    public function biayaExport(Request $request)
    {
        [$startDate, $endDate] = $this->validasiRentangTanggal($request);

        $data = Biaya::with('pj')
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->orderBy('tanggal')->get();

        $headers = ['Tanggal', 'Kategori', 'Nama Barang', 'Qty', 'Satuan', 'Harga', 'Jumlah', 'PJ', 'Keterangan'];
        $rows = $data->map(fn ($item) => [
            $item->tanggal->format('Y-m-d'),
            $item->kategori,
            $item->nama_barang,
            $item->qty,
            $item->satuan,
            (float) $item->harga,
            (float) $item->jumlah,
            $item->pj?->name,
            $item->keterangan,
        ]);

        // Tambahan khusus Biaya: sisipkan baris "Total" di baris terakhir, sama seperti template Excel aslinya
        $totalJumlah = $data->sum('jumlah');
        $baris = $rows->toArray();
        $baris[] = ['', '', '', '', '', '', '', '', '']; // baris kosong sebagai pemisah visual
        $baris[] = ['', '', '', '', '', 'TOTAL', (float) $totalJumlah, '', ''];

        return $this->exportSimpleExcel($headers, $baris, 'Pengeluaran Laundry', $this->namaFileRange('pengeluaran_laundry', $startDate, $endDate));
    }

    // ==========================================================================
    // ASET (SNAPSHOT -- kondisi TERKINI, tanpa filter tanggal, sesuai pola Django asli)
    // ==========================================================================

    public function asetExport()
    {
        $data = Aset::orderBy('nama_barang')->get();

        $headers = ['Nama Barang', 'Jumlah', 'Satuan', 'Merk/Tipe', 'Serial Number', 'Tahun Pengadaan', 'Tanggal Input', 'Keterangan'];
        $rows = $data->map(fn ($item) => [
            $item->nama_barang,
            $item->jumlah,
            $item->satuan,
            $item->merk,
            $item->serial_number,
            $item->tahun_pengadaan,
            $item->tanggal_input->format('Y-m-d'),
            $item->keterangan,
        ]);

        return $this->exportSimpleExcel($headers, $rows, 'Aset', 'laporan_aset_' . now()->format('Y-m-d') . '.xlsx');
    }

    // ==========================================================================
    // RENCANA KERJA / SCHEDULE (SNAPSHOT)
    // ==========================================================================

    public function scheduleExport()
    {
        $data = Tugas::with('penanggungJawab')->orderBy('target_waktu')->get();

        $headers = ['Pekerjaan', 'Deskripsi', 'Status', 'Penanggung Jawab', 'Target Waktu', 'Periode'];
        $rows = $data->map(fn ($item) => [
            $item->judul,
            $item->deskripsi,
            $item->status,
            $item->penanggungJawab?->name,
            $item->target_waktu,
            $item->periode,
        ]);

        return $this->exportSimpleExcel($headers, $rows, 'Rencana Kerja', 'laporan_rencana_kerja_' . now()->format('Y-m-d') . '.xlsx');
    }

    // ==========================================================================
    // STOK CHEMICAL (SNAPSHOT -- master data kondisi terkini)
    // ==========================================================================

    public function stokChemicalExport()
    {
        $data = StokChemical::orderBy('nama_chemical')->get();

        $headers = ['Nama Chemical', 'Jumlah Stok', 'Satuan', 'Update Terakhir'];
        $rows = $data->map(fn ($item) => [
            $item->nama_chemical,
            (float) $item->jumlah_stok,
            $item->unit,
            $item->update_terakhir?->format('Y-m-d H:i'),
        ]);

        return $this->exportSimpleExcel($headers, $rows, 'Stok Chemical', 'laporan_stok_chemical_' . now()->format('Y-m-d') . '.xlsx');
    }

    // ==========================================================================
    // HELPER PRIVAT -- dipakai bersama semua method export di atas
    // ==========================================================================

    // Validasi & ambil rentang tanggal dari query string, dipakai oleh semua export yang butuh filter tanggal
    private function validasiRentangTanggal(Request $request): array
    {
        $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date'],
        ]);

        return [$request->date('start_date'), $request->date('end_date')];
    }

    // Bikin nama file yang konsisten, menyertakan rentang tanggal, dipakai semua export ber-filter tanggal
    private function namaFileRange(string $prefix, $startDate, $endDate): string
    {
        return "{$prefix}_{$startDate->format('Y-m-d')}_sd_{$endDate->format('Y-m-d')}.xlsx";
    }

    // Helper UTAMA: bikin file Excel 1-sheet sederhana (headers + rows), dipakai oleh SEMUA export
    // di atas KECUALI Suhu (yang punya sheet tambahan berisi grafik, jadi ditangani manual di method-nya sendiri)
    private function exportSimpleExcel(array $headers, iterable $rows, string $sheetTitle, string $filename)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle($sheetTitle);
        $sheet->fromArray($headers, null, 'A1');

        $baris = 2;
        foreach ($rows as $row) {
            $sheet->fromArray($row, null, "A{$baris}");
            $baris++;
        }

        // Lebar kolom otomatis, dari kolom A sampai sejumlah kolom header yang ada
        $kolomTerakhir = Coordinate::stringFromColumnIndex(count($headers));
        foreach (range('A', $kolomTerakhir) as $kolom) {
            $sheet->getColumnDimension($kolom)->setAutoSize(true);
        }

        // Baris header dibuat bold, supaya gampang dibedakan dari data
        $sheet->getStyle('A1:' . $kolomTerakhir . '1')->getFont()->setBold(true);

        return $this->streamSpreadsheet($spreadsheet, $filename);
    }

    // Helper: kirim objek Spreadsheet sebagai file download, dipakai oleh semua export
    private function streamSpreadsheet(Spreadsheet $spreadsheet, string $filename)
    {
        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->setIncludeCharts(true); // aman selalu diaktifkan, tidak berpengaruh kalau memang tidak ada chart di sheet manapun
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    // Helper KHUSUS Suhu: susun data mentah jadi format grafik (dipakai method suhu() & suhuExport())
    private function susunDataGrafikSuhu($semuaData)
    {
        $chartLabels = $semuaData->pluck('tanggal')->map(fn ($tgl) => $tgl->format('Y-m-d'))
            ->unique()->sort()->values()->all();
        $ruanganList = $semuaData->pluck('ruangan')->unique()->values()->all();

        $datasetSuhu = [];
        $datasetKelembaban = [];

        foreach ($ruanganList as $ruangan) {
            $suhuPerTanggal = [];
            $kelembabanPerTanggal = [];
            foreach ($chartLabels as $tanggal) {
                $itemsHariIni = $semuaData->filter(fn ($item) => $item->ruangan === $ruangan && $item->tanggal->format('Y-m-d') === $tanggal);
                $suhuPerTanggal[] = $itemsHariIni->isNotEmpty() ? round((float) $itemsHariIni->avg('suhu'), 1) : null;
                $kelembabanPerTanggal[] = $itemsHariIni->isNotEmpty() ? round((float) $itemsHariIni->avg('kelembaban'), 1) : null;
            }
            $datasetSuhu[] = ['ruangan' => $ruangan, 'data' => $suhuPerTanggal];
            $datasetKelembaban[] = ['ruangan' => $ruangan, 'data' => $kelembabanPerTanggal];
        }

        return [$chartLabels, $datasetSuhu, $datasetKelembaban];
    }

    // Helper KHUSUS Suhu: tulis tabel pivot + grafik garis native ke sebuah sheet
    private function tulisPivotDanChart($sheet, array $chartLabels, array $dataset, string $judul)
    {
        $sheet->setCellValue('A1', 'Tanggal');
        $kolomKe = 1;
        foreach ($dataset as $seri) {
            $hurufKolom = Coordinate::stringFromColumnIndex($kolomKe + 1);
            $sheet->setCellValue($hurufKolom . '1', $seri['ruangan']);
            $kolomKe++;
        }

        foreach ($chartLabels as $i => $tanggal) {
            $nomorBaris = $i + 2;

            // Kolom A = tanggal
            $sheet->setCellValue('A' . $nomorBaris, $tanggal);

            $kolomKe = 1;
            foreach ($dataset as $seri) {
                $hurufKolom = Coordinate::stringFromColumnIndex($kolomKe + 1);

                // null dibiarkan kosong agar grafik tidak menganggap data yang hilang sebagai 0
                $sheet->setCellValue(
                    $hurufKolom . $nomorBaris,
                    $seri['data'][$i]
                );

                $kolomKe++;
            }
        }

        $jumlahBarisData = count($chartLabels);
        $jumlahSeri = count($dataset);
        $barisTerakhir = $jumlahBarisData + 1;

        if ($jumlahBarisData === 0 || $jumlahSeri === 0) {
            return; // tidak ada data -> tidak perlu (dan tidak bisa) bikin chart
        }

        $namaSheet = $sheet->getTitle();
        $dataSeriesLabels = [];
        $xAxisTickValues = [
            new DataSeriesValues('String', "'{$namaSheet}'!\$A\$2:\$A\${$barisTerakhir}", null, $jumlahBarisData),
        ];
        $dataSeriesValues = [];

        for ($k = 0; $k < $jumlahSeri; $k++) {
            $hurufKolom = Coordinate::stringFromColumnIndex($k + 2);
            $dataSeriesLabels[] = new DataSeriesValues('String', "'{$namaSheet}'!\${$hurufKolom}\$1", null, 1);
            $dataSeriesValues[] = new DataSeriesValues('Number', "'{$namaSheet}'!\${$hurufKolom}\$2:\${$hurufKolom}\${$barisTerakhir}", null, $jumlahBarisData);
        }

        $seriGrafik = new DataSeries(
            DataSeries::TYPE_LINECHART,
            DataSeries::GROUPING_STANDARD,
            range(0, $jumlahSeri - 1),
            $dataSeriesLabels,
            $xAxisTickValues,
            $dataSeriesValues
        );

        $plotArea = new PlotArea(null, [$seriGrafik]);
        $legend = new Legend(Legend::POSITION_RIGHT, null, false);
        $chart = new Chart('grafik_' . $namaSheet, new Title($judul), $legend, $plotArea);

        $kolomAwalChart = Coordinate::stringFromColumnIndex($jumlahSeri + 4);
        $chart->setTopLeftPosition('A' . ($barisTerakhir + 3));
        $chart->setBottomRightPosition($kolomAwalChart . ($barisTerakhir + 25));

        $sheet->addChart($chart);
    }
}
