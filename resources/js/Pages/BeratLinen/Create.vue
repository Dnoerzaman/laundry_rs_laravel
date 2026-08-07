<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import BeratLinenForm from '@/Components/BeratLinenForm.vue';

// Props dari BeratLinenController@create
const props = defineProps({
    ruanganOptions: Array,
    shiftOptions: Array,
});

// Form kosong dengan nilai default (tanggal = hari ini, ruangan & shift = pilihan pertama)
const form = useForm({
    tanggal: new Date().toISOString().slice(0, 10),
    ruangan: props.ruanganOptions[0] ?? '',
    shift: props.shiftOptions[0] ?? '',
    total_berat: null,
});

function submit() {
    // Kirim ke route 'berat-linen.store' lewat POST
    form.post(route('berat-linen.store'));
}
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tambah Catatan Berat Linen</h2>
        </template>

        <Head title="Tambah Berat Linen" />

        <div class="py-12">
            <div class="max-w-lg mx-auto sm:px-6 lg:px-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <form @submit.prevent="submit">
                        <!-- Field-field form diambil dari komponen shared BeratLinenForm -->
                        <BeratLinenForm :form="form" :ruangan-options="ruanganOptions" :shift-options="shiftOptions" />

                        <div class="flex justify-between mt-6">
                            <button type="submit" :disabled="form.processing"
                                    class="inline-flex items-center px-4 py-2 bg-gray-800 text-white rounded-md text-xs font-semibold uppercase tracking-widest hover:bg-gray-700 disabled:opacity-50">
                                Simpan
                            </button>
                            <Link :href="route('berat-linen.index')"
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
