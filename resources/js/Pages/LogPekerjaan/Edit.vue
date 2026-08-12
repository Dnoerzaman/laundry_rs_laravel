<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

// defineProps: menerima data 'log' (baris existing yang mau diedit) dari LogPekerjaanController@edit
const props = defineProps({
    log: Object,
});

// form diisi dengan DATA YANG SUDAH ADA (bukan kosong seperti di Create.vue),
// supaya user langsung lihat isi yang sedang diedit
const form = useForm({
    // props.log.tanggal dari server berformat lengkap (misal '2026-08-12T00:00:00.000000Z'),
    // .slice(0, 10) memotong supaya jadi 'YYYY-MM-DD' saja -- format yang dipahami <input type="date">
    tanggal: props.log.tanggal.slice(0, 10),
    keterangan: props.log.keterangan,
});

function submit() {
    // form.put(): kirim data lewat method PUT (bukan POST) ke route 'log-pekerjaan.update',
    // menyertakan id dari props.log.id supaya Laravel tahu baris mana yang mau di-update
    form.put(route('log-pekerjaan.update', props.log.id));
}
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Ubah Log Pekerjaan</h2>
        </template>

        <Head title="Ubah Log Pekerjaan" />

        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submit">

                        <div class="mb-4">
                            <label for="tanggal" class="block text-sm font-medium text-gray-700 mb-1">Tanggal</label>
                            <input id="tanggal" type="date" v-model="form.tanggal"
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                            <p v-if="form.errors.tanggal" class="text-sm text-red-600 mt-2">{{ form.errors.tanggal }}</p>
                        </div>

                        <div class="mb-4">
                            <label for="keterangan" class="block text-sm font-medium text-gray-700 mb-1">Keterangan Catatan / Kejadian</label>
                            <textarea id="keterangan" v-model="form.keterangan" rows="4"
                                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                            <p v-if="form.errors.keterangan" class="text-sm text-red-600 mt-2">{{ form.errors.keterangan }}</p>
                        </div>

                        <!-- Catatan: TIDAK ada field 'PJ' di form ini, sengaja -- lihat penjelasan -->
                        <!-- di LogPekerjaanController@update kenapa pj_id tidak ikut diubah lewat form ini -->

                        <div class="flex justify-between mt-6">
                            <button type="submit" :disabled="form.processing"
                                    class="inline-flex items-center px-4 py-2 bg-gray-800 text-white rounded-md text-xs font-semibold uppercase tracking-widest hover:bg-gray-700 disabled:opacity-50">
                                Simpan Perubahan
                            </button>
                            <Link :href="route('log-pekerjaan.index')"
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
