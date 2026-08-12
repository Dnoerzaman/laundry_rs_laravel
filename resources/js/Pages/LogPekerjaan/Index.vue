<script setup>
// AuthenticatedLayout: layout bawaan Breeze untuk halaman yang butuh login (ada navbar, dsb)
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

// Head: mengatur tag <title> di tab browser
// Link: pengganti <a href> biasa, tapi navigasinya lewat Inertia (tanpa reload penuh halaman)
// router: dipakai untuk trigger request manual (di sini untuk request DELETE saat hapus data)
import { Head, Link, router } from '@inertiajs/vue3';

// defineProps: menerima data yang dikirim dari LogPekerjaanController@index lewat Inertia::render()
// 'semuaLog' adalah hasil paginate() dari Laravel, isinya { data: [...baris...], links: [...], total, ... }
const props = defineProps({
    semuaLog: Object,
});

// Fungsi hapus, dipanggil saat tombol "Hapus" pada salah satu baris diklik
function hapus(id) {
    // confirm() adalah dialog konfirmasi bawaan browser -- kalau user klik "Cancel", fungsi berhenti di sini
    if (confirm('Yakin hapus catatan ini?')) {
        // router.delete: kirim request HTTP DELETE ke route 'log-pekerjaan.destroy' dengan parameter id,
        // dilakukan lewat Inertia (tanpa reload penuh halaman, terasa instan)
        router.delete(route('log-pekerjaan.destroy', id));
    }
}

// Fungsi kecil untuk format tanggal jadi "12 Agu 2026", pengganti filter Django {{ tanggal|date:"d M Y" }}
function formatTanggal(tanggal) {
    // new Date(tanggal) -> ubah string tanggal dari server jadi objek Date JavaScript
    // .toLocaleDateString('id-ID', {...}) -> format sesuai lokal Indonesia dengan opsi yang ditentukan
    return new Date(tanggal).toLocaleDateString('id-ID', {
        day: '2-digit',   // tanggal 2 digit, misal "05" bukan "5"
        month: 'short',    // nama bulan singkat, misal "Agu"
        year: 'numeric',    // tahun 4 digit
    });
}
</script>

<template>
    <!-- Bungkus semua isi halaman dengan layout Breeze (navbar, dropdown user, dsb sudah otomatis ada) -->
    <AuthenticatedLayout>
        <!-- Slot 'header': konten yang tampil di bagian atas layout, di bawah navbar -->
        <template #header>
            <!-- flex justify-between -> judul di kiri, tombol "Tambah" di kanan, sejajar horizontal -->
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Log Pekerjaan / Kejadian Harian
                </h2>
                <!-- Link ke halaman form tambah data baru -->
                <Link :href="route('log-pekerjaan.create')"
                      class="inline-flex items-center px-4 py-2 bg-gray-800 text-white rounded-md text-xs font-semibold uppercase tracking-widest hover:bg-gray-700">
                    + Tambah Catatan Baru
                </Link>
            </div>
        </template>

        <!-- Mengatur judul tab browser -->
        <Head title="Log Pekerjaan" />

        <!-- Container utama halaman -->
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

                <!-- Notifikasi sukses, muncul HANYA kalau ada flash message 'status' dari server -->
                <!-- $page.props.flash datang dari HandleInertiaRequests.php yang sudah dikonfigurasi sebelumnya -->
                <div v-if="$page.props.flash?.status" class="mb-4 p-4 bg-green-100 text-green-800 rounded-md">
                    {{ $page.props.flash.status }}
                </div>

                <!-- Kartu putih pembungkus tabel -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <!-- Header tabel -->
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase w-32">Tanggal</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Keterangan Catatan / Kejadian</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase w-40">PJ</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase w-40">Aksi</th>
                                </tr>
                            </thead>
                            <!-- Isi tabel -->
                            <tbody class="bg-white divide-y divide-gray-200">
                                <!-- v-for: looping array semuaLog.data (baris hasil paginate), pengganti {% for log in semua_log %} Django -->
                                <!-- :key wajib diisi (di sini pakai log.id) supaya Vue bisa lacak tiap baris secara efisien saat data berubah -->
                                <tr v-for="log in semuaLog.data" :key="log.id">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        {{ formatTanggal(log.tanggal) }}
                                    </td>
                                    <!-- whitespace-pre-line -> baris baru (enter) di teks keterangan tetap tampil sebagai baris baru,
                                         bukan digabung jadi satu baris panjang -->
                                    <td class="px-6 py-4 text-sm text-gray-700 whitespace-pre-line">
                                        {{ log.keterangan }}
                                    </td>
                                    <!-- log.pj adalah hasil eager-load relasi belongsTo dari Controller (with('pj')) -->
                                    <!-- ?. (optional chaining) -> kalau log.pj null/tidak ada, tidak error, cuma tidak tampil apa-apa -->
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        {{ log.pj?.name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                                        <!-- Link ke halaman edit, membawa id log ini -->
                                        <Link :href="route('log-pekerjaan.edit', log.id)"
                                              class="text-yellow-600 hover:text-yellow-800 font-medium mr-3">Ubah</Link>
                                        <!-- Tombol hapus, memanggil fungsi hapus() dengan id baris ini -->
                                        <button @click="hapus(log.id)" class="text-red-600 hover:text-red-800 font-medium">Hapus</button>
                                    </td>
                                </tr>
                                <!-- v-if: baris ini HANYA muncul kalau semuaLog.data kosong (belum ada data sama sekali) -->
                                <tr v-if="semuaLog.data.length === 0">
                                    <td colspan="4" class="px-6 py-4 text-center text-gray-400">
                                        Belum ada catatan log pekerjaan.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination: Laravel mengirim array 'links' berisi info tiap tombol halaman + url-nya -->
                    <div class="px-6 py-4 flex flex-wrap gap-1">
                        <!-- template v-for: looping tanpa perlu bikin elemen HTML pembungkus tambahan -->
                        <template v-for="(link, i) in semuaLog.links" :key="i">
                            <!-- Kalau link.url ada isinya (bukan null) -> render sebagai Link yang bisa diklik -->
                            <Link v-if="link.url" :href="link.url"
                                  class="px-3 py-1 text-sm rounded-md"
                                  :class="link.active ? 'bg-gray-800 text-white' : 'bg-white text-gray-700 border hover:bg-gray-50'"
                                  v-html="link.label" />
                            <!-- Kalau link.url null (misal label '...' atau halaman saat ini yang di-disable) -> tampilkan sebagai teks biasa, tidak bisa diklik -->
                            <span v-else class="px-3 py-1 text-sm text-gray-400" v-html="link.label" />
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
