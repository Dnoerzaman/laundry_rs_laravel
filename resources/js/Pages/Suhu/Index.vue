<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

// props.semuaSuhu adalah hasil paginate() dari SuhuController@index
const props = defineProps({
    semuaSuhu: Object,
});

// Fungsi hapus, dipanggil saat tombol "Hapus" diklik
function hapus(id) {
    if (confirm('Yakin hapus catatan pemantauan ini?')) {
        router.delete(route('suhu.destroy', id));
    }
}

// Fungsi format tanggal, pengganti filter Django {{ tanggal|date:"d M Y" }}
function formatTanggal(tanggal) {
    return new Date(tanggal).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
}

// Fungsi format jam, ambil HH:MM saja dari string waktu yang dikirim server (bisa berformat HH:MM:SS)
function formatJam(jam) {
    return jam?.slice(0, 5);
}

// Menentukan kelas warna untuk suhu -- MERAH kalau di luar standar 22.0-27.0°C, HIJAU kalau normal.
// Ini pengganti langsung dari kondisi Django: {% if item.suhu > 27 or item.suhu < 22 %}text-danger{% else %}text-success{% endif %}
function kelasSuhu(suhu) {
    return (suhu > 27 || suhu < 22) ? 'text-red-600' : 'text-green-600';
}

// Menentukan kelas warna untuk kelembaban -- KUNING/WARNING kalau di luar 40-60%, HIJAU kalau normal
function kelasKelembaban(kelembaban) {
    return (kelembaban > 60 || kelembaban < 40) ? 'text-amber-600' : 'text-green-600';
}
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Monitoring Suhu & Kelembaban Ruangan</h2>
                <Link :href="route('suhu.create')"
                      class="inline-flex items-center px-4 py-2 bg-gray-800 text-white rounded-md text-xs font-semibold uppercase tracking-widest hover:bg-gray-700">
                    Tambah Catatan Baru
                </Link>
            </div>
        </template>

        <Head title="Monitoring Suhu" />

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

                <div v-if="$page.props.flash?.status" class="mb-4 p-4 bg-green-100 text-green-800 rounded-md">
                    {{ $page.props.flash.status }}
                </div>

                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jam</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ruangan / Area</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Shift</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Suhu (°C)</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Kelembaban (%)</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Petugas</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Keterangan</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="item in semuaSuhu.data" :key="item.id">
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">{{ formatTanggal(item.tanggal) }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 font-semibold">{{ formatJam(item.jam) }} WIB</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm">
                                        <!-- Badge abu-abu untuk nama ruangan, meniru <span class="badge bg-secondary"> Django -->
                                        <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-gray-200 text-gray-700">{{ item.ruangan }}</span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">{{ item.waktu_ukur }}</td>
                                    <!-- :class dinamis dari fungsi kelasSuhu() -- otomatis merah kalau di luar standar -->
                                    <td class="px-4 py-3 whitespace-nowrap text-center text-sm font-bold" :class="kelasSuhu(item.suhu)">
                                        {{ item.suhu }} °C
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-center text-sm font-bold" :class="kelasKelembaban(item.kelembaban)">
                                        {{ item.kelembaban }} %
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">{{ item.petugas?.name }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700">{{ item.keterangan || '-' }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-center text-sm">
                                        <Link :href="route('suhu.edit', item.id)"
                                              class="text-yellow-600 hover:text-yellow-800 font-medium mr-3">Ubah</Link>
                                        <button @click="hapus(item.id)" class="text-red-600 hover:text-red-800 font-medium">Hapus</button>
                                    </td>
                                </tr>
                                <tr v-if="semuaSuhu.data.length === 0">
                                    <td colspan="9" class="px-4 py-4 text-center text-gray-400">Belum ada riwayat pencatatan suhu & kelembaban.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="px-6 py-4 flex flex-wrap gap-1">
                        <template v-for="(link, i) in semuaSuhu.links" :key="i">
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
