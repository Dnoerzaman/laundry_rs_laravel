<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AsetForm from '@/Components/AsetForm.vue';

const props = defineProps({
    aset: Object, // data existing yang sedang diedit
    satuanOptions: Array,
});

// Form diisi dengan data yang SUDAH ADA
const form = useForm({
    nama_barang: props.aset.nama_barang,
    jumlah: props.aset.jumlah,
    satuan: props.aset.satuan,
    merk: props.aset.merk,
    serial_number: props.aset.serial_number,
    tahun_pengadaan: props.aset.tahun_pengadaan,
    keterangan: props.aset.keterangan,
    // slice(0, 10) -> ambil 10 karakter pertama (YYYY-MM-DD) dari datetime lengkap yang dikirim server
    tanggal_input: props.aset.tanggal_input.slice(0, 10),
});

function submit() {
    form.put(route('aset.update', props.aset.id));
}
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Ubah Data Aset</h2>
        </template>

        <Head title="Ubah Aset" />

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
