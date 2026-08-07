<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import BeratLinenForm from '@/Components/BeratLinenForm.vue';

// Props dari BeratLinenController@edit — 'beratLinen' berisi data existing yang mau diedit
const props = defineProps({
    beratLinen: Object,
    ruanganOptions: Array,
    shiftOptions: Array,
});

// Form diisi dengan data yang SUDAH ADA (bukan kosong seperti di Create.vue)
const form = useForm({
    tanggal: props.beratLinen.tanggal.slice(0, 10), // ambil 10 karakter pertama (YYYY-MM-DD) dari datetime
    ruangan: props.beratLinen.ruangan,
    shift: props.beratLinen.shift,
    total_berat: props.beratLinen.total_berat,
});

function submit() {
    // form.put(): kirim method PUT ke route 'berat-linen.update' dengan parameter id
    form.put(route('berat-linen.update', props.beratLinen.id));
}
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Ubah Catatan Berat Linen</h2>
        </template>

        <Head title="Ubah Berat Linen" />

        <div class="py-12">
            <div class="max-w-lg mx-auto sm:px-6 lg:px-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <form @submit.prevent="submit">
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
