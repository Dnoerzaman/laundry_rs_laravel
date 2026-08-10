<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    semuaPenerimaan: Object,
});

function formatTanggal(tanggal) {
    return new Date(tanggal).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
}
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Riwayat Stok Masuk (Penerimaan Chemical)</h2>
                <Link :href="route('penerimaan-chemical.create')"
                      class="inline-flex items-center px-4 py-2 bg-gray-800 text-white rounded-md text-xs font-semibold uppercase tracking-widest hover:bg-gray-700">
                    Tambah Stok Masuk
                </Link>
            </div>
        </template>

        <Head title="Penerimaan Chemical" />

        <div class="py-12">
            <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">

                <!-- Navigasi cepat antar sub-halaman chemical -->
                <div class="flex gap-4 mb-4 text-sm">
                    <Link :href="route('stok-chemical.index')" class="text-gray-500 hover:text-gray-800 pb-1">Stok Chemical</Link>
                    <Link :href="route('pemakaian-chemical.index')" class="text-gray-500 hover:text-gray-800 pb-1">Riwayat Pemakaian</Link>
                    <span class="font-semibold text-gray-800 border-b-2 border-gray-800 pb-1">Riwayat Stok Masuk</span>
                </div>

                <div v-if="$page.props.flash?.status" class="mb-4 p-4 bg-green-100 text-green-800 rounded-md">
                    {{ $page.props.flash.status }}
                </div>

                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Chemical</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jumlah Masuk</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Petugas</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="row in semuaPenerimaan.data" :key="row.id">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ formatTanggal(row.tanggal) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ row.chemical?.nama_chemical }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-green-700">
                                        + {{ row.jumlah }} {{ row.chemical?.unit }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ row.petugas?.name }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-700">{{ row.keterangan || '-' }}</td>
                                </tr>
                                <tr v-if="semuaPenerimaan.data.length === 0">
                                    <td colspan="5" class="px-6 py-4 text-center text-gray-400">Belum ada riwayat stok masuk.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="px-6 py-4 flex flex-wrap gap-1">
                        <template v-for="(link, i) in semuaPenerimaan.links" :key="i">
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
