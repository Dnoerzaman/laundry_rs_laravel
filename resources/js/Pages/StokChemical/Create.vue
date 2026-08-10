<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import StokChemicalForm from '@/Components/StokChemicalForm.vue';

const props = defineProps({
    namaChemicalOptions: Array,
    unitOptions: Array,
});

const form = useForm({
    nama_chemical: props.namaChemicalOptions[0] ?? '',
    jumlah_stok: 0,
    unit: props.unitOptions[0] ?? '',
});

function submit() {
    form.post(route('stok-chemical.store'));
}
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tambah Chemical Baru</h2>
        </template>

        <Head title="Tambah Chemical" />

        <div class="py-12">
            <div class="max-w-lg mx-auto sm:px-6 lg:px-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <form @submit.prevent="submit">
                        <StokChemicalForm :form="form" :nama-chemical-options="namaChemicalOptions" :unit-options="unitOptions" />

                        <div class="flex justify-between mt-6">
                            <button type="submit" :disabled="form.processing"
                                    class="inline-flex items-center px-4 py-2 bg-gray-800 text-white rounded-md text-xs font-semibold uppercase tracking-widest hover:bg-gray-700 disabled:opacity-50">
                                Simpan
                            </button>
                            <Link :href="route('stok-chemical.index')"
                                  class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md text-xs font-semibold text-gray-700 uppercase tracking-widest hover:bg-gray-50">
                                Batal
                            </Link>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
