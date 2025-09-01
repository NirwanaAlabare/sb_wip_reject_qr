<table>
    <tr>
        <td>Dari : {{ $tanggal_awal }}</td>
        <td>Sampai : {{ $tanggal_akhir }}</td>
    </tr>
    <tr>

        <td>Filter tanggal : {{ ($tanggal ? $tanggal : "-")}}</td>
        <td>Filter no_transaksi : {{ ($no_transaksi ? $no_transaksi : "-")}}</td>
        <td>Filter tujuan : {{ ($tujuan ? $tujuan : "-")}}</td>
        <td>Filter ws : {{ ($kpno ? $kpno : "-")}}</td>
        <td>Filter style : {{ ($styleno ? $styleno : "-")}}</td>
        <td>Filter color : {{ ($color ? $color : "-")}}</td>
        <td>Filter size : {{ ($size ? $size : "-")}}</td>
    </tr>
    <tr></tr>
    <tr>
        <th style="border: 1px solid black;font-weight: 800;">Tanggal Kirim</th>
        <th style="border: 1px solid black;font-weight: 800;">No. Transaksi</th>
        <th style="border: 1px solid black;font-weight: 800;">Tujuan</th>
        <th style="border: 1px solid black;font-weight: 800;">Kode</th>
        <th style="border: 1px solid black;font-weight: 800;">Worksheet</th>
        <th style="border: 1px solid black;font-weight: 800;">Style</th>
        <th style="border: 1px solid black;font-weight: 800;">Color</th>
        <th style="border: 1px solid black;font-weight: 800;">Size</th>
        <th style="border: 1px solid black;font-weight: 800;">Quality Check</th>
        <th style="border: 1px solid black;font-weight: 800;">Grade</th>
        <th style="border: 1px solid black;font-weight: 800;">Defect Type Check</th>
        <th style="border: 1px solid black;font-weight: 800;">Defect Area Check</th>
    </tr>
    @foreach ($rejectOutDetail as $reject)
        <tr>
            <td style="border: 1px solid black;">{{  $reject->tanggal  }}</td>
            <td style="border: 1px solid black;">{{  $reject->no_transaksi  }}</td>
            <td style="border: 1px solid black;">{{  strtoupper($reject->tujuan)  }}</td>
            <td style="border: 1px solid black;">{{  $reject->kode_numbering  }}</td>
            <td style="border: 1px solid black;">{{  $reject->kpno  }}</td>
            <td style="border: 1px solid black;">{{  $reject->styleno  }}</td>
            <td style="border: 1px solid black;">{{  $reject->color  }}</td>
            <td style="border: 1px solid black;">{{  $reject->size  }}</td>
            <td style="border: 1px solid black;{{ ($reject->status == "reworked" ? "color: '#32a852';" : "color: '#db2525';") }}">{{  ($reject->status == "reworked" ? "GOOD" : "REJECT")  }}</td>
            <td style="border: 1px solid black;">{{  $reject->grade  }}</td>
            <td style="border: 1px solid black;">{{  $reject->defect_types  }}</td>
            <td style="border: 1px solid black;">{{  $reject->defect_areas  }}</td>
        </tr>
    @endforeach
    <tr>
        <td style="border: 1px solid black;">TOTAL</td>
        <td style="border: 1px solid black;" colspan="11">{{ $rejectOutDetail->count() }}</td>
    </tr>
</table>
