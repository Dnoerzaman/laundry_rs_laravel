<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransaksiLinenRequest;
use App\Models\StokLinen;
use App\Models\TransaksiLinen;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class TransaksiLinenController extends Controller
{
    // GET /stok-linen/transaksi -> form catat transaksi + riwayat 10 transaksi terakhir di sampingnya
    public function create()
    {
        return Inertia::render('TransaksiLinen/Create', [
            // Daftar semua kombinasi ruangan+linen untuk dropdown, urut ruangan lalu nama linen
            'stokLinenOptions' => StokLinen::orderBy('ruangan')
                ->orderBy('nama_linen')
                ->get(['id', 'ruangan', 'nama_linen', 'stok_akhir']),

            // Riwayat 10 transaksi TERBARU (bukan 10 pertama), diurutkan dari yang paling baru
            // with(['stokLinen', 'petugas']) -> eager load 2 relasi, hindari N+1 query saat looping di Vue
            'riwayatTransaksi' => TransaksiLinen::with(['stokLinen', 'petugas'])
                ->latest('id')  // urutkan dari id terbesar (paling baru dibuat) ke terkecil
                ->take(10)       // ambil 10 baris teratas saja
                ->get(),
        ]);
    }

    // POST /stok-linen/transaksi -> simpan transaksi & update stok_akhir
    public function store(StoreTransaksiLinenRequest $request)
    {
        DB::transaction(function () use ($request) {
            // lockForUpdate: kunci baris StokLinen ini selama transaksi berjalan,
            // mencegah race condition kalau ada 2 input transaksi bersamaan untuk kombinasi yang sama
            $stokLinen = StokLinen::lockForUpdate()->findOrFail($request->stok_linen_id);

            // Percabangan berdasarkan jenis transaksi, setara if/elif di Django
            if ($request->jenis_transaksi === 'MASUK') {
                // Linen masuk -> tambahkan ke stok_akhir, tidak perlu pengecekan minimum
                $stokLinen->stok_akhir += $request->jumlah;
            } else {
                // Linen keluar -> perlu dicek dulu apakah stok mencukupi
                if ($stokLinen->stok_akhir < $request->jumlah) {
                    // Lempar error validasi manual, otomatis redirect balik ke form dengan pesan ini
                    // di field 'jumlah' (setara form.add_error('jumlah', ...) di Django)
                    throw ValidationException::withMessages([
                        'jumlah' => "Stok {$stokLinen->nama_linen} di {$stokLinen->ruangan} tidak mencukupi. Sisa stok: {$stokLinen->stok_akhir}.",
                    ]);
                }
                $stokLinen->stok_akhir -= $request->jumlah;
            }

            // ->save() (bukan increment()/decrement()) supaya event model tetap terpicu
            // (StokLinen model meng-update kolom update_terakhir lewat event 'saving')
            $stokLinen->save();

            // Simpan baris riwayat transaksi, petugas otomatis dari user yang sedang login
            TransaksiLinen::create([
                ...$request->validated(),
                'petugas_id' => $request->user()->id,
            ]);
        });

        // redirect ke route yang SAMA (halaman form transaksi), setara success_url mengarah ke
        // dirinya sendiri di Django -> supaya user bisa langsung input transaksi berikutnya,
        // dan riwayat transaksi di sampingnya otomatis ter-refresh menampilkan data terbaru
        return redirect()
            ->route('transaksi-linen.create')
            ->with('status', 'Transaksi linen berhasil dicatat dan stok telah diperbarui.');
    }
}
