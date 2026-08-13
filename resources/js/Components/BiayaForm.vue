<script setup>
// computed: dipakai untuk menghitung "preview" Jumlah di layar SEBELUM form disubmit,
// murni untuk kenyamanan user melihat estimasi total sebelum klik simpan.
// Perhitungan RESMI/final tetap dilakukan di server (model Biaya), preview ini hanya visual
import { computed } from 'vue';

const props = defineProps({
    form: Object,           // object useForm() dari halaman induk
    kategoriOptions: Array, // daftar pilihan kategori
    satuanOptions: Array,    // daftar pilihan satuan
});

// computed otomatis dihitung ulang setiap kali form.qty atau form.harga berubah
const previewJumlah = computed(() => {
    const qty = Number(props.form.qty) || 0;     // fallback ke 0 kalau belum diisi/bukan angka
    const harga = Number(props.form.harga) || 0;
    return qty * harga;
});

// Fungsi format angka jadi format Rupiah, misal 15000 -> "Rp 15.000"
function formatRupiah(angka) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0, // tidak perlu tampilkan ",00" di belakang
    }).format(angka);
}
</script>

<template>
    <!-- Tanggal -->
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal</label>
        <input type="date" v-model="form.tanggal"
               class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
        <p v-if="form.errors.tanggal" class="text-sm text-red-600 mt-1">{{ form.errors.tanggal }}</p>
    </div>

    <!-- Kategori -->
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
        <select v-model="form.kategori"
                class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option v-for="opt in kategoriOptions" :key="opt" :value="opt">{{ opt }}</option>
        </select>
        <p v-if="form.errors.kategori" class="text-sm text-red-600 mt-1">{{ form.errors.kategori }}</p>
    </div>

    <!-- Nama Barang -->
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Barang</label>
        <input type="text" v-model="form.nama_barang"
               class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
        <p v-if="form.errors.nama_barang" class="text-sm text-red-600 mt-1">{{ form.errors.nama_barang }}</p>
    </div>

    <!-- Grid 2 kolom: Qty & Satuan sejajar -->
    <div class="grid grid-cols-2 gap-4 mb-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Qty</label>
            <input type="number" min="1" step="1" v-model.number="form.qty"
                   class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
            <p v-if="form.errors.qty" class="text-sm text-red-600 mt-1">{{ form.errors.qty }}</p>
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

    <!-- Harga satuan -->
    <div class="mb-2">
        <label class="block text-sm font-medium text-gray-700 mb-1">Harga Satuan (Rp)</label>
        <input type="number" min="0.01" step="0.01" v-model.number="form.harga"
               class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
        <p v-if="form.errors.harga" class="text-sm text-red-600 mt-1">{{ form.errors.harga }}</p>
    </div>

    <!-- Preview Jumlah (read-only, hanya informatif) -->
    <div class="mb-4 p-3 bg-gray-50 rounded-md flex justify-between items-center">
        <span class="text-sm text-gray-600">Jumlah (Qty × Harga):</span>
        <span class="text-base font-semibold text-gray-900">{{ formatRupiah(previewJumlah) }}</span>
    </div>

    <!-- Keterangan -->
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan (opsional)</label>
        <textarea v-model="form.keterangan" rows="2"
                  class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
    </div>
</template>
