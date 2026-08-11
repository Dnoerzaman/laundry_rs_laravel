<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

// props.semuaAset adalah hasil paginate() dari AsetController@index
const props = defineProps({
    semuaAset: Object,
});

// Fungsi hapus, dipanggil saat tombol "Hapus" diklik
function hapus(id, namaBarang) {
    // confirm() bawaan browser -> minta konfirmasi dulu sebelum kirim request DELETE
    if (confirm(`Yakin hapus aset "${namaBarang}"?`)) {
        // router.delete: kirim request DELETE lewat Inertia (tanpa reload penuh halaman).
        // Kalau server menolak (karena masih ada riwayat transaksi), pesan errornya otomatis
        // muncul lewat flash message 'error' (lihat blok notifikasi di template bawah)
        router.delete(route('aset.destroy', id));
    }
}

// Fungsi format tanggal, pengganti filter Django {{ tanggal|date:"d M Y" }}
function formatTanggal(tanggal) {
    return new Date(tanggal).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
}
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center flex-wrap gap-2">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Daftar Inventaris Aset</h2>
                <div class="flex gap-2">
                    <Link :href="route('transaksi-aset.create')"
                          class="inline-flex items-center px-4 py-2 bg-sky-600 text-white rounded-md text-xs font-semibold uppercase tracking-widest hover:bg-sky-700">
                        Catat Transaksi
                    </Link>
                    <Link :href="route('aset.create')"
                          class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md text-xs font-semibold uppercase tracking-widest hover:bg-green-700">
                        Tambah Aset Baru
                    </Link>
                </div>
            </div>
        </template>

        <Head title="Daftar Aset" />

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

                <!-- Notifikasi sukses (hijau) -->
                <div v-if="$page.props.flash?.status" class="mb-4 p-4 bg-green-100 text-green-800 rounded-md">
                    {{ $page.props.flash.status }}
                </div>
                <!-- Notifikasi error (merah) -- khusus untuk kasus gagal hapus karena masih ada riwayat transaksi -->
                <div v-if="$page.props.flash?.error" class="mb-4 p-4 bg-red-100 text-red-800 rounded-md">
                    {{ $page.props.flash.error }}
                </div>

                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Barang</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jumlah</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Satuan</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Merk/Tipe</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">SN</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Thn. Pengadaan</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tgl. Input</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="aset in semuaAset.data" :key="aset.id">
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">{{ aset.nama_barang }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm font-semibold text-gray-900">{{ aset.jumlah }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">{{ aset.satuan }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">{{ aset.merk || '-' }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">{{ aset.serial_number || '-' }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">{{ aset.tahun_pengadaan || '-' }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">{{ formatTanggal(aset.tanggal_input) }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-center text-sm">
                                        <Link :href="route('aset.edit', aset.id)"
                                              class="text-yellow-600 hover:text-yellow-800 font-medium mr-3">Ubah</Link>
                                        <button @click="hapus(aset.id, aset.nama_barang)" class="text-red-600 hover:text-red-800 font-medium">Hapus</button>
                                    </td>
                                </tr>
                                <tr v-if="semuaAset.data.length === 0">
                                    <td colspan="8" class="px-4 py-4 text-center text-gray-400">Belum ada data aset.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="px-6 py-4 flex flex-wrap gap-1">
                        <template v-for="(link, i) in semuaAset.links" :key="i">
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
