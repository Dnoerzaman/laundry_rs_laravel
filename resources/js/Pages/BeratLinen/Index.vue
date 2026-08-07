<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

// props.semuaDataBerat adalah objek hasil paginate() dari Laravel, isinya:
// { data: [...baris...], links: [...link pagination...], current_page, last_page, dst }
const props = defineProps({
    semuaDataBerat: Object,
});

// Fungsi hapus data, dipanggil saat tombol "Hapus" diklik
function hapus(id) {
    // confirm() bawaan browser, setara onsubmit="return confirm(...)" di versi Blade
    if (confirm('Yakin hapus data berat linen ini?')) {
        // router.delete: kirim request DELETE ke server lewat Inertia (tanpa reload halaman)
        router.delete(route('berat-linen.destroy', id));
    }
}

// Fungsi kecil untuk format tanggal jadi "06 Agu 2026", pengganti filter Django {{ data.tanggal|date:"d M Y" }}
function formatTanggal(tanggal) {
    return new Date(tanggal).toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    });
}
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Data Berat Linen Kotor Harian
                </h2>
                <Link :href="route('berat-linen.create')"
                      class="inline-flex items-center px-4 py-2 bg-gray-800 text-white rounded-md text-xs font-semibold uppercase tracking-widest hover:bg-gray-700">
                    + Tambah Catatan Berat
                </Link>
            </div>
        </template>

        <Head title="Data Berat Linen" />

        <div class="py-12">
            <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">

                <!-- Notifikasi sukses dari session flash (misal setelah simpan/hapus data) -->
                <div v-if="$page.props.flash?.status" class="mb-4 p-4 bg-green-100 text-green-800 rounded-md">
                    {{ $page.props.flash.status }}
                </div>

                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ruangan</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Shift</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Berat (Kg)</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Petugas</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <!-- v-for looping data.data (array baris hasil paginate) -->
                                <tr v-for="row in semuaDataBerat.data" :key="row.id">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ formatTanggal(row.tanggal) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ row.ruangan }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ row.shift }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">{{ row.total_berat }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ row.petugas?.name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                                        <Link :href="route('berat-linen.edit', row.id)"
                                              class="text-yellow-600 hover:text-yellow-800 font-medium mr-3">Ubah</Link>
                                        <button @click="hapus(row.id)" class="text-red-600 hover:text-red-800 font-medium">Hapus</button>
                                    </td>
                                </tr>
                                <!-- Baris fallback kalau data kosong -->
                                <tr v-if="semuaDataBerat.data.length === 0">
                                    <td colspan="6" class="px-6 py-4 text-center text-gray-400">
                                        Belum ada data berat linen.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination: Laravel mengirim array 'links' berisi nomor halaman + url-nya -->
                    <div class="px-6 py-4 flex flex-wrap gap-1">
                        <template v-for="(link, i) in semuaDataBerat.links" :key="i">
                            <!-- Link biasa kalau link.url tidak null -->
                            <Link v-if="link.url" :href="link.url"
                                  class="px-3 py-1 text-sm rounded-md"
                                  :class="link.active ? 'bg-gray-800 text-white' : 'bg-white text-gray-700 border hover:bg-gray-50'"
                                  v-html="link.label" />
                            <!-- Kalau url null (misal label '...' atau halaman disabled), tampilkan sebagai teks biasa -->
                            <span v-else class="px-3 py-1 text-sm text-gray-400" v-html="link.label" />
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
