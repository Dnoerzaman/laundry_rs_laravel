<?php

// Namespace controller ini, sesuai lokasi file di app/Http/Controllers
namespace App\Http\Controllers;

// Import model-model yang datanya kita butuhkan untuk statistik dashboard
use App\Models\PenerimaanLinen;       // untuk hitung jumlah checklist penerimaan linen
use App\Models\PemakaianChemical;     // untuk hitung jumlah transaksi pemakaian chemical
use App\Models\Tugas;                 // untuk hitung rencana kerja yang masih aktif
use App\Models\BeratLinenHarian;      // untuk agregasi total berat linen per ruangan

// Carbon adalah library tanggal bawaan Laravel, pengganti `timezone` + `calendar` di Django
use Carbon\Carbon;

// Inertia dipakai untuk mengirim data (props) dari Controller ke komponen Vue,
// pengganti `render(request, 'template.html', context)` di Django
use Inertia\Inertia;

// Controller khusus untuk halaman dashboard utama
class DashboardController extends Controller
{
    // Method index() dipanggil saat user membuka route '/dashboard'
    public function index()
    {
        // Ambil tanggal hari ini (tanpa jam), setara `timezone.now().date()` di Django
        $today = Carbon::today();

        // --- Kalkulasi rentang minggu ini ---
        // startOfWeek() di Carbon defaultnya mulai hari Senin (sama seperti Django weekday()==0)
        $startOfWeek = $today->copy()->startOfWeek();
        // Akhir minggu kerja = Senin + 5 hari = Sabtu (bukan Minggu), sesuai logika Django asli
        $endOfWeek = $startOfWeek->copy()->addDays(5);

        // --- Kalkulasi rentang bulan ini ---
        // startOfMonth() otomatis ambil tanggal 1 di bulan berjalan
        $startOfMonth = $today->copy()->startOfMonth();
        // endOfMonth() otomatis hitung tanggal terakhir bulan ini (28/29/30/31, sudah handle kabisat)
        $endOfMonth = $today->copy()->endOfMonth();

        // --- Data agregat untuk 3 kartu statistik di atas ---

        // Hitung jumlah baris PenerimaanLinen yang tanggalnya ada di rentang bulan ini
        $penerimaanBulanIni = PenerimaanLinen::whereBetween('tanggal', [$startOfMonth, $endOfMonth])->count();

        // Hitung jumlah baris PemakaianChemical yang tanggalnya ada di rentang bulan ini
        $pemakaianChemicalBulanIni = PemakaianChemical::whereBetween('tanggal', [$startOfMonth, $endOfMonth])->count();

        // Hitung jumlah Tugas yang statusnya BUKAN 'Selesai' (setara .exclude(status='Selesai') Django)
        $rencanaKerjaAktif = Tugas::where('status', '!=', 'Selesai')->count();

        // --- Data untuk tabel & chart berat linen per ruangan ---

        // Ambil semua pilihan ruangan dari konstanta model (pengganti PilihanRuangan.choices Django)
        $ruanganChoices = PenerimaanLinen::RUANGAN;

        // Query 1: total berat linen per ruangan, HANYA untuk rentang minggu ini
        // selectRaw + groupBy = setara .values('ruangan').annotate(total=Sum('total_berat')) di Django
        // pluck('total', 'ruangan') mengubah hasil jadi array asosiatif: ['Rawat Inap' => 12.5, ...]
        $beratMingguan = BeratLinenHarian::whereBetween('tanggal', [$startOfWeek, $endOfWeek])
            ->selectRaw('ruangan, SUM(total_berat) as total') // jumlahkan kolom total_berat per grup
            ->groupBy('ruangan')                               // grup berdasarkan nama ruangan
            ->pluck('total', 'ruangan');                        // jadikan key => value: ruangan => total

        // Query 2: sama seperti di atas, tapi untuk rentang bulan ini
        $beratBulanan = BeratLinenHarian::whereBetween('tanggal', [$startOfMonth, $endOfMonth])
            ->selectRaw('ruangan, SUM(total_berat) as total')
            ->groupBy('ruangan')
            ->pluck('total', 'ruangan');

        // Array kosong untuk menampung hasil akhir yang akan dikirim ke Vue
        $dataBeratPerRuangan = []; // untuk tabel rincian (semua ruangan, walau beratnya 0)
        $chartLabels = [];          // untuk pie chart: hanya label ruangan yang beratnya > 0
        $chartData = [];             // untuk pie chart: angka berat yang sejalan dengan $chartLabels

        // Looping semua pilihan ruangan yang ada di sistem (bukan cuma yang ada datanya)
        foreach ($ruanganChoices as $ruangan) {
            // Ambil berat bulan ini untuk ruangan ini; kalau tidak ada datanya, pakai 0
            // (float) memastikan hasilnya angka desimal, bukan string dari database
            $beratBulanIni = (float) ($beratBulanan[$ruangan] ?? 0);

            // Tambahkan baris data untuk tabel rincian (selalu ditambahkan, walau 0)
            $dataBeratPerRuangan[] = [
                'nama_ruangan' => $ruangan,                                    // nama ruangan untuk ditampilkan
                'berat_minggu_ini' => (float) ($beratMingguan[$ruangan] ?? 0), // berat minggu ini, default 0
                'berat_bulan_ini' => $beratBulanIni,                            // berat bulan ini yang sudah dihitung di atas
            ];

            // Untuk chart, hanya masukkan ruangan yang beratnya lebih dari 0
            // (supaya pie chart tidak penuh dengan slice kosong / bernilai 0)
            if ($beratBulanIni > 0) {
                $chartLabels[] = $ruangan;      // label slice = nama ruangan
                $chartData[] = $beratBulanIni;   // nilai slice = berat dalam kg
            }
        }

        // Kirim semua data ke komponen Vue 'Dashboard' (resources/js/Pages/Dashboard.vue)
        // Inertia::render() menggantikan render(request, 'accounts/dashboard.html', context) di Django —
        // bedanya, di sini datanya dikirim sebagai JSON langsung ke komponen Vue, bukan di-render server-side jadi HTML
        return Inertia::render('Dashboard', [
            'penerimaanBulanIni' => $penerimaanBulanIni,               // angka untuk kartu 1
            'pemakaianChemicalBulanIni' => $pemakaianChemicalBulanIni, // angka untuk kartu 2
            'rencanaKerjaAktif' => $rencanaKerjaAktif,                 // angka untuk kartu 3
            'dataBeratPerRuangan' => $dataBeratPerRuangan,             // array untuk tabel rincian
            'chartLabels' => $chartLabels,                              // array label untuk pie chart
            'chartData' => $chartData,                                  // array angka untuk pie chart
        ]);
    }
}