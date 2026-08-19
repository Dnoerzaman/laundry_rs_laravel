<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    penerimaan: Object,
    ruanganOptions: Array,
    itemOptions: Array,
    kondisiOptions: Array,
});

function itemKosong() {
    return {
        nama_item: props.itemOptions[0] ?? '',
        jumlah: 1,
        kondisi: 'Baik',
        keterangan: '',
    };
}

const form = useForm({
    tanggal: props.penerimaan.tanggal
        ? String(props.penerimaan.tanggal).slice(0, 10)
        : '',

    jam: props.penerimaan.jam ?? '',

    ruangan: props.penerimaan.ruangan ?? '',

    items: props.penerimaan.items?.map(item => ({
        nama_item: item.nama_item,
        jumlah: item.jumlah,
        kondisi: item.kondisi,
        keterangan: item.keterangan ?? '',
    })) ?? [itemKosong()],
});

function tambahItem() {
    form.items.push(itemKosong());
}

function hapusItem(index) {
    if (form.items.length > 1) {
        form.items.splice(index, 1);
    }
}

function submit() {
    form.put(
        route('checklist.update', props.penerimaan.id)
    );
}
</script>

<template>
    <AuthenticatedLayout>

        <Head title="Ubah Penerimaan Linen" />

        <template #header>

            <div class="flex justify-between items-center">

                <div>
                    <h2 class="font-semibold text-xl text-gray-800">
                        Ubah Penerimaan Linen
                    </h2>

                    <p class="text-sm text-gray-500 mt-1">
                        Perbaiki data penerimaan linen jika terdapat kesalahan input.
                    </p>
                </div>

            </div>

        </template>

        <div class="py-8">

            <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">

                <form
                    @submit.prevent="submit"
                    class="space-y-6"
                >

                    <!-- INFORMASI HEADER -->
                    <div class="bg-white rounded-lg shadow p-6">

                        <h3 class="font-medium text-gray-800 mb-5">
                            Informasi Penerimaan Linen
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                            <!-- Tanggal -->
                            <div>

                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Tanggal
                                </label>

                                <input
                                    type="date"
                                    v-model="form.tanggal"
                                    class="w-full border-gray-300 rounded-md shadow-sm"
                                />

                                <p
                                    v-if="form.errors.tanggal"
                                    class="text-sm text-red-600 mt-1"
                                >
                                    {{ form.errors.tanggal }}
                                </p>

                            </div>

                            <!-- Jam -->
                            <div>

                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Jam
                                </label>

                                <input
                                    type="time"
                                    v-model="form.jam"
                                    class="w-full border-gray-300 rounded-md shadow-sm"
                                />

                                <p
                                    v-if="form.errors.jam"
                                    class="text-sm text-red-600 mt-1"
                                >
                                    {{ form.errors.jam }}
                                </p>

                            </div>

                            <!-- Ruangan -->
                            <div>

                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Ruangan
                                </label>

                                <select
                                    v-model="form.ruangan"
                                    class="w-full border-gray-300 rounded-md shadow-sm"
                                >

                                    <option
                                        v-for="ruangan in ruanganOptions"
                                        :key="ruangan"
                                        :value="ruangan"
                                    >
                                        {{ ruangan }}
                                    </option>

                                </select>

                                <p
                                    v-if="form.errors.ruangan"
                                    class="text-sm text-red-600 mt-1"
                                >
                                    {{ form.errors.ruangan }}
                                </p>

                            </div>

                        </div>

                    </div>

                    <!-- ITEM LINEN -->
                    <div class="bg-white rounded-lg shadow p-6">

                        <div class="flex justify-between items-center mb-4">

                            <h3 class="font-medium text-gray-800">
                                Item Linen
                            </h3>

                        </div>

                        <p
                            v-if="form.errors.items"
                            class="text-sm text-red-600 mb-3"
                        >
                            {{ form.errors.items }}
                        </p>

                        <div class="overflow-x-auto">

                            <table class="min-w-full divide-y divide-gray-200 border">

                                <thead class="bg-gray-50">

                                    <tr>

                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                            Nama Item
                                        </th>

                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                            Jumlah
                                        </th>

                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                            Kondisi
                                        </th>

                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                            Keterangan
                                        </th>

                                        <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase">
                                            Aksi
                                        </th>

                                    </tr>

                                </thead>

                                <tbody class="divide-y divide-gray-200">

                                    <tr
                                        v-for="(item, index) in form.items"
                                        :key="index"
                                    >

                                        <!-- Nama item -->
                                        <td class="px-3 py-2">

                                            <select
                                                v-model="item.nama_item"
                                                class="w-full border-gray-300 rounded-md text-sm"
                                            >

                                                <option
                                                    v-for="opt in itemOptions"
                                                    :key="opt"
                                                    :value="opt"
                                                >
                                                    {{ opt }}
                                                </option>

                                            </select>

                                            <p
                                                v-if="form.errors[`items.${index}.nama_item`]"
                                                class="text-xs text-red-600 mt-1"
                                            >
                                                {{ form.errors[`items.${index}.nama_item`] }}
                                            </p>

                                        </td>

                                        <!-- Jumlah -->
                                        <td class="px-3 py-2">

                                            <input
                                                type="number"
                                                min="1"
                                                max="10000"
                                                v-model.number="item.jumlah"
                                                class="w-full border-gray-300 rounded-md text-sm"
                                            />

                                            <p
                                                v-if="form.errors[`items.${index}.jumlah`]"
                                                class="text-xs text-red-600 mt-1"
                                            >
                                                {{ form.errors[`items.${index}.jumlah`] }}
                                            </p>

                                        </td>

                                        <!-- Kondisi -->
                                        <td class="px-3 py-2">

                                            <select
                                                v-model="item.kondisi"
                                                class="w-full border-gray-300 rounded-md text-sm"
                                            >

                                                <option
                                                    v-for="opt in kondisiOptions"
                                                    :key="opt"
                                                    :value="opt"
                                                >
                                                    {{ opt }}
                                                </option>

                                            </select>

                                        </td>

                                        <!-- Keterangan -->
                                        <td class="px-3 py-2">

                                            <input
                                                type="text"
                                                v-model="item.keterangan"
                                                placeholder="Opsional"
                                                class="w-full border-gray-300 rounded-md text-sm"
                                            />

                                        </td>

                                        <!-- Hapus -->
                                        <td class="px-3 py-2 text-center">

                                            <button
                                                type="button"
                                                @click="hapusItem(index)"
                                                :disabled="form.items.length <= 1"
                                                class="text-red-600 hover:text-red-800 font-bold disabled:opacity-30 disabled:cursor-not-allowed"
                                            >
                                                &times;
                                            </button>

                                        </td>

                                    </tr>

                                </tbody>

                            </table>

                        </div>

                        <button
                            type="button"
                            @click="tambahItem"
                            class="mt-4 inline-flex items-center px-3 py-1.5 bg-green-600 text-white text-xs font-semibold rounded-md hover:bg-green-700"
                        >
                            + Tambah Item
                        </button>

                    </div>

                    <!-- TOMBOL -->
                    <div class="flex justify-end gap-2">

                        <Link
                            :href="route('checklist.show', penerimaan.id)"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md text-xs font-semibold uppercase hover:bg-gray-300"
                        >
                            Batal
                        </Link>

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-4 py-2 bg-gray-800 text-white rounded-md text-xs font-semibold uppercase hover:bg-gray-700 disabled:opacity-50"
                        >
                            {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                        </button>

                    </div>

                </form>

            </div>

        </div>

    </AuthenticatedLayout>
</template>