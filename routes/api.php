<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\IssueBookController;
use App\Http\Controllers\Api\BookShelveController;
use App\Http\Controllers\Api\BookmarkController;
use App\Http\Controllers\Api\BookTransferController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\Fms\CabBookingController;
use App\Http\Controllers\Api\Fms\FlightBookingController;
use App\Http\Controllers\Api\Fms\TrainBookingController;
use App\Http\Controllers\Api\Fms\HotelBookingController;
use App\Http\Controllers\Api\Fms\BookingHistoryController;
use App\Http\Controllers\Api\ASEController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CaveController;



/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


//ase
Route::post('login', [AuthController::class, 'sendOtp']);
Route::post('verify-otp', [AuthController::class, 'verifyOtp']);
Route::get('area/list', [ASEController::class, 'areaList']);
Route::post('activity/store', [ASEController::class, 'activityStore']);
Route::post('day/start', [ASEController::class, 'dayStart']);
Route::post('day/end', [ASEController::class, 'dayEnd']);
Route::get('check/visit/{id}', [ASEController::class, 'checkVisit']);



Route::post('day/start/activity/create', [ASEController::class, 'daystartactivityStore']);
Route::post('day/end/activity/create', [ASEController::class, 'dayendactivityStore']);
Route::get('dashboard/all/order/qty', [ASEController::class, 'aseSalesreport']);
Route::get('stores/list', [ASEController::class, 'storeList']);
Route::get('inactive/stores/list', [ASEController::class, 'inactivestoreList']);
Route::get('serach/store', [ASEController::class, 'searchStore']);


Route::get('distributor/list', [ASEController::class, 'distributorList']);
Route::post('add/store', [ASEController::class, 'addStore']);
Route::post('edit/store/{id}', [ASEController::class, 'editStore']);

Route::post('store/image/update', [ASEController::class, 'storeimageUpdate']);
Route::post('no-order-reason/update', [ASEController::class, 'noorder']);
Route::get('no-order-reason', [ASEController::class, 'noorderlist']);
Route::get('no-order-history/{id}', [ASEController::class, 'noorderhistory']);
Route::get('category', [ASEController::class, 'categoryList']);
Route::get('collection', [ASEController::class, 'collectionList']);



Route::get('category/product/collection/{id}', [ASEController::class, 'collectionWiseCategoryProduct']);
Route::get('category/{id}/products', [ASEController::class, 'categorywiseProduct']);
Route::get('products', [ASEController::class, 'productList']);

Route::get('collection/{collectionId}/category/{categoryId}', [ASEController::class, 'collectionCategoryWiseProducts']);
Route::get('products/show/{id}', [ASEController::class, 'productShow']);

Route::get('product/images/{id}', [ASEController::class, 'productImages']);
Route::get('products-color-size/show/{productid}', [ASEController::class, 'colorsize']);
Route::get('search/product', [ASEController::class, 'searchProduct']);
Route::get('multicolor/size', [ASEController::class, 'multicolorsize']);

Route::get('products-color/view/{productid}', [ASEController::class, 'productcolor']);
Route::get('product/color/details/{id}', [ASEController::class, 'productcolorDetails']);
Route::get('product/color/images', [ASEController::class, 'productcolorImages']);
Route::post('bulkAddTocart', [ASEController::class, 'bulkAddTocart']);
Route::get('cart/qty/{cartId}/{q}', [ASEController::class, 'qtyUpdate']);

Route::get('cart/preview/pdf/view/{userId}', [ASEController::class, 'cartPreviewPDF_view']);
Route::get('cart/clear/{id}', [ASEController::class, 'clearCart']);
Route::get('cart/delete/{id}', [ASEController::class, 'cartDelete']);
//return book
Route::get('cart/user/{id}', [ASEController::class, 'showByUser']);

Route::post('place-order-update', [BookController::class, 'placeOrderUpdate']);

Route::post('/return-bulk-book', [BookController::class, 'returnBook']);

Route::post('/return-bulk-book-for-captain', [BookController::class, 'returnBookForCaptain']);

Route::post('/save-fcm-token', [NotificationController::class, 'saveToken']);


Route::post('/save-notification', [NotificationController::class, 'Notification']);
Route::get('/notification-list-by-user', [NotificationController::class, 'notificationListByUser']);
Route::post('/notification-read', [NotificationController::class, 'markAsRead']);


Route::prefix('cab_bookings')->group(function () {
    // Route::get('/', [BookingController::class, 'index']);            
    // Route::get('/{id}', [BookingController::class, 'show']);         
    Route::post('/store', [CabBookingController::class, 'store']);  
    Route::post('/edit', [CabBookingController::class, 'edit']);  
    // Route::put('/{id}', [CabBookingController::class, 'update']);      
    // Route::delete('/{id}', [CabBookingController::class, 'destroy']);  
});

Route::prefix('flight_bookings')->group(function () {
    Route::post('/store', [FlightBookingController::class, 'store']); 
    Route::post('/edit', [FlightBookingController::class, 'edit']); 
});

Route::prefix('train_bookings')->group(function () {
    Route::post('/store', [TrainBookingController::class, 'store']);    
    Route::post('/edit', [TrainBookingController::class, 'edit']);    
});


Route::prefix('hotel_bookings')->group(function () {
    Route::post('/store', [HotelBookingController::class, 'store']);
    Route::post('/edit', [HotelBookingController::class, 'edit']);
});

Route::get('/room_list', [HotelBookingController::class, 'roomList']);
Route::get('/property_list', [HotelBookingController::class, 'propertyList']);
Route::get('/booked_hotel_list', [HotelBookingController::class, 'userRoomBookings']);



Route::get('bookings_history', [BookingHistoryController::class, 'getBookingHistory']);


Route::prefix('cancel_bookings')->group(function () {
    Route::post('/train', [TrainBookingController::class, 'cancelTrainBooking']);
    Route::post('/flight', [FlightBookingController::class, 'cancelFlightBooking']);
    Route::post('/cab', [CabBookingController::class, 'cancelCabBooking']);
    Route::post('/hotel', [HotelBookingController::class, 'cancelHotelBooking']);
});

//cave
Route::get('cave-search', [CaveController::class, 'search']);

Route::get('cave-list/{id}', [CaveController::class, 'index']);

Route::get('cave-detail/{id}', [CaveController::class, 'detail']);

Route::post('take-in', [CaveController::class, 'store']);

Route::post('received', [CaveController::class, 'received']);


Route::post('/take-out-request-send', [CaveController::class, 'takeOutRequest']);
Route::get('/my/request/vault/{id}', [CaveController::class, 'myrequestedvaultList']);
Route::get('/requested/vaults/by/user/{id}', [CaveController::class, 'requestedvaultList']);

Route::get('/issued-vaults/list-by-user/{id}', [CaveController::class, 'listByUser']);
Route::get('/scanned-vaults/list-by-authorized-user', [CaveController::class, 'scannedlistByUser']);
Route::post('/scan/to/accept/requested/vaults', [CaveController::class, 'statuschangeforRequestedvaults']);

Route::get('/vault/history/{id}', [CaveController::class, 'vaultHistory']);