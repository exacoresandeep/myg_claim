<table>
    <thead>
        <tr>
            <th>Trip ID</th>
            <th>Date</th>
            <th>Trip Type</th>
            <th>Employee Name</th>
            <th>Employee ID</th>
            <th>Visit Branch</th>
            <th>Grade</th>
            <th>Department</th>
            <th>Amount</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $item)
        <tr>
            <td>{{ $item['TripClaimID'] }}</td>
            <td>{{ $item['Date'] }}</td>
            <td>{{ $item['TripType'] }}</td>
            <td>{{ $item['EmployeeName'] }}</td>
            <td>{{ $item['EmployeeID'] }}</td>
            <td>{{ $item['VisitBranch'] }}</td>
            <td>{{ $item['Grade'] }}</td>
            <td>{{ $item['Department'] }}</td>
            <td>{{ $item['Amount'] }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
