<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { reactive } from 'vue';

const props = defineProps({
    penerimaan: Object,
    filters: Object,
    ruanganOptions: Array,
});

const filter = reactive({
    tanggal_dari: props.filters?.tanggal_dari ?? '',
    tanggal_sampai: props.filters?.tanggal_sampai ?? '',
    ruangan: props.filters?.ruangan ?? '',
});

function cari() {
    router.get(
        route('checklist.index'),
        filter,
        {
            preserveState: true,
            preserveScroll: true,
        }
    );
}

function resetFilter() {
    filter.tanggal_dari = '';
    filter.tanggal_sampai = '';
    filter.ruangan = '';

    router.get(route('checklist.index'));
}

function formatTanggal(tanggal) {
    if (!tanggal) return '-';

    const date = new Date(tanggal);

    return date.toLocaleDateString('id-ID', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
    });
}
</script>

<template>
    <AuthenticatedLayout>

        <Head title="Riwayat Penerimaan Linen" />

        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        Riwayat Penerimaan Linen
                    </h2>

                    <p class="text-sm text-gray-500 mt-1">
                        Daftar seluruh penerimaan linen yang telah dicatat.
                    </p>
                </div>

                <Link
                    :href="route('checklist.create')"
                    class="inline-flex items-center justify-center px-4 py-2 bg-gray-800 text-white rounded-md text-xs font-semibold uppercase tracking-widest hover:bg-gray-700"
                >
                    + Penerimaan Baru
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

                <!-- FILTER -->
                <div class="bg-white rounded-lg shadow p-6 mb-6">

                    <h3 class="font-medium text-gray-800 mb-4">
                        Filter Riwayat
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Tanggal Dari
                            </label>

                            <input
                                type="date"
                                v-model="filter.tanggal_dari"
                                class="w-full border-gray-300 rounded-md shadow-sm"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Tanggal Sampai
                            </label>

                            <input
                                type="date"
                                v-model="filter.tanggal_sampai"
                                class="w-full border-gray-300 rounded-md shadow-sm"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Ruangan
                            </label>

                            <select
                                v-model="filter.ruangan"
                                class="w-full border-gray-300 rounded-md shadow-sm"
                            >
                                <option value="">Semua Ruangan</option>

                                <option
                                    v-for="ruangan in ruanganOptions"
                                    :key="ruangan"
                                    :value="ruangan"
                                >
                                    {{ ruangan }}
                                </option>
                            </select>
                        </div>

                        <div class="flex items-end gap-2">
                            <button
                                type="button"
                                @click="cari"
                                class="px-4 py-2 bg-gray-800 text-white rounded-md text-sm hover:bg-gray-700"
                            >
                                Cari
                            </button>

                            <button
                                type="button"
                                @click="resetFilter"
                                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md text-sm hover:bg-gray-300"
                            >
                                Reset
                            </button>
                        </div>

                    </div>
                </div>

                <!-- PESAN BERHASIL -->
                <div
                    v-if="$page.props.flash?.status"
                    class="bg-green-100 border border-green-300 text-green-700 rounded-md px-4 py-3 mb-6"
                >
                    {{ $page.props.flash.status }}
                </div>

                <!-- TABEL -->
                <div class="bg-white rounded-lg shadow overflow-hidden">

                    <div class="px-6 py-4 border-b">
                        <h3 class="font-medium text-gray-800">
                            Data Penerimaan Linen
                        </h3>
                    </div>

                    <div class="overflow-x-auto">

                        <table class="min-w-full divide-y divide-gray-200">

                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        No
                                    </th>

                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Tanggal
                                    </th>

                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Jam
                                    </th>

                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Ruangan
                                    </th>

                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Petugas
                                    </th>

                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">
                                        Item
                                    </th>

                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-200">

                                <tr
                                    v-for="(data, index) in penerimaan.data"
                                    :key="data.id"
                                    class="hover:bg-gray-50"
                                >

                                    <td class="px-4 py-3 text-sm text-gray-700">
                                        {{ penerimaan.from + index }}
                                    </td>

                                    <td class="px-4 py-3 text-sm text-gray-700">
                                        {{ formatTanggal(data.tanggal) }}
                                    </td>

                                    <td class="px-4 py-3 text-sm text-gray-700">
                                        {{ data.jam }}
                                    </td>

                                    <td class="px-4 py-3 text-sm text-gray-700">
                                        {{ data.ruangan }}
                                    </td>

                                    <td class="px-4 py-3 text-sm text-gray-700">
                                        {{ data.petugas?.name ?? '-' }}
                                    </td>

                                    <td class="px-4 py-3 text-sm text-gray-700 text-center">
                                        {{ data.items?.length ?? 0 }}
                                    </td>

                                    <td class="px-4 py-3">
                                        <div class="flex justify-center gap-2">

                                            <Link
                                                :href="route('checklist.show', data.id)"
                                                class="px-3 py-1.5 bg-blue-600 text-white rounded text-xs hover:bg-blue-700"
                                            >
                                                Detail
                                            </Link>

                                            <Link
                                                :href="route('checklist.edit', data.id)"
                                                class="px-3 py-1.5 bg-yellow-500 text-white rounded text-xs hover:bg-yellow-600"
                                            >
                                                Ubah
                                            </Link>

                                        </div>
                                    </td>

                                </tr>

                                <tr v-if="penerimaan.data.length === 0">
                                    <td
                                        colspan="7"
                                        class="px-4 py-8 text-center text-gray-500"
                                    >
                                        Belum ada data penerimaan linen.
                                    </td>
                                </tr>

                            </tbody>

                        </table>

                    </div>

                    <!-- PAGINATION -->
                    <div
                        v-if="penerimaan.links?.length > 3"
                        class="px-6 py-4 border-t flex flex-wrap gap-1"
                    >
                        <template
                            v-for="(link, index) in penerimaan.links"
                            :key="index"
                        >

                            <Link
                                v-if="link.url"
                                :href="link.url"
                                v-html="link.label"
                                class="px-3 py-1.5 text-sm rounded border"
                                :class="link.active
                                    ? 'bg-gray-800 text-white border-gray-800'
                                    : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'"
                                preserve-scroll
                            />

                            <span
                                v-else
                                v-html="link.label"
                                class="px-3 py-1.5 text-sm rounded border text-gray-400"
                            />

                        </template>
                    </div>

                </div>

            </div>
        </div>

    </AuthenticatedLayout>
</template>