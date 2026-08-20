<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { reactive } from 'vue';

const props = defineProps({
    filters: {
        type: Object,
        required: true,
    },

    ruanganOptions: {
        type: Array,
        default: () => [],
    },

    summary: {
        type: Object,
        required: true,
    },

    rekapRuangan: {
        type: Array,
        default: () => [],
    },

    rekapJenisLinen: {
        type: Array,
        default: () => [],
    },

    trenHarian: {
        type: Array,
        default: () => [],
    },

    ruanganBermasalah: {
        type: Array,
        default: () => [],
    },

    linenBermasalah: {
        type: Array,
        default: () => [],
    },
});

/*
|--------------------------------------------------------------------------
| GRAFIK SVG TREN
|--------------------------------------------------------------------------
*/

function chartMax() {
    const values = props.trenHarian.map(
        item => Number(item.total_linen)
    );

    if (!values.length) {
        return 10;
    }

    return Math.max(...values, 10);
}

function chartX(index) {
    if (props.trenHarian.length <= 1) {
        return 50;
    }

    return 50 +
        (
            index /
            (props.trenHarian.length - 1)
        ) * 900;
}

function chartY(value) {
    const max = chartMax();

    return 230 -
        (
            Number(value) / max
        ) * 190;
}

function chartPoints() {
    return props.trenHarian
        .map((item, index) => {
            return `${chartX(index)},${chartY(item.total_linen)}`;
        })
        .join(' ');
}

/*
|--------------------------------------------------------------------------
| FILTER FORM
|--------------------------------------------------------------------------
*/

const form = reactive({
    tanggal_mulai: props.filters.tanggal_mulai ?? '',
    tanggal_akhir: props.filters.tanggal_akhir ?? '',
    ruangan: props.filters.ruangan ?? '',
});

/*
|--------------------------------------------------------------------------
| FILTER
|--------------------------------------------------------------------------
*/

function tampilkanRekap() {
    router.get(
        route('checklist.rekap'),
        {
            tanggal_mulai: form.tanggal_mulai,
            tanggal_akhir: form.tanggal_akhir,
            ruangan: form.ruangan || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        }
    );
}

/*
|--------------------------------------------------------------------------
| RESET FILTER
|--------------------------------------------------------------------------
*/

function resetFilter() {
    const today = new Date();

    const firstDay = new Date(
        today.getFullYear(),
        today.getMonth(),
        1
    );

    const formatDate = (date) => {
        const year = date.getFullYear();

        const month = String(
            date.getMonth() + 1
        ).padStart(2, '0');

        const day = String(
            date.getDate()
        ).padStart(2, '0');

        return `${year}-${month}-${day}`;
    };

    form.tanggal_mulai = formatDate(firstDay);
    form.tanggal_akhir = formatDate(today);
    form.ruangan = '';

    tampilkanRekap();
}

/*
|--------------------------------------------------------------------------
| FORMAT
|--------------------------------------------------------------------------
*/

function formatNumber(value) {
    return new Intl.NumberFormat('id-ID').format(value ?? 0);
}

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
        month: 'short',
        year: 'numeric',
    }).format(date);
}

/*
|--------------------------------------------------------------------------
| PERSENTASE
|--------------------------------------------------------------------------
*/

function formatPersentase(value) {
    return `${Number(value ?? 0).toFixed(2)}%`;
}

/*
|--------------------------------------------------------------------------
| KONDISI BADGE
|--------------------------------------------------------------------------
*/

function kondisiBadgeClass(kondisi) {
    if (kondisi === 'Baik') {
        return 'bg-green-100 text-green-800';
    }

    if (kondisi === 'Noda') {
        return 'bg-yellow-100 text-yellow-800';
    }

    if (kondisi === 'Rusak') {
        return 'bg-red-100 text-red-800';
    }

    return 'bg-gray-100 text-gray-800';
}

/*
|--------------------------------------------------------------------------
| JUMLAH HARI
|--------------------------------------------------------------------------
*/

function jumlahHari() {
    if (!form.tanggal_mulai || !form.tanggal_akhir) {
        return 0;
    }

    const mulai = new Date(form.tanggal_mulai);
    const akhir = new Date(form.tanggal_akhir);

    const selisih = akhir.getTime() - mulai.getTime();

    return Math.floor(selisih / (1000 * 60 * 60 * 24)) + 1;
}
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Rekap Penerimaan Linen" />

        <!--
        |--------------------------------------------------------------------------
        | HEADER
        |--------------------------------------------------------------------------
        -->
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        Rekap Penerimaan Linen
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">
                        Rekapitulasi penerimaan linen berdasarkan periode dan ruangan.
                    </p>
                </div>

                <div class="flex flex-wrap gap-2">
                    <a
                        :href="route('checklist.rekap.pdf', {
                            tanggal_mulai: form.tanggal_mulai,
                            tanggal_akhir: form.tanggal_akhir,
                            ruangan: form.ruangan || undefined,
                        })"
                        class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-md text-xs font-semibold uppercase tracking-widest hover:bg-red-700"
                    >
                        PDF
                    </a>

                    <a
                        :href="route('checklist.rekap.excel', {
                            tanggal_mulai: form.tanggal_mulai,
                            tanggal_akhir: form.tanggal_akhir,
                            ruangan: form.ruangan || undefined,
                        })"
                        class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md text-xs font-semibold uppercase tracking-widest hover:bg-green-700"
                    >
                        Excel
                    </a>

                    <Link
                        :href="route('checklist.index')"
                        class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-700 rounded-md text-xs font-semibold uppercase tracking-widest hover:bg-gray-300"
                    >
                        Riwayat
                    </Link>

                    <Link
                        :href="route('checklist.create')"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md text-xs font-semibold uppercase tracking-widest hover:bg-indigo-700"
                    >
                        + Penerimaan Baru
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <!--
                |--------------------------------------------------------------------------
                | FILTER
                |--------------------------------------------------------------------------
                -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="mb-5">
                        <h3 class="text-lg font-semibold text-gray-800">
                            Filter Laporan
                        </h3>
                        <p class="text-sm text-gray-500 mt-1">
                            Pilih periode dan ruangan yang ingin dianalisis.
                        </p>
                    </div>

                    <form
                        @submit.prevent="tampilkanRekap"
                        class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end"
                    >
                        <!-- Tanggal mulai -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Tanggal Mulai
                            </label>
                            <input
                                v-model="form.tanggal_mulai"
                                type="date"
                                required
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            />
                        </div>

                        <!-- Tanggal akhir -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Tanggal Akhir
                            </label>
                            <input
                                v-model="form.tanggal_akhir"
                                type="date"
                                required
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            />
                        </div>

                        <!-- Ruangan -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Ruangan
                            </label>
                            <select
                                v-model="form.ruangan"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
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

                        <!-- Tombol -->
                        <div class="flex gap-2">
                            <button
                                type="submit"
                                class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-md text-xs font-semibold uppercase tracking-widest hover:bg-indigo-700"
                            >
                                Tampilkan
                            </button>

                            <button
                                type="button"
                                @click="resetFilter"
                                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md text-xs font-semibold uppercase hover:bg-gray-300"
                            >
                                Reset
                            </button>
                        </div>
                    </form>

                    <div class="mt-4 text-sm text-gray-500">
                        Periode:
                        <span class="font-semibold text-gray-700">{{ form.tanggal_mulai }}</span>
                        sampai
                        <span class="font-semibold text-gray-700">{{ form.tanggal_akhir }}</span>
                        <span class="mx-2">•</span>
                        {{ jumlahHari() }} hari
                    </div>
                </div>

                <!--
                |--------------------------------------------------------------------------
                | KPI
                |--------------------------------------------------------------------------
                -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Total transaksi -->
                    <div class="bg-white rounded-lg shadow p-5 border-l-4 border-indigo-500">
                        <p class="text-sm font-medium text-gray-500">Total Transaksi</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900">
                            {{ formatNumber(summary.total_transaksi) }}
                        </p>
                        <p class="mt-1 text-xs text-gray-500">Penerimaan linen</p>
                    </div>

                    <!-- Status Laporan -->
                    <div
                        class="bg-white rounded-lg shadow p-5 border-l-4"
                        :class="
                            summary.status_laporan === 'Baik'
                                ? 'border-green-500'
                                : summary.status_laporan === 'Waspada'
                                    ? 'border-yellow-500'
                                    : 'border-red-500'
                        "
                    >
                        <p class="text-sm font-medium text-gray-500">Status Laporan</p>
                        <p
                            class="mt-2 text-2xl font-bold"
                            :class="
                                summary.status_laporan === 'Baik'
                                    ? 'text-green-700'
                                    : summary.status_laporan === 'Waspada'
                                        ? 'text-yellow-700'
                                        : 'text-red-700'
                            "
                        >
                            {{ summary.status_laporan }}
                        </p>
                        <p class="mt-1 text-xs text-gray-500">
                            Berdasarkan persentase linen bermasalah
                        </p>
                    </div>

                    <!-- Total linen -->
                    <div class="bg-white rounded-lg shadow p-5 border-l-4 border-blue-500">
                        <p class="text-sm font-medium text-gray-500">Total Linen</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900">
                            {{ formatNumber(summary.total_linen) }}
                        </p>
                        <p class="mt-1 text-xs text-gray-500">Linen diterima</p>
                    </div>

                    <!-- Baik -->
                    <div class="bg-white rounded-lg shadow p-5 border-l-4 border-green-500">
                        <p class="text-sm font-medium text-gray-500">Kondisi Baik</p>
                        <p class="mt-2 text-3xl font-bold text-green-700">
                            {{ formatNumber(summary.total_baik) }}
                        </p>
                        <p class="mt-1 text-xs text-gray-500">
                            {{ formatPersentase(summary.persentase_baik) }} dari total
                        </p>
                    </div>

                    <!-- Bermasalah -->
                    <div class="bg-white rounded-lg shadow p-5 border-l-4 border-red-500">
                        <p class="text-sm font-medium text-gray-500">Linen Bermasalah</p>
                        <p class="mt-2 text-3xl font-bold text-red-700">
                            {{ formatNumber(summary.total_bermasalah) }}
                        </p>
                        <p class="mt-1 text-xs text-red-600">
                            {{ formatPersentase(summary.persentase_bermasalah) }} dari total
                        </p>
                    </div>
                </div>

                <!--
                |--------------------------------------------------------------------------
                | RINGKASAN KONDISI
                |--------------------------------------------------------------------------
                -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="mb-5">
                        <h3 class="text-lg font-semibold text-gray-800">
                            Ringkasan Kondisi Linen
                        </h3>
                        <p class="text-sm text-gray-500 mt-1">
                            Distribusi kondisi linen pada periode yang dipilih.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Baik -->
                        <div class="rounded-lg border border-green-200 bg-green-50 p-5">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-green-800">Baik</span>
                                <span class="text-xs font-semibold text-green-700">
                                    {{ formatPersentase(summary.persentase_baik) }}
                                </span>
                            </div>
                            <p class="mt-2 text-2xl font-bold text-green-800">
                                {{ formatNumber(summary.total_baik) }}
                            </p>
                        </div>

                        <!-- Noda -->
                        <div class="rounded-lg border border-yellow-200 bg-yellow-50 p-5">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-yellow-800">Noda</span>
                                <span class="text-xs font-semibold text-yellow-700">
                                    {{ formatPersentase(summary.persentase_noda) }}
                                </span>
                            </div>
                            <p class="mt-2 text-2xl font-bold text-yellow-800">
                                {{ formatNumber(summary.total_noda) }}
                            </p>
                        </div>

                        <!-- Rusak -->
                        <div class="rounded-lg border border-red-200 bg-red-50 p-5">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-red-800">Rusak</span>
                                <span class="text-xs font-semibold text-red-700">
                                    {{ formatPersentase(summary.persentase_rusak) }}
                                </span>
                            </div>
                            <p class="mt-2 text-2xl font-bold text-red-800">
                                {{ formatNumber(summary.total_rusak) }}
                            </p>
                        </div>
                    </div>
                </div>

                <!--
                |--------------------------------------------------------------------------
                | REKAP PER RUANGAN
                |--------------------------------------------------------------------------
                -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="mb-5">
                        <h3 class="text-lg font-semibold text-gray-800">
                            Rekap Berdasarkan Ruangan
                        </h3>
                        <p class="text-sm text-gray-500 mt-1">
                            Perbandingan volume dan kondisi linen setiap ruangan.
                        </p>
                    </div>

                    <div v-if="rekapRuangan.length" class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 border">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ruangan</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Transaksi</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Total</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-green-600 uppercase">Baik</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-yellow-600 uppercase">Noda</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-red-600 uppercase">Rusak</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-red-600 uppercase">% Masalah</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr
                                    v-for="(row, index) in rekapRuangan"
                                    :key="row.ruangan"
                                    class="hover:bg-gray-50"
                                >
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ index + 1 }}</td>
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ row.ruangan }}</td>
                                    <td class="px-4 py-3 text-sm text-center text-gray-700">{{ formatNumber(row.total_transaksi) }}</td>
                                    <td class="px-4 py-3 text-sm text-center font-semibold text-gray-900">{{ formatNumber(row.total_linen) }}</td>
                                    <td class="px-4 py-3 text-sm text-center text-green-700">{{ formatNumber(row.total_baik) }}</td>
                                    <td class="px-4 py-3 text-sm text-center text-yellow-700">{{ formatNumber(row.total_noda) }}</td>
                                    <td class="px-4 py-3 text-sm text-center text-red-700">{{ formatNumber(row.total_rusak) }}</td>
                                    <td
                                        class="px-4 py-3 text-sm text-center font-semibold"
                                        :class="
                                            row.persentase_bermasalah > 10
                                                ? 'text-red-700'
                                                : row.persentase_bermasalah > 5
                                                    ? 'text-yellow-700'
                                                    : 'text-green-700'
                                        "
                                    >
                                        {{ formatPersentase(row.persentase_bermasalah) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-else class="border border-dashed rounded-lg p-8 text-center">
                        <p class="text-sm text-gray-500">
                            Tidak ada data penerimaan pada periode tersebut.
                        </p>
                    </div>

                    <!-- Ruangan Prioritas (Bermasalah) -->
                    <div
                        class="mt-6"
                        v-if="ruanganBermasalah.length"
                    >
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h4 class="font-semibold text-gray-800">
                                    Ruangan Prioritas
                                </h4>
                                <p class="text-xs text-gray-500 mt-1">
                                    Ruangan dengan persentase linen bermasalah tertinggi.
                                </p>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <div
                                v-for="(row, index) in ruanganBermasalah"
                                :key="row.ruangan"
                                class="border rounded-lg p-4"
                            >
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                    <div>
                                        <p class="font-semibold text-gray-800">
                                            {{ index + 1 }}. {{ row.ruangan }}
                                        </p>
                                        <p class="text-xs text-gray-500 mt-1">
                                            {{ formatNumber(row.total_bermasalah) }} linen bermasalah dari {{ formatNumber(row.total_linen) }} linen
                                        </p>
                                    </div>

                                    <div class="text-right">
                                        <span
                                            class="inline-flex px-3 py-1 rounded-full text-xs font-semibold"
                                            :class="
                                                row.persentase_bermasalah >= 10
                                                    ? 'bg-red-100 text-red-800'
                                                    : row.persentase_bermasalah >= 5
                                                        ? 'bg-yellow-100 text-yellow-800'
                                                        : 'bg-green-100 text-green-800'
                                            "
                                        >
                                            {{ formatPersentase(row.persentase_bermasalah) }}
                                        </span>
                                    </div>
                                </div>

                                <div class="mt-3 w-full bg-gray-200 rounded-full h-2">
                                    <div
                                        class="h-2 rounded-full"
                                        :class="
                                            row.persentase_bermasalah >= 10
                                                ? 'bg-red-500'
                                                : row.persentase_bermasalah >= 5
                                                    ? 'bg-yellow-500'
                                                    : 'bg-green-500'
                                        "
                                        :style="{
                                            width: `${Math.min(row.persentase_bermasalah, 100)}%`
                                        }"
                                    ></div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!--
                |--------------------------------------------------------------------------
                | REKAP JENIS LINEN
                |--------------------------------------------------------------------------
                -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="mb-5">
                        <h3 class="text-lg font-semibold text-gray-800">
                            Rekap Berdasarkan Jenis Linen
                        </h3>
                        <p class="text-sm text-gray-500 mt-1">
                            Volume dan kondisi setiap jenis linen yang diterima.
                        </p>
                    </div>

                    <div v-if="rekapJenisLinen.length" class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 border">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jenis Linen</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Total</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-green-600 uppercase">Baik</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-yellow-600 uppercase">Noda</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-red-600 uppercase">Rusak</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-red-600 uppercase">% Masalah</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr
                                    v-for="(row, index) in rekapJenisLinen"
                                    :key="row.nama_item"
                                    class="hover:bg-gray-50"
                                >
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ index + 1 }}</td>
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ row.nama_item }}</td>
                                    <td class="px-4 py-3 text-sm text-center font-semibold text-gray-900">{{ formatNumber(row.total_linen) }}</td>
                                    <td class="px-4 py-3 text-sm text-center text-green-700">{{ formatNumber(row.total_baik) }}</td>
                                    <td class="px-4 py-3 text-sm text-center text-yellow-700">{{ formatNumber(row.total_noda) }}</td>
                                    <td class="px-4 py-3 text-sm text-center text-red-700">{{ formatNumber(row.total_rusak) }}</td>
                                    <td
                                        class="px-4 py-3 text-sm text-center font-semibold"
                                        :class="
                                            row.persentase_bermasalah > 10
                                                ? 'text-red-700'
                                                : row.persentase_bermasalah > 5
                                                    ? 'text-yellow-700'
                                                    : 'text-green-700'
                                        "
                                    >
                                        {{ formatPersentase(row.persentase_bermasalah) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-else class="border border-dashed rounded-lg p-8 text-center">
                        <p class="text-sm text-gray-500">
                            Tidak ada data jenis linen pada periode tersebut.
                        </p>
                    </div>

                    <!-- Jenis Linen Prioritas (Bermasalah) -->
                    <div
                        class="mt-6"
                        v-if="linenBermasalah.length"
                    >
                        <div class="mb-4">
                            <h4 class="font-semibold text-gray-800">
                                Jenis Linen Prioritas
                            </h4>
                            <p class="text-xs text-gray-500 mt-1">
                                Jenis linen dengan persentase masalah tertinggi.
                            </p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div
                                v-for="(row, index) in linenBermasalah"
                                :key="row.nama_item"
                                class="border rounded-lg p-4"
                            >
                                <div class="flex justify-between items-start gap-3">
                                    <div>
                                        <p class="font-semibold text-gray-800">
                                            {{ index + 1 }}. {{ row.nama_item }}
                                        </p>
                                        <p class="text-xs text-gray-500 mt-1">
                                            Total: {{ formatNumber(row.total_linen) }}
                                        </p>
                                    </div>

                                    <span
                                        class="px-2 py-1 rounded-full text-xs font-semibold"
                                        :class="
                                            row.persentase_bermasalah >= 10
                                                ? 'bg-red-100 text-red-800'
                                                : row.persentase_bermasalah >= 5
                                                    ? 'bg-yellow-100 text-yellow-800'
                                                    : 'bg-green-100 text-green-800'
                                        "
                                    >
                                        {{ formatPersentase(row.persentase_bermasalah) }}
                                    </span>
                                </div>

                                <div class="mt-3 grid grid-cols-3 gap-2 text-xs">
                                    <div class="rounded bg-green-50 p-2 text-center">
                                        <p class="text-green-700">Baik</p>
                                        <p class="font-bold text-green-800">
                                            {{ formatNumber(row.total_baik) }}
                                        </p>
                                    </div>

                                    <div class="rounded bg-yellow-50 p-2 text-center">
                                        <p class="text-yellow-700">Noda</p>
                                        <p class="font-bold text-yellow-800">
                                            {{ formatNumber(row.total_noda) }}
                                        </p>
                                    </div>

                                    <div class="rounded bg-red-50 p-2 text-center">
                                        <p class="text-red-700">Rusak</p>
                                        <p class="font-bold text-red-800">
                                            {{ formatNumber(row.total_rusak) }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!--
                |--------------------------------------------------------------------------
                | TREND HARIAN (GRAFIK SVG)
                |--------------------------------------------------------------------------
                -->
                <div v-if="trenHarian.length" class="mb-8">
                    <div class="border rounded-lg p-4 bg-gray-50 overflow-x-auto">
                        <svg viewBox="0 0 1000 280" class="w-full min-w-[700px]">
                            <!-- Grid -->
                            <line x1="50" y1="40" x2="950" y2="40" stroke="#e5e7eb" />
                            <line x1="50" y1="135" x2="950" y2="135" stroke="#e5e7eb" />
                            <line x1="50" y1="230" x2="950" y2="230" stroke="#e5e7eb" />

                            <!-- Axis -->
                            <line x1="50" y1="40" x2="50" y2="230" stroke="#9ca3af" />
                            <line x1="50" y1="230" x2="950" y2="230" stroke="#9ca3af" />

                            <!-- Trend Line -->
                            <polyline
                                :points="chartPoints()"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="3"
                                class="text-indigo-600"
                            />

                            <!-- Points -->
                            <circle
                                v-for="(item, index) in trenHarian"
                                :key="item.tanggal"
                                :cx="chartX(index)"
                                :cy="chartY(item.total_linen)"
                                r="4"
                                class="fill-indigo-600"
                            />
                        </svg>
                    </div>
                </div>

                <!--
                |--------------------------------------------------------------------------
                | TABEL TREN HARIAN
                |--------------------------------------------------------------------------
                -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="mb-5">
                        <h3 class="text-lg font-semibold text-gray-800">
                            Tren Harian Penerimaan Linen
                        </h3>
                        <p class="text-sm text-gray-500 mt-1">
                            Volume penerimaan linen setiap hari.
                        </p>
                    </div>

                    <div v-if="trenHarian.length" class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 border">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Transaksi</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Total Linen</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-green-600 uppercase">Baik</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-yellow-600 uppercase">Noda</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-red-600 uppercase">Rusak</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr
                                    v-for="row in trenHarian"
                                    :key="row.tanggal"
                                    class="hover:bg-gray-50"
                                >
                                    <td class="px-4 py-3 text-sm text-gray-700">{{ formatTanggal(row.tanggal) }}</td>
                                    <td class="px-4 py-3 text-sm text-center text-gray-700">{{ formatNumber(row.total_transaksi) }}</td>
                                    <td class="px-4 py-3 text-sm text-center font-semibold text-gray-900">{{ formatNumber(row.total_linen) }}</td>
                                    <td class="px-4 py-3 text-sm text-center text-green-700">{{ formatNumber(row.total_baik) }}</td>
                                    <td class="px-4 py-3 text-sm text-center text-yellow-700">{{ formatNumber(row.total_noda) }}</td>
                                    <td class="px-4 py-3 text-sm text-center text-red-700">{{ formatNumber(row.total_rusak) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-else class="border border-dashed rounded-lg p-8 text-center">
                        <p class="text-sm text-gray-500">
                            Tidak ada data tren pada periode tersebut.
                        </p>
                    </div>
                </div>

                <!--
                |--------------------------------------------------------------------------
                | FOOTER
                |--------------------------------------------------------------------------
                -->
                <div class="flex justify-between items-center">
                    <Link
                        :href="route('checklist.index')"
                        class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-700 rounded-md text-xs font-semibold uppercase tracking-widest hover:bg-gray-300"
                    >
                        ← Kembali ke Riwayat
                    </Link>

                    <Link
                        :href="route('checklist.create')"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md text-xs font-semibold uppercase tracking-widest hover:bg-indigo-700"
                    >
                        + Penerimaan Baru
                    </Link>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>