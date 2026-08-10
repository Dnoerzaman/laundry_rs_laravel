<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePenerimaanChemicalRequest;
use App\Models\PenerimaanChemical;
use App\Models\StokChemical;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PenerimaanChemicalController extends Controller
{
    // GET /penerimaan-chemical -> daftar riwayat stok masuk
    public function index()
    {
        $semuaPenerimaan = PenerimaanChemical::with(['chemical', 'petugas'])
            ->orderByDesc('tanggal')
            ->paginate(10);

        return Inertia::render('PenerimaanChemical/Index', [
            'semuaPenerimaan' => $semuaPenerimaan,
        ]);
    }

    // GET /penerimaan-chemical/tambah -> form tambah stok masuk
    public function create()
    {
        return Inertia::render('PenerimaanChemical/Create', [
            'chemicalOptions' => StokChemical::orderBy('nama_chemical')
                ->get(['id', 'nama_chemical', 'unit', 'jumlah_stok']),
        ]);
    }

    // POST /penerimaan-chemical -> simpan penerimaan & tambah stok
    public function store(StorePenerimaanChemicalRequest $request)
    {
        // Sama seperti PemakaianChemicalController, dibungkus transaksi supaya
        // "tambah stok" dan "simpan riwayat penerimaan" konsisten (semua berhasil atau semua batal)
        DB::transaction(function () use ($request) {
            // lockForUpdate: kunci baris ini selama transaksi, mencegah race condition
            // kalau ada input stok masuk untuk chemical yang sama secara bersamaan
            $chemical = StokChemical::lockForUpdate()->findOrFail($request->chemical_id);

            // Logika utama: tambahkan stok (kebalikan dari pemakaian, tidak perlu pengecekan minimum)
            $chemical->jumlah_stok += $request->jumlah;
            $chemical->save(); // ->save() supaya event model tetap update kolom update_terakhir

            // Simpan baris riwayat penerimaan
            PenerimaanChemical::create([
                ...$request->validated(),
                'petugas_id' => $request->user()->id,
            ]);
        });

        return redirect()
            ->route('penerimaan-chemical.index')
            ->with('status', 'Stok chemical berhasil ditambahkan.');
    }
}
