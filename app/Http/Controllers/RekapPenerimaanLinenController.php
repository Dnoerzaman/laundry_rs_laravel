<?php

namespace App\Http\Controllers;

use App\Models\PenerimaanLinen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class RekapPenerimaanLinenController extends Controller
{
    /**
     * Menampilkan rekap penerimaan linen.
     */
    public function index(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | FILTER
        |--------------------------------------------------------------------------
        */

        // Default: tanggal 1 bulan berjalan
        $tanggalMulai = $request->input(
            'tanggal_mulai',
            now()->startOfMonth()->format('Y-m-d')
        );

        // Default: hari ini
        $tanggalAkhir = $request->input(
            'tanggal_akhir',
            now()->format('Y-m-d')
        );

        $ruangan = $request->input('ruangan');


        /*
        |--------------------------------------------------------------------------
        | BASE QUERY
        |--------------------------------------------------------------------------
        |
        | Semua query laporan menggunakan tanggal operasional
        | penerimaan_linens.tanggal.
        |
        */

        $baseQuery = DB::table('penerimaan_linens')
            ->join(
                'item_linens',
                'item_linens.penerimaan_id',
                '=',
                'penerimaan_linens.id'
            )
            ->whereBetween(
                'penerimaan_linens.tanggal',
                [$tanggalMulai, $tanggalAkhir]
            );

        /*
        |--------------------------------------------------------------------------
        | FILTER RUANGAN
        |--------------------------------------------------------------------------
        */

        if (!empty($ruangan)) {
            $baseQuery->where(
                'penerimaan_linens.ruangan',
                $ruangan
            );
        }


        /*
        |--------------------------------------------------------------------------
        | RINGKASAN UTAMA
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


        /*
        |--------------------------------------------------------------------------
        | KONVERSI ANGKA
        |--------------------------------------------------------------------------
        |
        | Hasil agregasi database kadang dikembalikan sebagai string.
        | Kita ubah menjadi integer agar frontend menerima angka.
        |
        */

        $totalTransaksi = (int) ($summary->total_transaksi ?? 0);
        $totalLinen = (int) ($summary->total_linen ?? 0);
        $totalBaik = (int) ($summary->total_baik ?? 0);
        $totalNoda = (int) ($summary->total_noda ?? 0);
        $totalRusak = (int) ($summary->total_rusak ?? 0);

        $totalBermasalah = $totalNoda + $totalRusak;

        $persentaseBermasalah = $totalLinen > 0
            ? round(($totalBermasalah / $totalLinen) * 100, 2)
            : 0;

        $persentaseBaik = $totalLinen > 0
            ? round(($totalBaik / $totalLinen) * 100, 2)
            : 0;

        $persentaseNoda = $totalLinen > 0
            ? round(($totalNoda / $totalLinen) * 100, 2)
            : 0;

        $persentaseRusak = $totalLinen > 0
            ? round(($totalRusak / $totalLinen) * 100, 2)
            : 0;


        /*
        |--------------------------------------------------------------------------
        | REKAP PER RUANGAN
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
            ->orderByDesc('total_linen')
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
            ->values();


        /*
        |--------------------------------------------------------------------------
        | REKAP PER JENIS LINEN
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
            ->orderByDesc('total_linen')
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
            ->values();


        /*
        |--------------------------------------------------------------------------
        | TREND HARIAN
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
        | OPTIONS RUANGAN
        |--------------------------------------------------------------------------
        */

        $ruanganOptions = PenerimaanLinen::RUANGAN;


        /*
        |--------------------------------------------------------------------------
        | RESPONSE INERTIA
        |--------------------------------------------------------------------------
        */

        return Inertia::render('Checklist/Rekap', [

            'filters' => [
                'tanggal_mulai' => $tanggalMulai,
                'tanggal_akhir' => $tanggalAkhir,
                'ruangan' => $ruangan,
            ],

            'ruanganOptions' => $ruanganOptions,

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
            ],

            'rekapRuangan' => $rekapRuangan,

            'rekapJenisLinen' => $rekapJenisLinen,

            'trenHarian' => $trenHarian,
        ]);
    }
}