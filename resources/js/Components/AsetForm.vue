<script setup>
// Komponen shared untuk field form Aset, dipakai di Create.vue & Edit.vue
const props = defineProps({
    form: Object,          // object useForm() dari halaman induk
    satuanOptions: Array,  // daftar pilihan satuan (Unit/Pcs/Set/Buah)
});
</script>

<template>
    <!-- Nama barang -->
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Barang</label>
        <input type="text" v-model="form.nama_barang"
               class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
        <p v-if="form.errors.nama_barang" class="text-sm text-red-600 mt-1">{{ form.errors.nama_barang }}</p>
    </div>

    <!-- Grid 2 kolom untuk Jumlah & Satuan (biar ringkas, sejajar) -->
    <div class="grid grid-cols-2 gap-4 mb-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah</label>
            <input type="number" min="0" step="1" v-model.number="form.jumlah"
                   class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
            <p v-if="form.errors.jumlah" class="text-sm text-red-600 mt-1">{{ form.errors.jumlah }}</p>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Satuan</label>
            <select v-model="form.satuan"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option v-for="opt in satuanOptions" :key="opt" :value="opt">{{ opt }}</option>
            </select>
            <p v-if="form.errors.satuan" class="text-sm text-red-600 mt-1">{{ form.errors.satuan }}</p>
        </div>
    </div>

    <!-- Merk/Tipe -->
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">Merk / Tipe</label>
        <input type="text" v-model="form.merk"
               class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
        <p v-if="form.errors.merk" class="text-sm text-red-600 mt-1">{{ form.errors.merk }}</p>
    </div>

    <!-- Serial Number -->
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">Serial Number (SN)</label>
        <input type="text" v-model="form.serial_number"
               class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
        <p v-if="form.errors.serial_number" class="text-sm text-red-600 mt-1">{{ form.errors.serial_number }}</p>
    </div>

    <!-- Grid 2 kolom untuk Tahun Pengadaan & Tanggal Input -->
    <div class="grid grid-cols-2 gap-4 mb-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tahun Pengadaan</label>
            <input type="number" placeholder="Contoh: 2024" v-model.number="form.tahun_pengadaan"
                   class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
            <p v-if="form.errors.tahun_pengadaan" class="text-sm text-red-600 mt-1">{{ form.errors.tahun_pengadaan }}</p>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Input</label>
            <input type="date" v-model="form.tanggal_input"
                   class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
            <p v-if="form.errors.tanggal_input" class="text-sm text-red-600 mt-1">{{ form.errors.tanggal_input }}</p>
        </div>
    </div>

    <!-- Keterangan -->
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label>
        <textarea v-model="form.keterangan" rows="3"
                  class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
        <p v-if="form.errors.keterangan" class="text-sm text-red-600 mt-1">{{ form.errors.keterangan }}</p>
    </div>
</template>
