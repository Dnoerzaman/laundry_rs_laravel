<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePenerimaanLinenRequest;
use App\Models\ItemLinen;         // untuk ambil daftar konstanta NAMA_ITEM & KONDISI
use App\Models\PenerimaanLinen;   // model utama (header transaksi)
use Illuminate\Support\Facades\DB; // untuk DB::transaction() -> setara `with transaction.atomic()` Django
use Inertia\Inertia;

// Controller khusus untuk form "Checklist Penerimaan Linen Kotor"
class ChecklistController extends Controller
{
    // Menampilkan halaman form (GET /checklist/tambah)
    public function create()
    {
        // Kirim semua daftar pilihan (dropdown) ke komponen Vue,
        // supaya Vue tidak perlu hardcode ulang daftar ruangan/item/kondisi
        return Inertia::render('Checklist/Create', [
            'ruanganOptions' => PenerimaanLinen::RUANGAN, // daftar ruangan untuk dropdown header
            'itemOptions' => ItemLinen::NAMA_ITEM,          // daftar nama item untuk dropdown tiap baris
            'kondisiOptions' => ItemLinen::KONDISI,          // daftar kondisi: Baik/Noda/Rusak
        ]);
    }

    // Menyimpan data form (POST /checklist)
    public function store(StorePenerimaanLinenRequest $request)
    {
        // DB::transaction: semua query di dalam closure ini dianggap SATU kesatuan.
        // Kalau salah satu gagal (misal simpan item error), SEMUA di-rollback (batal semua),
        // jadi tidak mungkin ada header PenerimaanLinen yang tersimpan tanpa item-nya.
        // Ini setara `with transaction.atomic():` di Django.
        DB::transaction(function () use ($request) {
            // Buat baris header PenerimaanLinen dulu
            $penerimaan = PenerimaanLinen::create([
                'tanggal' => $request->validated('tanggal'), // ambil field 'tanggal' yang sudah divalidasi
                'jam' => $request->validated('jam'),           // field 'jam'
                'ruangan' => $request->validated('ruangan'),    // field 'ruangan'
                'petugas_id' => $request->user()->id,            // otomatis isi dari user yang sedang login
            ]);

            // Looping setiap item yang dikirim dari form (array 'items')
            foreach ($request->validated('items') as $item) {
                // items() adalah relasi hasMany yang sudah didefinisikan di model PenerimaanLinen
                // ->create($item) otomatis mengisi kolom penerimaan_id dengan id header di atas
                $penerimaan->items()->create($item);
            }
        });

        // Setelah berhasil simpan, redirect ke dashboard (sama seperti Django: return redirect('dashboard'))
        // ->with('status', ...) mengirim pesan flash yang bisa ditampilkan di halaman tujuan
        return redirect()
            ->route('dashboard')
            ->with('status', 'Data penerimaan berhasil disimpan!');
    }
}
