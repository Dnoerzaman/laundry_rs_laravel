<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    penerimaan: Object,
});

function formatTanggal(tanggal) {
    if (!tanggal) return '-';

    const date = new Date(tanggal);

    return date.toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
    });
}
</script>

<template>
    <AuthenticatedLayout>

        <Head title="Detail Penerimaan Linen" />

        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">

                <div>
                    <h2 class="font-semibold text-xl text-gray-800">
                        Detail Penerimaan Linen
                    </h2>

                    <p class="text-sm text-gray-500 mt-1">
                        Detail data penerimaan linen.
                    </p>
                </div>

                <div class="flex gap-2">

                    <Link
                        :href="route('checklist.index')"
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md text-xs font-semibold uppercase hover:bg-gray-300"
                    >
                        Kembali
                    </Link>

                    <Link
                        :href="route('checklist.edit', penerimaan.id)"
                        class="px-4 py-2 bg-yellow-500 text-white rounded-md text-xs font-semibold uppercase hover:bg-yellow-600"
                    >
                        Ubah
                    </Link>

                </div>

            </div>
        </template>

        <div class="py-8">

            <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">

                <!-- PESAN -->
                <div
                    v-if="$page.props.flash?.status"
                    class="bg-green-100 border border-green-300 text-green-700 rounded-md px-4 py-3 mb-6"
                >
                    {{ $page.props.flash.status }}
                </div>

                <!-- INFORMASI HEADER -->
                <div class="bg-white rounded-lg shadow p-6 mb-6">

                    <h3 class="font-medium text-gray-800 mb-5">
                        Informasi Penerimaan
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-5">

                        <div>
                            <p class="text-xs text-gray-500 uppercase">
                                Tanggal
                            </p>

                            <p class="font-medium text-gray-800 mt-1">
                                {{ formatTanggal(penerimaan.tanggal) }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-gray-500 uppercase">
                                Jam
                            </p>

                            <p class="font-medium text-gray-800 mt-1">
                                {{ penerimaan.jam }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-gray-500 uppercase">
                                Ruangan
                            </p>

                            <p class="font-medium text-gray-800 mt-1">
                                {{ penerimaan.ruangan }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-gray-500 uppercase">
                                Petugas
                            </p>

                            <p class="font-medium text-gray-800 mt-1">
                                {{ penerimaan.petugas?.name ?? '-' }}
                            </p>
                        </div>

                    </div>

                </div>

                <!-- DETAIL ITEM -->
                <div class="bg-white rounded-lg shadow overflow-hidden">

                    <div class="px-6 py-4 border-b">
                        <h3 class="font-medium text-gray-800">
                            Detail Item Linen
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
                                        Nama Item
                                    </th>

                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">
                                        Jumlah
                                    </th>

                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">
                                        Kondisi
                                    </th>

                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Keterangan
                                    </th>

                                </tr>

                            </thead>

                            <tbody class="divide-y divide-gray-200">

                                <tr
                                    v-for="(item, index) in penerimaan.items"
                                    :key="item.id"
                                >

                                    <td class="px-4 py-3 text-sm">
                                        {{ index + 1 }}
                                    </td>

                                    <td class="px-4 py-3 text-sm font-medium text-gray-800">
                                        {{ item.nama_item }}
                                    </td>

                                    <td class="px-4 py-3 text-sm text-center">
                                        {{ item.jumlah }}
                                    </td>

                                    <td class="px-4 py-3 text-sm text-center">

                                        <span
                                            class="inline-flex px-2 py-1 rounded-full text-xs font-medium"
                                            :class="{
                                                'bg-green-100 text-green-700': item.kondisi === 'Baik',
                                                'bg-yellow-100 text-yellow-700': item.kondisi === 'Noda',
                                                'bg-red-100 text-red-700': item.kondisi === 'Rusak',
                                            }"
                                        >
                                            {{ item.kondisi }}
                                        </span>

                                    </td>

                                    <td class="px-4 py-3 text-sm text-gray-600">
                                        {{ item.keterangan || '-' }}
                                    </td>

                                </tr>

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        </div>

    </AuthenticatedLayout>
</template>