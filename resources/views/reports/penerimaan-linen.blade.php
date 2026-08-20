<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <title>
        Rekap Penerimaan Linen
    </title>

    <style>

        @page {
            margin: 25px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #222;
        }

        h1 {
            font-size: 18px;
            text-align: center;
            margin-bottom: 5px;
        }

        h2 {
            font-size: 13px;
            margin-top: 20px;
            margin-bottom: 8px;
        }

        .subtitle {
            text-align: center;
            font-size: 10px;
            margin-bottom: 20px;
        }

        .summary {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .summary td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }

        .summary .label {
            font-size: 9px;
            color: #666;
        }

        .summary .value {
            font-size: 16px;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        th {
            background: #f1f5f9;
            font-weight: bold;
        }

        th,
        td {
            border: 1px solid #d1d5db;
            padding: 5px;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .status-baik {
            color: #15803d;
            font-weight: bold;
        }

        .status-waspada {
            color: #a16207;
            font-weight: bold;
        }

        .status-perhatian {
            color: #b91c1c;
            font-weight: bold;
        }

        .footer {
            margin-top: 20px;
            font-size: 8px;
            color: #666;
        }

    </style>

</head>


<body>

    <h1>
        LAPORAN REKAP PENERIMAAN LINEN
    </h1>

    <div class="subtitle">

        Periode:
        {{ $filters['tanggal_mulai'] }}
        s/d
        {{ $filters['tanggal_akhir'] }}

        <br>

        Ruangan:
        {{ $filters['ruangan'] ?: 'Semua Ruangan' }}

    </div>


    <!-- KPI -->

    <table class="summary">

        <tr>

            <td>
                <div class="label">
                    Total Transaksi
                </div>

                <div class="value">
                    {{ number_format($summary['total_transaksi'], 0, ',', '.') }}
                </div>
            </td>


            <td>
                <div class="label">
                    Total Linen
                </div>

                <div class="value">
                    {{ number_format($summary['total_linen'], 0, ',', '.') }}
                </div>
            </td>


            <td>
                <div class="label">
                    Baik
                </div>

                <div class="value">
                    {{ number_format($summary['total_baik'], 0, ',', '.') }}
                </div>
            </td>


            <td>
                <div class="label">
                    Noda
                </div>

                <div class="value">
                    {{ number_format($summary['total_noda'], 0, ',', '.') }}
                </div>
            </td>


            <td>
                <div class="label">
                    Rusak
                </div>

                <div class="value">
                    {{ number_format($summary['total_rusak'], 0, ',', '.') }}
                </div>
            </td>


            <td>

                <div class="label">
                    % Bermasalah
                </div>

                <div class="value">
                    {{ number_format($summary['persentase_bermasalah'], 2, ',', '.') }}%
                </div>

            </td>


            <td>

                <div class="label">
                    Status
                </div>

                <div
                    class="
                        value
                        @if($summary['status_laporan'] === 'Baik')
                            status-baik
                        @elseif($summary['status_laporan'] === 'Waspada')
                            status-waspada
                        @else
                            status-perhatian
                        @endif
                    "
                >
                    {{ $summary['status_laporan'] }}
                </div>

            </td>

        </tr>

    </table>


    <!-- Rekap Ruangan -->

    <h2>
        1. Rekap Berdasarkan Ruangan
    </h2>

    <table>

        <thead>

            <tr>

                <th>
                    No
                </th>

                <th>
                    Ruangan
                </th>

                <th>
                    Transaksi
                </th>

                <th>
                    Total
                </th>

                <th>
                    Baik
                </th>

                <th>
                    Noda
                </th>

                <th>
                    Rusak
                </th>

                <th>
                    Bermasalah
                </th>

                <th>
                    %
                </th>

            </tr>

        </thead>


        <tbody>

            @foreach($rekapRuangan as $index => $row)

                <tr>

                    <td class="text-center">
                        {{ $index + 1 }}
                    </td>

                    <td>
                        {{ $row['ruangan'] }}
                    </td>

                    <td class="text-center">
                        {{ number_format($row['total_transaksi'], 0, ',', '.') }}
                    </td>

                    <td class="text-center">
                        {{ number_format($row['total_linen'], 0, ',', '.') }}
                    </td>

                    <td class="text-center">
                        {{ number_format($row['total_baik'], 0, ',', '.') }}
                    </td>

                    <td class="text-center">
                        {{ number_format($row['total_noda'], 0, ',', '.') }}
                    </td>

                    <td class="text-center">
                        {{ number_format($row['total_rusak'], 0, ',', '.') }}
                    </td>

                    <td class="text-center">
                        {{ number_format($row['total_bermasalah'], 0, ',', '.') }}
                    </td>

                    <td class="text-center">
                        {{ number_format($row['persentase_bermasalah'], 2, ',', '.') }}%
                    </td>

                </tr>

            @endforeach

        </tbody>

    </table>


    <!-- Rekap Jenis Linen -->

    <h2>
        2. Rekap Berdasarkan Jenis Linen
    </h2>

    <table>

        <thead>

            <tr>

                <th>
                    No
                </th>

                <th>
                    Jenis Linen
                </th>

                <th>
                    Total
                </th>

                <th>
                    Baik
                </th>

                <th>
                    Noda
                </th>

                <th>
                    Rusak
                </th>

                <th>
                    Bermasalah
                </th>

                <th>
                    %
                </th>

            </tr>

        </thead>


        <tbody>

            @foreach($rekapJenisLinen as $index => $row)

                <tr>

                    <td class="text-center">
                        {{ $index + 1 }}
                    </td>

                    <td>
                        {{ $row['nama_item'] }}
                    </td>

                    <td class="text-center">
                        {{ number_format($row['total_linen'], 0, ',', '.') }}
                    </td>

                    <td class="text-center">
                        {{ number_format($row['total_baik'], 0, ',', '.') }}
                    </td>

                    <td class="text-center">
                        {{ number_format($row['total_noda'], 0, ',', '.') }}
                    </td>

                    <td class="text-center">
                        {{ number_format($row['total_rusak'], 0, ',', '.') }}
                    </td>

                    <td class="text-center">
                        {{ number_format($row['total_bermasalah'], 0, ',', '.') }}
                    </td>

                    <td class="text-center">
                        {{ number_format($row['persentase_bermasalah'], 2, ',', '.') }}%
                    </td>

                </tr>

            @endforeach

        </tbody>

    </table>


    <!-- Tren -->

    <h2>
        3. Tren Harian
    </h2>

    <table>

        <thead>

            <tr>

                <th>
                    Tanggal
                </th>

                <th>
                    Transaksi
                </th>

                <th>
                    Total Linen
                </th>

                <th>
                    Baik
                </th>

                <th>
                    Noda
                </th>

                <th>
                    Rusak
                </th>

            </tr>

        </thead>


        <tbody>

            @foreach($trenHarian as $row)

                <tr>

                    <td>
                        {{ $row['tanggal'] }}
                    </td>

                    <td class="text-center">
                        {{ number_format($row['total_transaksi'], 0, ',', '.') }}
                    </td>

                    <td class="text-center">
                        {{ number_format($row['total_linen'], 0, ',', '.') }}
                    </td>

                    <td class="text-center">
                        {{ number_format($row['total_baik'], 0, ',', '.') }}
                    </td>

                    <td class="text-center">
                        {{ number_format($row['total_noda'], 0, ',', '.') }}
                    </td>

                    <td class="text-center">
                        {{ number_format($row['total_rusak'], 0, ',', '.') }}
                    </td>

                </tr>

            @endforeach

        </tbody>

    </table>


    <div class="footer">

        Laporan dihasilkan oleh Sistem Informasi Laundry Rumah Sakit.

        <br>

        Waktu generate:
        {{ now()->format('d-m-Y H:i:s') }}

    </div>

</body>

</html>