<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import TugasForm from '@/Components/TugasForm.vue';

// Props dari TugasController@create
const props = defineProps({
    users: Array,
    statusOptions: Array,
    targetWaktuOptions: Array,
});

// form: object reaktif untuk data yang akan dikirim ke server.
// Nilai default: status = pilihan pertama ('Belum Dikerjakan'), target_waktu = pilihan pertama ('Minggu ke-1')
const form = useForm({
    judul: '',
    deskripsi: '',
    status: props.statusOptions[0] ?? '',
    penanggung_jawab_id: null, // default: belum ditentukan
    target_waktu: props.targetWaktuOptions[0] ?? '',
    periode: '',
});

function submit() {
    form.post(route('schedule.store'));
}
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tambah Rencana Kerja Baru</h2>
        </template>

        <Head title="Tambah Rencana Kerja" />

        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submit">
                        <!-- Semua field form diambil dari komponen shared TugasForm -->
                        <TugasForm :form="form" :users="users" :status-options="statusOptions" :target-waktu-options="targetWaktuOptions" />

                        <div class="flex justify-between mt-6">
                            <button type="submit" :disabled="form.processing"
                                    class="inline-flex items-center px-4 py-2 bg-gray-800 text-white rounded-md text-xs font-semibold uppercase tracking-widest hover:bg-gray-700 disabled:opacity-50">
                                Simpan
                            </button>
                            <Link :href="route('schedule.index')"
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
