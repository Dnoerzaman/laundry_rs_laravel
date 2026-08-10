<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePemakaianChemicalRequest;
use App\Models\PemakaianChemical;
use App\Models\StokChemical;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException; // untuk melempar error validasi manual (dari dalam Controller, bukan Request)
use Inertia\Inertia;

class PemakaianChemicalController extends Controller
{
    // GET /pemakaian-chemical -> daftar riwayat pemakaian
    public function index()
    {
        // with(['chemical', 'petugas']) -> load 2 relasi sekaligus dalam 1 query tambahan (hindari N+1)
        $semuaPemakaian = PemakaianChemical::with(['chemical', 'petugas'])
            ->orderByDesc('tanggal')
            ->paginate(10);

        return Inertia::render('PemakaianChemical/Index', [
            'semuaPemakaian' => $semuaPemakaian,
        ]);
    }

    // GET /pemakaian-chemical/catat -> form catat pemakaian baru
    public function create()
    {
        // Kirim daftar chemical yang ada, LENGKAP dengan stok saat ini,
        // supaya user bisa lihat langsung stok tersedia sebelum submit (mencegah error di awal, UX lebih baik dari versi Django)
        return Inertia::render('PemakaianChemical/Create', [
            'chemicalOptions' => StokChemical::orderBy('nama_chemical')
                ->get(['id', 'nama_chemical', 'unit', 'jumlah_stok']),
        ]);
    }

    // POST /pemakaian-chemical -> simpan pemakaian & kurangi stok
    public function store(StorePemakaianChemicalRequest $request)
    {
        // DB::transaction: pastikan "kurangi stok" dan "simpan riwayat pemakaian" terjadi bersamaan,
        // kalau salah satu gagal, semua dibatalkan (rollback) — setara `with transaction.atomic():` Django
        DB::transaction(function () use ($request) {
            // lockForUpdate(): "kunci" baris chemical ini selama transaksi berjalan,
            // supaya kalau ada 2 orang input pemakaian bersamaan untuk chemical yang sama,
            // stoknya tetap terhitung benar (tidak race condition). Ini penambahan dari versi Django.
            $chemical = StokChemical::lockForUpdate()->findOrFail($request->chemical_id);

            // Cek stok cukup atau tidak, setara pengecekan `if chemical.jumlah_stok < pemakaian.jumlah` di Django
            if ($chemical->jumlah_stok < $request->jumlah) {
                // ValidationException: melempar error validasi secara manual dari dalam Controller.
                // Laravel otomatis redirect kembali ke form dengan pesan error ini terisi di field 'jumlah',
                // setara form.add_error('jumlah', ...) + return self.form_invalid(form) di Django
                throw ValidationException::withMessages([
                    'jumlah' => "Stok {$chemical->nama_chemical} tidak mencukupi. Stok tersedia: {$chemical->jumlah_stok} {$chemical->unit}.",
                ]);
            }

            // Kurangi stok. Pakai $chemical->save() (bukan decrement()) supaya event model 'saving'
            // tetap terpicu (yang otomatis update kolom update_terakhir, lihat model StokChemical)
            $chemical->jumlah_stok -= $request->jumlah;
            $chemical->save();

            // Simpan baris riwayat pemakaian, dengan petugas otomatis dari user yang sedang login
            PemakaianChemical::create([
                ...$request->validated(),
                'petugas_id' => $request->user()->id,
            ]);
        });

        return redirect()
            ->route('pemakaian-chemical.index')
            ->with('status', 'Pemakaian chemical berhasil dicatat. Stok telah diperbarui.');
    }
}
