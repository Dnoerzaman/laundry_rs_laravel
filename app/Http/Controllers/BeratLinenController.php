<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBeratLinenHarianRequest;
use App\Http\Requests\UpdateBeratLinenHarianRequest;
use App\Models\BeratLinenHarian;
use App\Models\PenerimaanLinen; // untuk daftar pilihan ruangan (dropdown)
use Inertia\Inertia;

class BeratLinenController extends Controller
{
    // GET /checklist/berat -> daftar semua catatan berat linen (paginate)
    public function index()
    {
        // with('petugas') supaya nama petugas langsung ikut dalam 1 query (hindari N+1 query)
        // orderByDesc('tanggal') -> data terbaru tampil paling atas
        // paginate(10) -> setara paginate_by = 10 di Django ListView
        $semuaDataBerat = BeratLinenHarian::with('petugas')
            ->orderByDesc('tanggal')
            ->paginate(10);

        // withQueryString() memastikan link pagination tetap bawa query string lain kalau ada (misal filter),
        // penting supaya navigasi halaman tidak reset filter yang mungkin ditambahkan nanti
        $semuaDataBerat->withQueryString();

        return Inertia::render('BeratLinen/Index', [
            'semuaDataBerat' => $semuaDataBerat,
        ]);
    }

    // GET /checklist/berat/tambah -> form tambah data baru
    public function create()
    {
        return Inertia::render('BeratLinen/Create', [
            'ruanganOptions' => PenerimaanLinen::RUANGAN, // dropdown ruangan
            'shiftOptions' => BeratLinenHarian::SHIFT,      // dropdown shift
        ]);
    }

    // POST /checklist/berat -> simpan data baru
    public function store(StoreBeratLinenHarianRequest $request)
    {
        // create() dengan tambahan petugas_id dari user yang sedang login
        // (setara form.instance.petugas = self.request.user di Django CreateView)
        BeratLinenHarian::create([
            ...$request->validated(),         // sebar semua field yang lolos validasi (tanggal, ruangan, shift, total_berat)
            'petugas_id' => $request->user()->id, // tambahkan field petugas_id secara manual
        ]);

        return redirect()
            ->route('berat-linen.index')
            ->with('status', 'Data berat linen berhasil ditambahkan.');
    }

    // GET /checklist/berat/{id}/ubah -> form edit
    // Route-model-binding: Laravel otomatis cari baris BeratLinenHarian berdasarkan {id} di URL
    public function edit(BeratLinenHarian $beratLinen)
    {
        return Inertia::render('BeratLinen/Edit', [
            'beratLinen' => $beratLinen,                    // data yang sedang diedit, untuk isi awal form
            'ruanganOptions' => PenerimaanLinen::RUANGAN,
            'shiftOptions' => BeratLinenHarian::SHIFT,
        ]);
    }

    // PUT /checklist/berat/{id} -> simpan perubahan
    public function update(UpdateBeratLinenHarianRequest $request, BeratLinenHarian $beratLinen)
    {
        // Petugas TIDAK diubah saat update (sama seperti Django: field petugas tidak ada di form UpdateView)
        $beratLinen->update($request->validated());

        return redirect()
            ->route('berat-linen.index')
            ->with('status', 'Data berat linen berhasil diperbarui.');
    }

    // DELETE /checklist/berat/{id} -> hapus data
    public function destroy(BeratLinenHarian $beratLinen)
    {
        $beratLinen->delete();

        return redirect()
            ->route('berat-linen.index')
            ->with('status', 'Data berat linen berhasil dihapus.');
    }
}
