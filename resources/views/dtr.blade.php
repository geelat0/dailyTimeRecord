<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Daily Time Record</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            margin: 1cm;
            
            
        }
        .header-legend-container {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 10px;
        }
        .header {
            text-align: left;
            width: 60%;
        }
        .header h2, .header p {
            margin: 2px;
        }
        .legend {
            font-size: 9px;
            text-align: left;
            width: 35%;
        }
        .legend .title {
            font-weight: bold;
            margin-bottom: 5px;
        }
        .legend-items {
            display: flex;
            justify-content: space-between;
            margin-left: 20px;
        }
        .items-left, .items-right {
            width: 48%; /* Adjust width to fit both columns */
        }
        .items-left p, .items-right p {
            margin: 2px 0;
        }
        .note {
            margin-top: 10px;
            font-size: 8px;
            margin-left: 20px;

        }
        .info {
            margin-top: 20px;
            font-size: 12px;
            display: flex;
            justify-content: space-between; /* Distributes items evenly */
            align-items: center; /* Aligns items vertically */
        }
        .info p {
            margin: 0; /* Removes extra spacing between elements */
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
        }
        th, td {
            border: 1px solid black;
            padding: 4px;
            text-align: center;
        }
        .signature {
            position: absolute;
            bottom: 0;
            left: 0;
            margin-bottom: 100px;
            width: 100%;
            text-align: center;
            font-size: 10px;
        }
        .signature div {
            display: inline-block;
            width: 40%;
            text-align: center;
            margin-top: 10px;
            border-top: 1px solid black;
            margin-right: 2%; /* Add space between the two divs */
        }
        .cut-off {
            margin-top: 30px;
            font-size: 12px;
        }
        .certify{
            font-size: 10px;
            margin-bottom: 50px;
            text-align: center;
        }
        .name {
            font-weight: bold;
            font-size: 18px;
        }
    </style>
</head>
<body>
    <div style="text-align: right; font-size: 10px; margin-bottom: 10px;">
        <p><b>Date Printed:</b> {{ \Carbon\Carbon::now()->format('m/d/Y') }}</p>
    </div>
    <div class="header-legend-container">
        <div class="header">
            <h2>Department of Social Welfare & Development V</h2>
            <p>Regional Government Site Rawis</p>
            <p>Legazpi</p>
            <p>Philippines</p>


            <div class="cut-off">
                <p>fo5.dswd.gov.ph</p>
                <p style="font-size: 12px;"><b>Daily Time Record for the period of <b>{{ $start }}</b> to <b>{{ $end }}</b></b></p>
            </div>

            <div class="info">
                <p>Employee No.: <b>{{ $employee_no }}</b></p>
                <p>Name: <b class="name">{{ $name }}</b></p>
            </div>
        </div>

       
        <div class="legend">
            <p class="title">Legend:</p>
            <div class="legend-items">
                <div class="items-left">
                    <p>LOW - Length of Work</p>
                    <p>OT - Over Time</p>
                    <p>UT - Under Time</p>
                    <p>LT - Late</p>
                    <p>ND - Night Differential</p>
                </div>
                <div class="items-right">
                    <p>WD - Whole Day</p>
                    <p>HD - Half Day</p>
                    <p>HDL - Half Day Leave</p>
                </div>
            </div>
            <div class="note">
                <p style="text-align: center;"><b>Note:</b> *** All the computations below are in hours and minutes format.</p>
            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th rowspan="2">Date</th>
                <th rowspan="2">Day</th>
                <th rowspan="2">In</th>
                <th colspan="2">Break</th>
                <th rowspan="2">Out</th>
                <th colspan="2">Overtime</th>
                <th rowspan="2">LOW</th>
                <th rowspan="2">OT</th>
                <th rowspan="2">UT</th>
                <th rowspan="2">LT</th>
                <th rowspan="2">Short Break</th>
                <th colspan="2">ND</th>
                <th colspan="2">Others</th>
            </tr>
            <tr>
                <th>Out</th>
                <th>In</th>
                <th>In</th>
                <th>Out</th>
                <th>Reg</th>
                <th>OT</th>
                <th>Status</th>
                <th>Holidays</th>
            </tr>
        </thead>
        <tbody>
            @foreach($records as $record)
                <tr>
                    <td>{{ $record['date'] }}</td>
                    <td>{{ $record['day'] }}</td>
                    <td>{{ $record['in'] }}</td>
                    <td>{{ $record['break_out'] }}</td>
                    <td>{{ $record['break_in'] }}</td>
                    <td>{{ $record['out'] }}</td>
                    <td>{{ $record['ot_in'] }}</td>
                    <td>{{ $record['ot_out'] }}</td>
                    <td>{{ $record['low'] }}</td>
                    <td>{{ $record['ot'] }}</td>
                    <td>{{ $record['ut'] }}</td>
                    <td>{{ $record['lt'] }}</td>
                    <td>{{ $record['short_break'] }}</td>
                    <td>{{ $record['nd_reg'] }}</td>
                    <td>{{ $record['nd_ot'] }}</td>
                    <td>{{ $record['status'] }}</td>
                    <td style="color: {{ $record['holidays'] === 'Rest Day' ? 'red' : 'black' }};">
                        {{ $record['holidays'] }}
                    </td>                 
                </tr>
            @endforeach
        </tbody>
    </table>

  

    <div class="signature">
        <p class="certify">I certify that the entries in this record were made by myself daily at the time of arrival and departure from the office and are true and correct.</p>
        <div>Employee’s Signature</div>
        <div>Authorized Official</div>
    </div>
</body>
</html>
