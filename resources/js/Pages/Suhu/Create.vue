<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import SuhuForm from '@/Components/SuhuForm.vue';

// Props dari SuhuController@create
const props = defineProps({
    ruanganOptions: Array,   // daftar pilihan ruangan
    waktuUkurOptions: Object, // objek {key: label} untuk dropdown shift/waktu ukur
});

// Ambil key PERTAMA dari objek waktuUkurOptions sebagai nilai default dropdown,
// karena Object.keys() pada objek {Pagi: '...', Siang: '...', Sore: '...'} akan menghasilkan ['Pagi', 'Siang', 'Sore']
const form = useForm({
    tanggal: new Date().toISOString().slice(0, 10), // default hari ini
    jam: new Date().toTimeString().slice(0, 5),       // default jam sekarang, format HH:MM
    ruangan: props.ruanganOptions[0] ?? '',
    waktu_ukur: Object.keys(props.waktuUkurOptions)[0] ?? '',
    suhu: null,
    kelembaban: null,
    keterangan: '',
});

function submit() {
    form.post(route('suhu.store'));
}
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Catat Suhu & Kelembaban Harian</h2>
        </template>

        <Head title="Catat Suhu" />

        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <form @submit.prevent="submit">
                        <!-- Semua field form diambil dari komponen shared SuhuForm -->
                        <SuhuForm :form="form" :ruangan-options="ruanganOptions" :waktu-ukur-options="waktuUkurOptions" />

                        <div class="flex justify-between mt-6">
                            <button type="submit" :disabled="form.processing"
                                    class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md text-xs font-semibold uppercase tracking-widest hover:bg-green-700 disabled:opacity-50">
                                Simpan Data
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
