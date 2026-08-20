<?php

namespace App\Http\Controllers;

use App\Models\PenerimaanLinen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Barryvdh\DomPDF\Facade\Pdf;

class RekapPenerimaanLinenController extends Controller
{
    /**
     * Ambil filter laporan.
     */
    private function getFilters(Request $request): array
    {
        $tanggalMulai = $request->input(
            'tanggal_mulai',
            now()->startOfMonth()->format('Y-m-d')
        );

        $tanggalAkhir = $request->input(
            'tanggal_akhir',
            now()->format('Y-m-d')
        );

        $ruangan = $request->input('ruangan');

        return [
            'tanggal_mulai' => $tanggalMulai,
            'tanggal_akhir' => $tanggalAkhir,
            'ruangan' => $ruangan,
        ];
    }

    /**
     * Base query seluruh laporan.
     */
    private function baseQuery(array $filters)
    {
        $query = DB::table('penerimaan_linens')
            ->join(
                'item_linens',
                'item_linens.penerimaan_id',
                '=',
                'penerimaan_linens.id'
            )
            ->whereBetween(
                'penerimaan_linens.tanggal',
                [
                    $filters['tanggal_mulai'],
                    $filters['tanggal_akhir'],
                ]
            );

        if (!empty($filters['ruangan'])) {
            $query->where(
                'penerimaan_linens.ruangan',
                $filters['ruangan']
            );
        }

        return $query;
    }

    /**
     * Buat seluruh data laporan.
     */
    private function getReportData(array $filters): array
    {
        $baseQuery = $this->baseQuery($filters);

        /*
        |--------------------------------------------------------------------------
        | SUMMARY
        |--------------------------------------------------------------------------
        */

        $summary = (clone $baseQuery)
            ->selectRaw(
                'COUNT(DISTINCT penerimaan_linens.id) as total_transaksi'
            )
            ->selectRaw(
                'COALESCE(SUM(item_linens.jumlah), 0) as total_linen'
            )
            ->selectRaw(
                "COALESCE(SUM(
                    CASE
                        WHEN item_linens.kondisi = 'Baik'
                        THEN item_linens.jumlah
                        ELSE 0
                    END
                ), 0) as total_baik"
            )
            ->selectRaw(
                "COALESCE(SUM(
                    CASE
                        WHEN item_linens.kondisi = 'Noda'
                        THEN item_linens.jumlah
                        ELSE 0
                    END
                ), 0) as total_noda"
            )
            ->selectRaw(
                "COALESCE(SUM(
                    CASE
                        WHEN item_linens.kondisi = 'Rusak'
                        THEN item_linens.jumlah
                        ELSE 0
                    END
                ), 0) as total_rusak"
            )
            ->first();

        $totalTransaksi = (int) ($summary->total_transaksi ?? 0);
        $totalLinen = (int) ($summary->total_linen ?? 0);
        $totalBaik = (int) ($summary->total_baik ?? 0);
        $totalNoda = (int) ($summary->total_noda ?? 0);
        $totalRusak = (int) ($summary->total_rusak ?? 0);

        $totalBermasalah = $totalNoda + $totalRusak;

        $persentaseBaik = $totalLinen > 0
            ? round(($totalBaik / $totalLinen) * 100, 2)
            : 0;

        $persentaseNoda = $totalLinen > 0
            ? round(($totalNoda / $totalLinen) * 100, 2)
            : 0;

        $persentaseRusak = $totalLinen > 0
            ? round(($totalRusak / $totalLinen) * 100, 2)
            : 0;

        $persentaseBermasalah = $totalLinen > 0
            ? round(($totalBermasalah / $totalLinen) * 100, 2)
            : 0;


        /*
        |--------------------------------------------------------------------------
        | REKAP RUANGAN
        |--------------------------------------------------------------------------
        */

        $rekapRuangan = (clone $baseQuery)
            ->select(
                'penerimaan_linens.ruangan'
            )
            ->selectRaw(
                'COUNT(DISTINCT penerimaan_linens.id) as total_transaksi'
            )
            ->selectRaw(
                'COALESCE(SUM(item_linens.jumlah), 0) as total_linen'
            )
            ->selectRaw(
                "COALESCE(SUM(
                    CASE
                        WHEN item_linens.kondisi = 'Baik'
                        THEN item_linens.jumlah
                        ELSE 0
                    END
                ), 0) as total_baik"
            )
            ->selectRaw(
                "COALESCE(SUM(
                    CASE
                        WHEN item_linens.kondisi = 'Noda'
                        THEN item_linens.jumlah
                        ELSE 0
                    END
                ), 0) as total_noda"
            )
            ->selectRaw(
                "COALESCE(SUM(
                    CASE
                        WHEN item_linens.kondisi = 'Rusak'
                        THEN item_linens.jumlah
                        ELSE 0
                    END
                ), 0) as total_rusak"
            )
            ->groupBy(
                'penerimaan_linens.ruangan'
            )
            ->get()
            ->map(function ($row) {
                $total = (int) $row->total_linen;
                $noda = (int) $row->total_noda;
                $rusak = (int) $row->total_rusak;
                $bermasalah = $noda + $rusak;

                return [
                    'ruangan' => $row->ruangan,
                    'total_transaksi' => (int) $row->total_transaksi,
                    'total_linen' => $total,
                    'total_baik' => (int) $row->total_baik,
                    'total_noda' => $noda,
                    'total_rusak' => $rusak,
                    'total_bermasalah' => $bermasalah,
                    'persentase_bermasalah' => $total > 0
                        ? round(($bermasalah / $total) * 100, 2)
                        : 0,
                ];
            })
            ->sortByDesc('persentase_bermasalah')
            ->values();


        /*
        |--------------------------------------------------------------------------
        | REKAP JENIS LINEN
        |--------------------------------------------------------------------------
        */

        $rekapJenisLinen = (clone $baseQuery)
            ->select(
                'item_linens.nama_item'
            )
            ->selectRaw(
                'COALESCE(SUM(item_linens.jumlah), 0) as total_linen'
            )
            ->selectRaw(
                "COALESCE(SUM(
                    CASE
                        WHEN item_linens.kondisi = 'Baik'
                        THEN item_linens.jumlah
                        ELSE 0
                    END
                ), 0) as total_baik"
            )
            ->selectRaw(
                "COALESCE(SUM(
                    CASE
                        WHEN item_linens.kondisi = 'Noda'
                        THEN item_linens.jumlah
                        ELSE 0
                    END
                ), 0) as total_noda"
            )
            ->selectRaw(
                "COALESCE(SUM(
                    CASE
                        WHEN item_linens.kondisi = 'Rusak'
                        THEN item_linens.jumlah
                        ELSE 0
                    END
                ), 0) as total_rusak"
            )
            ->groupBy(
                'item_linens.nama_item'
            )
            ->get()
            ->map(function ($row) {
                $total = (int) $row->total_linen;
                $noda = (int) $row->total_noda;
                $rusak = (int) $row->total_rusak;
                $bermasalah = $noda + $rusak;

                return [
                    'nama_item' => $row->nama_item,
                    'total_linen' => $total,
                    'total_baik' => (int) $row->total_baik,
                    'total_noda' => $noda,
                    'total_rusak' => $rusak,
                    'total_bermasalah' => $bermasalah,
                    'persentase_bermasalah' => $total > 0
                        ? round(($bermasalah / $total) * 100, 2)
                        : 0,
                ];
            })
            ->sortByDesc('persentase_bermasalah')
            ->values();


        /*
        |--------------------------------------------------------------------------
        | TREN HARIAN
        |--------------------------------------------------------------------------
        */

        $trenHarian = (clone $baseQuery)
            ->select(
                'penerimaan_linens.tanggal'
            )
            ->selectRaw(
                'COUNT(DISTINCT penerimaan_linens.id) as total_transaksi'
            )
            ->selectRaw(
                'COALESCE(SUM(item_linens.jumlah), 0) as total_linen'
            )
            ->selectRaw(
                "COALESCE(SUM(
                    CASE
                        WHEN item_linens.kondisi = 'Baik'
                        THEN item_linens.jumlah
                        ELSE 0
                    END
                ), 0) as total_baik"
            )
            ->selectRaw(
                "COALESCE(SUM(
                    CASE
                        WHEN item_linens.kondisi = 'Noda'
                        THEN item_linens.jumlah
                        ELSE 0
                    END
                ), 0) as total_noda"
            )
            ->selectRaw(
                "COALESCE(SUM(
                    CASE
                        WHEN item_linens.kondisi = 'Rusak'
                        THEN item_linens.jumlah
                        ELSE 0
                    END
                ), 0) as total_rusak"
            )
            ->groupBy(
                'penerimaan_linens.tanggal'
            )
            ->orderBy(
                'penerimaan_linens.tanggal'
            )
            ->get()
            ->map(function ($row) {
                return [
                    'tanggal' => $row->tanggal,
                    'total_transaksi' => (int) $row->total_transaksi,
                    'total_linen' => (int) $row->total_linen,
                    'total_baik' => (int) $row->total_baik,
                    'total_noda' => (int) $row->total_noda,
                    'total_rusak' => (int) $row->total_rusak,
                ];
            })
            ->values();


        /*
        |--------------------------------------------------------------------------
        | PRIORITAS RUANGAN
        |--------------------------------------------------------------------------
        */

        $ruanganBermasalah = $rekapRuangan
            ->filter(function ($row) {
                return $row['total_bermasalah'] > 0;
            })
            ->take(5)
            ->values();


        /*
        |--------------------------------------------------------------------------
        | PRIORITAS JENIS LINEN
        |--------------------------------------------------------------------------
        */

        $linenBermasalah = $rekapJenisLinen
            ->filter(function ($row) {
                return $row['total_bermasalah'] > 0;
            })
            ->take(5)
            ->values();


        /*
        |--------------------------------------------------------------------------
        | STATUS LAPORAN
        |--------------------------------------------------------------------------
        */

        if ($persentaseBermasalah >= 10) {
            $statusLaporan = 'Perlu Perhatian';
        } elseif ($persentaseBermasalah >= 5) {
            $statusLaporan = 'Waspada';
        } else {
            $statusLaporan = 'Baik';
        }


        return [
            'summary' => [
                'total_transaksi' => $totalTransaksi,
                'total_linen' => $totalLinen,
                'total_baik' => $totalBaik,
                'total_noda' => $totalNoda,
                'total_rusak' => $totalRusak,
                'total_bermasalah' => $totalBermasalah,

                'persentase_baik' => $persentaseBaik,
                'persentase_noda' => $persentaseNoda,
                'persentase_rusak' => $persentaseRusak,
                'persentase_bermasalah' => $persentaseBermasalah,

                'status_laporan' => $statusLaporan,
            ],

            'rekapRuangan' => $rekapRuangan,

            'rekapJenisLinen' => $rekapJenisLinen,

            'trenHarian' => $trenHarian,

            'ruanganBermasalah' => $ruanganBermasalah,

            'linenBermasalah' => $linenBermasalah,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | HALAMAN REKAP
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $filters = $this->getFilters($request);

        $data = $this->getReportData($filters);

        return Inertia::render(
            'Checklist/Rekap',
            [
                'filters' => $filters,

                'ruanganOptions' => PenerimaanLinen::RUANGAN,

                ...$data,
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | EXPORT EXCEL
    |--------------------------------------------------------------------------
    */

    public function exportExcel(Request $request)
    {
        $filters = $this->getFilters($request);

        $data = $this->getReportData($filters);

        $spreadsheet = new Spreadsheet();

        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setTitle('Rekap Linen');


        /*
        |--------------------------------------------------------------------------
        | JUDUL
        |--------------------------------------------------------------------------
        */

        $sheet->setCellValue(
            'A1',
            'LAPORAN REKAP PENERIMAAN LINEN'
        );

        $sheet->mergeCells('A1:H1');

        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);

        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');


        $sheet->setCellValue(
            'A2',
            'Periode'
        );

        $sheet->setCellValue(
            'B2',
            $filters['tanggal_mulai']
            . ' s/d '
            . $filters['tanggal_akhir']
        );

        $sheet->setCellValue(
            'A3',
            'Ruangan'
        );

        $sheet->setCellValue(
            'B3',
            $filters['ruangan'] ?: 'Semua Ruangan'
        );


        /*
        |--------------------------------------------------------------------------
        | KPI
        |--------------------------------------------------------------------------
        */

        $row = 5;

        $sheet->setCellValue("A{$row}", 'KPI');
        $sheet->setCellValue("B{$row}", 'Nilai');

        $sheet->getStyle("A{$row}:B{$row}")
            ->getFont()
            ->setBold(true);

        $kpi = [
            'Total Transaksi' => $data['summary']['total_transaksi'],
            'Total Linen' => $data['summary']['total_linen'],
            'Total Baik' => $data['summary']['total_baik'],
            'Total Noda' => $data['summary']['total_noda'],
            'Total Rusak' => $data['summary']['total_rusak'],
            'Total Bermasalah' => $data['summary']['total_bermasalah'],
            'Persentase Bermasalah' =>
                $data['summary']['persentase_bermasalah'] . '%',
            'Status Laporan' =>
                $data['summary']['status_laporan'],
        ];

        $row++;

        foreach ($kpi as $label => $value) {
            $sheet->setCellValue("A{$row}", $label);
            $sheet->setCellValue("B{$row}", $value);
            $row++;
        }


        /*
        |--------------------------------------------------------------------------
        | REKAP RUANGAN
        |--------------------------------------------------------------------------
        */

        $row += 2;

        $sheet->setCellValue(
            "A{$row}",
            'REKAP PER RUANGAN'
        );

        $sheet->getStyle("A{$row}")
            ->getFont()
            ->setBold(true);

        $row++;

        $headers = [
            'Ruangan',
            'Transaksi',
            'Total Linen',
            'Baik',
            'Noda',
            'Rusak',
            'Bermasalah',
            '% Bermasalah',
        ];

        foreach ($headers as $index => $header) {
            $column = chr(65 + $index);

            $sheet->setCellValue(
                "{$column}{$row}",
                $header
            );
        }

        $sheet->getStyle("A{$row}:H{$row}")
            ->getFont()
            ->setBold(true);

        $row++;

        foreach ($data['rekapRuangan'] as $item) {
            $values = [
                $item['ruangan'],
                $item['total_transaksi'],
                $item['total_linen'],
                $item['total_baik'],
                $item['total_noda'],
                $item['total_rusak'],
                $item['total_bermasalah'],
                $item['persentase_bermasalah'] . '%',
            ];

            foreach ($values as $index => $value) {
                $column = chr(65 + $index);

                $sheet->setCellValue(
                    "{$column}{$row}",
                    $value
                );
            }

            $row++;
        }


        /*
        |--------------------------------------------------------------------------
        | REKAP JENIS LINEN
        |--------------------------------------------------------------------------
        */

        $row += 2;

        $sheet->setCellValue(
            "A{$row}",
            'REKAP PER JENIS LINEN'
        );

        $sheet->getStyle("A{$row}")
            ->getFont()
            ->setBold(true);

        $row++;

        $headers = [
            'Jenis Linen',
            'Total Linen',
            'Baik',
            'Noda',
            'Rusak',
            'Bermasalah',
            '% Bermasalah',
        ];

        foreach ($headers as $index => $header) {
            $column = chr(65 + $index);

            $sheet->setCellValue(
                "{$column}{$row}",
                $header
            );
        }

        $sheet->getStyle("A{$row}:G{$row}")
            ->getFont()
            ->setBold(true);

        $row++;

        foreach ($data['rekapJenisLinen'] as $item) {
            $values = [
                $item['nama_item'],
                $item['total_linen'],
                $item['total_baik'],
                $item['total_noda'],
                $item['total_rusak'],
                $item['total_bermasalah'],
                $item['persentase_bermasalah'] . '%',
            ];

            foreach ($values as $index => $value) {
                $column = chr(65 + $index);

                $sheet->setCellValue(
                    "{$column}{$row}",
                    $value
                );
            }

            $row++;
        }


        /*
        |--------------------------------------------------------------------------
        | TREND HARIAN
        |--------------------------------------------------------------------------
        */

        $row += 2;

        $sheet->setCellValue(
            "A{$row}",
            'TREN HARIAN'
        );

        $sheet->getStyle("A{$row}")
            ->getFont()
            ->setBold(true);

        $row++;

        $headers = [
            'Tanggal',
            'Transaksi',
            'Total Linen',
            'Baik',
            'Noda',
            'Rusak',
        ];

        foreach ($headers as $index => $header) {
            $column = chr(65 + $index);

            $sheet->setCellValue(
                "{$column}{$row}",
                $header
            );
        }

        $sheet->getStyle("A{$row}:F{$row}")
            ->getFont()
            ->setBold(true);

        $row++;

        foreach ($data['trenHarian'] as $item) {
            $values = [
                $item['tanggal'],
                $item['total_transaksi'],
                $item['total_linen'],
                $item['total_baik'],
                $item['total_noda'],
                $item['total_rusak'],
            ];

            foreach ($values as $index => $value) {
                $column = chr(65 + $index);

                $sheet->setCellValue(
                    "{$column}{$row}",
                    $value
                );
            }

            $row++;
        }


        /*
        |--------------------------------------------------------------------------
        | AUTO WIDTH
        |--------------------------------------------------------------------------
        */

        foreach (range('A', 'H') as $column) {
            $sheet
                ->getColumnDimension($column)
                ->setAutoSize(true);
        }


        /*
        |--------------------------------------------------------------------------
        | DOWNLOAD
        |--------------------------------------------------------------------------
        */

        $filename =
            'rekap-penerimaan-linen-'
            . $filters['tanggal_mulai']
            . '-'
            . $filters['tanggal_akhir']
            . '.xlsx';

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(
            function () use ($writer) {
                $writer->save('php://output');
            },
            $filename,
            [
                'Content-Type' =>
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | EXPORT PDF
    |--------------------------------------------------------------------------
    */

    public function exportPdf(Request $request)
    {
        $filters = $this->getFilters($request);

        $data = $this->getReportData($filters);

        $pdf = Pdf::loadView(
            'reports.penerimaan-linen',
            [
                'filters' => $filters,
                ...$data,
            ]
        );

        $pdf->setPaper('a4', 'landscape');

        $filename =
            'rekap-penerimaan-linen-'
            . $filters['tanggal_mulai']
            . '-'
            . $filters['tanggal_akhir']
            . '.pdf';

        return $pdf->download($filename);
    }
}