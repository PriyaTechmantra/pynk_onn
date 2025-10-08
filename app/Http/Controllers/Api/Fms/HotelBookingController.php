<?php

namespace App\Http\Controllers\Api\Fms;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\HotelBooking;
use App\Models\Room;
use App\Models\Property;
use App\Models\HotelBookingGuest;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use DB;
class HotelBookingController extends Controller
{
    // public function store(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'user_id' => 'required|exists:users,id',
    //         'room_id' => 'required|exists:rooms,id',
    //         'property_id' => 'required|exists:properties,id',
    //         'checkin_date' => 'required|date',
    //         'checkout_date' => 'required|date|after:checkin_date',
    //         'guest_number' => 'required|integer|min:1',
    //         'room_number' => 'required|integer|min:1',
    //         'bill_to' => 'required|integer|in:1,2,3', 
            
    //     ]);
    
    //     if ($validator->fails()) {
    //         return response()->json(['status' => false, 'errors' => $validator->errors()], 400);
    //     }
    
    //     $validatedData = $validator->validated();
    
    //     $checkinDate = Carbon::parse($validatedData['checkin_date'])->format('Y-m-d l');
    //     $checkoutDate = Carbon::parse($validatedData['checkout_date'])->format('Y-m-d l');
    
    //     $booking = HotelBooking::create([
    //         'user_id' => $validatedData['user_id'], 
    //         'room_id' => $validatedData['room_id'],
    //         'property_id' => $validatedData['property_id'],
    //         'checkin_date' => $checkinDate,  
    //         'checkout_date' => $checkoutDate, 
    //         'guest_number' => $validatedData['guest_number'],
    //         'room_number' => $validatedData['room_number'],
    //         'bill_to' => $validatedData['bill_to'],

    //     ]);
    
    //     return response()->json(['status' => true, 'message' => 'Booking created successfully', 'data' => $booking], 201);
    // }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'property_id' => 'nullable|exists:properties,id',
            'checkin_date' => 'required',
            'checkout_date' => 'required|after:checkin_date',
            'guest_number' => 'required|integer|min:1',
            'bill_to' => 'required|integer|in:1,2,3',
           
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 400);
        }

        $validatedData = $validator->validated();
         $orderData = HotelBooking::select('sequence_no')->latest('sequence_no')->first();
            
            //if (empty($orderData->sequence_no)) {
                if (!empty($orderData->sequence_no)) {
                    $new_sequence_no = (int) $orderData->sequence_no + 1;
    
                } else {
                    $new_sequence_no = 1;
    
                }
                $ordNo = sprintf("%'.05d", $new_sequence_no);
                $uniqueNo = 'FM'.'-'.'HB'.'-'.date('Y').'-'.$ordNo;
        $checkinDate = Carbon::parse($validatedData['checkin_date'])->format('Y-m-d H:i A');
        $checkoutDate = Carbon::parse($validatedData['checkout_date'])->format('Y-m-d H:i A');

        $booking = HotelBooking::create([
            'user_id' => $validatedData['user_id'],
            'property_id' => $validatedData['property_id'],
            'checkin_date' => $validatedData['checkin_date'],
            'checkout_date' => $validatedData['checkout_date'],
            'guest_number' => $validatedData['guest_number'],
            'bill_to' => $validatedData['bill_to'],
            'matter_code' => $request['matter_code']?? null,
            'guest_type' => $request['guest_type']?? null,
            'hotel_type' => $request['hotel_type']?? null,
            'text' => $request['text']?? null,
            'seat_preference' => $request['seat_preference']?? null,
             'food_preference' => $request['food_preference']?? null,
            'purpose_description' => $request['purpose_description']?? null,
            'sequence_no' => $new_sequence_no?? null,
             'order_no' => $uniqueNo?? null,
        ]);

        /*foreach ($validatedData['guests'] as $guestData) {
            HotelBookingGuest::create([
                'hotel_booking_id' => $booking->id,
                'guest_name' => $guestData['guest_name'],
                'guest_email' => $guestData['guest_email'],
                'guest_contact' => $guestData['guest_contact'],
                'guest_country' => $guestData['guest_country'],
                'guest_city' => $guestData['guest_city'],
                'guest_state' => $guestData['guest_state'],
                'guest_pincode' => $guestData['guest_pincode'],
            ]);
        }
        $bookingWithGuests = HotelBooking::with('guests')->find($booking->id);*/
        return response()->json([
            'status' => true,
            'message' => 'Hotel Booked successfully',
            'data' => [
                'booking' => $booking 
            ]
        ], 201);
    }


    public function roomList(Request $request)
    {
       $data=Room::get();
    

        if ($data) {
            return response()->json(['status'=>true,'message' => 'List of room','data' => $data ], 200);
        }else {
            return response()->json([
                'status' => false,
                'message' => 'Room list not found'
            ], 404);
        }
    }

    public function propertyList(Request $request)
    {
       $data=Property::get();
    

        if ($data) {
            return response()->json(['status'=>true,'message' => 'List of property','data' => $data ], 200);
        }else {
            return response()->json([
                'status' => false,
                'message' => 'Property list not found'
            ], 404);
        }
    }
    public function userRoomBookings(Request $request)
    {
        $userId=$request->user_id;
        $bookings = HotelBooking::with(['room', 'property','user','guests'])
            ->where('user_id', $userId)
            ->where('status', 1)
            ->get();

        if ($bookings->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No bookings found for this user.',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $bookings,
        ], 200);
    }

     public function cancelHotelBooking(Request $request)
  {
    $validator = Validator::make($request->all(), [
        'id' => 'required|exists:hotel_bookings,id',
        
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'error' => $validator->errors()
        ], 400);
    }

    $validatedData = $validator->validated();
    $orderData = HotelBooking::findOrFail($request->id);
    if (!$orderData) {
        return response()->json([
            'status' => false,
            'message' => 'Booking not found.'
        ], 404);
    }
    $now = Carbon::now();
    $currentHour = (int)$now->format('H');

    // ⛔ Restrict actions from 7:00 PM to 10:00 AM
    if ($currentHour >= 19 || $currentHour < 10) {
        return response()->json([
            'status' => false,
            'message' => 'Your Travel requisition is registered. We shall get back to you in next business hours.In case of any urgency, you may contact the Travel Desk (Admin) directly on mobile phone.'
        ], 403);
    }

    // ⛔ Restrict edits/cancellations less than 6 hours before pickup time
    $pickupDateTime = Carbon::parse($orderData['checkin_date']);

    if ($now->greaterThan($pickupDateTime->copy()->subHours(6))) {
        return response()->json([
            'status' => false,
            'message' => 'Edits or cancellations must be made at least 6 hours before the checkin time.'
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
        'room_id' => $request['room_id'],
        'property_id'=> $request['property_id'],
        'guest_type' => $request->guest_type ?? null,
        'checkin_date' => $request['checkin_date'],
        'checkout_date'=> $request['checkout_date'],
        'matter_code' => $request->matter_code ?? null,
        'room_number'=> $request->room_number ?? null,
        'guest_number' => $request->guest_number ?? null,
        'hotel_type' => $request->hotel_type ?? null,
        'text' => $request->text ?? null,
        'seat_preference' => $request['seat_preference'],
        'food_preference' => $request['food_preference'],
        'purpose_description' => $request->purpose_description ?? null,
    
         'updated_at' => now()
    ];
        
    // Compare and insert into log
    foreach ($newData as $field => $newValue) {
        $oldValue = $orderData->$field ?? null;

        if ($newValue != $oldValue) {
            DB::table('edit_logs')->insert([
                'table_name' => 'hotel_bookings',
                'record_id' => $request->id,
                'field' => $field,
                'old_value' => $oldValue,
                'new_value' => $newValue,
                'updated_by' => $orderData->user_id,
                'created_at' => now()
            ]);
        }
    }

    DB::table('hotel_bookings')->where('id', $request->id)->update($newData);
    return response()->json([
        'status' => true,
        'message' => 'Hotel booking request updated successfully.',
        'data' => $orderData
    ]);
}


    
}
