<table>
    <tr>
        <td>{{ $waktu_export }}</td>
    </tr>
    <tr>
        <td>Filter kode : {{ ($kode_numbering ? $kode_numbering : '-' ) }}</td>
        <td>Filter waktu : {{ ($waktu ? $waktu : '-' ) }}</td>
        <td>Filter department : {{ ($department ? $department : '-' ) }}</td>
        <td>Filter line : {{ ($line ? $line : '-' ) }}</td>
        <td>Filter ws : {{ ($ws ? $ws : '-' ) }}</td>
        <td>Filter style : {{ ($style ? $style : '-' ) }}</td>
        <td>Filter size : {{ ($size ? $size : '-' ) }}</td>
        <td>Filter quality check : {{ ($quality_check ? $quality_check : '-' ) }}</td>
        <td>Filter grade : {{ ($grade ? $grade : '-' ) }}</td>
        <td>Filter defect type check : {{ ($defect_type_check ? $defect_type_check : '-' ) }}</td>
        <td>Filter defect area check : {{ ($defect_area_check ? $defect_area_check : '-' ) }}</td>
    </tr>
    <tr></tr>
    <tr>
        <th style="border: 1px solid black;">Kode</th>
        <th style="border: 1px solid black;">Waktu</th>
        <th style="border: 1px solid black;">Dept.</th>
        <th style="border: 1px solid black;">Line</th>
        <th style="border: 1px solid black;">Worksheet</th>
        <th style="border: 1px solid black;">Style</th>
        <th style="border: 1px solid black;">Color</th>
        <th style="border: 1px solid black;">Size</th>
        <th style="border: 1px solid black;">Quality Check</th>
        <th style="border: 1px solid black;">Grade</th>
        <th style="border: 1px solid black;">Defect Type Check</th>
        <th style="border: 1px solid black;">Defect Area Check</th>
    </tr>
    @foreach ($rejectOut as $reject)
        <tr>
            <td style="border: 1px solid black;">{{ $reject->kode_numbering }}</td>
            <td style="border: 1px solid black;">{{ $reject->updated_at }}</td>
            <td style="border: 1px solid black;">{{ $reject->output_type }}</td>
            <td style="border: 1px solid black;">{{ $reject->username }}</td>
            <td style="border: 1px solid black;">{{ $reject->kpno }}</td>
            <td style="border: 1px solid black;">{{ $reject->styleno }}</td>
            <td style="border: 1px solid black;">{{ $reject->color }}</td>
            <td style="border: 1px solid black;">{{ $reject->size }}</td>
            <td style="border: 1px solid black;">{{ ($reject->status == 'reworked' ? 'GOOD' : 'REJECT') }}</td>
            <td style="border: 1px solid black;">{{ $reject->grade }}</td>
            <td style="border: 1px solid black;">{{ $reject->defect_types }}</td>
            <td style="border: 1px solid black;">{{ $reject->defect_areas }}</td>
        </tr>
    @endforeach
    <tr>
        <td style="border: 1px solid black;">TOTAL</td>
        <td style="border: 1px solid black;" colspan="11">{{ $rejectOut->count() }}</td>
    </tr>
</table>
