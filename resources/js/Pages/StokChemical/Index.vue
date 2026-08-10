<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    semuaStok: Object, // hasil paginate() dari StokChemicalController@index
});

function hapus(id, nama) {
    if (confirm(`Yakin hapus chemical "${nama}"? Riwayat pemakaian/penerimaan yang terkait TIDAK bisa dihapus selama masih ada riwayat transaksinya.`)) {
        router.delete(route('stok-chemical.destroy', id));
    }
}

function formatTanggal(tanggal) {
    return new Date(tanggal).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
}
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center flex-wrap gap-2">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Daftar Stok Chemical</h2>
                <div class="flex gap-2">
                    <!-- Tombol "Catat Pemakaian" -> warna merah, ke halaman form pemakaian -->
                    <Link :href="route('pemakaian-chemical.create')"
                          class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-md text-xs font-semibold uppercase tracking-widest hover:bg-red-700">
                        Catat Pemakaian
                    </Link>
                    <!-- Tombol "Tambah Stok Masuk" -> warna hijau, ke halaman Penerimaan (bukan stok-chemical) -->
                    <Link :href="route('penerimaan-chemical.create')"
                          class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md text-xs font-semibold uppercase tracking-widest hover:bg-green-700">
                        Tambah Stok Masuk
                    </Link>
                    <!-- Tombol "Tambah Chemical Baru" -> untuk master data baru -->
                    <Link :href="route('stok-chemical.create')"
                          class="inline-flex items-center px-4 py-2 bg-gray-800 text-white rounded-md text-xs font-semibold uppercase tracking-widest hover:bg-gray-700">
                        Tambah Chemical Baru
                    </Link>
                </div>
            </div>
        </template>

        <Head title="Stok Chemical" />

        <div class="py-12">
            <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">

                <!-- Navigasi cepat antar sub-halaman chemical -->
                <div class="flex gap-4 mb-4 text-sm">
                    <span class="font-semibold text-gray-800 border-b-2 border-gray-800 pb-1">Stok Chemical</span>
                    <Link :href="route('pemakaian-chemical.index')" class="text-gray-500 hover:text-gray-800 pb-1">Riwayat Pemakaian</Link>
                    <Link :href="route('penerimaan-chemical.index')" class="text-gray-500 hover:text-gray-800 pb-1">Riwayat Stok Masuk</Link>
                </div>

                <div v-if="$page.props.flash?.status" class="mb-4 p-4 bg-green-100 text-green-800 rounded-md">
                    {{ $page.props.flash.status }}
                </div>

                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Chemical</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jumlah Stok</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Satuan</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Update Terakhir</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="stok in semuaStok.data" :key="stok.id">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ stok.nama_chemical }}</td>
                                    <!-- Beri warna merah kalau stok menipis (di bawah 5), sebagai indikator visual tambahan -->
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold"
                                        :class="stok.jumlah_stok < 5 ? 'text-red-600' : 'text-gray-900'">
                                        {{ stok.jumlah_stok }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ stok.unit }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ formatTanggal(stok.update_terakhir) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                                        <Link :href="route('stok-chemical.edit', stok.id)"
                                              class="text-yellow-600 hover:text-yellow-800 font-medium mr-3">Ubah</Link>
                                        <button @click="hapus(stok.id, stok.nama_chemical)" class="text-red-600 hover:text-red-800 font-medium">Hapus</button>
                                    </td>
                                </tr>
                                <tr v-if="semuaStok.data.length === 0">
                                    <td colspan="5" class="px-6 py-4 text-center text-gray-400">Belum ada data stok chemical.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="px-6 py-4 flex flex-wrap gap-1">
                        <template v-for="(link, i) in semuaStok.links" :key="i">
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
