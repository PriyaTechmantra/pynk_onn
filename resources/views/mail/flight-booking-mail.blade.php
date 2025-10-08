<!DOCTYPE html>
<html>
<head>
    <title>Flight Booking Confirmation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f8f8;
            margin: 0;
            padding: 20px;
        }
        .container {
            background: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            max-width: 800px;
            margin: auto;
        }
        h2 {
            color: #333;
            text-align: center;
        }
        .table-scroll {
            min-height: 300px; /* change height as needed */
            overflow-y: auto;
            overflow-x: auto;
            border: 1px solid #ddd;
            margin-top: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            font-size: 14px;
        }
        th {
            background-color: #007bff;
            color: white;
            white-space: nowrap;
        }
        .footer {
            text-align: center;
            font-size: 14px;
            margin-top: 20px;
            color: #666;
        }
        .table-block {
            display:flex;
            align-items:center;
            border-bottom:1px solid #ccc;
        }
        .table-block .mail-head {
            max-width:140px;
            flex:0 0 140px;
            font-size:15px;
            font-weight:bold;
            color:#000;
            padding:5px;
        }
        .table-block .mail-body {
            flex:1;
            padding:5px;
            font-size:14px;
            color:#000;
        }
    </style>
</head>
<body>

    <div class="container">
        <h2>Flight Booking Confirmation</h2>
        <p>Dear {{ $name }},</p>
        <p>Your flight booking has been successfully recorded. Below are the details:</p>
        <div class="table-scroll">


            <div class="table-block">
                <div class="mail-head">Order No<div>
                <div class="mail-body"><div>
            </div>



            <table>
            <!-- <thead>
                <tr>
                    
                    <th>Order No</th>
                    
                    <th>From</th>
                    <th>To</th>
                    <th>Trip Type</th>
                    <th>Departure Date</th>
                    <th>Preferred Departure Time</th>
                    <th>Return Date</th>
                    <th>Traveller</th>
                    <th>Seat Preference</th>
                    <th>Food Preference</th>
                    <th>Bill to</th>
                    <th>Matter Code</th>
                    <th>Purpose/Description</th>
                </tr>
            </thead> -->
            <tbody>
               
                    @php
                        $travellers = explode(',', $flightBooking->traveller);
                        $seatPreferences = explode(',', $flightBooking->seat_preference ?? '');
                        $foodPreferences = explode(',', $flightBooking->food_preference ?? '');

                        $formattedTravellers = [];
                        foreach ($travellers as $i => $traveller) {
                            if (trim($traveller) !== '') {
                                $formattedTravellers[] = [
                                    'name' => trim($traveller),
                                    'seat_preference' => $seatPreferences[$i] ?? 'N/A',
                                    'food_preference' => $foodPreferences[$i] ?? 'N/A',
                                ];
                            }
                        }
                    @endphp
                    @if (count($formattedTravellers) > 0)
                        @foreach ($formattedTravellers as $key => $traveller)
                            <tr>
                                <td>
                                    <table>
                                    @if ($key == 0)
                                        <tr>
                                            <td>Order No</td>
                                            <td rowspan="{{ count($formattedTravellers) }}">{{ $flightBooking->order_no }}</td>
                                        </tr>

                                        <tr>
                                            <td>From</td>
                                            <td rowspan="{{ count($formattedTravellers) }}">{{ $flightBooking->from }}</td>
                                        </tr>

                                        <tr>
                                            <td>To</td>
                                            <td rowspan="{{ count($formattedTravellers) }}">{{ $flightBooking->to }}</td>
                                        </tr>

                                        <tr>
                                            <td>Trip Type</td>
                                            <td rowspan="{{ count($formattedTravellers) }}">
                                                {{ $flightBooking->trip_type == 1 ? 'One way' : 'Round Trip' }}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>Departure Date</td>
                                            <td rowspan="{{ count($formattedTravellers) }}">{{ date('j M Y', strtotime($flightBooking->departure_date)) }}</td>
                                        </tr>
                                        <tr>
                                            <td>Preferred Departure Time</td>
                                            <td rowspan="{{ count($formattedTravellers) }}">{{ $flightBooking->arrival_time }}</td>
                                        </tr>
                                        <tr>
                                            <td>Return Date</td>
                                            <td rowspan="{{ count($formattedTravellers) }}">
                                                {{ $flightBooking->trip_type == 2 ? ($flightBooking->return_date ?? '') : '' }}
                                            </td>
                                        </tr>

                                    @endif
                                        <tr>
                                            <td>Traveller</td>
                                            <td>{{ $traveller['name'] ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td>Seat Preference</td>
                                            <td>{{ $traveller['seat_preference'] ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td>Food Preference</td>
                                            <td>{{ $traveller['food_preference'] ?? 'N/A' }}</td>
                                        </tr>
                                    
                                    @if ($key == 0)
                                        <tr>
                                            <td>Bill to</td>
                                            <td rowspan="{{ count($formattedTravellers) }}">
                                                {{ $flightBooking->bill_to == 1 ? 'Firm' : ($flightBooking->bill_to == 2 ? 'Third Party' : 'Matter Expenses') }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Matter Code</td>
                                            <td rowspan="{{ count($formattedTravellers) }}">{{ $flightBooking->matter_code }}</td>
                                        </tr>
                                        <tr>
                                            <td>Purpose/Description</td>
                                            <td rowspan="{{ count($formattedTravellers) }}">{{ $flightBooking->purpose_description ?? '' }}</td>
                                        </tr>
                                        
                                        
                                        
                                    @endif
                                    </table>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td>
                                <table>
                                    <tr>
                                        <td>Order No</td>
                                        <td>{{ $flightBooking->order_no }}</td>
                                    </tr>
                                    <tr>
                                        <td>From</td>
                                        <td>{{ $flightBooking->from }}</td>
                                    </tr>
                                    <tr>
                                        <td>To</td>
                                        <td>{{ $flightBooking->to }}</td>
                                    </tr>
                                    <tr>
                                        <td>Trip Type</td>
                                        <td>{{ $flightBooking->trip_type == 1 ? 'One way' : 'Round Trip' }}</td>
                                    </tr>
                                    <tr>
                                        <td>Departure Date</td>
                                        <td> {{date('j M Y', strtotime($flightBooking->departure_date)) }}</td>
                                    </tr>
                                    <tr>
                                        <td>Preferred Departure Time</td>
                                        <td>{{ $flightBooking->arrival_time }}</td>
                                    </tr>
                                    <tr>
                                        <td>Return Date</td>
                                        <td>{{ $flightBooking->trip_type == 2 ? ($flightBooking->return_date ?? '') : '' }}</td>
                                    </tr>
                                    <tr>
                                        <td>Traveller</td>
                                        <td>N/A</td>
                                    </tr>
                                    <tr>
                                        <td>Seat Preference</td>
                                        <td>N/A</td>
                                    </tr>
                                    <tr>
                                        <td>Food Preference</td>
                                        <td>N/A</td>
                                    </tr>
                                    <tr>
                                        <td>Bill to</td>
                                        <td>{{ $flightBooking->bill_to == 1 ? 'Firm' : ($flightBooking->bill_to == 2 ? 'Third Party' : 'Matter Expenses') }}</td>
                                    </tr>
                                    <tr>
                                        <td>Matter Code</td>
                                        <td>{{ $flightBooking->matter_code }}</td>
                                    </tr>
                                    <tr>
                                        <td>Purpose/Description</td>
                                        <td>{{ $flightBooking->purpose_description ?? '' }}</td>
                                    </tr>

                                </table>
                            <td>
                        </tr>
                    @endif
                
            </tbody>
        </table>
        </div>
        <p class="footer">Thank you for choosing us. Safe travels!</p>
    </div>

</body>
</html>
