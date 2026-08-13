<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

// Props dari BiayaController@index
const props = defineProps({
    semuaBiaya: Object,      // hasil paginate() untuk bulan yang sedang aktif
    totalPeriodeIni: [Number, String], // total SEMUA baris di bulan ini (bukan cuma yang tampil di halaman)
    bulan: Number,             // angka bulan yang sedang aktif (1-12)
    tahun: Number,              // angka tahun yang sedang aktif
    labelPeriode: String,        // label siap-tampil, misal "Agustus 2026"
});

// Fungsi pindah ke bulan sebelumnya/berikutnya.
// arah: -1 untuk mundur satu bulan, +1 untuk maju satu bulan
function pindahBulan(arah) {
    // Hitung bulan & tahun baru. JavaScript bulan 0-11 secara native, tapi kita simpan 1-12
    // di server, jadi perlu sedikit konversi manual di sini
    let bulanBaru = props.bulan + arah;
    let tahunBaru = props.tahun;

    if (bulanBaru > 12) {
        bulanBaru = 1;
        tahunBaru += 1;
    } else if (bulanBaru < 1) {
        bulanBaru = 12;
        tahunBaru -= 1;
    }

    // router.get: minta halaman ini lagi ke server, tapi dengan query string bulan/tahun yang baru.
    // preserveState & preserveScroll -> transisi terasa instan, tanpa reload penuh halaman
    router.get(route('biaya.index'), { bulan: bulanBaru, tahun: tahunBaru }, {
        preserveState: true,
        preserveScroll: true,
    });
}

// Fungsi kembali ke bulan SEKARANG (hari ini), dipanggil dari tombol "Bulan Ini"
function keBulanIni() {
    const sekarang = new Date();
    router.get(route('biaya.index'), {
        bulan: sekarang.getMonth() + 1, // getMonth() JS itu 0-11, makanya +1 supaya jadi 1-12
        tahun: sekarang.getFullYear(),
    }, { preserveState: true, preserveScroll: true });
}

function hapus(id, namaBarang) {
    if (confirm(`Yakin hapus catatan "${namaBarang}"?`)) {
        router.delete(route('biaya.destroy', id));
    }
}

function formatTanggal(tanggal) {
    return new Date(tanggal).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
}

// Format Rupiah, dipakai berulang untuk kolom Harga, Jumlah, dan baris Total
function formatRupiah(angka) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(angka);
}
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center flex-wrap gap-2">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Pengeluaran Laundry</h2>
                <Link :href="route('biaya.create')"
                      class="inline-flex items-center px-4 py-2 bg-gray-800 text-white rounded-md text-xs font-semibold uppercase tracking-widest hover:bg-gray-700">
                    + Tambah Pengeluaran
                </Link>
            </div>
        </template>

        <Head title="Pengeluaran Laundry" />

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

                <div v-if="$page.props.flash?.status" class="mb-4 p-4 bg-green-100 text-green-800 rounded-md">
                    {{ $page.props.flash.status }}
                </div>

                <!-- ============ NAVIGASI PERIODE BULAN ============ -->
                <!-- Meniru cara kerja "sheet per bulan" di Excel: tombol panah kiri/kanan untuk pindah bulan -->
                <div class="bg-white rounded-lg shadow p-4 mb-4 flex items-center justify-between flex-wrap gap-2">
                    <div class="flex items-center gap-3">
                        <button @click="pindahBulan(-1)"
                                class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 rounded-md text-sm font-medium">
                            &larr; Bulan Sebelumnya
                        </button>
                        <!-- labelPeriode dikirim siap-jadi dari server, misal "Agustus 2026" -->
                        <span class="text-lg font-semibold text-gray-800 min-w-[160px] text-center">{{ labelPeriode }}</span>
                        <button @click="pindahBulan(1)"
                                class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 rounded-md text-sm font-medium">
                            Bulan Berikutnya &rarr;
                        </button>
                    </div>
                    <button @click="keBulanIni"
                            class="text-xs text-sky-600 hover:text-sky-800 underline">
                        Kembali ke Bulan Ini
                    </button>
                </div>
                <!-- ============ AKHIR NAVIGASI PERIODE ============ -->

                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kategori</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Barang</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Qty</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Satuan</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Harga</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Jumlah</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">PJ</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="row in semuaBiaya.data" :key="row.id">
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">{{ formatTanggal(row.tanggal) }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">{{ row.kategori }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700">{{ row.nama_barang }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-center text-sm text-gray-700">{{ row.qty }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">{{ row.satuan }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 text-right">{{ formatRupiah(row.harga) }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm font-semibold text-gray-900 text-right">{{ formatRupiah(row.jumlah) }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">{{ row.pj?.name }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-center text-sm">
                                        <Link :href="route('biaya.edit', row.id)"
                                              class="text-yellow-600 hover:text-yellow-800 font-medium mr-3">Ubah</Link>
                                        <button @click="hapus(row.id, row.nama_barang)" class="text-red-600 hover:text-red-800 font-medium">Hapus</button>
                                    </td>
                                </tr>
                                <tr v-if="semuaBiaya.data.length === 0">
                                    <td colspan="9" class="px-4 py-4 text-center text-gray-400">
                                        Belum ada catatan pengeluaran untuk periode {{ labelPeriode }}.
                                    </td>
                                </tr>
                            </tbody>

                            <!-- ============ BARIS TOTAL (footer tabel) ============ -->
                            <!-- Meniru baris "Total" di baris paling bawah template Excel kamu -->
                            <tfoot v-if="semuaBiaya.data.length > 0" class="bg-gray-50 border-t-2 border-gray-300">
                                <tr>
                                    <!-- colspan="6" -> gabungkan sel dari kolom Tanggal sampai Harga jadi satu, label "Total" di tengah -->
                                    <td colspan="6" class="px-4 py-3 text-right text-sm font-bold text-gray-800">
                                        Total Pengeluaran {{ labelPeriode }}:
                                    </td>
                                    <td class="px-4 py-3 text-right text-base font-bold text-gray-900">
                                        {{ formatRupiah(totalPeriodeIni) }}
                                    </td>
                                    <!-- 2 sel kosong supaya jumlah kolom tetap sejajar dengan kolom PJ & Aksi di atasnya -->
                                    <td colspan="2"></td>
                                </tr>
                            </tfoot>
                            <!-- ============ AKHIR BARIS TOTAL ============ -->
                        </table>
                    </div>

                    <div class="px-6 py-4 flex flex-wrap gap-1">
                        <template v-for="(link, i) in semuaBiaya.links" :key="i">
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
