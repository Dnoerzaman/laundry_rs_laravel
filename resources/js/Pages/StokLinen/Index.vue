<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

// Props dari StokLinenController@index
const props = defineProps({
    semuaStokLinen: Object, // hasil paginate()
    ruanganOptions: Array,   // daftar pilihan ruangan untuk dropdown filter
    filterRuangan: String,    // nilai filter yang SEDANG aktif (dikirim balik dari server, bisa null)
});

// Variabel reaktif lokal untuk menampung pilihan filter di dropdown.
// Diisi dari props.filterRuangan supaya kalau halaman di-refresh, dropdown tetap
// menunjukkan filter yang sebelumnya dipilih (bukan reset ke "Semua Ruangan")
const filterRuangan = ref(props.filterRuangan || '');

// watch: fungsi ini otomatis dijalankan setiap kali nilai filterRuangan berubah
// (misal user memilih opsi lain di dropdown)
watch(filterRuangan, (nilaiBaru) => {
    // router.get: kirim request GET baru ke server dengan query string ?ruangan=...
    router.get(
        route('stok-linen.index'),                                  // tujuan: halaman ini juga
        nilaiBaru ? { ruangan: nilaiBaru } : {},                       // kalau filter dikosongkan, jangan kirim parameter ruangan sama sekali
        {
            preserveState: true,  // jangan reset state komponen lain (misal posisi scroll)
            preserveScroll: true,  // jangan scroll balik ke atas halaman
            replace: true,           // ganti entry di history browser (bukan menambah baru), supaya tombol "back" tidak "nyangkut" di tiap perubahan filter
        }
    );
});

// Fungsi untuk mengosongkan filter, dipanggil dari tombol "Reset Filter"
function resetFilter() {
    filterRuangan.value = '';
}

function formatTanggal(tanggal) {
    return new Date(tanggal).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
}
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center flex-wrap gap-2">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Daftar Stok Linen per Ruangan</h2>
                <div class="flex gap-2">
                    <Link :href="route('transaksi-linen.create')"
                          class="inline-flex items-center px-4 py-2 bg-sky-600 text-white rounded-md text-xs font-semibold uppercase tracking-widest hover:bg-sky-700">
                        Catat Transaksi
                    </Link>
                    <Link :href="route('stok-linen.create')"
                          class="inline-flex items-center px-4 py-2 bg-gray-800 text-white rounded-md text-xs font-semibold uppercase tracking-widest hover:bg-gray-700">
                        Tambah Stok Linen
                    </Link>
                </div>
            </div>
        </template>

        <Head title="Stok Linen" />

        <div class="py-12">
            <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">

                <div v-if="$page.props.flash?.status" class="mb-4 p-4 bg-green-100 text-green-800 rounded-md">
                    {{ $page.props.flash.status }}
                </div>

                <!-- ============ BLOK FILTER RUANGAN ============ -->
                <div class="bg-white rounded-lg shadow p-4 mb-4 flex items-center gap-3 flex-wrap">
                    <label for="filter-ruangan" class="text-sm font-medium text-gray-700">Filter Ruangan:</label>
                    <!-- v-model langsung ke filterRuangan; perubahan otomatis memicu watch() di atas -->
                    <select id="filter-ruangan" v-model="filterRuangan"
                            class="border-gray-300 rounded-md shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <!-- value="" -> opsi "semua ruangan", tidak mengirim filter apa pun -->
                        <option value="">-- Semua Ruangan --</option>
                        <option v-for="opt in ruanganOptions" :key="opt" :value="opt">{{ opt }}</option>
                    </select>

                    <!-- Tombol reset hanya tampil kalau ada filter yang sedang aktif -->
                    <button v-if="filterRuangan" @click="resetFilter"
                            class="text-xs text-gray-500 hover:text-gray-800 underline">
                        Reset Filter
                    </button>

                    <!-- Info jumlah hasil, memakai properti 'total' bawaan hasil paginate() Laravel -->
                    <span class="text-xs text-gray-400 ml-auto">
                        Menampilkan {{ semuaStokLinen.total }} data{{ filterRuangan ? ` untuk ruangan "${filterRuangan}"` : '' }}
                    </span>
                </div>
                <!-- ============ AKHIR BLOK FILTER ============ -->

                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ruangan</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Linen</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stok Akhir</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Keterangan</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Update Terakhir</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="linen in semuaStokLinen.data" :key="linen.id">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ linen.ruangan }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ linen.nama_linen }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">{{ linen.stok_akhir }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-700">{{ linen.keterangan || '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ formatTanggal(linen.update_terakhir) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                                        <Link :href="route('stok-linen.edit', linen.id)"
                                              class="text-yellow-600 hover:text-yellow-800 font-medium">Ubah</Link>
                                        <!-- Tidak ada tombol Hapus, sesuai versi Django asli -->
                                    </td>
                                </tr>
                                <tr v-if="semuaStokLinen.data.length === 0">
                                    <td colspan="6" class="px-6 py-4 text-center text-gray-400">
                                        {{ filterRuangan ? `Belum ada data linen untuk ruangan "${filterRuangan}".` : 'Belum ada data linen. Silakan tambah stok linen baru.' }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="px-6 py-4 flex flex-wrap gap-1">
                        <template v-for="(link, i) in semuaStokLinen.links" :key="i">
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
