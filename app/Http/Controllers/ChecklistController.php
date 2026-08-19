<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePenerimaanLinenRequest;
use App\Http\Requests\UpdatePenerimaanLinenRequest;
use App\Models\ItemLinen;
use App\Models\PenerimaanLinen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ChecklistController extends Controller
{
    /**
     * Menampilkan riwayat penerimaan linen.
     *
     * GET /checklist
     */
    public function index(Request $request)
    {
        $query = PenerimaanLinen::with([
            'petugas',
            'items',
            'updatedBy',
            'items',
        ])->latest('tanggal')->latest('jam');

        // Filter tanggal mulai
        if ($request->filled('tanggal_dari')) {
            $query->whereDate('tanggal', '>=', $request->tanggal_dari);
        }

        // Filter tanggal sampai
        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('tanggal', '<=', $request->tanggal_sampai);
        }

        // Filter ruangan
        if ($request->filled('ruangan')) {
            $query->where('ruangan', $request->ruangan);
        }

        $penerimaan = $query
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Checklist/Index', [
            'penerimaan' => $penerimaan,

            'filters' => [
                'tanggal_dari' => $request->tanggal_dari,
                'tanggal_sampai' => $request->tanggal_sampai,
                'ruangan' => $request->ruangan,
            ],

            'ruanganOptions' => PenerimaanLinen::RUANGAN,
        ]);
    }

    /**
     * Menampilkan form tambah penerimaan linen.
     *
     * GET /checklist/tambah
     */
    public function create()
    {
        return Inertia::render('Checklist/Create', [
            'ruanganOptions' => PenerimaanLinen::RUANGAN,
            'itemOptions' => ItemLinen::NAMA_ITEM,
            'kondisiOptions' => ItemLinen::KONDISI,
        ]);
    }

    /**
     * Menyimpan penerimaan linen baru.
     *
     * POST /checklist
     */
    public function store(StorePenerimaanLinenRequest $request)
    {
        DB::transaction(function () use ($request) {

            $penerimaan = PenerimaanLinen::create([
                'tanggal' => $request->validated('tanggal'),
                'jam' => $request->validated('jam'),
                'ruangan' => $request->validated('ruangan'),
                'petugas_id' => $request->user()->id,
                //audit trail
                'created_by' => $request->user()->id,
                'updated_by' => $request->user()->id,
            ]);

            foreach ($request->validated('items') as $item) {
                $penerimaan->items()->create($item);
            }
        });

        return redirect()
            ->route('checklist.index')
            ->with('status', 'Data penerimaan linen berhasil disimpan.');
    }

    /**
     * Menampilkan detail satu penerimaan linen.
     *
     * GET /checklist/{penerimaan}
     */
    public function show(PenerimaanLinen $penerimaan)
    {
        $penerimaan->load([
            'petugas',
            'createdBy',
            'updatedBy',
            'items',
        ]);

        return Inertia::render('Checklist/Show', [
            'penerimaan' => $penerimaan,
        ]);
    }

    /**
     * Menampilkan form edit.
     *
     * GET /checklist/{penerimaan}/ubah
     */
    public function edit(PenerimaanLinen $penerimaan)
    {
        $penerimaan->load('items', 'createdBy', 'updatedBy');

        return Inertia::render('Checklist/Edit', [
            'penerimaan' => $penerimaan,
            'ruanganOptions' => PenerimaanLinen::RUANGAN,
            'itemOptions' => ItemLinen::NAMA_ITEM,
            'kondisiOptions' => ItemLinen::KONDISI,
        ]);
    }

    /**
     * Memperbarui penerimaan linen.
     *
     * PUT /checklist/{penerimaan}
     */
    public function update(
        UpdatePenerimaanLinenRequest $request,
        PenerimaanLinen $penerimaan
    ) {
        DB::transaction(function () use ($request, $penerimaan) {

            // Update data header
            $penerimaan->update([
                'tanggal' => $request->validated('tanggal'),
                'jam' => $request->validated('jam'),
                'ruangan' => $request->validated('ruangan'),
                //audit trail
                'updated_by' => $request->user()->id,
            ]);

            /*
             * Untuk tahap awal, detail lama dihapus kemudian dibuat
             * kembali berdasarkan data hasil edit.
             *
             * Semuanya berada di dalam transaction sehingga jika
             * terjadi error, seluruh perubahan akan di-rollback.
             */
            $penerimaan->items()->delete();

            foreach ($request->validated('items') as $item) {
                $penerimaan->items()->create($item);
            }
        });

        return redirect()
            ->route('checklist.show', $penerimaan)
            ->with('status', 'Data penerimaan linen berhasil diperbarui.');
    }
}