<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import StokChemicalForm from '@/Components/StokChemicalForm.vue';

const props = defineProps({
    stokChemical: Object,
    namaChemicalOptions: Array,
    unitOptions: Array,
});

const form = useForm({
    nama_chemical: props.stokChemical.nama_chemical,
    jumlah_stok: props.stokChemical.jumlah_stok,
    unit: props.stokChemical.unit,
});

function submit() {
    form.put(route('stok-chemical.update', props.stokChemical.id));
}
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Ubah Stok Chemical</h2>
        </template>

        <Head title="Ubah Chemical" />

        <div class="py-12">
            <div class="max-w-lg mx-auto sm:px-6 lg:px-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <form @submit.prevent="submit">
                        <StokChemicalForm :form="form" :nama-chemical-options="namaChemicalOptions" :unit-options="unitOptions" />

                        <!-- Peringatan: mengubah "Jumlah Stok" di sini langsung menimpa angka stok, -->
                        <!-- BUKAN mencatat transaksi masuk/keluar. Gunakan menu Pemakaian/Penerimaan untuk transaksi normal. -->
                        <p class="text-xs text-amber-600 bg-amber-50 border border-amber-200 rounded p-2 mb-4">
                            Perhatian: mengubah "Jumlah Stok" di sini langsung menimpa angka stok tanpa tercatat
                            sebagai riwayat transaksi. Untuk pemakaian/penerimaan rutin, gunakan menu masing-masing.
                        </p>

                        <div class="flex justify-between">
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
