<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAsetRequest;
use App\Http\Requests\UpdateAsetRequest;
use App\Models\Aset;
use Illuminate\Database\QueryException; // dipakai untuk menangkap error "foreign key violation" saat hapus data
use Inertia\Inertia;

class AsetController extends Controller
{
    // GET /aset -> daftar semua aset (paginate)
    public function index()
    {
        // orderByDesc('tanggal_input') -> aset yang paling baru diinput tampil di atas
        $semuaAset = Aset::orderByDesc('tanggal_input')->paginate(10);

        return Inertia::render('Aset/Index', [
            'semuaAset' => $semuaAset,
        ]);
    }

    // GET /aset/tambah -> form tambah aset baru
    public function create()
    {
        return Inertia::render('Aset/Create', [
            'satuanOptions' => Aset::SATUAN, // daftar pilihan satuan untuk dropdown
        ]);
    }

    // POST /aset -> simpan aset baru
    public function store(StoreAsetRequest $request)
    {
        Aset::create($request->validated());

        return redirect()
            ->route('aset.index')
            ->with('status', 'Aset baru berhasil ditambahkan.');
    }

    // GET /aset/{id}/ubah -> form edit
    public function edit(Aset $aset)
    {
        return Inertia::render('Aset/Edit', [
            'aset' => $aset,
            'satuanOptions' => Aset::SATUAN,
        ]);
    }

    // PUT /aset/{id} -> simpan perubahan
    public function update(UpdateAsetRequest $request, Aset $aset)
    {
        $aset->update($request->validated());

        return redirect()
            ->route('aset.index')
            ->with('status', 'Data aset berhasil diperbarui.');
    }

    // DELETE /aset/{id} -> hapus aset, DENGAN penanganan khusus kalau masih punya riwayat transaksi
    public function destroy(Aset $aset)
    {
        // try/catch di sini setara try/except ProtectedError di Django AsetDeleteView.
        // Migration Tahap 1 sudah set TransaksiAset.aset_id -> ->restrictOnDelete(),
        // jadi kalau aset ini masih direferensikan oleh baris TransaksiAset manapun,
        // database (PostgreSQL) akan MENOLAK penghapusan dan melempar error foreign key.
        try {
            $aset->delete();
        } catch (QueryException $e) {
            // Kode SQLSTATE '23503' = foreign key violation di PostgreSQL,
            // ini adalah kode error spesifik untuk kasus "masih direferensikan tabel lain"
            if ($e->getCode() === '23503') {
                // Redirect balik dengan pesan error yang jelas, setara messages.error() Django
                return redirect()
                    ->route('aset.index')
                    ->with('error', 'Aset ini tidak bisa dihapus karena sudah memiliki riwayat transaksi. Hapus riwayat transaksinya terlebih dahulu.');
            }

            // Kalau errornya bukan soal foreign key (jenis error lain yang tidak terduga),
            // lempar ulang exception-nya supaya tetap terlihat sebagai bug yang perlu diperbaiki,
            // bukan disembunyikan begitu saja
            throw $e;
        }

        return redirect()
            ->route('aset.index')
            ->with('status', 'Aset berhasil dihapus.');
    }
}
