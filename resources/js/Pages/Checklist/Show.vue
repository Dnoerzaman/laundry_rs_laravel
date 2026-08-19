<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    penerimaan: {
        type: Object,
        required: true,
    },
});

/*
|--------------------------------------------------------------------------
| Helper
|--------------------------------------------------------------------------
*/

/**
 * Format tanggal menjadi:
 * 19 Agustus 2026
 */
function formatTanggal(tanggal) {
    if (!tanggal) {
        return '-';
    }

    const date = new Date(tanggal);

    if (Number.isNaN(date.getTime())) {
        return tanggal;
    }

    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
    }).format(date);
}

/**
 * Format tanggal dan waktu menjadi:
 * 19 Agustus 2026, 08:15
 */
function formatTanggalWaktu(datetime) {
    if (!datetime) {
        return '-';
    }

    const date = new Date(datetime);

    if (Number.isNaN(date.getTime())) {
        return datetime;
    }

    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    }).format(date);
}

/**
 * Menampilkan jam.
 */
function formatJam(jam) {
    if (!jam) {
        return '-';
    }

    return String(jam).slice(0, 5);
}

/**
 * Menentukan badge warna berdasarkan kondisi linen.
 */
function kondisiClass(kondisi) {
    switch (kondisi) {
        case 'Baik':
            return 'bg-green-100 text-green-800';

        case 'Noda':
            return 'bg-yellow-100 text-yellow-800';

        case 'Rusak':
            return 'bg-red-100 text-red-800';

        default:
            return 'bg-gray-100 text-gray-800';
    }
}

/**
 * Menentukan apakah data pernah diperbarui.
 */
function sudahDiperbarui() {
    if (!props.penerimaan.created_at || !props.penerimaan.updated_at) {
        return false;
    }

    return (
        new Date(props.penerimaan.updated_at).getTime() >
        new Date(props.penerimaan.created_at).getTime()
    );
}

/**
 * Mendapatkan nama user yang membuat data.
 *
 * createdBy adalah relasi audit baru.
 */
function namaPembuat() {
    return (
        props.penerimaan.created_by?.name ??
        props.penerimaan.createdBy?.name ??
        props.penerimaan.petugas?.name ??
        '-'
    );
}

/**
 * Mendapatkan nama user yang terakhir mengubah data.
 */
function namaPengubah() {
    return (
        props.penerimaan.updated_by?.name ??
        props.penerimaan.updatedBy?.name ??
        '-'
    );
}
</script>

<template>
    <AuthenticatedLayout>

        <Head title="Detail Penerimaan Linen" />

        <!--
        |--------------------------------------------------------------------------
        | HEADER
        |--------------------------------------------------------------------------
        -->

        <template #header>

            <div
                class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3"
            >

                <div>

                    <h2
                        class="font-semibold text-xl text-gray-800 leading-tight"
                    >
                        Detail Penerimaan Linen
                    </h2>

                    <p class="text-sm text-gray-500 mt-1">
                        Informasi lengkap penerimaan linen dari ruangan.
                    </p>

                </div>

                <div class="flex flex-wrap gap-2">

                    <!-- Kembali ke riwayat -->

                    <Link
                        :href="route('checklist.index')"
                        class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-700 rounded-md text-xs font-semibold uppercase tracking-widest hover:bg-gray-300"
                    >
                        Riwayat
                    </Link>

                    <!-- Edit -->

                    <Link
                        :href="
                            route(
                                'checklist.edit',
                                penerimaan.id
                            )
                        "
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md text-xs font-semibold uppercase tracking-widest hover:bg-indigo-700"
                    >
                        Edit
                    </Link>

                </div>

            </div>

        </template>


        <div class="py-8">

            <div
                class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6"
            >

                <!--
                |--------------------------------------------------------------------------
                | INFORMASI UTAMA
                |--------------------------------------------------------------------------
                -->

                <div
                    class="bg-white rounded-lg shadow p-6"
                >

                    <div
                        class="flex items-center justify-between mb-5"
                    >

                        <div>

                            <h3
                                class="text-lg font-semibold text-gray-800"
                            >
                                Informasi Penerimaan
                            </h3>

                            <p
                                class="text-sm text-gray-500 mt-1"
                            >
                                Data utama transaksi penerimaan linen.
                            </p>

                        </div>

                        <div
                            class="text-sm text-gray-500"
                        >
                            ID:
                            <span
                                class="font-semibold text-gray-700"
                            >
                                #{{ penerimaan.id }}
                            </span>
                        </div>

                    </div>


                    <div
                        class="grid grid-cols-1 md:grid-cols-3 gap-5"
                    >

                        <!-- Tanggal -->

                        <div>

                            <dt
                                class="text-sm font-medium text-gray-500"
                            >
                                Tanggal
                            </dt>

                            <dd
                                class="mt-1 text-sm font-semibold text-gray-900"
                            >
                                {{ formatTanggal(penerimaan.tanggal) }}
                            </dd>

                        </div>


                        <!-- Jam -->

                        <div>

                            <dt
                                class="text-sm font-medium text-gray-500"
                            >
                                Jam Penerimaan
                            </dt>

                            <dd
                                class="mt-1 text-sm font-semibold text-gray-900"
                            >
                                {{ formatJam(penerimaan.jam) }}
                            </dd>

                        </div>


                        <!-- Ruangan -->

                        <div>

                            <dt
                                class="text-sm font-medium text-gray-500"
                            >
                                Ruangan
                            </dt>

                            <dd
                                class="mt-1 text-sm font-semibold text-gray-900"
                            >
                                {{ penerimaan.ruangan || '-' }}
                            </dd>

                        </div>

                    </div>

                </div>


                <!--
                |--------------------------------------------------------------------------
                | DETAIL ITEM LINEN
                |--------------------------------------------------------------------------
                -->

                <div
                    class="bg-white rounded-lg shadow p-6"
                >

                    <div
                        class="flex items-center justify-between mb-5"
                    >

                        <div>

                            <h3
                                class="text-lg font-semibold text-gray-800"
                            >
                                Detail Linen
                            </h3>

                            <p
                                class="text-sm text-gray-500 mt-1"
                            >
                                Daftar linen yang diterima.
                            </p>

                        </div>

                        <div
                            class="text-sm text-gray-500"
                        >
                            Total jenis:
                            <span
                                class="font-semibold text-gray-800"
                            >
                                {{ penerimaan.items?.length ?? 0 }}
                            </span>
                        </div>

                    </div>


                    <div
                        v-if="
                            penerimaan.items &&
                            penerimaan.items.length > 0
                        "
                        class="overflow-x-auto"
                    >

                        <table
                            class="min-w-full divide-y divide-gray-200 border"
                        >

                            <thead class="bg-gray-50">

                                <tr>

                                    <th
                                        class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                    >
                                        No
                                    </th>

                                    <th
                                        class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                    >
                                        Nama Item
                                    </th>

                                    <th
                                        class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase"
                                    >
                                        Jumlah
                                    </th>

                                    <th
                                        class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase"
                                    >
                                        Kondisi
                                    </th>

                                    <th
                                        class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                    >
                                        Keterangan
                                    </th>

                                </tr>

                            </thead>


                            <tbody
                                class="bg-white divide-y divide-gray-200"
                            >

                                <tr
                                    v-for="(
                                        item,
                                        index
                                    ) in penerimaan.items"
                                    :key="item.id ?? index"
                                    class="hover:bg-gray-50"
                                >

                                    <!-- No -->

                                    <td
                                        class="px-4 py-3 text-sm text-gray-600"
                                    >
                                        {{ index + 1 }}
                                    </td>


                                    <!-- Nama Item -->

                                    <td
                                        class="px-4 py-3 text-sm font-medium text-gray-900"
                                    >
                                        {{ item.nama_item }}
                                    </td>


                                    <!-- Jumlah -->

                                    <td
                                        class="px-4 py-3 text-sm text-gray-900 text-center font-semibold"
                                    >
                                        {{ item.jumlah }}
                                    </td>


                                    <!-- Kondisi -->

                                    <td
                                        class="px-4 py-3 text-center"
                                    >

                                        <span
                                            class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium"
                                            :class="
                                                kondisiClass(
                                                    item.kondisi
                                                )
                                            "
                                        >
                                            {{ item.kondisi }}
                                        </span>

                                    </td>


                                    <!-- Keterangan -->

                                    <td
                                        class="px-4 py-3 text-sm text-gray-600"
                                    >
                                        {{ item.keterangan || '-' }}
                                    </td>

                                </tr>

                            </tbody>

                        </table>

                    </div>


                    <!-- Tidak ada item -->

                    <div
                        v-else
                        class="border border-dashed rounded-lg p-8 text-center"
                    >

                        <p
                            class="text-sm text-gray-500"
                        >
                            Tidak ada detail item linen pada penerimaan ini.
                        </p>

                    </div>

                </div>


                <!--
                |--------------------------------------------------------------------------
                | AUDIT TRAIL
                |--------------------------------------------------------------------------
                -->

                <div
                    class="bg-white rounded-lg shadow p-6"
                >

                    <div class="mb-5">

                        <h3
                            class="text-lg font-semibold text-gray-800"
                        >
                            Informasi Audit
                        </h3>

                        <p
                            class="text-sm text-gray-500 mt-1"
                        >
                            Informasi pengguna yang membuat dan terakhir
                            mengubah data penerimaan.
                        </p>

                    </div>


                    <div
                        class="grid grid-cols-1 md:grid-cols-2 gap-6"
                    >

                        <!--
                        |--------------------------------------------------------------------------
                        | CREATED
                        |--------------------------------------------------------------------------
                        -->

                        <div
                            class="border rounded-lg p-4 bg-gray-50"
                        >

                            <div
                                class="flex items-center gap-2 mb-4"
                            >

                                <div
                                    class="w-9 h-9 rounded-full bg-green-100 flex items-center justify-center"
                                >
                                    <svg
                                        class="w-5 h-5 text-green-600"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M12 4v16m8-8H4"
                                        />
                                    </svg>
                                </div>

                                <div>

                                    <h4
                                        class="font-semibold text-gray-800"
                                    >
                                        Data Dibuat
                                    </h4>

                                    <p
                                        class="text-xs text-gray-500"
                                    >
                                        Informasi pembuatan data
                                    </p>

                                </div>

                            </div>


                            <div class="space-y-3">

                                <div>

                                    <p
                                        class="text-xs text-gray-500"
                                    >
                                        Dibuat oleh
                                    </p>

                                    <p
                                        class="text-sm font-semibold text-gray-900"
                                    >
                                        {{ namaPembuat() }}
                                    </p>

                                </div>


                                <div>

                                    <p
                                        class="text-xs text-gray-500"
                                    >
                                        Dibuat pada
                                    </p>

                                    <p
                                        class="text-sm text-gray-900"
                                    >
                                        {{
                                            formatTanggalWaktu(
                                                penerimaan.created_at
                                            )
                                        }}
                                    </p>

                                </div>

                            </div>

                        </div>


                        <!--
                        |--------------------------------------------------------------------------
                        | UPDATED
                        |--------------------------------------------------------------------------
                        -->

                        <div
                            class="border rounded-lg p-4 bg-gray-50"
                        >

                            <div
                                class="flex items-center gap-2 mb-4"
                            >

                                <div
                                    class="w-9 h-9 rounded-full bg-indigo-100 flex items-center justify-center"
                                >
                                    <svg
                                        class="w-5 h-5 text-indigo-600"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.5-8.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 8.5-8.5z"
                                        />
                                    </svg>
                                </div>

                                <div>

                                    <h4
                                        class="font-semibold text-gray-800"
                                    >
                                        Perubahan Terakhir
                                    </h4>

                                    <p
                                        class="text-xs text-gray-500"
                                    >
                                        Informasi perubahan terakhir
                                    </p>

                                </div>

                            </div>


                            <div class="space-y-3">

                                <div>

                                    <p
                                        class="text-xs text-gray-500"
                                    >
                                        Diubah oleh
                                    </p>

                                    <p
                                        class="text-sm font-semibold text-gray-900"
                                    >
                                        {{
                                            sudahDiperbarui()
                                                ? namaPengubah()
                                                : namaPembuat()
                                        }}
                                    </p>

                                </div>


                                <div>

                                    <p
                                        class="text-xs text-gray-500"
                                    >
                                        Diubah pada
                                    </p>

                                    <p
                                        class="text-sm text-gray-900"
                                    >
                                        {{
                                            sudahDiperbarui()
                                                ? formatTanggalWaktu(
                                                      penerimaan.updated_at
                                                  )
                                                : formatTanggalWaktu(
                                                      penerimaan.created_at
                                                  )
                                        }}
                                    </p>

                                </div>

                            </div>

                        </div>

                    </div>


                    <!--
                    |--------------------------------------------------------------------------
                    | INFORMASI KOMPATIBILITAS DATA LAMA
                    |--------------------------------------------------------------------------
                    -->

                    <div
                        v-if="
                            !penerimaan.created_at ||
                            !penerimaan.updated_at
                        "
                        class="mt-5 p-4 bg-yellow-50 border border-yellow-200 rounded-lg"
                    >

                        <div
                            class="flex gap-3"
                        >

                            <svg
                                class="w-5 h-5 text-yellow-600 flex-shrink-0"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 9v2m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.67 1.73-3L13.73 4a2 2 0 00-3.46 0L3.34 16c-.77 1.33.19 3 1.73 3z"
                                />
                            </svg>

                            <div>

                                <p
                                    class="text-sm font-medium text-yellow-800"
                                >
                                    Informasi audit belum tersedia
                                </p>

                                <p
                                    class="text-xs text-yellow-700 mt-1"
                                >
                                    Data ini dibuat sebelum fitur audit trail
                                    diterapkan.
                                </p>

                            </div>

                        </div>

                    </div>

                </div>


                <!--
                |--------------------------------------------------------------------------
                | FOOTER ACTION
                |--------------------------------------------------------------------------
                -->

                <div
                    class="flex flex-wrap justify-between items-center gap-3"
                >

                    <Link
                        :href="route('checklist.index')"
                        class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-700 rounded-md text-xs font-semibold uppercase tracking-widest hover:bg-gray-300"
                    >
                        ← Kembali ke Riwayat
                    </Link>


                    <Link
                        :href="
                            route(
                                'checklist.edit',
                                penerimaan.id
                            )
                        "
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md text-xs font-semibold uppercase tracking-widest hover:bg-indigo-700"
                    >
                        Ubah Data
                    </Link>

                </div>

            </div>

        </div>

    </AuthenticatedLayout>
</template>