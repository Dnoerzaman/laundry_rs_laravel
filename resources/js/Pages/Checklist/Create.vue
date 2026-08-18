<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
// useForm: helper resmi Inertia untuk bikin object form reaktif.
// Otomatis handle: CSRF token, kirim data ke server, isi $errors kalau validasi gagal,
// dan status 'processing' saat sedang submit. Ini pengganti keseluruhan pola
// {% csrf_token %} + {{ form.as_p }} + {{ form.errors }} di Django.

// Props dari ChecklistController@create — daftar pilihan untuk tiap dropdown
const props = defineProps({
    ruanganOptions: Array, // daftar ruangan, misal ['Kamar Bedah', 'Rawat Inap', ...]
    itemOptions: Array,     // daftar nama item linen, misal ['Baju Perawat', 'Handuk', ...]
    kondisiOptions: Array,   // daftar kondisi: ['Baik', 'Noda', 'Rusak']
});

// Fungsi kecil untuk membuat 1 baris item kosong (dipakai saat tambah baris baru & inisialisasi awal)
function itemKosong() {
    return {
        nama_item: props.itemOptions[0] ?? '', // default: pilihan pertama di daftar
        jumlah: 1,                                // default jumlah 1
        kondisi: 'Baik',                           // default kondisi Baik
        keterangan: '',                             // kosong, opsional
    };
}

// form: object reaktif yang menampung SEMUA data yang akan dikirim ke server sekaligus
// (header + array items), lebih simpel dibanding formset Django yang butuh manajemen
// TOTAL_FORMS/INITIAL_FORMS secara manual lewat DOM.
const form = useForm({
    tanggal: new Date().toISOString().slice(0, 10), // default hari ini, format YYYY-MM-DD
    jam: new Date().toTimeString().slice(0, 5),       // default jam sekarang, format HH:MM
    ruangan: props.ruanganOptions[0] ?? '',            // default: ruangan pertama di daftar
    items: [itemKosong()],                              // mulai dengan 1 baris item kosong (setara extra=1 Django)
});

// Menambah 1 baris item baru ke array form.items
// Ini jauh lebih simpel dibanding Django yang harus clone <template> HTML manual lewat JS
function tambahItem() {
    form.items.push(itemKosong());
}

// Menghapus 1 baris item berdasarkan posisinya (index) di array
function hapusItem(index) {
    // Minimal harus ada 1 baris tersisa (setara min_num=1 di Django), jadi kalau tinggal 1, tidak boleh dihapus
    if (form.items.length > 1) {
        form.items.splice(index, 1); // splice: hapus 1 elemen mulai dari posisi `index`
    }
}

// Fungsi yang dijalankan saat form di-submit
function submit() {
    // form.post(): kirim data ke route 'checklist.store' lewat method POST
    // Kalau server balas error validasi, form.errors otomatis terisi dan komponen re-render menampilkan pesan error
    form.post(route('checklist.store'));
}
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">

        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Form Penerimaan Linen
            </h2>

            <p class="text-sm text-gray-500 mt-1">
                Catat penerimaan linen kotor dari ruangan.
            </p>
        </div>

        <div class="flex gap-2">

        <Link
            :href="route('checklist.index')"
            class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-700 rounded-md text-xs font-semibold uppercase tracking-widest hover:bg-gray-300"
        >
            Riwayat
        </Link>

        <Link
            :href="route('berat-linen.index')"
            class="inline-flex items-center px-4 py-2 bg-sky-600 text-white rounded-md text-xs font-semibold uppercase tracking-widest hover:bg-sky-700"
        >
            Catat Berat Linen
        </Link>

    </div>

</div>
        </template>

        <Head title="Checklist Penerimaan Linen" />

        <div class="py-12">
            <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
                <!-- @submit.prevent: mencegah reload halaman bawaan browser, ditangani lewat fungsi submit() di atas -->
                <form @submit.prevent="submit" class="space-y-6">

                    <!-- Kartu 1: Informasi header (tanggal, jam, ruangan) -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="font-medium text-gray-800 mb-4">Informasi Penerimaan Linen Kotor</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                            <!-- Input tanggal -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal</label>
                                <!-- v-model: two-way binding, tiap ketikan otomatis update form.tanggal -->
                                <input type="date" v-model="form.tanggal"
                                       class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                                <!-- Tampilkan pesan error hanya kalau ada error untuk field 'tanggal' -->
                                <p v-if="form.errors.tanggal" class="text-sm text-red-600 mt-1">{{ form.errors.tanggal }}</p>
                            </div>

                            <!-- Input jam -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Jam</label>
                                <input type="time" v-model="form.jam"
                                       class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                                <p v-if="form.errors.jam" class="text-sm text-red-600 mt-1">{{ form.errors.jam }}</p>
                            </div>

                            <!-- Dropdown ruangan -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Ruangan</label>
                                <select v-model="form.ruangan"
                                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <!-- v-for: looping array ruanganOptions untuk bikin daftar <option> -->
                                    <option v-for="opt in ruanganOptions" :key="opt" :value="opt">{{ opt }}</option>
                                </select>
                                <p v-if="form.errors.ruangan" class="text-sm text-red-600 mt-1">{{ form.errors.ruangan }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Kartu 2: Tabel item linen (dinamis, bisa tambah/hapus baris) -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="font-medium text-gray-800 mb-4">Checklist Item Linen</h3>

                        <!-- Pesan error kalau array items kosong / kurang dari 1 -->
                        <p v-if="form.errors.items" class="text-sm text-red-600 mb-3">{{ form.errors.items }}</p>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 border">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase w-1/3">Nama Item</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase w-24">Jumlah</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase w-40">Kondisi</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Keterangan</th>
                                        <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase w-16">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <!-- v-for dengan (item, index): item = isi barisnya, index = posisi ke berapa di array -->
                                    <tr v-for="(item, index) in form.items" :key="index">
                                        <!-- Dropdown nama item, v-model langsung ke items[index].nama_item -->
                                        <td class="px-3 py-2">
                                            <select v-model="item.nama_item" class="w-full border-gray-300 rounded-md text-sm">
                                                <option v-for="opt in itemOptions" :key="opt" :value="opt">{{ opt }}</option>
                                            </select>
                                            <!-- Error spesifik untuk baris ini, misal form.errors['items.0.nama_item'] -->
                                            <p v-if="form.errors[`items.${index}.nama_item`]" class="text-xs text-red-600 mt-1">
                                                {{ form.errors[`items.${index}.nama_item`] }}
                                            </p>
                                        </td>

                                        <!-- Input jumlah -->
                                        <td class="px-3 py-2">
                                            <input type="number" min="1" v-model.number="item.jumlah"
                                                   class="w-full border-gray-300 rounded-md text-sm" />
                                        </td>

                                        <!-- Dropdown kondisi -->
                                        <td class="px-3 py-2">
                                            <select v-model="item.kondisi" class="w-full border-gray-300 rounded-md text-sm">
                                                <option v-for="opt in kondisiOptions" :key="opt" :value="opt">{{ opt }}</option>
                                            </select>
                                        </td>

                                        <!-- Input keterangan (opsional) -->
                                        <td class="px-3 py-2">
                                            <input type="text" v-model="item.keterangan" placeholder="Opsional"
                                                   class="w-full border-gray-300 rounded-md text-sm" />
                                        </td>

                                        <!-- Tombol hapus baris. disabled kalau baris tinggal 1 (tidak boleh kosong) -->
                                        <td class="px-3 py-2 text-center">
                                            <button type="button" @click="hapusItem(index)"
                                                    :disabled="form.items.length <= 1"
                                                    class="text-red-600 hover:text-red-800 font-bold disabled:opacity-30 disabled:cursor-not-allowed">
                                                &times;
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Tombol tambah baris baru -->
                        <button type="button" @click="tambahItem"
                                class="mt-4 inline-flex items-center px-3 py-1.5 bg-green-600 text-white text-xs font-semibold rounded-md hover:bg-green-700">
                            + Tambah Item
                        </button>
                    </div>

                    <!-- Tombol submit -->
                    <div class="flex justify-end">
                        <!-- :disabled="form.processing" mencegah submit dobel selagi request masih berjalan -->
                        <button type="submit" :disabled="form.processing"
                                class="inline-flex items-center px-4 py-2 bg-gray-800 text-white rounded-md text-xs font-semibold uppercase tracking-widest hover:bg-gray-700 disabled:opacity-50">
                            Simpan Data Penerimaan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
