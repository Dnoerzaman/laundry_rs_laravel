<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStokLinenRequest;
use App\Http\Requests\UpdateStokLinenRequest;
use App\Models\ItemLinen;
use App\Models\PenerimaanLinen;
use App\Models\StokLinen;
use Illuminate\Http\Request; // dipakai untuk membaca query string ?ruangan=... dari URL
use Inertia\Inertia;

class StokLinenController extends Controller
{
    // GET /stok-linen -> daftar stok linen, dengan opsi filter berdasarkan ruangan
    public function index(Request $request)
    {
        // Mulai query dasar (belum dieksekusi ke database, baru "rencana query")
        $query = StokLinen::query();

        // $request->filled('ruangan') -> true kalau parameter ?ruangan=... ada DAN tidak kosong
        // (beda dengan has(), yang tetap true walau isinya string kosong '')
        if ($request->filled('ruangan')) {
            // Tambahkan kondisi WHERE ruangan = '...' HANYA kalau user memang memilih filter
            $query->where('ruangan', $request->ruangan);
        }

        // Urutkan berdasarkan ruangan dulu, baru nama linen di dalam ruangan yang sama —
        // supaya data yang satu ruangan selalu tampil berkelompok, lebih mudah dibaca
        $semuaStokLinen = $query->orderBy('ruangan')
            ->orderBy('nama_linen')
            ->paginate(10)
            // withQueryString(): pastikan link pagination (halaman 2, 3, dst) tetap
            // membawa filter ?ruangan=... yang sedang aktif, tidak ke-reset ke "semua ruangan"
            ->withQueryString();

        return Inertia::render('StokLinen/Index', [
            'semuaStokLinen' => $semuaStokLinen,
            'ruanganOptions' => PenerimaanLinen::RUANGAN, // daftar pilihan untuk dropdown filter
            // Kirim balik nilai filter yang SEDANG aktif, supaya dropdown filter di Vue
            // otomatis menampilkan pilihan yang sesuai setelah halaman di-refresh/reload
            'filterRuangan' => $request->ruangan,
        ]);
    }

    // GET /stok-linen/tambah -> form tambah kombinasi ruangan+linen baru
    public function create()
    {
        return Inertia::render('StokLinen/Create', [
            'ruanganOptions' => PenerimaanLinen::RUANGAN,
            'namaLinenOptions' => ItemLinen::NAMA_ITEM,
        ]);
    }

    // POST /stok-linen -> simpan data baru
    public function store(StoreStokLinenRequest $request)
    {
        StokLinen::create($request->validated());

        return redirect()
            ->route('stok-linen.index')
            ->with('status', 'Stok linen baru berhasil ditambahkan.');
    }

    // GET /stok-linen/{id}/ubah -> form edit
    public function edit(StokLinen $stokLinen)
    {
        return Inertia::render('StokLinen/Edit', [
            'stokLinen' => $stokLinen,
            'ruanganOptions' => PenerimaanLinen::RUANGAN,
            'namaLinenOptions' => ItemLinen::NAMA_ITEM,
        ]);
    }

    // PUT /stok-linen/{id} -> simpan perubahan
    // Catatan: sesuai Django asli, TIDAK ADA method destroy() -- stok linen tidak bisa dihapus dari UI,
    // hanya bisa ditambah/diubah, supaya riwayat kombinasi ruangan+linen tetap terjaga
    public function update(UpdateStokLinenRequest $request, StokLinen $stokLinen)
    {
        $stokLinen->update($request->validated());

        return redirect()
            ->route('stok-linen.index')
            ->with('status', 'Data stok linen berhasil diperbarui.');
    }
}
