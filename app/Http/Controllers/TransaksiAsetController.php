<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransaksiAsetRequest;
use App\Models\Aset;
use App\Models\TransaksiAset;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class TransaksiAsetController extends Controller
{
    // GET /aset/transaksi -> form catat transaksi + riwayat 10 transaksi terakhir di sampingnya
    public function create()
    {
        return Inertia::render('TransaksiAset/Create', [
            // Daftar semua aset untuk dropdown, urut nama biar gampang dicari
            'asetOptions' => Aset::orderBy('nama_barang')->get(['id', 'nama_barang', 'jumlah', 'satuan']),

            // Riwayat 10 transaksi TERBARU. with(['aset', 'petugas']) -> eager load 2 relasi sekaligus,
            // supaya tidak perlu query tambahan satu-satu saat looping data di Vue (hindari N+1 query)
            'riwayatTransaksi' => TransaksiAset::with(['aset', 'petugas'])
                ->latest('id') // urutkan dari id terbesar (baris paling baru dibuat) ke terkecil
                ->take(10)
                ->get(),
        ]);
    }

    // POST /aset/transaksi -> simpan transaksi & update jumlah aset
    public function store(StoreTransaksiAsetRequest $request)
    {
        // DB::transaction: bungkus "update jumlah aset" dan "simpan riwayat transaksi" jadi satu kesatuan,
        // supaya kalau salah satu gagal, semua dibatalkan (rollback) -> data tetap konsisten
        DB::transaction(function () use ($request) {
            // lockForUpdate: kunci baris aset ini selama transaksi berjalan, mencegah race condition
            // kalau ada 2 user mencatat transaksi untuk aset yang sama secara bersamaan
            $aset = Aset::lockForUpdate()->findOrFail($request->aset_id);

            // Percabangan berdasarkan jenis transaksi, setara if/elif di Django
            if ($request->jenis_transaksi === 'PENAMBAHAN') {
                // Penambahan aset -> tambahkan ke jumlah, tidak perlu pengecekan minimum
                $aset->jumlah += $request->jumlah;
            } else {
                // Pengurangan aset -> perlu dicek dulu apakah jumlahnya mencukupi
                if ($aset->jumlah < $request->jumlah) {
                    // Lempar error validasi manual -> otomatis redirect balik ke form
                    // dengan pesan ini muncul di field 'jumlah' (setara form.add_error('jumlah', ...) Django)
                    throw ValidationException::withMessages([
                        'jumlah' => "Jumlah aset \"{$aset->nama_barang}\" tidak mencukupi untuk dikurangi. Sisa saat ini: {$aset->jumlah}.",
                    ]);
                }
                $aset->jumlah -= $request->jumlah;
            }

            // ->save() (bukan increment()/decrement()) -> supaya event model tetap terpicu kalau nanti
            // ada logic tambahan yang bergantung pada event 'saving'/'updating' di model Aset
            $aset->save();

            // Simpan baris riwayat transaksi, petugas otomatis dari user yang sedang login
            TransaksiAset::create([
                ...$request->validated(),
                'petugas_id' => $request->user()->id,
            ]);
        });

        // Redirect ke route yang SAMA (halaman form transaksi), setara success_url yang
        // mengarah ke dirinya sendiri di Django -> supaya user bisa langsung input transaksi
        // berikutnya, dan riwayat di kolom kanan otomatis ter-refresh dengan data terbaru
        return redirect()
            ->route('transaksi-aset.create')
            ->with('status', 'Transaksi aset berhasil dicatat dan jumlah aset telah diperbarui.');
    }
}
