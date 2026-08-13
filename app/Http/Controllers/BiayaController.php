<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBiayaRequest;
use App\Http\Requests\UpdateBiayaRequest;
use App\Models\Biaya;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon; // dipakai untuk kalkulasi bulan sebelumnya/berikutnya dan nama bulan dalam Bahasa Indonesia
use Inertia\Inertia;

class BiayaController extends Controller
{
    // GET /biaya -> daftar pengeluaran untuk SATU periode bulan tertentu, dengan total otomatis
    public function index(Request $request)
    {
        // Ambil parameter bulan & tahun dari query string (?bulan=8&tahun=2026).
        // $request->integer('bulan', now()->month) -> kalau parameter tidak dikirim, pakai bulan SAAT INI sebagai default
        $bulan = $request->integer('bulan', now()->month);
        $tahun = $request->integer('tahun', now()->year);

        // Query dasar: filter baris yang tanggal-nya jatuh pada bulan & tahun yang dipilih.
        // whereYear() dan whereMonth() otomatis mengekstrak bagian tahun/bulan dari kolom 'tanggal'
        $queryPeriodeIni = Biaya::whereYear('tanggal', $tahun)->whereMonth('tanggal', $bulan);

        // clone() dipakai supaya query di atas bisa dipakai DUA KALI secara independen:
        // 1x untuk ambil daftar barisnya (dengan pagination), 1x lagi untuk hitung total SEMUA baris
        // (tanpa clone, query builder akan "rusak"/berubah setelah dipakai sekali)
        $semuaBiaya = (clone $queryPeriodeIni)
            ->with('pj')                 // eager load relasi PJ, hindari N+1 query
            ->orderBy('tanggal')            // urut tanggal naik (seperti baris di spreadsheet, kronologis)
            ->orderBy('id')                  // kalau tanggalnya sama, urutkan berdasarkan urutan input
            ->paginate(15)
            ->withQueryString();               // pastikan link pagination tetap membawa ?bulan=...&tahun=...

        // Total keseluruhan untuk periode ini -- DIHITUNG DARI SEMUA BARIS (bukan cuma yang tampil di halaman
        // saat ini), makanya query total ini terpisah dari query paginate() di atas
        $totalPeriodeIni = (clone $queryPeriodeIni)->sum('jumlah');

        return Inertia::render('Biaya/Index', [
            'semuaBiaya' => $semuaBiaya,
            'totalPeriodeIni' => $totalPeriodeIni,
            'bulan' => $bulan,   // dikirim balik supaya Vue tahu bulan mana yang sedang aktif
            'tahun' => $tahun,
            // Nama bulan dalam Bahasa Indonesia (misal "Agustus 2026"), dihitung sekali di sini
            // supaya Vue tidak perlu bikin logic konversi angka bulan -> nama bulan sendiri
            'labelPeriode' => Carbon::createFromDate($tahun, $bulan, 1)->translatedFormat('F Y'),
        ]);
    }

    // GET /biaya/tambah -> form tambah catatan baru
    public function create()
    {
        return Inertia::render('Biaya/Create', [
            'kategoriOptions' => Biaya::KATEGORI,
            'satuanOptions' => Biaya::SATUAN,
        ]);
    }

    // POST /biaya -> simpan catatan baru
    public function store(StoreBiayaRequest $request)
    {
        // 'jumlah' TIDAK disertakan di sini -- otomatis dihitung oleh event 'saving' di model Biaya
        Biaya::create([
            ...$request->validated(),
            'pj_id' => $request->user()->id, // otomatis dari user yang sedang login
        ]);

        return redirect()
            ->route('biaya.index')
            ->with('status', 'Catatan pengeluaran berhasil disimpan.');
    }

    // GET /biaya/{id}/ubah -> form edit
    public function edit(Biaya $biaya)
    {
        return Inertia::render('Biaya/Edit', [
            'biaya' => $biaya,
            'kategoriOptions' => Biaya::KATEGORI,
            'satuanOptions' => Biaya::SATUAN,
        ]);
    }

    // PUT /biaya/{id} -> simpan perubahan
    public function update(UpdateBiayaRequest $request, Biaya $biaya)
    {
        // PJ TIDAK diubah saat update (konsisten dengan pola log_pekerjaan: PJ tetap
        // tercatat sebagai orang yang PERTAMA KALI menginput data ini)
        //
        // 'jumlah' juga otomatis dihitung ULANG oleh event 'saving' kalau qty/harga berubah
        $biaya->update($request->validated());

        return redirect()
            ->route('biaya.index')
            ->with('status', 'Catatan pengeluaran berhasil diperbarui.');
    }

    // DELETE /biaya/{id} -> hapus catatan
    public function destroy(Biaya $biaya)
    {
        $biaya->delete();

        return redirect()
            ->route('biaya.index')
            ->with('status', 'Catatan pengeluaran berhasil dihapus.');
    }
}
