<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, onMounted, onBeforeUnmount, watch, nextTick } from 'vue';
import Chart from 'chart.js/auto';

// Props dari LaporanController@suhu
const props = defineProps({
    dataTabel: Object,       // hasil paginate() untuk tabel di layar
    startDate: String,        // tanggal mulai filter, format 'YYYY-MM-DD'
    endDate: String,           // tanggal selesai filter
    chartLabels: Array,          // daftar tanggal unik (sumbu-X grafik)
    datasetSuhu: Array,            // [{ruangan, data: [...]}, ...] untuk grafik suhu
    datasetKelembaban: Array,       // sama, tapi untuk grafik kelembaban
});

// Variabel reaktif lokal untuk filter form, diisi dari nilai awal props
const filterStart = ref(props.startDate);
const filterEnd = ref(props.endDate);

// Fungsi terapkan filter, dipanggil saat tombol "Terapkan" diklik
function terapkanFilter() {
    router.get(route('laporan.suhu'), {
        start_date: filterStart.value,
        end_date: filterEnd.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
}

// Referensi ke elemen <canvas> untuk masing-masing grafik
const canvasSuhu = ref(null);
const canvasKelembaban = ref(null);
// Menyimpan instance Chart.js aktif, supaya bisa di-destroy saat data berganti atau komponen ditutup
let chartSuhuInstance = null;
let chartKelembabanInstance = null;

// Palet warna untuk tiap garis (ruangan). Kalau ruangannya lebih banyak dari palet ini,
// Chart.js akan otomatis mengulang warna dari awal -- cukup untuk kebutuhan sekarang
const paletWarna = [
    'rgb(54, 162, 235)', 'rgb(255, 99, 132)', 'rgb(255, 206, 86)',
    'rgb(75, 192, 192)', 'rgb(153, 102, 255)', 'rgb(255, 159, 64)',
];

// Fungsi generik untuk bikin/refresh line chart, dipakai untuk grafik Suhu MAUPUN Kelembaban
// (menghindari duplikasi kode karena strukturnya identik, cuma datanya beda)
function buatChart(canvasRef, instanceLama, dataset, labelSumbuY) {
    // Kalau sebelumnya sudah ada chart di canvas ini, hancurkan dulu supaya tidak dobel/tumpang tindih
    if (instanceLama) {
        instanceLama.destroy();
    }

    // Susun 'datasets' sesuai format yang diminta Chart.js: array of {label, data, borderColor, ...}
    const datasetsChart = dataset.map((seri, index) => ({
        label: seri.ruangan,                              // nama ruangan jadi label garis
        data: seri.data,                                    // array angka (bisa null di beberapa titik -- otomatis jadi garis putus)
        borderColor: paletWarna[index % paletWarna.length],   // warna garis, berulang kalau ruangan lebih banyak dari palet
        backgroundColor: 'transparent',
        tension: 0.3,                                            // 0.3 -> garis sedikit melengkung halus, bukan patah-patah kaku
        spanGaps: false,                                            // false -> titik null tetap tampil sebagai celah/putus, bukan disambung paksa
    }));

    return new Chart(canvasRef.value, {
        type: 'line',
        data: {
            labels: props.chartLabels, // sumbu-X: daftar tanggal
            datasets: datasetsChart,
        },
        options: {
            responsive: true,
            interaction: { mode: 'index', intersect: false }, // hover di mana saja pada garis, tooltip tetap muncul
            plugins: {
                legend: { position: 'top' },
            },
            scales: {
                y: {
                    title: { display: true, text: labelSumbuY },
                },
            },
        },
    });
}

// Fungsi untuk (re)generate KEDUA chart sekaligus
function renderUlangChart() {
    chartSuhuInstance = buatChart(canvasSuhu, chartSuhuInstance, props.datasetSuhu, 'Suhu (°C)');
    chartKelembabanInstance = buatChart(canvasKelembaban, chartKelembabanInstance, props.datasetKelembaban, 'Kelembaban (%)');
}

// onMounted: render chart pertama kali saat halaman selesai dimuat
onMounted(() => {
    renderUlangChart();
});

// watch terhadap props.chartLabels: kalau user ganti filter tanggal, Inertia akan re-render komponen
// dengan props baru dari server -- watch ini memastikan chart ikut di-refresh dengan data terbaru
watch(() => props.chartLabels, async () => {
    // nextTick() -> tunggu Vue selesai update DOM dulu, baru render chart, supaya canvas-nya sudah "siap"
    await nextTick();
    renderUlangChart();
});

// Bersihkan chart saat komponen ditutup (pindah halaman), mencegah memory leak
onBeforeUnmount(() => {
    if (chartSuhuInstance) chartSuhuInstance.destroy();
    if (chartKelembabanInstance) chartKelembabanInstance.destroy();
});

function formatTanggal(tanggal) {
    return new Date(tanggal).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
}
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Rekap Suhu & Kelembaban</h2>
        </template>

        <Head title="Rekap Suhu" />

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <!-- ============ FILTER TANGGAL + TOMBOL EXPORT ============ -->
                <div class="bg-white rounded-lg shadow p-4 flex flex-wrap items-end gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Tanggal Mulai</label>
                        <input type="date" v-model="filterStart" class="border-gray-300 rounded-md shadow-sm text-sm" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Tanggal Selesai</label>
                        <input type="date" v-model="filterEnd" class="border-gray-300 rounded-md shadow-sm text-sm" />
                    </div>
                    <button @click="terapkanFilter"
                            class="px-4 py-2 bg-gray-800 text-white rounded-md text-xs font-semibold uppercase tracking-widest hover:bg-gray-700">
                        Terapkan
                    </button>

                    <!-- Tombol export: PAKAI <a> BIASA, BUKAN <Link> Inertia -- karena ini men-download file,
                         bukan berpindah halaman. Kalau pakai <Link>, Inertia akan coba proses response-nya
                         sebagai halaman baru dan gagal, bukan memicu download file di browser -->
                    <a :href="route('laporan.suhu.export', { start_date: filterStart, end_date: filterEnd })"
                       class="ml-auto px-4 py-2 bg-green-600 text-white rounded-md text-xs font-semibold uppercase tracking-widest hover:bg-green-700">
                        ⬇ Export ke Excel
                    </a>
                </div>

                <!-- ============ GRAFIK GARIS SUHU ============ -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="font-medium text-gray-800 mb-4">Tren Suhu Ruangan</h3>
                    <canvas ref="canvasSuhu"></canvas>
                    <p v-if="chartLabels.length === 0" class="text-center text-gray-400 mt-4">
                        Tidak ada data pada rentang tanggal ini.
                    </p>
                </div>

                <!-- ============ GRAFIK GARIS KELEMBABAN ============ -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="font-medium text-gray-800 mb-4">Tren Kelembaban Ruangan</h3>
                    <canvas ref="canvasKelembaban"></canvas>
                </div>

                <!-- ============ TABEL DATA MENTAH ============ -->
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jam</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ruangan</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Waktu Ukur</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Suhu</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Kelembaban</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Petugas</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="item in dataTabel.data" :key="item.id">
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">{{ formatTanggal(item.tanggal) }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">{{ item.jam?.slice(0, 5) }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">{{ item.ruangan }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">{{ item.waktu_ukur }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-center text-sm">{{ item.suhu }} °C</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-center text-sm">{{ item.kelembaban }} %</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">{{ item.petugas?.name }}</td>
                                </tr>
                                <tr v-if="dataTabel.data.length === 0">
                                    <td colspan="7" class="px-4 py-4 text-center text-gray-400">Tidak ada data pada rentang tanggal ini.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="px-6 py-4 flex flex-wrap gap-1">
                        <template v-for="(link, i) in dataTabel.links" :key="i">
                            <Link v-if="link.url" :href="link.url"
                                  class="px-3 py-1 text-sm rounded-md"
                                  :class="link.active ? 'bg-gray-800 text-white' : 'bg-white text-gray-700 border hover:bg-gray-50'"
                                  v-html="link.label" />
                            <span v-else class="px-3 py-1 text-sm text-gray-400" v-html="link.label" />
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
