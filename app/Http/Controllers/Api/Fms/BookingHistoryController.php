<?php

namespace App\Http\Controllers\Api\Fms;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CabBooking;
use App\Models\FlightBooking;
use App\Models\TrainBooking;
use App\Models\HotelBooking;
use App\Models\User;

class BookingHistoryController extends Controller
{
    public function getBookingHistory(Request $request)
    {
        $userId = $request->user_id;

        $user = User::find($userId);
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found.'
            ], 404);
        }

         $cabBookings = CabBooking::where('user_id', $userId)->get();
            // Process cab bookings to add 'travelers' as an array
            $processedCabBookings = $cabBookings->map(function ($booking) {
                $booking->traveller = array_map('trim', explode(',', $booking->traveller)); // Convert to array
                return $booking;
            });
             $flightBookings = FlightBooking::where('user_id', $userId)->get();

            $processedFlightBookings = $flightBookings->map(function ($booking) {
                $travellers = explode(',', $booking->traveller);
                $seatPreferences = explode(',', $booking->seat_preference ?? '');
                $foodPreferences = explode(',', $booking->food_preference ?? '');
            
                $formattedTravellers = [];
            
                foreach ($travellers as $index => $traveller) {
                    $formattedTravellers[] = [
                        'name' => trim($traveller),
                        'seat_preference' => $seatPreferences[$index] ?? null,
                        'food_preference' => $foodPreferences[$index] ?? null,
                    ];
                }
            
                $booking->traveller = $formattedTravellers; // Replace with formatted array
                return $booking;
            });
        $trainBookings = TrainBooking::where('user_id', $userId)->get();
          $processedTrainBookings = $trainBookings->map(function ($booking) {
              $travellers = explode(',', $booking->traveller);
                $seatPreferences = explode(',', $booking->seat_preference ?? '');
                $foodPreferences = explode(',', $booking->food_preference ?? '');
            
                $formattedTravellers = [];
            
                foreach ($travellers as $index => $traveller) {
                    $formattedTravellers[] = [
                        'name' => trim($traveller),
                        'seat_preference' => $seatPreferences[$index] ?? null,
                        'food_preference' => $foodPreferences[$index] ?? null,
                    ];
                }
                 $booking->traveller = $formattedTravellers; // Convert to array
                return $booking;
            });
        $hotelBookings = HotelBooking::where('user_id', $userId)->with('property')->get();
             $processedHotelBookings = $hotelBookings->map(function ($booking) {
                $booking->guest_type = array_map('trim', explode(',', $booking->guest_type)); // Convert to array
                return $booking;
            });
        $response = [
            'cab_bookings' => $processedCabBookings->isNotEmpty() ? $processedCabBookings : 'No cab bookings found',
            'flight_bookings' => $processedFlightBookings->isNotEmpty() ? $processedFlightBookings : 'No flight bookings found',
            'train_bookings' => $processedTrainBookings->isNotEmpty() ? $processedTrainBookings : 'No train bookings found',
            'hotel_bookings' => $processedHotelBookings->isNotEmpty() ? $processedHotelBookings : 'No hotel bookings found',
            'user_details' => $user
        ];

        return response()->json([
            'status' => true,
            'message' => 'Booking history retrieved successfully.',
            'data' => $response
        ], 200);
    }

}