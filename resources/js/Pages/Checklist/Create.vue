<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
// useForm: helper resmi Inertia untuk bikin object form reaktif.
// Otomatis handle: CSRF token, kirim data ke server, isi $errors kalau validasi gagal,
// dan status 'processing' saat sedang submit.

// Props dari ChecklistController@create — daftar pilihan untuk tiap dropdown
const props = defineProps({
    ruanganOptions: Array,
    itemOptions: Array,
    kondisiOptions: Array,
});

/*
|--------------------------------------------------------------------------
| Helper
|--------------------------------------------------------------------------
*/

/**
 * Membuat satu baris item linen baru.
 */
function itemKosong() {
    return {
        nama_item: props.itemOptions[0] ?? '',
        jumlah: 1,
        kondisi: props.kondisiOptions?.[0] ?? 'Baik',
        keterangan: '',
    };
}

/**
 * Mengecek apakah kondisi membutuhkan keterangan.
 *
 * Sesuai aturan bisnis:
 * - Noda  -> wajib keterangan
 * - Rusak -> wajib keterangan
 */
function membutuhkanKeterangan(kondisi) {
    return ['Noda', 'Rusak'].includes(kondisi);
}

/**
 * Mengecek apakah item tertentu merupakan duplikat.
 */
function itemDuplikat(index) {
    const namaItem = form.items[index]?.nama_item;

    if (!namaItem) {
        return false;
    }

    return form.items.some(
        (item, itemIndex) =>
            itemIndex !== index &&
            item.nama_item === namaItem
    );
}

/*
|--------------------------------------------------------------------------
| Form
|--------------------------------------------------------------------------
*/

const form = useForm({
    tanggal: new Date().toISOString().slice(0, 10),

    jam: new Date().toTimeString().slice(0, 5),

    ruangan: props.ruanganOptions[0] ?? '',

    items: [itemKosong()],
});

/*
|--------------------------------------------------------------------------
| Item Actions
|--------------------------------------------------------------------------
*/

/**
 * Menambahkan item baru.
 */
function tambahItem() {
    form.items.push(itemKosong());
}

/**
 * Menghapus item berdasarkan index.
 *
 * Minimal harus ada satu item.
 */
function hapusItem(index) {
    if (form.items.length > 1) {
        form.items.splice(index, 1);
    }
}

/*
|--------------------------------------------------------------------------
| Validation Helper
|--------------------------------------------------------------------------
*/

/**
 * Validasi frontend sebelum request dikirim ke Laravel.
 *
 * Validasi backend tetap menjadi validasi utama.
 */
function validasiFrontend() {
    form.clearErrors();

    let valid = true;

    /*
    |--------------------------------------------------------------------------
    | Validasi tanggal
    |--------------------------------------------------------------------------
    */

    if (!form.tanggal) {
        form.setError(
            'tanggal',
            'Tanggal penerimaan wajib diisi.'
        );

        valid = false;
    } else {
        const tanggalInput = new Date(`${form.tanggal}T00:00:00`);

        const sekarang = new Date();

        const tanggalHariIni = new Date(
            sekarang.getFullYear(),
            sekarang.getMonth(),
            sekarang.getDate()
        );

        if (tanggalInput > tanggalHariIni) {
            form.setError(
                'tanggal',
                'Tanggal penerimaan tidak boleh melebihi hari ini.'
            );

            valid = false;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Validasi jam
    |--------------------------------------------------------------------------
    */

    if (!form.jam) {
        form.setError(
            'jam',
            'Jam penerimaan wajib diisi.'
        );

        valid = false;
    }

    /*
    |--------------------------------------------------------------------------
    | Validasi ruangan
    |--------------------------------------------------------------------------
    */

    if (!form.ruangan) {
        form.setError(
            'ruangan',
            'Ruangan wajib dipilih.'
        );

        valid = false;
    }

    /*
    |--------------------------------------------------------------------------
    | Validasi jumlah item
    |--------------------------------------------------------------------------
    */

    if (!form.items.length) {
        form.setError(
            'items',
            'Minimal harus ada 1 item linen.'
        );

        valid = false;
    }

    /*
    |--------------------------------------------------------------------------
    | Validasi setiap item
    |--------------------------------------------------------------------------
    */

    form.items.forEach((item, index) => {

        /*
        |--------------------------------------------------------------------------
        | Nama item
        |--------------------------------------------------------------------------
        */

        if (!item.nama_item) {
            form.setError(
                `items.${index}.nama_item`,
                'Nama item linen wajib dipilih.'
            );

            valid = false;
        }

        /*
        |--------------------------------------------------------------------------
        | Duplikasi item
        |--------------------------------------------------------------------------
        */

        if (itemDuplikat(index)) {
            form.setError(
                `items.${index}.nama_item`,
                'Item linen yang sama tidak boleh dimasukkan lebih dari satu kali.'
            );

            valid = false;
        }

        /*
        |--------------------------------------------------------------------------
        | Jumlah
        |--------------------------------------------------------------------------
        */

        const jumlah = Number(item.jumlah);

        if (!Number.isInteger(jumlah)) {
            form.setError(
                `items.${index}.jumlah`,
                'Jumlah linen harus berupa angka bulat.'
            );

            valid = false;
        } else if (jumlah < 1) {
            form.setError(
                `items.${index}.jumlah`,
                'Jumlah linen minimal 1.'
            );

            valid = false;
        } else if (jumlah > 10000) {
            form.setError(
                `items.${index}.jumlah`,
                'Jumlah linen tidak boleh lebih dari 10.000.'
            );

            valid = false;
        }

        /*
        |--------------------------------------------------------------------------
        | Kondisi
        |--------------------------------------------------------------------------
        */

        if (!item.kondisi) {
            form.setError(
                `items.${index}.kondisi`,
                'Kondisi linen wajib dipilih.'
            );

            valid = false;
        }

        /*
        |--------------------------------------------------------------------------
        | Keterangan
        |--------------------------------------------------------------------------
        */

        const keterangan = String(
            item.keterangan ?? ''
        ).trim();

        if (
            membutuhkanKeterangan(item.kondisi) &&
            !keterangan
        ) {
            form.setError(
                `items.${index}.keterangan`,
                'Keterangan wajib diisi untuk linen dengan kondisi Noda atau Rusak.'
            );

            valid = false;
        }

        /*
        |--------------------------------------------------------------------------
        | Panjang keterangan
        |--------------------------------------------------------------------------
        */

        if (keterangan.length > 255) {
            form.setError(
                `items.${index}.keterangan`,
                'Keterangan maksimal 255 karakter.'
            );

            valid = false;
        }
    });

    return valid;
}

/*
|--------------------------------------------------------------------------
| Submit
|--------------------------------------------------------------------------
*/

function submit() {
    /*
     * Validasi frontend hanya untuk memberikan feedback
     * lebih cepat kepada user.
     *
     * Validasi backend Laravel tetap menjadi pengaman utama.
     */
    if (!validasiFrontend()) {
        return;
    }

    form.post(route('checklist.store'), {
        preserveScroll: true,
    });
}
</script>

<template>
    <AuthenticatedLayout>

        <template #header>
            <div
                class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3"
            >

                <div>
                    <h2
                        class="font-semibold text-xl text-gray-800 leading-tight"
                    >
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

                <form
                    @submit.prevent="submit"
                    class="space-y-6"
                >

                    <!--
                    |--------------------------------------------------------------------------
                    | KARTU 1 - INFORMASI PENERIMAAN
                    |--------------------------------------------------------------------------
                    -->

                    <div class="bg-white rounded-lg shadow p-6">

                        <h3
                            class="font-medium text-gray-800 mb-4"
                        >
                            Informasi Penerimaan Linen Kotor
                        </h3>

                        <div
                            class="grid grid-cols-1 md:grid-cols-3 gap-4"
                        >

                            <!-- Tanggal -->

                            <div>

                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                >
                                    Tanggal
                                </label>

                                <input
                                    type="date"
                                    v-model="form.tanggal"
                                    :max="
                                        new Date()
                                            .toISOString()
                                            .slice(0, 10)
                                    "
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />

                                <p
                                    v-if="form.errors.tanggal"
                                    class="text-sm text-red-600 mt-1"
                                >
                                    {{ form.errors.tanggal }}
                                </p>

                            </div>

                            <!-- Jam -->

                            <div>

                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                >
                                    Jam
                                </label>

                                <input
                                    type="time"
                                    v-model="form.jam"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />

                                <p
                                    v-if="form.errors.jam"
                                    class="text-sm text-red-600 mt-1"
                                >
                                    {{ form.errors.jam }}
                                </p>

                            </div>

                            <!-- Ruangan -->

                            <div>

                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                >
                                    Ruangan
                                </label>

                                <select
                                    v-model="form.ruangan"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >

                                    <option
                                        v-for="opt in ruanganOptions"
                                        :key="opt"
                                        :value="opt"
                                    >
                                        {{ opt }}
                                    </option>

                                </select>

                                <p
                                    v-if="form.errors.ruangan"
                                    class="text-sm text-red-600 mt-1"
                                >
                                    {{ form.errors.ruangan }}
                                </p>

                            </div>

                        </div>
                    </div>


                    <!--
                    |--------------------------------------------------------------------------
                    | KARTU 2 - ITEM LINEN
                    |--------------------------------------------------------------------------
                    -->

                    <div class="bg-white rounded-lg shadow p-6">

                        <h3
                            class="font-medium text-gray-800 mb-4"
                        >
                            Checklist Item Linen
                        </h3>

                        <p
                            v-if="form.errors.items"
                            class="text-sm text-red-600 mb-3"
                        >
                            {{ form.errors.items }}
                        </p>

                        <div class="overflow-x-auto">

                            <table
                                class="min-w-full divide-y divide-gray-200 border"
                            >

                                <thead class="bg-gray-50">

                                    <tr>

                                        <th
                                            class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase w-1/3"
                                        >
                                            Nama Item
                                        </th>

                                        <th
                                            class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase w-24"
                                        >
                                            Jumlah
                                        </th>

                                        <th
                                            class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase w-40"
                                        >
                                            Kondisi
                                        </th>

                                        <th
                                            class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase"
                                        >
                                            Keterangan
                                        </th>

                                        <th
                                            class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase w-16"
                                        >
                                            Aksi
                                        </th>

                                    </tr>

                                </thead>

                                <tbody
                                    class="divide-y divide-gray-200"
                                >

                                    <tr
                                        v-for="(item, index) in form.items"
                                        :key="index"
                                    >

                                        <!-- Nama Item -->

                                        <td class="px-3 py-2 align-top">

                                            <select
                                                v-model="item.nama_item"
                                                class="w-full border-gray-300 rounded-md text-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            >

                                                <option
                                                    v-for="opt in itemOptions"
                                                    :key="opt"
                                                    :value="opt"
                                                >
                                                    {{ opt }}
                                                </option>

                                            </select>

                                            <p
                                                v-if="
                                                    form.errors[
                                                        `items.${index}.nama_item`
                                                    ]
                                                "
                                                class="text-xs text-red-600 mt-1"
                                            >
                                                {{
                                                    form.errors[
                                                        `items.${index}.nama_item`
                                                    ]
                                                }}
                                            </p>

                                        </td>


                                        <!-- Jumlah -->

                                        <td class="px-3 py-2 align-top">

                                            <input
                                                type="number"
                                                min="1"
                                                max="10000"
                                                step="1"
                                                v-model.number="item.jumlah"
                                                class="w-full border-gray-300 rounded-md text-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            />

                                            <p
                                                v-if="
                                                    form.errors[
                                                        `items.${index}.jumlah`
                                                    ]
                                                "
                                                class="text-xs text-red-600 mt-1"
                                            >
                                                {{
                                                    form.errors[
                                                        `items.${index}.jumlah`
                                                    ]
                                                }}
                                            </p>

                                        </td>


                                        <!-- Kondisi -->

                                        <td class="px-3 py-2 align-top">

                                            <select
                                                v-model="item.kondisi"
                                                class="w-full border-gray-300 rounded-md text-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            >

                                                <option
                                                    v-for="opt in kondisiOptions"
                                                    :key="opt"
                                                    :value="opt"
                                                >
                                                    {{ opt }}
                                                </option>

                                            </select>

                                            <p
                                                v-if="
                                                    form.errors[
                                                        `items.${index}.kondisi`
                                                    ]
                                                "
                                                class="text-xs text-red-600 mt-1"
                                            >
                                                {{
                                                    form.errors[
                                                        `items.${index}.kondisi`
                                                    ]
                                                }}
                                            </p>

                                        </td>


                                        <!-- Keterangan -->

                                        <td class="px-3 py-2 align-top">

                                            <label
                                                class="block text-xs font-medium text-gray-600 mb-1"
                                            >
                                                Keterangan

                                                <span
                                                    v-if="
                                                        membutuhkanKeterangan(
                                                            item.kondisi
                                                        )
                                                    "
                                                    class="text-red-600"
                                                >
                                                    *
                                                </span>
                                            </label>

                                            <input
                                                type="text"
                                                v-model="item.keterangan"
                                                :placeholder="
                                                    membutuhkanKeterangan(
                                                        item.kondisi
                                                    )
                                                        ? 'Jelaskan kondisi linen'
                                                        : 'Opsional'
                                                "
                                                maxlength="255"
                                                class="w-full border-gray-300 rounded-md text-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            />

                                            <p
                                                v-if="
                                                    form.errors[
                                                        `items.${index}.keterangan`
                                                    ]
                                                "
                                                class="text-xs text-red-600 mt-1"
                                            >
                                                {{
                                                    form.errors[
                                                        `items.${index}.keterangan`
                                                    ]
                                                }}
                                            </p>

                                        </td>


                                        <!-- Aksi -->

                                        <td
                                            class="px-3 py-2 text-center align-top"
                                        >

                                            <button
                                                type="button"
                                                @click="hapusItem(index)"
                                                :disabled="
                                                    form.items.length <= 1
                                                "
                                                title="Hapus item"
                                                class="text-red-600 hover:text-red-800 font-bold disabled:opacity-30 disabled:cursor-not-allowed"
                                            >
                                                &times;
                                            </button>

                                        </td>

                                    </tr>

                                </tbody>

                            </table>

                        </div>


                        <!-- Tombol Tambah Item -->

                        <button
                            type="button"
                            @click="tambahItem"
                            class="mt-4 inline-flex items-center px-3 py-1.5 bg-green-600 text-white text-xs font-semibold rounded-md hover:bg-green-700"
                        >
                            + Tambah Item
                        </button>

                    </div>


                    <!--
                    |--------------------------------------------------------------------------
                    | TOMBOL SUBMIT
                    |--------------------------------------------------------------------------
                    -->

                    <div class="flex justify-end">

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex items-center px-4 py-2 bg-gray-800 text-white rounded-md text-xs font-semibold uppercase tracking-widest hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed"
                        >

                            <span v-if="form.processing">
                                Menyimpan...
                            </span>

                            <span v-else>
                                Simpan Data Penerimaan
                            </span>

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </AuthenticatedLayout>
</template>