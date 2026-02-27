<table>
    <tbody>
        @foreach($rows as $row)
            <tr>
                <th>{{ $row['label'] }}</th>
                <td>{{ $row['value'] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
