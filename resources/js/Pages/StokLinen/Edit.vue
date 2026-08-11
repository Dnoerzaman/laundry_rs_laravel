<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import StokLinenForm from '@/Components/StokLinenForm.vue';

const props = defineProps({
    stokLinen: Object,
    ruanganOptions: Array,
    namaLinenOptions: Array,
});

const form = useForm({
    ruangan: props.stokLinen.ruangan,
    nama_linen: props.stokLinen.nama_linen,
    stok_akhir: props.stokLinen.stok_akhir,
    keterangan: props.stokLinen.keterangan,
});

function submit() {
    form.put(route('stok-linen.update', props.stokLinen.id));
}
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Ubah Stok Linen</h2>
        </template>

        <Head title="Ubah Stok Linen" />

        <div class="py-12">
            <div class="max-w-lg mx-auto sm:px-6 lg:px-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <form @submit.prevent="submit">
                        <StokLinenForm :form="form" :ruangan-options="ruanganOptions" :nama-linen-options="namaLinenOptions" />

                        <p class="text-xs text-amber-600 bg-amber-50 border border-amber-200 rounded p-2 mb-4">
                            Perhatian: mengubah "Stok Akhir" di sini langsung menimpa angka stok tanpa
                            tercatat sebagai riwayat transaksi. Untuk transaksi masuk/keluar rutin,
                            gunakan menu "Catat Transaksi".
                        </p>

                        <div class="flex justify-between">
                            <button type="submit" :disabled="form.processing"
                                    class="inline-flex items-center px-4 py-2 bg-gray-800 text-white rounded-md text-xs font-semibold uppercase tracking-widest hover:bg-gray-700 disabled:opacity-50">
                                Simpan
                            </button>
                            <Link :href="route('stok-linen.index')"
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
