<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import SuhuForm from '@/Components/SuhuForm.vue';

// Props dari SuhuController@edit -- 'suhu' berisi data existing yang mau diedit
const props = defineProps({
    suhu: Object,
    ruanganOptions: Array,
    waktuUkurOptions: Object,
});

// Form diisi dengan data yang SUDAH ADA
const form = useForm({
    tanggal: props.suhu.tanggal.slice(0, 10), // ambil 10 karakter pertama (YYYY-MM-DD) dari datetime lengkap
    jam: props.suhu.jam.slice(0, 5),            // ambil 5 karakter pertama (HH:MM) dari format waktu lengkap
    ruangan: props.suhu.ruangan,
    waktu_ukur: props.suhu.waktu_ukur,
    suhu: props.suhu.suhu,
    kelembaban: props.suhu.kelembaban,
    keterangan: props.suhu.keterangan,
});

function submit() {
    // form.put(): kirim method PUT ke route 'suhu.update' dengan parameter id
    form.put(route('suhu.update', props.suhu.id));
}
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Ubah Catatan Suhu & Kelembaban</h2>
        </template>

        <Head title="Ubah Catatan Suhu" />

        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <form @submit.prevent="submit">
                        <SuhuForm :form="form" :ruangan-options="ruanganOptions" :waktu-ukur-options="waktuUkurOptions" />

                        <div class="flex justify-between mt-6">
                            <button type="submit" :disabled="form.processing"
                                    class="inline-flex items-center px-4 py-2 bg-gray-800 text-white rounded-md text-xs font-semibold uppercase tracking-widest hover:bg-gray-700 disabled:opacity-50">
                                Simpan Perubahan
                            </button>
                            <Link :href="route('suhu.index')"
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
