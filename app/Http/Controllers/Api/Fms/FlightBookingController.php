<?php

namespace App\Http\Controllers\Api\Fms;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FlightBooking; 
use App\Models\User;
use App\Models\MailActivity;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use DB;
class FlightBookingController extends Controller
{
   public function store(Request $request)
 {
    $validator = Validator::make($request->all(), [
        'user_id' => 'required|exists:users,id', 
        'trip_type' => 'required|integer|in:1,2', 
        'from' => 'required|string|max:255',
        'to' => 'required|string|max:255', 
        'departure_date' => 'required',
        'arrival_time' =>'required',
        'return_date' => 'required_if:trip_type,2|nullable|date',
        'bill_to' => 'required|integer|in:1,2,3', 
        'traveller' => 'required|array',
        'traveller.*.name' => 'required|string|max:255',
        'traveller.*.seat_preference' => 'nullable|string|max:255',
        'traveller.*.food_preference' => 'nullable|string|max:255',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'errors' => $validator->errors(),
        ], 400);
    }

    $validatedData = $validator->validated();
    $orderData = FlightBooking::select('sequence_no')->latest('sequence_no')->first();

    $new_sequence_no = !empty($orderData->sequence_no) ? (int) $orderData->sequence_no + 1 : 1;
    $ordNo = sprintf("%'.05d", $new_sequence_no);
    $uniqueNo = 'FM'.'-'.'FB'.'-'.date('Y').'-'.$ordNo;

    // Traveller details in comma-separated format
    $travellerNames = collect($validatedData['traveller'])->pluck('name')->implode(',');
    $seatPreferences = collect($validatedData['traveller'])->pluck('seat_preference')->implode(',');
    $foodPreferences = collect($validatedData['traveller'])->pluck('food_preference')->implode(',');

    $flightBooking = FlightBooking::create([
        'user_id' => $validatedData['user_id'],
        'trip_type' => $validatedData['trip_type'],
        'from' => $validatedData['from'],
        'to' => $validatedData['to'],
        'departure_date' => $validatedData['departure_date'],
        'arrival_time' => $validatedData['arrival_time'],
        'return_date' => $validatedData['return_date'] ?? null, 
        'traveller' => $travellerNames ?? null,
        'seat_preference' => $seatPreferences ?? null,
        'food_preference' => $foodPreferences ?? null,
        'bill_to' => $validatedData['bill_to'],
        'matter_code' => $request['matter_code'] ?? null,
        'purpose_description' => $request['purpose_description'] ?? null,
        'sequence_no' => $new_sequence_no ?? null,
        'order_no' => $uniqueNo ?? null,
    ]);
    
        $user=User::where('id',$validatedData['user_id'])->first();
            $email_data = [
                'name' => $user->name,
                'subject' => 'Gentle Acknowledgement – Booking Information',
                'email' => $user->email,
                'flightBooking' => $flightBooking,
               
                'blade_file' => 'mail/flight-booking-mail',
            ];
                $mailLog = MailActivity::create([
                    'email' => $user->email,
                    'type' => 'flight-booking-information-sent',
                    'sent_at' => now(),
                    'status' => 'pending',
                ]);
                try {
                    // Send email
                     SendMail($email_data);
            
                    // Update the log status to "sent" on success
                    $mailLog->update(['status' => 'sent']);
            
                   
                } catch (\Exception $e) {
                     dd('Exception:', $e->getMessage());
                    // Update the log status to "failed" on error
                    $mailLog->update(['status' => 'failed']);
            
                    //return response()->json(['success' => false, 'message' => 'Failed to send email.'], 500);
                }
    if (!$flightBooking) {
        return response()->json([
            'status' => false,
            'message' => 'Failed to create flight booking. Please try again.',
        ], 500);
    }

    return response()->json([
        'status' => true,
        'message' => 'Flight booking created successfully.',
        'data' => $flightBooking,
    ], 201);
}


    public function cancelFlightBooking(Request $request)
  {
      
    $validator = Validator::make($request->all(), [
        'id' => 'required|exists:flight_bookings,id',
        
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'error' => $validator->errors()
        ], 400);
    }

    $validatedData = $validator->validated();
    $travellers = collect($request['traveller'] ?? []);

    $travellerNames = $travellers->pluck('name')->implode(',');
    $seatPreferences = $travellers->pluck('seat_preference')->implode(',');
    $foodPreferences = $travellers->pluck('food_preference')->implode(',');
    $orderData = FlightBooking::findOrFail($request->id);
    if (!$orderData) {
        return response()->json([
            'status' => false,
            'message' => 'Booking not found.'
        ], 404);
    }
    $now = Carbon::now();
    $currentHour = (int)$now->format('H');
    $today = Carbon::today();
    $pickupDate = Carbon::parse($orderData->departure_date);
    if ($pickupDate->lessThan($today)) {
        return response()->json([
            'status' => false,
            'message' => 'Booking cannot be edited. Departure date is before today.'
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
    $pickupDateTime = Carbon::parse($orderData['departure_date'] . ' ' . $orderData['arrival_time']);

    if ($now->greaterThan($pickupDateTime->copy()->subHours(6))) {
        return response()->json([
            'status' => false,
            'message' => 'Edits or cancellations must be made at least 6 hours before the pickup time.'
        ], 403);
    }

    // ✅ Cancellation flow
    if ( $request->has('cancel') && $request->cancel == true) {
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
        'from' => $request['from'],
        'to' => $request->to ?? null,
        'departure_date' => $request['departure_date'],
        'arrival_time' => $request['arrival_time'],
        'trip_type' => $request['trip_type'],
        'return_date' => $request['return_date'],
        
        'matter_code' => $request->matter_code ?? null,
         'traveller' => $travellerNames ?? null,
        'seat_preference' => $seatPreferences ?? null,
        'food_preference' => $foodPreferences ?? null,
        'purpose_description' => $request->purpose_description ?? null,
    
        'updated_at' => now()
    ];
    
    // Compare and insert into log
    foreach ($newData as $field => $newValue) {
        $oldValue = $orderData->$field ?? null;

        if ($newValue != $oldValue) {
            DB::table('edit_logs')->insert([
                'table_name' => 'flight_bookings',
                'record_id' => $request->id,
                'field' => $field,
                'old_value' => $oldValue,
                'new_value' => $newValue,
                'updated_by' => $orderData->user_id,
                'created_at' => now()
            ]);
        }
    }

    DB::table('flight_bookings')->where('id', $request->id)->update($newData);
    return response()->json([
        'status' => true,
        'message' => 'Flight booking updated successfully.',
        'data' => $orderData
    ]);
}

}
