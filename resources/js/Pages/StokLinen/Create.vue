<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import StokLinenForm from '@/Components/StokLinenForm.vue';

const props = defineProps({
    ruanganOptions: Array,
    namaLinenOptions: Array,
});

const form = useForm({
    ruangan: props.ruanganOptions[0] ?? '',
    nama_linen: props.namaLinenOptions[0] ?? '',
    stok_akhir: 0,
    keterangan: '',
});

function submit() {
    form.post(route('stok-linen.store'));
}
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tambah Stok Linen Baru</h2>
        </template>

        <Head title="Tambah Stok Linen" />

        <div class="py-12">
            <div class="max-w-lg mx-auto sm:px-6 lg:px-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <form @submit.prevent="submit">
                        <StokLinenForm :form="form" :ruangan-options="ruanganOptions" :nama-linen-options="namaLinenOptions" />

                        <div class="flex justify-between mt-6">
                            <button type="submit" :disabled="form.processing"
                                    class="inline-flex items-center px-4 py-2 bg-gray-800 text-white rounded-md text-xs font-semibold uppercase tracking-widest hover:bg-gray-700 disabled:opacity-50">
                                Simpan
                            </button>
                            <Link :href="route('stok-linen.index')"
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
