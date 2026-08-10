<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStokChemicalRequest;
use App\Http\Requests\UpdateStokChemicalRequest;
use App\Models\StokChemical;
use Inertia\Inertia;

// Controller untuk master data Stok Chemical (CRUD biasa, tanpa logika transaksi khusus)
class StokChemicalController extends Controller
{
    // GET /stok-chemical -> daftar semua chemical
    public function index()
    {
        $semuaStok = StokChemical::orderBy('nama_chemical')->paginate(10);

        return Inertia::render('StokChemical/Index', [
            'semuaStok' => $semuaStok,
        ]);
    }

    // GET /stok-chemical/create -> form tambah chemical baru
    public function create()
    {
        return Inertia::render('StokChemical/Create', [
            'namaChemicalOptions' => StokChemical::NAMA_CHEMICAL, // dropdown nama chemical
            'unitOptions' => StokChemical::UNIT,                    // dropdown satuan
        ]);
    }

    // POST /stok-chemical -> simpan chemical baru
    public function store(StoreStokChemicalRequest $request)
    {
        StokChemical::create($request->validated());

        return redirect()
            ->route('stok-chemical.index')
            ->with('status', 'Chemical baru berhasil ditambahkan.');
    }

    // GET /stok-chemical/{id}/edit -> form ubah
    public function edit(StokChemical $stokChemical)
    {
        return Inertia::render('StokChemical/Edit', [
            'stokChemical' => $stokChemical,
            'namaChemicalOptions' => StokChemical::NAMA_CHEMICAL,
            'unitOptions' => StokChemical::UNIT,
        ]);
    }

    // PUT /stok-chemical/{id} -> simpan perubahan
    public function update(UpdateStokChemicalRequest $request, StokChemical $stokChemical)
    {
        $stokChemical->update($request->validated());

        return redirect()
            ->route('stok-chemical.index')
            ->with('status', 'Data chemical berhasil diperbarui.');
    }

    // DELETE /stok-chemical/{id} -> hapus
    public function destroy(StokChemical $stokChemical)
    {
        $stokChemical->delete();

        return redirect()
            ->route('stok-chemical.index')
            ->with('status', 'Data chemical berhasil dihapus.');
    }
}
