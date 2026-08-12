<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
// useForm: helper resmi Inertia untuk bikin object form reaktif.
// Otomatis handle: CSRF token, kirim data ke server, isi form.errors kalau validasi gagal,
// dan status form.processing saat request sedang berjalan
import { Head, Link, useForm } from '@inertiajs/vue3';

// form: object reaktif yang menampung data yang akan dikirim ke server.
// Nilai awal 'tanggal' langsung diisi hari ini, supaya user tidak perlu klik-klik date picker
// kalau memang mau mencatat kejadian hari ini juga (kasus paling umum)
const form = useForm({
    // new Date() -> tanggal & waktu sekarang
    // .toISOString() -> ubah jadi format standar 'YYYY-MM-DDTHH:mm:ss.sssZ'
    // .slice(0, 10) -> ambil 10 karakter pertama saja, yaitu 'YYYY-MM-DD' (format yang dipahami <input type="date">)
    tanggal: new Date().toISOString().slice(0, 10),
    keterangan: '', // kosong, user yang isi manual
});

// Fungsi yang dijalankan saat form di-submit
function submit() {
    // form.post(): kirim data lewat method POST ke route 'log-pekerjaan.store'.
    // Kalau server membalas error validasi, form.errors otomatis terisi dan
    // komponen ini re-render menampilkan pesan error di bawah field yang salah
    form.post(route('log-pekerjaan.store'));
}
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tambah Log Pekerjaan</h2>
        </template>

        <Head title="Tambah Log Pekerjaan" />

        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <!-- @submit.prevent -> mencegah reload halaman bawaan browser saat form disubmit, -->
                    <!-- ditangani lewat fungsi submit() di atas -->
                    <form @submit.prevent="submit">

                        <!-- Field tanggal -->
                        <div class="mb-4">
                            <label for="tanggal" class="block text-sm font-medium text-gray-700 mb-1">Tanggal</label>
                            <!-- v-model: two-way binding, tiap ketikan/pilihan otomatis update form.tanggal -->
                            <input id="tanggal" type="date" v-model="form.tanggal"
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                            <!-- Pesan error muncul HANYA kalau form.errors.tanggal ada isinya (setelah submit gagal validasi) -->
                            <p v-if="form.errors.tanggal" class="text-sm text-red-600 mt-2">{{ form.errors.tanggal }}</p>
                        </div>

                        <!-- Field keterangan -->
                        <div class="mb-4">
                            <label for="keterangan" class="block text-sm font-medium text-gray-700 mb-1">Keterangan Catatan / Kejadian</label>
                            <textarea id="keterangan" v-model="form.keterangan" rows="4"
                                      placeholder="Tuliskan catatan pekerjaan atau kejadian penting di sini..."
                                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                            <p v-if="form.errors.keterangan" class="text-sm text-red-600 mt-2">{{ form.errors.keterangan }}</p>
                        </div>

                        <!-- Tombol aksi -->
                        <div class="flex justify-between mt-6">
                            <!-- :disabled="form.processing" -> tombol otomatis nonaktif selama request sedang dikirim, -->
                            <!-- mencegah user klik submit dua kali (dobel data) -->
                            <button type="submit" :disabled="form.processing"
                                    class="inline-flex items-center px-4 py-2 bg-gray-800 text-white rounded-md text-xs font-semibold uppercase tracking-widest hover:bg-gray-700 disabled:opacity-50">
                                Simpan Catatan
                            </button>
                            <Link :href="route('log-pekerjaan.index')"
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
