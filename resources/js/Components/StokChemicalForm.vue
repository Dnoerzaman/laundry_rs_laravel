<script setup>
// Komponen shared untuk field form StokChemical, dipakai di Create.vue & Edit.vue
const props = defineProps({
    form: Object,               // object useForm() dari halaman induk
    namaChemicalOptions: Array, // daftar pilihan nama chemical
    unitOptions: Array,          // daftar pilihan satuan (Liter/Kg/Pcs)
});
</script>

<template>
    <!-- Dropdown nama chemical -->
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Chemical</label>
        <select v-model="form.nama_chemical"
                class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option v-for="opt in namaChemicalOptions" :key="opt" :value="opt">{{ opt }}</option>
        </select>
        <p v-if="form.errors.nama_chemical" class="text-sm text-red-600 mt-1">{{ form.errors.nama_chemical }}</p>
    </div>

    <!-- Input jumlah stok -->
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Stok</label>
        <input type="number" step="0.01" min="0" v-model.number="form.jumlah_stok"
               class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
        <p v-if="form.errors.jumlah_stok" class="text-sm text-red-600 mt-1">{{ form.errors.jumlah_stok }}</p>
    </div>

    <!-- Dropdown unit/satuan -->
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">Satuan</label>
        <select v-model="form.unit"
                class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option v-for="opt in unitOptions" :key="opt" :value="opt">{{ opt }}</option>
        </select>
        <p v-if="form.errors.unit" class="text-sm text-red-600 mt-1">{{ form.errors.unit }}</p>
    </div>
</template>
