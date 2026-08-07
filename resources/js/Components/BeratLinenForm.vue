<script setup>
// Komponen ini dipakai bersama oleh halaman Create.vue dan Edit.vue,
// supaya field form tidak perlu ditulis dua kali (setara partial _form di versi Blade)

// defineProps: menerima 'form' (object useForm dari parent) dan daftar pilihan dropdown
const props = defineProps({
    form: Object,           // object useForm() yang dikelola oleh halaman induk (Create.vue / Edit.vue)
    ruanganOptions: Array,  // daftar pilihan ruangan
    shiftOptions: Array,     // daftar pilihan shift
});
</script>

<template>
    <!-- Input tanggal -->
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal</label>
        <input type="date" v-model="form.tanggal"
               class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
        <p v-if="form.errors.tanggal" class="text-sm text-red-600 mt-1">{{ form.errors.tanggal }}</p>
    </div>

    <!-- Dropdown ruangan -->
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">Ruangan</label>
        <select v-model="form.ruangan"
                class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option v-for="opt in ruanganOptions" :key="opt" :value="opt">{{ opt }}</option>
        </select>
        <p v-if="form.errors.ruangan" class="text-sm text-red-600 mt-1">{{ form.errors.ruangan }}</p>
    </div>

    <!-- Dropdown shift -->
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">Shift</label>
        <select v-model="form.shift"
                class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option v-for="opt in shiftOptions" :key="opt" :value="opt">{{ opt }}</option>
        </select>
        <p v-if="form.errors.shift" class="text-sm text-red-600 mt-1">{{ form.errors.shift }}</p>
    </div>

    <!-- Input total berat (angka desimal, dalam Kg) -->
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">Total Berat (Kg)</label>
        <!-- v-model.number: otomatis konversi input jadi tipe angka, bukan string -->
        <input type="number" step="0.1" placeholder="Contoh: 15.5" v-model.number="form.total_berat"
               class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
        <p v-if="form.errors.total_berat" class="text-sm text-red-600 mt-1">{{ form.errors.total_berat }}</p>
    </div>
</template>
