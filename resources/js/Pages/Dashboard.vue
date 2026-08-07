<script setup>
// AuthenticatedLayout: layout bawaan Breeze untuk halaman yang butuh login (ada navbar, dsb)
// Setara {% extends 'accounts/base.html' %} di Django
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

// Head: komponen dari Inertia untuk mengatur tag <title> di tab browser
// Setara {% block title %}...{% endblock %} di Django
import { Head } from '@inertiajs/vue3';

// ref: bikin variabel reaktif, dipakai untuk menyimpan referensi elemen <canvas>
// onMounted: fungsi ini otomatis dijalankan SEKALI setelah komponen selesai dirender ke halaman
// onBeforeUnmount: dijalankan SEBELUM komponen dihapus dari halaman (dipakai untuk bersih-bersih chart)
import { ref, onMounted, onBeforeUnmount } from 'vue';

// Import Chart.js. "chart.js/auto" otomatis daftarkan semua tipe chart & komponen,
// jadi tidak perlu import manual satu-satu (pie, legend, tooltip, dst)
// CATATAN: jalankan `sail npm install chart.js` dulu sebelum file ini bisa jalan
import Chart from 'chart.js/auto';

// defineProps: mendefinisikan data apa saja yang diterima dari Controller lewat Inertia::render()
// Ini setara `context` dictionary yang dikirim ke template Django
const props = defineProps({
    penerimaanBulanIni: Number,        // jumlah checklist penerimaan linen bulan ini
    pemakaianChemicalBulanIni: Number, // jumlah transaksi pemakaian chemical bulan ini
    rencanaKerjaAktif: Number,          // jumlah tugas yang statusnya belum 'Selesai'
    dataBeratPerRuangan: Array,          // array of { nama_ruangan, berat_minggu_ini, berat_bulan_ini }
    chartLabels: Array,                   // label ruangan untuk pie chart (hanya yang beratnya > 0)
    chartData: Array,                      // angka berat (kg) untuk tiap label di atas
});

// Variabel reaktif kosong dulu, nanti akan otomatis terisi elemen <canvas> lewat atribut ref="chartCanvas" di template
const chartCanvas = ref(null);

// Variabel biasa (bukan reaktif) untuk menyimpan instance Chart.js yang sedang aktif,
// supaya nanti bisa di-destroy() saat komponen ditutup (mencegah chart menumpuk di memory)
let chartInstance = null;

// Fungsi kecil untuk format angka jadi 2 desimal, pengganti filter Django {{ value|floatformat:2 }}
function formatBerat(value) {
    // Number(value) memastikan value berupa angka (jaga-jaga kalau datang sebagai string)
    // .toFixed(2) membulatkan ke 2 angka di belakang koma
    return Number(value).toFixed(2);
}

// onMounted: kode di dalam sini jalan setelah elemen <canvas ref="chartCanvas"> benar-benar ada di halaman
onMounted(() => {
    // Hanya bikin chart kalau memang ada datanya (setara pengecekan `if chartData.length > 0` di Django)
    if (props.chartData.length > 0) {
        // Buat instance Chart.js baru, nempel ke elemen canvas lewat chartCanvas.value
        chartInstance = new Chart(chartCanvas.value, {
            type: 'pie', // jenis chart: pie (lingkaran), sama seperti versi Django
            data: {
                labels: props.chartLabels, // label tiap potongan pie, diambil dari prop chartLabels
                datasets: [
                    {
                        label: 'Berat Linen (Kg)', // muncul di tooltip saat hover
                        data: props.chartData,       // angka berat tiap potongan pie
                        // Warna isi tiap potongan (urut sesuai urutan data)
                        backgroundColor: [
                            'rgba(54, 162, 235, 0.7)',
                            'rgba(255, 99, 132, 0.7)',
                            'rgba(255, 206, 86, 0.7)',
                            'rgba(75, 192, 192, 0.7)',
                            'rgba(153, 102, 255, 0.7)',
                            'rgba(255, 159, 64, 0.7)',
                            'rgba(99, 255, 132, 0.7)',
                        ],
                        // Warna garis tepi tiap potongan (versi lebih pekat dari backgroundColor)
                        borderColor: [
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 99, 132, 1)',
                            'rgba(255, 206, 86, 1)',
                            'rgba(75, 192, 192, 1)',
                            'rgba(153, 102, 255, 1)',
                            'rgba(255, 159, 64, 1)',
                            'rgba(99, 255, 132, 1)',
                        ],
                        borderWidth: 1, // ketebalan garis tepi tiap potongan, dalam pixel
                    },
                ],
            },
            options: {
                responsive: true, // chart otomatis menyesuaikan lebar/tinggi container-nya
                plugins: {
                    legend: {
                        position: 'top', // keterangan warna (legend) diletakkan di atas chart
                    },
                    title: {
                        display: true, // tampilkan judul di atas chart
                        text: 'Persentase Berat Linen per Ruangan (Bulan Ini)', // isi judulnya
                    },
                },
            },
        });
    }
    // Kalau chartData kosong, kita tidak bikin apa-apa di sini —
    // pesan "Belum ada data" ditangani langsung di <template> lewat v-if (lihat di bawah)
});

// onBeforeUnmount: jalan otomatis kalau user pindah halaman (misal klik menu lain)
onBeforeUnmount(() => {
    // Kalau ada chart yang masih aktif, hancurkan dulu instance-nya
    if (chartInstance) {
        chartInstance.destroy(); // membersihkan chart dari memory, mencegah memory leak di SPA
    }
});
</script>

<template>
    <!-- Bungkus semua isi halaman dengan layout Breeze (navbar dkk sudah otomatis ada) -->
    <AuthenticatedLayout>
        <!-- Slot 'header': judul yang tampil di bagian atas layout, di bawah navbar -->
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard</h2>
        </template>

        <!-- Mengatur judul tab browser jadi "Dashboard - <APP_NAME>" -->
        <Head title="Dashboard" />

        <!-- Container utama halaman, py-12 = padding atas-bawah, space-y-6 = jarak antar section -->
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <!-- Sapaan ke user yang sedang login. -->
                <!-- $page.props.auth.user tersedia otomatis di semua halaman Inertia lewat HandleInertiaRequests middleware -->
                <p class="text-gray-500">
                    Selamat datang kembali, {{ $page.props.auth.user.name }}!
                </p>

                <!-- Grid 3 kolom untuk kartu statistik (otomatis jadi 1 kolom di layar HP, grid-cols-1) -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                    <!-- Kartu 1: Penerimaan Linen bulan ini (warna biru) -->
                    <div class="bg-blue-600 text-white rounded-lg shadow p-6">
                        <h6 class="text-sm font-medium opacity-90 mb-2">Penerimaan Linen (Bulan Ini)</h6>
                        <p class="text-3xl font-bold">
                            <!-- {{ }} di Vue = interpolasi data, otomatis update kalau prop berubah -->
                            {{ penerimaanBulanIni }}
                            <span class="text-base font-normal opacity-90">Checklist</span>
                        </p>
                    </div>

                    <!-- Kartu 2: Pemakaian Chemical bulan ini (warna merah) -->
                    <div class="bg-red-600 text-white rounded-lg shadow p-6">
                        <h6 class="text-sm font-medium opacity-90 mb-2">Pemakaian Chemical (Bulan Ini)</h6>
                        <p class="text-3xl font-bold">
                            {{ pemakaianChemicalBulanIni }}
                            <span class="text-base font-normal opacity-90">Transaksi</span>
                        </p>
                    </div>

                    <!-- Kartu 3: Rencana kerja aktif (warna kuning) -->
                    <div class="bg-yellow-500 text-gray-900 rounded-lg shadow p-6">
                        <h6 class="text-sm font-medium opacity-90 mb-2">Rencana Kerja Aktif</h6>
                        <p class="text-3xl font-bold">
                            {{ rencanaKerjaAktif }}
                            <span class="text-base font-normal opacity-90">Tugas</span>
                        </p>
                    </div>
                </div>

                <!-- Kartu besar: ringkasan berat linen, isinya chart + tabel berdampingan -->
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <!-- Header kartu -->
                    <div class="px-6 py-4 border-b">
                        <h4 class="text-lg font-semibold text-gray-800">
                            Ringkasan Berat Linen Kotor (Kg) - Bulan Ini
                        </h4>
                    </div>

                    <!-- Body kartu: grid 12 kolom, chart ambil 5 kolom, tabel ambil 7 kolom -->
                    <div class="p-6 grid grid-cols-1 md:grid-cols-12 gap-6 items-center">

                        <!-- Kolom kiri: canvas tempat Chart.js menggambar pie chart -->
                        <div class="md:col-span-5">
                            <!-- ref="chartCanvas" menghubungkan elemen ini ke variabel chartCanvas di <script> -->
                            <canvas ref="chartCanvas"></canvas>

                            <!-- v-if: elemen ini HANYA muncul kalau chartData kosong (tidak ada data bulan ini) -->
                            <p v-if="chartData.length === 0" class="text-center text-gray-400 mt-4">
                                Belum ada data berat linen bulan ini.
                            </p>
                        </div>

                        <!-- Kolom kanan: tabel rincian berat per ruangan -->
                        <div class="md:col-span-7 overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Nama Ruangan</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Berat Minggu Ini</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Berat Bulan Ini</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <!-- v-for: looping array dataBeratPerRuangan, pengganti {% for item in ... %} Django -->
                                    <!-- :key wajib diisi (di sini pakai nama_ruangan) supaya Vue bisa lacak tiap baris secara efisien -->
                                    <tr v-for="item in dataBeratPerRuangan" :key="item.nama_ruangan">
                                        <td class="px-4 py-2 text-sm text-gray-700">{{ item.nama_ruangan }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-700">{{ formatBerat(item.berat_minggu_ini) }}</td>
                                        <td class="px-4 py-2 text-sm font-semibold text-gray-900">{{ formatBerat(item.berat_bulan_ini) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
