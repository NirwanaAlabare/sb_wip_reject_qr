<table>
    <thead>
        <tr>
            <th style="border: 1px solid black; font-weight: 800;">Time IN</th>
            <th style="border: 1px solid black; font-weight: 800;">Time OUT</th>
            <th style="border: 1px solid black; font-weight: 800;">Line</th>
            <th style="border: 1px solid black; font-weight: 800;">Dept.</th>
            <th style="border: 1px solid black; font-weight: 800;">QR</th>
            <th style="border: 1px solid black; font-weight: 800;">No. WS</th>
            <th style="border: 1px solid black; font-weight: 800;">Style</th>
            <th style="border: 1px solid black; font-weight: 800;">Color</th>
            <th style="border: 1px solid black; font-weight: 800;">Size</th>
            <th style="border: 1px solid black; font-weight: 800;">Type</th>
            <th style="border: 1px solid black; font-weight: 800;">Area</th>
            <th style="border: 1px solid black; font-weight: 800;">Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($rejectInOut as $reject)
            <tr>
                <td style="border: 1px solid black;">{{ $reject->time_in }}</td>
                <td style="border: 1px solid black;">{{ $reject->time_out }}</td>
                <td style="border: 1px solid black;">{{ $reject->sewing_line }}</td>
                <td style="border: 1px solid black;">{{ ($reject->output_type == "packing" ? "finishing" : $reject->output_type) }}</td>
                <td style="border: 1px solid black;">{{ $reject->kode_numbering }}</td>
                <td style="border: 1px solid black;">{{ $reject->no_ws }}</td>
                <td style="border: 1px solid black;">{{ $reject->style }}</td>
                <td style="border: 1px solid black;">{{ $reject->color }}</td>
                <td style="border: 1px solid black;">{{ $reject->size }}</td>
                <td style="border: 1px solid black;">{{ $reject->defect_type }}</td>
                <td style="border: 1px solid black;">{{ $reject->defect_area }}</td>
                <td style="border: 1px solid black;">{{ $reject->status }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
