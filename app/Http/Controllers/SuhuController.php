<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSuhuRequest;
use App\Http\Requests\UpdateSuhuRequest;
use App\Models\SuhuRuangan;
use Inertia\Inertia;

// Controller untuk modul Monitoring Suhu & Kelembaban Ruangan.
// Modul ini murni CRUD (tidak ada logika stok/transaksi seperti chemical/stok_linen/aset),
// jadi controller-nya lebih sederhana dibanding modul-modul sebelumnya.
class SuhuController extends Controller
{
    // GET /suhu -> daftar semua catatan suhu (paginate)
    public function index()
    {
        // with('petugas') -> eager load relasi petugas dalam 1 query tambahan (hindari N+1 saat looping di Vue)
        // orderByDesc('tanggal') lalu orderByDesc('jam') -> catatan terbaru (tanggal & jam) tampil paling atas
        // paginate(15) -> setara paginate_by = 15 di Django (beda dari modul lain yang kebanyakan 10)
        $semuaSuhu = SuhuRuangan::with('petugas')
            ->orderByDesc('tanggal')
            ->orderByDesc('jam')
            ->paginate(15);

        return Inertia::render('Suhu/Index', [
            'semuaSuhu' => $semuaSuhu,
        ]);
    }

    // GET /suhu/tambah -> form tambah catatan baru
    public function create()
    {
        return Inertia::render('Suhu/Create', [
            'ruanganOptions' => SuhuRuangan::RUANGAN,     // daftar pilihan ruangan/area untuk dropdown
            'waktuUkurOptions' => SuhuRuangan::WAKTU_UKUR,  // array asosiatif ['Pagi' => 'Pagi (Shift 1)', ...] untuk dropdown
        ]);
    }

    // POST /suhu -> simpan catatan baru
    public function store(StoreSuhuRequest $request)
    {
        // create() dengan tambahan petugas_id dari user yang sedang login,
        // setara form.instance.petugas = self.request.user di Django CreateView
        SuhuRuangan::create([
            ...$request->validated(),           // sebar semua field yang lolos validasi
            'petugas_id' => $request->user()->id, // tambahkan field petugas_id secara manual
        ]);

        return redirect()
            ->route('suhu.index')
            ->with('status', 'Catatan suhu & kelembaban berhasil disimpan!');
    }

    // GET /suhu/{id}/ubah -> form edit
    // Route-model-binding: Laravel otomatis cari baris SuhuRuangan berdasarkan {id} di URL
    public function edit(SuhuRuangan $suhu)
    {
        return Inertia::render('Suhu/Edit', [
            'suhu' => $suhu, // data existing untuk isi awal form
            'ruanganOptions' => SuhuRuangan::RUANGAN,
            'waktuUkurOptions' => SuhuRuangan::WAKTU_UKUR,
        ]);
    }

    // PUT /suhu/{id} -> simpan perubahan
    public function update(UpdateSuhuRequest $request, SuhuRuangan $suhu)
    {
        // Petugas TIDAK diubah saat update (sama seperti Django: field petugas tidak ada di form UpdateView,
        // jadi tetap tercatat siapa yang PERTAMA KALI menginput data ini)
        $suhu->update($request->validated());

        return redirect()
            ->route('suhu.index')
            ->with('status', 'Catatan suhu & kelembaban berhasil diperbarui!');
    }

    // DELETE /suhu/{id} -> hapus catatan
    public function destroy(SuhuRuangan $suhu)
    {
        $suhu->delete();

        return redirect()
            ->route('suhu.index')
            ->with('status', 'Catatan pemantauan berhasil dihapus.');
    }
}
