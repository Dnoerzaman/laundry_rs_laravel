<script setup>
// Komponen shared untuk field form Tugas, dipakai di Create.vue & Edit.vue,
// supaya field-field ini tidak perlu ditulis dua kali di dua halaman berbeda
const props = defineProps({
    form: Object,             // object useForm() dari halaman induk (Create.vue / Edit.vue)
    users: Array,              // daftar semua user untuk dropdown Penanggung Jawab, {id, name}
    statusOptions: Array,        // daftar pilihan status: Belum Dikerjakan / Sedang Dikerjakan / Selesai
    targetWaktuOptions: Array,    // daftar pilihan target minggu: Minggu ke-1 s/d Minggu ke-4
});
</script>

<template>
    <!-- Judul pekerjaan -->
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">Pekerjaan</label>
        <input type="text" v-model="form.judul"
               class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
        <p v-if="form.errors.judul" class="text-sm text-red-600 mt-1">{{ form.errors.judul }}</p>
    </div>

    <!-- Deskripsi (opsional) -->
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
        <textarea v-model="form.deskripsi" rows="3"
                  class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
        <p v-if="form.errors.deskripsi" class="text-sm text-red-600 mt-1">{{ form.errors.deskripsi }}</p>
    </div>

    <!-- Dropdown status -->
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
        <select v-model="form.status"
                class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <!-- v-for: looping array statusOptions, bikin 1 <option> untuk tiap pilihan -->
            <option v-for="opt in statusOptions" :key="opt" :value="opt">{{ opt }}</option>
        </select>
        <p v-if="form.errors.status" class="text-sm text-red-600 mt-1">{{ form.errors.status }}</p>
    </div>

    <!-- Dropdown penanggung jawab -->
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">Penanggung Jawab</label>
        <!-- v-model.number: pastikan penanggung_jawab_id tersimpan sebagai angka (integer id), bukan string,
             supaya cocok dengan tipe kolom id di database saat dikirim ke server -->
        <select v-model.number="form.penanggung_jawab_id"
                class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <!-- :value="null" -> opsi ini mewakili "belum ditentukan" (kolom penanggung_jawab_id nullable di database) -->
            <option :value="null">-- Belum Ditentukan --</option>
            <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
        </select>
        <p v-if="form.errors.penanggung_jawab_id" class="text-sm text-red-600 mt-1">{{ form.errors.penanggung_jawab_id }}</p>
    </div>

    <!-- Dropdown target waktu -->
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">Target Waktu</label>
        <select v-model="form.target_waktu"
                class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option v-for="opt in targetWaktuOptions" :key="opt" :value="opt">{{ opt }}</option>
        </select>
        <p v-if="form.errors.target_waktu" class="text-sm text-red-600 mt-1">{{ form.errors.target_waktu }}</p>
    </div>

    <!-- Periode (teks bebas, misal "Agustus 2026") -->
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">Periode (contoh: Agustus 2026)</label>
        <input type="text" v-model="form.periode" placeholder="Contoh: Agustus 2026"
               class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
        <p v-if="form.errors.periode" class="text-sm text-red-600 mt-1">{{ form.errors.periode }}</p>
    </div>
</template>
