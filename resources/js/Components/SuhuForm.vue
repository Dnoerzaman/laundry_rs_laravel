<script setup>
// Komponen shared untuk field form SuhuRuangan, dipakai di Create.vue & Edit.vue
const props = defineProps({
    form: Object,             // object useForm() dari halaman induk
    ruanganOptions: Array,    // daftar pilihan ruangan/area
    waktuUkurOptions: Object, // daftar pilihan shift, berbentuk objek {key: label}, BUKAN array biasa
});
</script>

<template>
    <!-- Grid 2 kolom: Tanggal & Jam sejajar -->
    <div class="grid grid-cols-2 gap-4 mb-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal</label>
            <input type="date" v-model="form.tanggal"
                   class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
            <p v-if="form.errors.tanggal" class="text-sm text-red-600 mt-1">{{ form.errors.tanggal }}</p>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Jam Pengukuran</label>
            <input type="time" v-model="form.jam"
                   class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
            <p v-if="form.errors.jam" class="text-sm text-red-600 mt-1">{{ form.errors.jam }}</p>
        </div>
    </div>

    <!-- Grid 2 kolom: Ruangan & Shift/Waktu Ukur sejajar -->
    <div class="grid grid-cols-2 gap-4 mb-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Ruangan / Area</label>
            <select v-model="form.ruangan"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option v-for="opt in ruanganOptions" :key="opt" :value="opt">{{ opt }}</option>
            </select>
            <p v-if="form.errors.ruangan" class="text-sm text-red-600 mt-1">{{ form.errors.ruangan }}</p>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Shift / Waktu</label>
            <select v-model="form.waktu_ukur"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <!-- Object.entries() ubah objek {key: label} jadi array pasangan [key, label],
                     supaya bisa di-looping dengan v-for (sama seperti array biasa) -->
                <option v-for="[key, label] in Object.entries(waktuUkurOptions)" :key="key" :value="key">
                    {{ label }}
                </option>
            </select>
            <p v-if="form.errors.waktu_ukur" class="text-sm text-red-600 mt-1">{{ form.errors.waktu_ukur }}</p>
        </div>
    </div>

    <!-- Grid 2 kolom: Suhu & Kelembaban sejajar, dengan teks bantuan standar -->
    <div class="grid grid-cols-2 gap-4 mb-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Suhu (°C)</label>
            <input type="number" step="0.1" placeholder="Contoh: 24.5" v-model.number="form.suhu"
                   class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
            <!-- Teks bantuan, bukan validasi wajib -- sama seperti versi Django (cuma hint visual) -->
            <span class="text-xs text-gray-400">Standar: 22.0°C - 27.0°C</span>
            <p v-if="form.errors.suhu" class="text-sm text-red-600 mt-1">{{ form.errors.suhu }}</p>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Kelembaban (%)</label>
            <input type="number" placeholder="Contoh: 55" v-model.number="form.kelembaban"
                   class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
            <span class="text-xs text-gray-400">Standar: 40% - 60%</span>
            <p v-if="form.errors.kelembaban" class="text-sm text-red-600 mt-1">{{ form.errors.kelembaban }}</p>
        </div>
    </div>

    <!-- Keterangan -->
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label>
        <input type="text" v-model="form.keterangan" placeholder="Keterangan tambahan (opsional)"
               class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
        <p v-if="form.errors.keterangan" class="text-sm text-red-600 mt-1">{{ form.errors.keterangan }}</p>
    </div>
</template>
