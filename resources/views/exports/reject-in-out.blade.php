<table>
    <tr>
        <td>Dari : {{ $dateFrom }}</td>
        <td>Sampai : {{ $dateTo }}</td>
    </tr>
    <tr></tr>
    <tr>
        <th style="border: 1px solid #000; font-weight:  800;">Kode</th>
        <th style="border: 1px solid #000; font-weight:  800;">Waktu Cek</th>
        <th style="border: 1px solid #000; font-weight:  800;">Dept.</th>
        <th style="border: 1px solid #000; font-weight:  800;">Line</th>
        <th style="border: 1px solid #000; font-weight:  800;">No. WS</th>
        <th style="border: 1px solid #000; font-weight:  800;">Style</th>
        <th style="border: 1px solid #000; font-weight:  800;">Color</th>
        <th style="border: 1px solid #000; font-weight:  800;">Size</th>
        <th style="border: 1px solid #000; font-weight:  800;">Defect Type QC</th>
        <th style="border: 1px solid #000; font-weight:  800;">Quality Check</th>
        <th style="border: 1px solid #000; font-weight:  800;">Grade</th>
        <th style="border: 1px solid #000; font-weight:  800;">Defect Type Check</th>
        <th style="border: 1px solid #000; font-weight:  800;">Defect Area Check</th>
    </tr>
    @foreach ($rejectInOut as $reject)
        <tr>
            <td style="border: 1px solid #000;">{{ $reject->kode_numbering }}</td>
            <td style="border: 1px solid #000;">{{ $reject->time_in }}</td>
            <td style="border: 1px solid #000;">{{ ($reject->output_type == "packing" ? "FINISHING" : strtoupper($reject->output_type)) }}</td>
            <td style="border: 1px solid #000;">{{ $reject->sewing_line }}</td>
            <td style="border: 1px solid #000;">{{ $reject->no_ws }}</td>
            <td style="border: 1px solid #000;">{{ $reject->style }}</td>
            <td style="border: 1px solid #000;">{{ $reject->color }}</td>
            <td style="border: 1px solid #000;">{{ $reject->size }}</td>
            <td style="border: 1px solid #000;">{{ $reject->defect_type }}</td>
            <td style="border: 1px solid #000;">{{ $reject->status == 'reworked' ? 'GOOD' : 'REJECT' }}</td>
            <td style="border: 1px solid #000;">{{ $reject->grade }}</td>
            <td style="border: 1px solid #000;">{{ $reject->defect_types_check }}</td>
            <td style="border: 1px solid #000;">{{ $reject->defect_areas_check }}</td>
        </tr>
    @endforeach
</table>
