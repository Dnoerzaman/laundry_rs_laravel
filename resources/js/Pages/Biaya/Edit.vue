<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import BiayaForm from '@/Components/BiayaForm.vue';

const props = defineProps({
    biaya: Object, // data existing yang sedang diedit
    kategoriOptions: Array,
    satuanOptions: Array,
});

// Form diisi dari data existing. 'jumlah' tidak dimasukkan ke form sama sekali --
// nilainya akan otomatis dihitung ULANG oleh server berdasarkan qty & harga yang baru
const form = useForm({
    tanggal: props.biaya.tanggal.slice(0, 10),
    kategori: props.biaya.kategori,
    nama_barang: props.biaya.nama_barang,
    qty: props.biaya.qty,
    satuan: props.biaya.satuan,
    harga: props.biaya.harga,
    keterangan: props.biaya.keterangan,
});

function submit() {
    form.put(route('biaya.update', props.biaya.id));
}
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Ubah Catatan Pengeluaran</h2>
        </template>

        <Head title="Ubah Pengeluaran" />

        <div class="py-12">
            <div class="max-w-lg mx-auto sm:px-6 lg:px-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <form @submit.prevent="submit">
                        <BiayaForm :form="form" :kategori-options="kategoriOptions" :satuan-options="satuanOptions" />

                        <div class="flex justify-between mt-6">
                            <button type="submit" :disabled="form.processing"
                                    class="inline-flex items-center px-4 py-2 bg-gray-800 text-white rounded-md text-xs font-semibold uppercase tracking-widest hover:bg-gray-700 disabled:opacity-50">
                                Simpan Perubahan
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
