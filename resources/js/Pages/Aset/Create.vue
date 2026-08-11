<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AsetForm from '@/Components/AsetForm.vue';

const props = defineProps({
    satuanOptions: Array,
});

const form = useForm({
    nama_barang: '',
    jumlah: 1,
    satuan: props.satuanOptions[0] ?? '',
    merk: '',
    serial_number: '',
    tahun_pengadaan: null,
    keterangan: '',
    tanggal_input: new Date().toISOString().slice(0, 10), // default hari ini
});

function submit() {
    form.post(route('aset.store'));
}
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tambah Aset Baru</h2>
        </template>

        <Head title="Tambah Aset" />

        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <form @submit.prevent="submit">
                        <AsetForm :form="form" :satuan-options="satuanOptions" />

                        <div class="flex justify-between mt-6">
                            <button type="submit" :disabled="form.processing"
                                    class="inline-flex items-center px-4 py-2 bg-gray-800 text-white rounded-md text-xs font-semibold uppercase tracking-widest hover:bg-gray-700 disabled:opacity-50">
                                Simpan
                            </button>
                            <Link :href="route('aset.index')"
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
