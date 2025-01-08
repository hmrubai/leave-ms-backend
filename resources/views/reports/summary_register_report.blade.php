<!DOCTYPE html>
<html>
<head>
    <title>Summary Register Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin-bottom: 40mm;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            text-align: left;
            padding: 8px;
        }
        th {
            background-color: #f4f4f4;
        }

        .logo-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            text-align: center;
        }
        .logo-header img {
            height: 50px;
        }
        .logo-header .report-title {
            font-size: 16px;
            font-weight: bold;
            text-align: center;
            flex: 1;
        }
        .custom-hr {
            margin: 10px 0;
            width: 100%;
            border: none; border-top: 1px solid #333; border-image: linear-gradient(to right, #ff7e5f, #feb47b) 1;
        }

        /* Footer Styles */
        @page {
            margin: 20mm 10mm;
        }
        .footer {
            position: fixed;
            bottom: -20mm;
            left: 0;
            right: 0;
            height: 20mm;
            text-align: center;
            font-size: 12px;
            color: #555;
            border-top: 1px solid #ddd;
        }
        .footer .page-number:before {
            content: "Page " counter(page) " of " counter(pages);
        }

    </style>
</head>
<body>

    <div class="logo-header">
        <img src="{{ public_path('images/bb_logo.png') }}" alt="BacBon Limited"/>
        <p><strong>Address:</strong> House # 13 (8th Floor), Block-C, Main Road, Banasree, Rampura, Dhaka-1219.</p>
        <p><strong>Phone:</strong> 09611900205, +88 02 8396601, <strong>Email:</strong> info@bacbonltd.com</p>
        <br/>
        <div class="report-title">Summary Register Report</div>
    </div>
    <hr class="custom-hr">

    @if ($has_department)
        <p><strong>Department Name:</strong> {{ $department->name }}</p>
        <p><strong>Employee Count:</strong> {{ $employee_count }}</p>
    @endif
    @if (!$has_department)
        <p><strong>Employee Count:</strong> {{ $employee_count }}</p>
    @endif

    <p><strong>Date Range:</strong> {{ request()->start_date }} to {{ request()->end_date }}</p>
    <hr class="custom-hr">
    <br/>
    <table>
        <thead>
            <tr>
                <th>Leave Policy</th>
                <th>Availed Days</th>
                <th>Application Count</th>
                <th>Half Day Count</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($report as $item)
                <tr>
                    <td>{{ $item->leave_title }}({{ $item->leave_short_code }})</td>
                    <td>{{ $item->total_applied_days }}</td>
                    <td>{{ $item->total_leave_count }}</td>
                    <td>{{ $item->half_day_count }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <br/>
        <span class="page-number"></span>
        <br>
        <span style="font-size: 13px;">© {{ date('Y') }} <span style="color:rgb(0, 125, 215); font-weight: bold;">BacBon Limited.</span> All Rights Reserved.</span>
    </div>
</body>
</html>