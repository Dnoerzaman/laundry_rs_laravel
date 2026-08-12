<?php

// Namespace sesuai lokasi file di app/Http/Controllers
namespace App\Http\Controllers;

// Import Form Request untuk validasi store & update (2 class berbeda, meski rules-nya sama persis,
// supaya ke depannya kalau aturan store & update perlu dibedakan, tinggal ubah salah satunya)
use App\Http\Requests\StoreLogPekerjaanRequest;
use App\Http\Requests\UpdateLogPekerjaanRequest;

// Import model LogPekerjaan (sudah dibuat di Tahap 1, tabel log_pekerjaans)
use App\Models\LogPekerjaan;

// Inertia dipakai untuk mengirim data dari Controller ke komponen Vue,
// menggantikan render(request, 'template.html', context) di Django
use Inertia\Inertia;

// Controller untuk modul Log Pekerjaan / Kejadian Harian.
// Modul ini murni CRUD sederhana (tidak ada logika stok/transaksi), mirip pola modul Suhu,
// bedanya di sini ada field 'pj' (penanggung jawab) yang otomatis diisi dari user yang login.
class LogPekerjaanController extends Controller
{
    // GET /log-pekerjaan -> daftar semua catatan log pekerjaan (paginate)
    public function index()
    {
        // with('pj') -> eager load relasi 'pj' (penanggung jawab, relasi ke model User)
        // dalam 1 query tambahan, supaya tidak query berulang satu-satu saat looping di Vue (hindari N+1)
        //
        // orderByDesc('tanggal') lalu orderByDesc('dibuat_pada') -> setara Meta.ordering = ['-tanggal', '-dibuat_pada']
        // di Django: urutkan dulu berdasarkan tanggal (terbaru di atas), kalau tanggalnya SAMA,
        // baru diurutkan berdasarkan waktu pembuatan (yang paling baru dibuat tampil lebih dulu)
        //
        // paginate(10) -> setara paginate_by = 10 di Django ListView, otomatis potong per 10 baris per halaman
        $semuaLog = LogPekerjaan::with('pj')
            ->orderByDesc('tanggal')
            ->orderByDesc('dibuat_pada')
            ->paginate(10);

        // Inertia::render() mengirim komponen Vue 'LogPekerjaan/Index' beserta data $semuaLog sebagai props.
        // Data ini otomatis muncul di Vue lewat defineProps({ semuaLog: Object })
        return Inertia::render('LogPekerjaan/Index', [
            'semuaLog' => $semuaLog,
        ]);
    }

    // GET /log-pekerjaan/tambah -> form tambah catatan baru
    public function create()
    {
        // Tidak perlu kirim props apa pun ke halaman ini, karena form-nya cuma 2 field bebas
        // (tanggal & keterangan), tidak ada dropdown yang butuh daftar pilihan dari server
        return Inertia::render('LogPekerjaan/Create');
    }

    // POST /log-pekerjaan -> simpan catatan baru
    public function store(StoreLogPekerjaanRequest $request)
    {
        // LogPekerjaan::create([...]) -> insert baris baru ke tabel log_pekerjaans
        //
        // ...$request->validated() -> "sebarkan" (spread) semua field yang sudah lolos validasi
        // (tanggal, keterangan) sebagai key-value ke dalam array ini
        //
        // 'pj_id' => $request->user()->id -> tambahkan field pj_id secara manual, diisi ID user
        // yang sedang login. Ini SETARA dengan baris Django:
        //     form.instance.pj = self.request.user
        // yang ada di method form_valid() pada CreateView versi Django asli
        LogPekerjaan::create([
            ...$request->validated(),
            'pj_id' => $request->user()->id,
        ]);

        // redirect()->route(...) -> arahkan browser ke halaman daftar log pekerjaan
        // ->with('status', ...) -> kirim pesan flash "sukses" yang akan ditampilkan sebagai notifikasi
        // hijau di halaman tujuan (lewat $page.props.flash.status di Vue)
        return redirect()
            ->route('log-pekerjaan.index')
            ->with('status', 'Catatan log pekerjaan berhasil disimpan.');
    }

    // GET /log-pekerjaan/{id}/ubah -> form edit
    //
    // Parameter (LogPekerjaan $logPekerjaan) memakai fitur "Route Model Binding" Laravel:
    // Laravel otomatis mengambil baris LogPekerjaan dari database berdasarkan {id} yang ada di URL,
    // kalau tidak ketemu otomatis menampilkan halaman 404 -- tidak perlu nulis query manual
    public function edit(LogPekerjaan $logPekerjaan)
    {
        // Kirim data existing ke Vue supaya form bisa menampilkan nilai yang SUDAH ADA
        // (bukan form kosong seperti di halaman Create)
        return Inertia::render('LogPekerjaan/Edit', [
            'log' => $logPekerjaan,
        ]);
    }

    // PUT /log-pekerjaan/{id} -> simpan perubahan
    public function update(UpdateLogPekerjaanRequest $request, LogPekerjaan $logPekerjaan)
    {
        // $logPekerjaan->update(...) -> UPDATE baris yang sudah ditemukan lewat Route Model Binding di atas
        //
        // PENTING: field 'pj_id' SENGAJA TIDAK ikut di-update di sini, karena field 'pj' memang
        // tidak ada di form Update versi Django asli (PJ hanya diisi SEKALI saat pertama kali dibuat,
        // supaya riwayat "siapa yang awalnya mencatat kejadian ini" tetap terjaga meski datanya diedit
        // oleh orang lain / oleh dirinya sendiri di kemudian hari)
        $logPekerjaan->update($request->validated());

        return redirect()
            ->route('log-pekerjaan.index')
            ->with('status', 'Catatan log pekerjaan berhasil diperbarui.');
    }

    // DELETE /log-pekerjaan/{id} -> hapus catatan
    public function destroy(LogPekerjaan $logPekerjaan)
    {
        // ->delete() -> hapus baris ini dari database.
        // Tidak perlu try/catch seperti di AsetController, karena tidak ada tabel lain
        // yang mereferensikan log_pekerjaans lewat foreign key -- aman dihapus langsung
        $logPekerjaan->delete();

        return redirect()
            ->route('log-pekerjaan.index')
            ->with('status', 'Catatan log pekerjaan berhasil dihapus.');
    }
}
