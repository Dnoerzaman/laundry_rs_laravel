<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

// chemicalOptions: array of {id, nama_chemical, unit, jumlah_stok}, dari PemakaianChemicalController@create
const props = defineProps({
    chemicalOptions: Array,
});

const form = useForm({
    tanggal: new Date().toISOString().slice(0, 10),
    chemical_id: props.chemicalOptions[0]?.id ?? null, // default: chemical pertama di daftar
    jumlah: null,
    keterangan: '',
});

// computed: nilai turunan yang otomatis dihitung ulang setiap kali dependency-nya (form.chemical_id) berubah
// Dipakai untuk menampilkan info "Stok tersedia: X unit" di bawah dropdown, membantu user sebelum submit
const chemicalTerpilih = computed(() => {
    return props.chemicalOptions.find(c => c.id === form.chemical_id);
});

function submit() {
    form.post(route('pemakaian-chemical.store'));
}
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Catat Pemakaian Chemical</h2>
        </template>

        <Head title="Catat Pemakaian Chemical" />

        <div class="py-12">
            <div class="max-w-lg mx-auto sm:px-6 lg:px-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <form @submit.prevent="submit">

                        <!-- Tanggal -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal</label>
                            <input type="date" v-model="form.tanggal"
                                   class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                            <p v-if="form.errors.tanggal" class="text-sm text-red-600 mt-1">{{ form.errors.tanggal }}</p>
                        </div>

                        <!-- Dropdown chemical -->
                        <div class="mb-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Chemical</label>
                            <!-- v-model.number: pastikan chemical_id tersimpan sebagai angka, bukan string, supaya cocok dengan tipe id di database -->
                            <select v-model.number="form.chemical_id"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option v-for="opt in chemicalOptions" :key="opt.id" :value="opt.id">{{ opt.nama_chemical }}</option>
                            </select>
                            <p v-if="form.errors.chemical_id" class="text-sm text-red-600 mt-1">{{ form.errors.chemical_id }}</p>
                        </div>

                        <!-- Info stok tersedia, otomatis update saat dropdown di atas berubah -->
                        <p v-if="chemicalTerpilih" class="text-xs text-gray-500 mb-4">
                            Stok tersedia saat ini: <span class="font-semibold">{{ chemicalTerpilih.jumlah_stok }} {{ chemicalTerpilih.unit }}</span>
                        </p>

                        <!-- Jumlah pemakaian -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Pemakaian</label>
                            <input type="number" step="0.01" min="0.01" v-model.number="form.jumlah"
                                   class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                            <!-- Pesan error ini otomatis terisi kalau backend menolak (misal stok tidak cukup) -->
                            <p v-if="form.errors.jumlah" class="text-sm text-red-600 mt-1">{{ form.errors.jumlah }}</p>
                        </div>

                        <!-- Keterangan -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label>
                            <textarea v-model="form.keterangan" rows="3"
                                      class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                            <p v-if="form.errors.keterangan" class="text-sm text-red-600 mt-1">{{ form.errors.keterangan }}</p>
                        </div>

                        <div class="flex justify-between">
                            <button type="submit" :disabled="form.processing"
                                    class="inline-flex items-center px-4 py-2 bg-gray-800 text-white rounded-md text-xs font-semibold uppercase tracking-widest hover:bg-gray-700 disabled:opacity-50">
                                Simpan Pemakaian
                            </button>
                            <Link :href="route('pemakaian-chemical.index')"
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
