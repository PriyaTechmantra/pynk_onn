<?php

namespace App\Http\Controllers\Api\Fms;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CabBooking;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use DB;
class CabBookingController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            //'booking_type' => 'required|integer|in:1,2,3',
            'from_location' => 'required|string|max:255',
            'bill_to' => 'required|integer|in:1,2,3',
            //'cab_type' => 'required|integer|in:1,2,3',
            'to_location' => [
                //'required_if:booking_type,1,3',
                'required',
                'string',
                'max:255'
            ],
            //'departure_date' => [
              //  'required_if:booking_type,1,3',
              //  'nullable',
             //   'date'
            //],
            'pickup_date' => [
               // 'required_if:booking_type,2',
                'required',
                'date',
            ],
            'pickup_time' => 'required',
            /*'hours' => [
                'required_if:booking_type,2',
                'nullable',
                'integer',
                'min:1',
            ],*/
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'error' => $validator->errors()
            ], 400);
        }

        $validatedData = $validator->validated();
            $orderData = CabBooking::select('sequence_no')->latest('sequence_no')->first();
            
            //if (empty($orderData->sequence_no)) {
                if (!empty($orderData->sequence_no)) {
                    $new_sequence_no = (int) $orderData->sequence_no + 1;
    
                } else {
                    $new_sequence_no = 1;
    
                }
                $ordNo = sprintf("%'.05d", $new_sequence_no);
                $uniqueNo = 'FM'.'-'.'CB'.'-'.date('Y').'-'.$ordNo;
        //$departureDate = isset($validatedData['departure_date'])
           // ? Carbon::parse($validatedData['departure_date'])->format('Y-m-d')
           // : null;

        //$pickupDate = isset($validatedData['pickup_date'])? Carbon::parse($validatedData['pickup_date'])->format('Y-m-d'): null;

        //$pickupTime = isset($validatedData['pickup_time'])? Carbon::createFromFormat('h:i', $validatedData['pickup_time'])->format('H:i:s'): null;

        $booking = CabBooking::create([
            'user_id' => $validatedData['user_id'],
            'bill_to' => $validatedData['bill_to'],
           // 'cab_type' => $validatedData['cab_type'],
           // 'booking_type' => $validatedData['booking_type'],
            'from_location' => $validatedData['from_location'],
            'to_location' => $validatedData['to_location']?? null,
            //'departure_date' => $departureDate,
            'pickup_date' => $validatedData['pickup_date'],
            'pickup_time' => $validatedData['pickup_time'],
            'matter_code' => $request['matter_code']?? null,
            'traveller' =>implode(',',$request['traveller'])?? null,
           
            'purpose_description' => $request['purpose_description']?? null,
             'sequence_no' => $new_sequence_no?? null,
             'order_no' => $uniqueNo?? null,
        ]);

        if (!$booking) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to book cab, please try again later'
            ], 500);
        }

        return response()->json([
            'status' => true,
            'message' => 'Cab booked successfully',
            'data' => $booking
        ]);
    }
    
    
    
   public function cancelCabBooking(Request $request)
  {
    $validator = Validator::make($request->all(), [
        'id' => 'required|exists:cab_bookings,id',
        
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'error' => $validator->errors()
        ], 400);
    }

    $validatedData = $validator->validated();
    $orderData = CabBooking::findOrFail($request->id);
    if (!$orderData) {
        return response()->json([
            'status' => false,
            'message' => 'Booking not found.'
        ], 404);
    }
    $now = Carbon::now();
    $currentHour = (int)$now->format('H');
    $today = Carbon::today();
    $pickupDate = Carbon::parse($orderData->pickup_date);
    if ($pickupDate->lessThan($today)) {
        return response()->json([
            'status' => false,
            'message' => 'Booking cannot be edited. Pickup date is before today.'
        ], 403);
        
    }
    // ⛔ Restrict actions from 7:00 PM to 10:00 AM
    if ($currentHour >= 19 || $currentHour < 10) {
        return response()->json([
            'status' => false,
            'message' => 'Your Travel requisition is registered. We shall get back to you in next business hours.In case of any urgency, you may contact the Travel Desk (Admin) directly on mobile phone.'
        ], 403);
    }

    // ⛔ Restrict edits/cancellations less than 6 hours before pickup time
    $pickupDateTime = Carbon::parse($orderData['pickup_date'] . ' ' . $orderData['pickup_time']);

    if ($now->greaterThan($pickupDateTime->copy()->subHours(6))) {
        return response()->json([
            'status' => false,
            'message' => 'Edits or cancellations must be made at least 6 hours before the pickup time.'
        ], 403);
    }

    // ✅ Cancellation flow
    if ($request->has('cancel') && $request->cancel == true) {
        $orderData->status = 4; // Cancelled
        $orderData->cancellation_remarks = $request->remarks ?? 'No remarks provided';
        $orderData->save();

        return response()->json([
            'status' => true,
            'message' => 'Booking cancellation requested successfully.',
            'data' => $orderData
        ]);
    }

    // ✅ Update flow
   $newData = [
        'bill_to' => $request['bill_to'],
        'from_location' => $request['from_location'],
        'to_location' => $request->to_location ?? null,
        'pickup_date' => $request['pickup_date'],
        'pickup_time' => $request['pickup_time'],
        'matter_code' => $request->matter_code ?? null,
        'traveller' => is_array($request->traveller) ? implode(',', $request->traveller) : null,
        'purpose_description' => $request->purpose_description ?? null,
       'updated_at' => now()
    ];
    
     // Compare and insert into log
    foreach ($newData as $field => $newValue) {
        $oldValue = $orderData->$field ?? null;

        if ($newValue != $oldValue) {
            DB::table('edit_logs')->insert([
                'table_name' => 'cab_bookings',
                'record_id' => $request->id,
                'field' => $field,
                'old_value' => $oldValue,
                'new_value' => $newValue,
                'updated_by' => $orderData->user_id,
                'created_at' => now()
            ]);
        }
    }

    DB::table('cab_bookings')->where('id', $request->id)->update($newData);

    return response()->json([
        'status' => true,
        'message' => 'Cab updated successfully.',
        'data' => $orderData
    ]);
}


    

    

}
