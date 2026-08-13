<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTugasRequest;
use App\Http\Requests\UpdateTugasRequest;
// Model Tugas sudah dibuat di Tahap 1 (tabel 'tugas'), dengan const STATUS & TARGET_WAKTU
use App\Models\Tugas;
// Model User dipakai untuk ambil daftar pilihan "Penanggung Jawab" di dropdown form
use App\Models\User;
use Inertia\Inertia;

// Controller untuk modul Rencana Kerja / Jadwal Tugas.
// Sama seperti LogPekerjaanController, modul ini murni CRUD tanpa logika stok/transaksi
class TugasController extends Controller
{
    // GET /schedule -> daftar semua rencana kerja (paginate)
    public function index()
    {
        // with('penanggungJawab') -> eager load relasi ke User (siapa yang bertanggung jawab),
        // dalam 1 query tambahan, hindari N+1 query saat looping di Vue
        //
        // orderBy('target_waktu') -> setara Meta.ordering = ['target_waktu'] di Django:
        // urutkan berdasarkan target minggu (Minggu ke-1 duluan, baru Minggu ke-2, dst)
        $semuaTugas = Tugas::with('penanggungJawab')
            ->orderBy('target_waktu')
            ->paginate(10);

        return Inertia::render('Schedule/Index', [
            'semuaTugas' => $semuaTugas,
        ]);
    }

    // GET /schedule/create -> form tambah rencana kerja baru
    public function create()
    {
        // Kirim daftar semua user untuk dropdown "Penanggung Jawab".
        // orderBy('name') -> urut alfabet, supaya gampang dicari di dropdown yang panjang
        return Inertia::render('Schedule/Create', [
            'users' => User::orderBy('name')->get(['id', 'name']),
            'statusOptions' => Tugas::STATUS,
            'targetWaktuOptions' => Tugas::TARGET_WAKTU,
        ]);
    }

    // POST /schedule -> simpan rencana kerja baru
    public function store(StoreTugasRequest $request)
    {
        // Tugas::create() -> insert baris baru. Semua field (termasuk penanggung_jawab_id)
        // datang langsung dari $request->validated(), TIDAK ada field yang otomatis diisi
        // dari user login di sini (beda dari LogPekerjaan yang 'pj' otomatis dari user login) --
        // karena "penanggung jawab" tugas belum tentu sama dengan orang yang membuat rencananya
        Tugas::create($request->validated());

        return redirect()
            ->route('schedule.index')
            ->with('status', 'Rencana kerja berhasil disimpan.');
    }

    // GET /schedule/{id}/edit -> form edit
    // Route Model Binding: Laravel otomatis ambil baris Tugas berdasarkan {id} di URL
    public function edit(Tugas $tugas)
    {
        return Inertia::render('Schedule/Edit', [
            'tugas' => $tugas,
            'users' => User::orderBy('name')->get(['id', 'name']),
            'statusOptions' => Tugas::STATUS,
            'targetWaktuOptions' => Tugas::TARGET_WAKTU,
        ]);
    }

    // PUT /schedule/{id} -> simpan perubahan
    public function update(UpdateTugasRequest $request, Tugas $tugas)
    {
        // Semua field BOLEH diubah di sini (beda dari LogPekerjaan yang mengunci field 'pj'),
        // karena status pekerjaan dan penanggung jawabnya memang wajar berubah seiring waktu
        // (misal tugas pindah tangan, atau status berubah dari "Sedang Dikerjakan" ke "Selesai")
        $tugas->update($request->validated());

        return redirect()
            ->route('schedule.index')
            ->with('status', 'Rencana kerja berhasil diperbarui.');
    }

    // DELETE /schedule/{id} -> hapus rencana kerja
    public function destroy(Tugas $tugas)
    {
        $tugas->delete();

        return redirect()
            ->route('schedule.index')
            ->with('status', 'Rencana kerja berhasil dihapus.');
    }
}
