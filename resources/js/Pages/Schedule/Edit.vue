<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import TugasForm from '@/Components/TugasForm.vue';

// Props dari TugasController@edit -- 'tugas' berisi data existing yang mau diedit
const props = defineProps({
    tugas: Object,
    users: Array,
    statusOptions: Array,
    targetWaktuOptions: Array,
});

// Form diisi dengan data yang SUDAH ADA (bukan kosong seperti di Create.vue)
const form = useForm({
    judul: props.tugas.judul,
    deskripsi: props.tugas.deskripsi,
    status: props.tugas.status,
    penanggung_jawab_id: props.tugas.penanggung_jawab_id, // ini kolom biasa (foreign key), bukan relasi -- tetap snake_case
    target_waktu: props.tugas.target_waktu,
    periode: props.tugas.periode,
});

function submit() {
    // form.put(): kirim method PUT ke route 'schedule.update' dengan parameter id
    form.put(route('schedule.update', props.tugas.id));
}
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Ubah Rencana Kerja</h2>
        </template>

        <Head title="Ubah Rencana Kerja" />

        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submit">
                        <TugasForm :form="form" :users="users" :status-options="statusOptions" :target-waktu-options="targetWaktuOptions" />

                        <div class="flex justify-between mt-6">
                            <button type="submit" :disabled="form.processing"
                                    class="inline-flex items-center px-4 py-2 bg-gray-800 text-white rounded-md text-xs font-semibold uppercase tracking-widest hover:bg-gray-700 disabled:opacity-50">
                                Simpan Perubahan
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
