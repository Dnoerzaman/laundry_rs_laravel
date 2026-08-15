<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

// Props dari LaporanController@index
const props = defineProps({
    startDate: String,
    endDate: String,
});

// Filter tanggal bersama, dipakai untuk SEMUA export yang butuh rentang tanggal
const filterStart = ref(props.startDate);
const filterEnd = ref(props.endDate);

// Terapkan filter -> update URL halaman ini (supaya kalau di-refresh, filter tetap kepakai)
function terapkanFilter() {
    router.get(route('laporan.index'), {
        start_date: filterStart.value,
        end_date: filterEnd.value,
    }, { preserveState: true, preserveScroll: true });
}

// computed: objek query yang dipakai berulang untuk semua link export ber-filter tanggal,
// supaya tidak perlu tulis {start_date: filterStart, end_date: filterEnd} berkali-kali di template
const queryTanggal = computed(() => ({
    start_date: filterStart.value,
    end_date: filterEnd.value,
}));

// Daftar laporan yang PAKAI filter tanggal (route Ziggy + deskripsi singkat untuk ditampilkan)
const laporanDenganTanggal = [
    { route: 'laporan.checklist.export', judul: 'Checklist Penerimaan Linen', deskripsi: 'Rincian per item linen yang diterima.' },
    { route: 'laporan.pemakaian-chemical.export', judul: 'Pemakaian Chemical', deskripsi: 'Riwayat pemakaian chemical harian.' },
    { route: 'laporan.transaksi-linen.export', judul: 'Transaksi Linen', deskripsi: 'Riwayat linen masuk & keluar per ruangan.' },
    { route: 'laporan.berat-linen.export', judul: 'Berat Linen Harian', deskripsi: 'Rekap berat linen kotor per shift.' },
    { route: 'laporan.log-pekerjaan.export', judul: 'Log Pekerjaan', deskripsi: 'Catatan kejadian & pekerjaan harian.' },
    { route: 'laporan.biaya.export', judul: 'Pengeluaran Laundry', deskripsi: 'Rekap biaya, lengkap dengan baris Total.' },
];

// Daftar laporan SNAPSHOT (kondisi terkini, tidak butuh filter tanggal)
const laporanSnapshot = [
    { route: 'laporan.aset.export', judul: 'Aset', deskripsi: 'Seluruh inventaris aset saat ini.' },
    { route: 'laporan.schedule.export', judul: 'Rencana Kerja', deskripsi: 'Seluruh tugas & statusnya saat ini.' },
    { route: 'laporan.stok-chemical.export', judul: 'Stok Chemical', deskripsi: 'Stok chemical saat ini (master data).' },
];
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Rekap Laporan</h2>
        </template>

        <Head title="Rekap Laporan" />

        <div class="py-12">
            <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <!-- Kartu khusus Suhu, tetap paling atas karena punya halaman dedicated dengan grafik -->
                <Link :href="route('laporan.suhu')"
                      class="block bg-white rounded-lg shadow p-6 hover:shadow-md transition-shadow border-l-4 border-sky-500">
                    <h3 class="font-semibold text-gray-800 mb-1">📈 Rekap Suhu & Kelembaban</h3>
                    <p class="text-sm text-gray-500">Grafik tren interaktif + export Excel dengan grafik garis native.</p>
                </Link>

                <!-- ============ FILTER TANGGAL BERSAMA ============ -->
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
                    <p class="text-xs text-gray-400 w-full">
                        Rentang tanggal ini berlaku untuk semua laporan di bagian "Export dengan Rentang Tanggal" di bawah.
                    </p>
                </div>

                <!-- ============ GRUP 1: EXPORT DENGAN FILTER TANGGAL ============ -->
                <div>
                    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3">
                        Export dengan Rentang Tanggal
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div v-for="item in laporanDenganTanggal" :key="item.route"
                             class="bg-white rounded-lg shadow p-4 flex items-center justify-between gap-3">
                            <div>
                                <p class="font-medium text-gray-800 text-sm">{{ item.judul }}</p>
                                <p class="text-xs text-gray-500">{{ item.deskripsi }}</p>
                            </div>
                            <!-- <a href> biasa, BUKAN <Link> Inertia -- ini memicu download file, bukan navigasi halaman -->
                            <a :href="route(item.route, queryTanggal)"
                               class="shrink-0 px-3 py-1.5 bg-green-600 text-white rounded-md text-xs font-semibold hover:bg-green-700">
                                ⬇ Excel
                            </a>
                        </div>
                    </div>
                </div>

                <!-- ============ GRUP 2: EXPORT SNAPSHOT (TANPA FILTER TANGGAL) ============ -->
                <div>
                    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3">
                        Export Snapshot (Kondisi Terkini)
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div v-for="item in laporanSnapshot" :key="item.route"
                             class="bg-white rounded-lg shadow p-4 flex items-center justify-between gap-3">
                            <div>
                                <p class="font-medium text-gray-800 text-sm">{{ item.judul }}</p>
                                <p class="text-xs text-gray-500">{{ item.deskripsi }}</p>
                            </div>
                            <!-- Tidak ada query tanggal di sini, karena laporan snapshot memang tidak difilter tanggal -->
                            <a :href="route(item.route)"
                               class="shrink-0 px-3 py-1.5 bg-green-600 text-white rounded-md text-xs font-semibold hover:bg-green-700">
                                ⬇ Excel
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
