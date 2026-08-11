<script setup>
// Komponen shared untuk field form StokLinen, dipakai di Create.vue & Edit.vue
const props = defineProps({
    form: Object,           // object useForm() dari halaman induk
    ruanganOptions: Array,  // daftar pilihan ruangan
    namaLinenOptions: Array, // daftar pilihan nama linen
});
</script>

<template>
    <!-- Dropdown ruangan -->
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">Ruangan</label>
        <select v-model="form.ruangan"
                class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option v-for="opt in ruanganOptions" :key="opt" :value="opt">{{ opt }}</option>
        </select>
        <p v-if="form.errors.ruangan" class="text-sm text-red-600 mt-1">{{ form.errors.ruangan }}</p>
    </div>

    <!-- Dropdown nama linen -->
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Linen</label>
        <select v-model="form.nama_linen"
                class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option v-for="opt in namaLinenOptions" :key="opt" :value="opt">{{ opt }}</option>
        </select>
        <!-- Error uniqueness (kombinasi ruangan+linen sudah ada) muncul di sini -->
        <p v-if="form.errors.nama_linen" class="text-sm text-red-600 mt-1">{{ form.errors.nama_linen }}</p>
    </div>

    <!-- Input stok akhir -->
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">Stok Akhir</label>
        <input type="number" min="0" step="1" v-model.number="form.stok_akhir"
               class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
        <p v-if="form.errors.stok_akhir" class="text-sm text-red-600 mt-1">{{ form.errors.stok_akhir }}</p>
    </div>

    <!-- Keterangan -->
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label>
        <textarea v-model="form.keterangan" rows="3"
                  class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
        <p v-if="form.errors.keterangan" class="text-sm text-red-600 mt-1">{{ form.errors.keterangan }}</p>
    </div>
</template>
