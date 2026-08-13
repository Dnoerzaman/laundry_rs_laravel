<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import BiayaForm from '@/Components/BiayaForm.vue';

const props = defineProps({
    kategoriOptions: Array,
    satuanOptions: Array,
});

// Perhatikan: TIDAK ada field 'jumlah' di sini -- itu memang tidak pernah
// dikirim dari form sama sekali, murni dihitung di server
const form = useForm({
    tanggal: new Date().toISOString().slice(0, 10), // default hari ini
    kategori: props.kategoriOptions[0] ?? '',
    nama_barang: '',
    qty: 1,
    satuan: props.satuanOptions[0] ?? '',
    harga: null,
    keterangan: '',
});

function submit() {
    form.post(route('biaya.store'));
}
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tambah Catatan Pengeluaran</h2>
        </template>

        <Head title="Tambah Pengeluaran" />

        <div class="py-12">
            <div class="max-w-lg mx-auto sm:px-6 lg:px-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <form @submit.prevent="submit">
                        <BiayaForm :form="form" :kategori-options="kategoriOptions" :satuan-options="satuanOptions" />

                        <div class="flex justify-between mt-6">
                            <button type="submit" :disabled="form.processing"
                                    class="inline-flex items-center px-4 py-2 bg-gray-800 text-white rounded-md text-xs font-semibold uppercase tracking-widest hover:bg-gray-700 disabled:opacity-50">
                                Simpan
                            </button>
                            <Link :href="route('biaya.index')"
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
