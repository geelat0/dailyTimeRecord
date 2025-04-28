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
            font-size: 10px;
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
            font-size: 10px;

        }
        .items-left, .items-right {
            width: 48%; /* Adjust width to fit both columns */
        }
        .items-left p, .items-right p {
            margin: 2px 0;
        }
        .note {
            margin-top: 10px;
            margin-left: 20px;
            font-size: 10px;


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
            border: 0.1px solid black;
            padding: 4px;
            text-align: center;
            font-weight: normal;
        }

        th {
            font-size: 10px;
        }
        td {
            font-size: 7px;
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
            margin: 0 2%;
            font-size: 10px;
            position: relative;
        }
        .signature div::before {
            content: '';
            position: absolute;
            top: -25px;
            left: 0;
            width: 100%;
            border-top: 1px solid black;
        }
        .cut-off {
            margin-top: 30px;
            font-size: 12px;
        }
        .certify{
            font-size: 7px;
            margin-bottom: 70px;
            text-align: center;
        }
        .name {
            font-weight: bold;
            font-size: 18px;
        }

        .total-header {
            text-align: center;
            font-weight: bold;
            letter-spacing: 2px;
            font-size: 14px;

        }
        .table-container {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding-top: 5px;
        }
        .table-left, .table-right {
            width: 48%;
        }

        .total-table {
            width: 100%;
            border-collapse: collapse;
        }

        /* .total-table td th{
            padding: 5px;
            text-align: left;
            font-size: 12px;
            border:none;
        } */

        .total-table td {
          border:none;
          font-size: 10px;
          text-align: right;


        }
        .bold {
            font-weight: bold;
        }
        .info_title{
            font-weight: normal;
            text-decoration: underline
        }
    </style>
</head>
<body>
    <div style="text-align: right; font-size: 10px; margin-bottom: 10px;">
        <p>Date Printed: {{ \Carbon\Carbon::now()->format('m/d/Y') }}</p>
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
                <p> <b class="info_title">Employee No.:</b> <b> {{ $employee_no }}</b></p>
                <p><b class="info_title">Name :</b> <b class="name">{{ $name }}</b></p>
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
                <th rowspan="2" style="width: 6%;">Short Break LT</th>
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

    <div class="total-header">T O T A L</div>
    <div class="table-container">
        <div class="table-left">
            <table class="total-table">
                <tr class="bold">
                    <td></td><td>REG</td>
                    <td>OFF</td> <td>SHP</td> <td>LHP</td> <td>SHP/OFF</td> <td>LHP/OFF</td>
                </tr>
                <tr>
                    <td class="bold">LOW :</td> <td>0.00</td>
                    <td>0.00</td> <td>0.00</td> <td>0.00</td> <td>0.00</td> <td>0.00</td>
                </tr>
                <tr>
                    <td class="bold">OT :</td> <td>0.00</td>
                    <td>0.00</td> <td>0.00</td> <td>0.00</td> <td>0.00</td> <td>0.00</td>
                </tr>
                <tr>
                    <td class="bold">ND :</td> <td>0.00</td>
                    <td>0.00</td> <td>0.00</td> <td>0.00</td> <td>0.00</td> <td>0.00</td>
                </tr>
                <tr>
                    <td class="bold">OT ND :</td> <td>0.00</td>
                    <td>0.00</td> <td>0.00</td> <td>0.00</td> <td>0.00</td> <td>0.00</td>
                </tr>
            </table>
        </div>

        <div class="table-right">
            <table class="total-table">
                <tr>
                    <td class="bold">Length of Work :</td> <td>{{$lengthOfWork == 0 ? '0.0' : $lengthOfWork}}</td>
                    <td class="bold">Absences :</td> <td>{{ $absence == 0 ? '0.0' : $absence }}</td>
                </tr>
                <tr>
                    <td class="bold">Special Holiday :</td> <td>0.0</td>
                    <td class="bold">Day-Off :</td> <td>{{ $dayOff == 0 ? '0.0' : $dayOff }}</td>
                </tr>
                <tr>
                    <td class="bold">Legal Holiday :</td> <td>0.0</td>
                    <td class="bold">Late Minutes :</td> <td>{{ $late == 0 ? '0.0' : $late }}</td>
                </tr>
                <tr>
                    <td class="bold">Leave :</td> <td>{{ $leave == 0 ? '0.0' : $leave }}</td>
                    <td class="bold">UT Minutes :</td> <td>{{ $undertime == 0 ? '0.0' : $undertime }}</td>
                </tr>
                <tr>
                    <td></td><td></td>
                    <td class="bold" style="font-size: 12px;">Total  :</td> <td>{{ $totalLateAndUndertimeandAbsence == 0 ? '0.0' : $totalLateAndUndertimeandAbsence }}</td>
                </tr>

            </table>
        </div>
    </div>

    <div class="signature">
        <p class="certify">I certify that the entries in this record were made by myself daily at the time of arrival and departure from the office and are true and correct.</p>
        <div>
            @if($signature)
                <img src="{{ $signature ? $signature : '' }}" alt="Employee Signature" style="max-width: 190px; max-height: 70px; position: absolute; bottom: 25px; left: 50%; transform: translateX(-50%); mix-blend-mode: multiply; -webkit-filter: grayscale(100%); filter: grayscale(100%);">
            @endif
            Employee's Signature
        </div>
        <div>
            Authorized Official
        </div>
    </div>
</body>
</html>
