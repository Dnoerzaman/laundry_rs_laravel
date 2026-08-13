<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

// props.semuaTugas adalah hasil paginate() dari TugasController@index
const props = defineProps({
    semuaTugas: Object,
});

// Objek pemetaan status -> kelas warna Tailwind, dipakai untuk kasih badge warna beda-beda per status.
// Didefinisikan di luar <script setup> logic biasa (langsung sebagai variabel biasa) karena nilainya
// tetap/statis, tidak perlu reaktif
const kelasBadgeStatus = {
    'Selesai': 'bg-green-100 text-green-800',
    'Sedang Dikerjakan': 'bg-yellow-100 text-yellow-800',
    'Belum Dikerjakan': 'bg-gray-100 text-gray-800',
};

// Fungsi hapus, dipanggil saat tombol "Hapus" diklik
function hapus(id, judul) {
    if (confirm(`Yakin hapus rencana kerja "${judul}"?`)) {
        router.delete(route('schedule.destroy', id));
    }
}

// Fungsi memotong teks deskripsi supaya tidak terlalu panjang di tabel,
// pengganti filter Django {{ tugas.deskripsi|truncatewords:15 }}
function potongTeks(teks, jumlahKata = 15) {
    if (!teks) return '-'; // kalau deskripsi kosong/null, tampilkan strip saja
    const kata = teks.split(' ');           // pecah teks jadi array kata per spasi
    if (kata.length <= jumlahKata) return teks; // kalau sudah pendek, tampilkan apa adanya
    return kata.slice(0, jumlahKata).join(' ') + '...'; // ambil N kata pertama, sambung lagi, tambah "..."
}
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Rencana Kerja</h2>
                <Link :href="route('schedule.create')"
                      class="inline-flex items-center px-4 py-2 bg-gray-800 text-white rounded-md text-xs font-semibold uppercase tracking-widest hover:bg-gray-700">
                    + Tambah Rencana Kerja
                </Link>
            </div>
        </template>

        <Head title="Rencana Kerja" />

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

                <div v-if="$page.props.flash?.status" class="mb-4 p-4 bg-green-100 text-green-800 rounded-md">
                    {{ $page.props.flash.status }}
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pekerjaan</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Keterangan</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Penanggung Jawab</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Target Waktu</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Periode</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <!-- v-for: looping array semuaTugas.data (baris hasil paginate) -->
                                <tr v-for="tugas in semuaTugas.data" :key="tugas.id">
                                    <td class="px-6 py-4 text-sm text-gray-700">{{ tugas.judul }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ potongTeks(tugas.deskripsi) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <!-- :class dinamis: ambil warna dari objek kelasBadgeStatus berdasarkan status baris ini.
                                             '?? fallback' -> kalau status tidak ketemu di objek (harusnya tidak pernah terjadi
                                             karena sudah divalidasi), pakai warna abu-abu netral sebagai jaga-jaga -->
                                        <span class="px-2 py-1 rounded-full text-xs font-medium"
                                              :class="kelasBadgeStatus[tugas.status] ?? 'bg-gray-100 text-gray-800'">
                                            {{ tugas.status }}
                                        </span>
                                    </td>
                                    <!-- tugas.penanggungJawab adalah hasil eager-load relasi belongsTo dari Controller.
                                         PENTING: key JSON-nya ikut nama method relasi di model (penanggungJawab, camelCase),
                                         BUKAN otomatis berubah jadi snake_case -- beda dengan nama kolom biasa -->
                                    <!-- ?. (optional chaining) + ?? '-' -> kalau belum ada penanggung jawab (null), tampilkan strip -->
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        {{ tugas.penanggungJawab?.name ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ tugas.target_waktu }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ tugas.periode ?? '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                                        <Link :href="route('schedule.edit', tugas.id)"
                                              class="text-yellow-600 hover:text-yellow-800 font-medium mr-3">Ubah</Link>
                                        <button @click="hapus(tugas.id, tugas.judul)" class="text-red-600 hover:text-red-800 font-medium">Hapus</button>
                                    </td>
                                </tr>
                                <tr v-if="semuaTugas.data.length === 0">
                                    <td colspan="7" class="px-6 py-4 text-center text-gray-400">
                                        Belum ada rencana kerja.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="px-6 py-4 flex flex-wrap gap-1">
                        <template v-for="(link, i) in semuaTugas.links" :key="i">
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
