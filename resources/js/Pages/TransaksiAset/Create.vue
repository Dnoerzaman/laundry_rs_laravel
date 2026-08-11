<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

// Props dari TransaksiAsetController@create
const props = defineProps({
    asetOptions: Array,     // daftar aset untuk dropdown, {id, nama_barang, jumlah, satuan}
    riwayatTransaksi: Array, // 10 transaksi terakhir, untuk kolom kanan
});

const form = useForm({
    aset_id: props.asetOptions[0]?.id ?? null,
    jenis_transaksi: 'PENAMBAHAN', // default: penambahan
    jumlah: null,
    tanggal: new Date().toISOString().slice(0, 10),
    keterangan: '',
});

// computed: cari data aset lengkap yang sedang dipilih di dropdown,
// dipakai untuk menampilkan info "Jumlah saat ini: X unit" di bawah dropdown
const asetTerpilih = computed(() => {
    return props.asetOptions.find(a => a.id === form.aset_id);
});

function submit() {
    form.post(route('transaksi-aset.store'), {
        // Setelah berhasil, kosongkan field jumlah & keterangan saja (bukan semua field),
        // supaya user bisa langsung catat transaksi berikutnya untuk aset yang sama dengan cepat
        onSuccess: () => {
            form.reset('jumlah', 'keterangan');
        },
    });
}

function formatTanggal(tanggal) {
    return new Date(tanggal).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
}
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Catat Transaksi Aset</h2>
        </template>

        <Head title="Catat Transaksi Aset" />

        <div class="py-12">
            <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">

                <div v-if="$page.props.flash?.status" class="mb-4 p-4 bg-green-100 text-green-800 rounded-md">
                    {{ $page.props.flash.status }}
                </div>

                <!-- Grid 2 kolom: form di kiri (5/12), riwayat di kanan (7/12) -->
                <div class="grid grid-cols-1 md:grid-cols-12 gap-6">

                    <!-- ===== KOLOM KIRI: FORM INPUT ===== -->
                    <div class="md:col-span-5">
                        <div class="bg-white rounded-lg shadow p-6">
                            <h3 class="font-medium text-gray-800 mb-4">Form Transaksi</h3>
                            <form @submit.prevent="submit">

                                <!-- Dropdown aset -->
                                <div class="mb-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Aset</label>
                                    <select v-model.number="form.aset_id"
                                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option v-for="opt in asetOptions" :key="opt.id" :value="opt.id">{{ opt.nama_barang }}</option>
                                    </select>
                                    <p v-if="form.errors.aset_id" class="text-sm text-red-600 mt-1">{{ form.errors.aset_id }}</p>
                                </div>

                                <!-- Info jumlah saat ini, otomatis update sesuai pilihan dropdown -->
                                <p v-if="asetTerpilih" class="text-xs text-gray-500 mb-4">
                                    Jumlah saat ini: <span class="font-semibold">{{ asetTerpilih.jumlah }} {{ asetTerpilih.satuan }}</span>
                                </p>

                                <!-- Dropdown jenis transaksi -->
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Transaksi</label>
                                    <select v-model="form.jenis_transaksi"
                                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="PENAMBAHAN">Penambahan</option>
                                        <option value="PENGURANGAN">Pengurangan</option>
                                    </select>
                                    <p v-if="form.errors.jenis_transaksi" class="text-sm text-red-600 mt-1">{{ form.errors.jenis_transaksi }}</p>
                                </div>

                                <!-- Jumlah -->
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah</label>
                                    <input type="number" min="1" step="1" v-model.number="form.jumlah"
                                           class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                                    <!-- Pesan error "jumlah tidak mencukupi" dari backend akan muncul di sini -->
                                    <p v-if="form.errors.jumlah" class="text-sm text-red-600 mt-1">{{ form.errors.jumlah }}</p>
                                </div>

                                <!-- Tanggal -->
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal</label>
                                    <input type="date" v-model="form.tanggal"
                                           class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                                    <p v-if="form.errors.tanggal" class="text-sm text-red-600 mt-1">{{ form.errors.tanggal }}</p>
                                </div>

                                <!-- Keterangan -->
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label>
                                    <textarea v-model="form.keterangan" rows="2"
                                              class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                                </div>

                                <div class="grid gap-2">
                                    <button type="submit" :disabled="form.processing"
                                            class="w-full inline-flex justify-center items-center px-4 py-2 bg-gray-800 text-white rounded-md text-xs font-semibold uppercase tracking-widest hover:bg-gray-700 disabled:opacity-50">
                                        Simpan Transaksi
                                    </button>
                                    <Link :href="route('aset.index')"
                                          class="w-full inline-flex justify-center items-center px-4 py-2 bg-white border border-gray-300 rounded-md text-xs font-semibold text-gray-700 uppercase tracking-widest hover:bg-gray-50">
                                        Kembali ke Daftar Aset
                                    </Link>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- ===== KOLOM KANAN: RIWAYAT TRANSAKSI ===== -->
                    <div class="md:col-span-7">
                        <div class="bg-white rounded-lg shadow p-6">
                            <h3 class="font-medium text-gray-800 mb-4">Riwayat Transaksi Terakhir</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 text-sm">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Aset</th>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Jenis</th>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Jumlah</th>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Petugas</th>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Keterangan</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200">
                                        <tr v-for="trx in riwayatTransaksi" :key="trx.id">
                                            <td class="px-3 py-2 whitespace-nowrap text-gray-700">{{ formatTanggal(trx.tanggal) }}</td>
                                            <td class="px-3 py-2 text-gray-700">{{ trx.aset?.nama_barang }}</td>
                                            <td class="px-3 py-2 whitespace-nowrap">
                                                <!-- Badge hijau untuk Penambahan, merah untuk Pengurangan -->
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium"
                                                      :class="trx.jenis_transaksi === 'PENAMBAHAN' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                                                    {{ trx.jenis_transaksi === 'PENAMBAHAN' ? 'Penambahan' : 'Pengurangan' }}
                                                </span>
                                            </td>
                                            <td class="px-3 py-2 whitespace-nowrap font-semibold text-gray-900">{{ trx.jumlah }}</td>
                                            <td class="px-3 py-2 whitespace-nowrap text-gray-700">{{ trx.petugas?.name }}</td>
                                            <td class="px-3 py-2 text-gray-700">{{ trx.keterangan || '-' }}</td>
                                        </tr>
                                        <tr v-if="riwayatTransaksi.length === 0">
                                            <td colspan="6" class="px-3 py-4 text-center text-gray-400">Belum ada riwayat transaksi.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
