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
Route::get('state/list', [ASEController::class, 'stateList']);
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
Route::post('edit/store', [ASEController::class, 'editStore']);

Route::post('store/image/update', [ASEController::class, 'storeimageUpdate']);
Route::post('store/pan/update', [ASEController::class, 'storepanimageUpdate']);
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

Route::get('product/images/{id}/{colorId}', [ASEController::class, 'productImages']);
Route::get('products-color/view/{productid}', [ASEController::class, 'productcolor']);
Route::get('multicolor/size', [ASEController::class, 'multicolorsize']);
Route::get('search/product', [ASEController::class, 'searchProduct']);


Route::post('bulkAddTocart', [ASEController::class, 'bulkAddTocart']);
Route::get('cart/qty/update', [ASEController::class, 'cartqtyUpdate']);
Route::get('cart/preview/pdf/url', [ASEController::class, 'cartPreviewPDF_URL']);
Route::get('cart/pdf/view', [ASEController::class, 'cartPreviewPDF_view']);
Route::get('cart/clear/{id}', [ASEController::class, 'clearCart']);
Route::get('cart/delete/{id}', [ASEController::class, 'cartDelete']);
//return book
Route::get('cart/user', [ASEController::class, 'showByUser']);

Route::post('place-order-update', [ASEController::class, 'placeOrderUpdate']);

Route::get('order/list', [ASEController::class, 'orderList']);

Route::get('order/details/{id}', [ASEController::class, 'orderDetails']);

Route::get('order/pdf/url/{id}', [ASEController::class, 'orderPDF_URL']);


Route::get('order/pdf/view/{id}', [ASEController::class, 'orderPDF_view']);
Route::post('my-orders', [ASEController::class, 'myOrdersFilter']);

Route::post('store-wise-report-ase', [ASEController::class, 'storeReportASE']);

Route::post('product-wise-report-ase', [ASEController::class, 'productReportASE']);
Route::get('catalogue', [ASEController::class, 'catalogueList']);
Route::get('scheme', [ASEController::class, 'schemeList']);
Route::get('news', [ASEController::class, 'newsList']);

Route::get('primary/order/list', [ASEController::class, 'primaryorderList']);

Route::get('primary/order/details/{id}', [ASEController::class, 'primaryorderDetails']);
//mom
Route::get('distributor/mom/list', [ASEController::class, 'momList']);

Route::post('distributor/mom/store', [ASEController::class, 'momStore']);
//primary order

Route::post('distributor/bulkAddTocart', [ASEController::class, 'distributorbulkAddTocart']);
Route::get('distributor/cart/qty/update', [ASEController::class, 'distributorcartqtyUpdate']);
Route::get('distributor/cart/preview/pdf/url', [ASEController::class, 'distributorcartPreviewPDF_URL']);
Route::get('distributor/cart/pdf/view', [ASEController::class, 'distributorcartPreviewPDF_view']);
Route::get('distributor/cart/clear/{id}', [ASEController::class, 'distributorclearCart']);
Route::get('distributor/cart/delete/{id}', [ASEController::class, 'distributorcartDelete']);

Route::get('distributor/cart/list', [ASEController::class, 'showBydistributor']);

Route::post('distributor-place-order', [ASEController::class, 'distributorplaceOrderUpdate']);

Route::get('distributor/order/pdf/url/{id}', [ASEController::class, 'distributororderPDF_URL']);


Route::get('distributor/order/pdf/view/{id}', [ASEController::class, 'distributororderPDF_view']);

Route::get('activity', [ASEController::class, 'activityList']);

Route::get('store/onn/currency/ase', [ASEController::class, 'onncurrencyASE']);
Route::post('store/reward/order/detail/ase', [ASEController::class, 'rewardorderaseDetail']);
//order approval 
Route::post('store/reward/order/status/ase', [ASEController::class, 'rewardorderaseStatus']);

//ASM
Route::get('notification/list', [ASEController::class, 'notificationList']);
Route::post('read-notification', [ASEController::class, 'readNotification']);

Route::get('asm/ase/list/{id}', [ASEController::class, 'aseList']);

Route::get('inactive/ase/report/asm', [ASEController::class, 'inactiveAseListASM']);

Route::get('ase/stores/list', [ASEController::class, 'asestoreList']);
//area list
Route::get('asm/area/list/{id}', [ASEController::class, 'asmareaList']);
//distributor list
Route::get('asm/distributor/list', [ASEController::class, 'asmdistributorList']);
Route::post('team-wise-report-asm', [ASEController::class, 'storeReportASM']);

Route::post('product-wise-report-asm', [ASEController::class, 'productReportASM']);

Route::get('store/onn/currency/asm', [ASEController::class, 'onncurrencyASM']);
Route::post('store/reward/order/detail/asm', [ASEController::class, 'rewardorderasmDetail']);
//order approval 
Route::post('store/reward/order/status/asm', [ASEController::class, 'rewardorderasmStatus']);

//RSM
Route::get('inactive/ase/report/rsm', [ASEController::class, 'inactiveAseListRSM']);
Route::get('rsm/area/list/{id}', [ASEController::class, 'rsmareaList']);

Route::post('team-wise-report-rsm', [ASEController::class, 'storeReportRSM']);

Route::post('product-wise-report-rsm', [ASEController::class, 'productReportRSM']);
//distributor list
Route::get('rsm/distributor/list', [ASEController::class, 'rsmdistributorList']);
Route::get('store/onn/currency/rsm', [ASEController::class, 'onncurrencyRSM']);
Route::post('store/reward/order/detail/rsm', [ASEController::class, 'rewardorderrsmDetail']);
//order approval 
Route::post('store/reward/order/status/rsm', [ASEController::class, 'rewardorderrsmStatus']);
//VP

Route::get('inactive/ase/report/vp', [ASEController::class, 'inactiveAseListVP']);

Route::get('vp/state/list/{id}', [ASEController::class, 'vpstateList']);
Route::get('vp/state/area/list', [ASEController::class, 'vpstateareaList']);

Route::post('team-wise-report-vp', [ASEController::class, 'storeReportVP']);

Route::post('product-wise-report-vp', [ASEController::class, 'productReportVP']);

Route::get('store/onn/currency/vp', [ASEController::class, 'onncurrencyVP']);
Route::get('vp/distributor/list', [ASEController::class, 'vpdistributorList']);
Route::post('store/reward/order/detail/vp', [ASEController::class, 'rewardordervpDetail']);
//order approval 
Route::post('store/reward/order/status/vp', [ASEController::class, 'rewardordervpStatus']);
//Distributor


Route::post('distributor/addTocart', [ASEController::class, 'distributorAddTocart']);
Route::get('distributor/app/cart/qty/update', [ASEController::class, 'distributorappcartqtyUpdate']);
Route::get('distributor/app/cart/preview/pdf/url', [ASEController::class, 'distributorappcartPreviewPDF_URL']);
Route::get('distributor/app/cart/pdf/view', [ASEController::class, 'distributorappcartPreviewPDF_view']);
Route::get('distributor/app/cart/clear/{id}', [ASEController::class, 'distributorappclearCart']);
Route::get('distributor/app/cart/delete/{id}', [ASEController::class, 'distributorappcartDelete']);
Route::get('distributor/app/cart/list', [ASEController::class, 'showBydistributorapp']);

Route::post('distributor-app-place-order', [ASEController::class, 'distributorappplaceOrderUpdate']);

Route::get('distributor/app/order/pdf/url/{id}', [ASEController::class, 'distributorapporderPDF_URL']);


Route::get('distributor/app/order/pdf/view/{id}', [ASEController::class, 'distributorapporderPDF_view']);


Route::get('distributor/order/list', [ASEController::class, 'distributorprimaryorderList']);

Route::post('distributor/store/order/datewise', [ASEController::class, 'storeOrder']);
Route::post('distributor/store/order/datewise/csv', [ASEController::class, 'csvExport']);
//product wise store order list for distributor
Route::post('distributor/store/order/productwise', [ASEController::class, 'productOrder']);
Route::post('distributor/store/order/productwise/csv', [ASEController::class, 'csvProductExport']);

Route::get('store/onn/currency/distributor', [ASEController::class, 'onncurrencyDistributor']);
Route::post('store/reward/order/detail/distributor', [ASEController::class, 'rewardorderdistributorDetail']);
//order approval 
Route::post('store/reward/order/status/distributor', [ASEController::class, 'rewardorderdistributorStatus']);

Route::get('distributor/store/list', [ASEController::class, 'distributorstoreList']);



//retailer

Route::get('all/state/list', [ASEController::class, 'allstateList']);
Route::get('all/area/list/{id}', [ASEController::class, 'allareaList']);
Route::post('retailer/login', [ASEController::class, 'retailerLogin']);
Route::post('retailer/login-with-pin', [ASEController::class, 'retailerLoginPin']);
// remove profile
Route::get('retailer/remove/profile/{id}', [ASEController::class, 'retailerremoveProfile']);

Route::post('retailer/register', [ASEController::class, 'retailerRegister']);

Route::post('retailer/pin/generate', [ASEController::class, 'retailerpinGenerate']);
// aadhar document add
Route::post('retailer/aadhar/upload', [ASEController::class, 'retailerCreateAadhar']);

Route::post('retailer/pan/upload', [ASEController::class, 'retailerCreatePan']);

Route::post('retailer/gst/upload', [ASEController::class, 'retailerCreateGst']);

Route::post('retailer/image/upload', [ASEController::class, 'retailerCreateImage']);

Route::get('retailer/profile/{id}', [ASEController::class, 'retailermyprofile']);
//edit profile
Route::post('retailer/update/profile/{id}', [ASEController::class, 'retailerupdateProfile']);
//change password
Route::post('retailer/change/password', [ASEController::class, 'retailerchangePassword']);
//fetch top 5  product
Route::get('retailer/product/view/{id}', [ASEController::class, 'retailerproductView']);
//fetch all product
Route::get('retailer/product/list', [ASEController::class, 'retailerproductList']);
//fetch product by slug

//total wallet balance count
Route::get('retailer/wallet/balance/{id}', [ASEController::class, 'retailerwalletBalance']);
//brochure
Route::get('retailer/brochure', [ASEController::class, 'retailerbrochureindex']);

//barcode scan
Route::post('retailer/qrcode/scan', [ASEController::class, 'retailerBarcode']);
//5 order history user wise 
Route::get('retailer/order', [ASEController::class, 'retailerOrder']);

Route::get('retailer/order/details/{id}', [ASEController::class, 'retailerOrderDetails']);
//reward history
Route::post('retailer/reward/history', [ASEController::class, 'retailerrewardHistory']);
//transaction history
Route::post('retailer/transaction/history', [ASEController::class, 'retailerTransaction']);
//reward cart list

Route::get('reward/cart/user/{id}', [ASEController::class, 'retailerRewardCart']);
Route::get('reward/cart/clear/{id}', [ASEController::class, 'retailerRewardCartclear']);
Route::get('reward/cart/qty/{cartId}/{q}', [ASEController::class, 'retailerRewardCartqtyUpdate']);
Route::post('reward/AddTocart', [ASEController::class, 'retailerrewardbulkAddTocart']);
//order-place

Route::post('reward/place/order', [ASEController::class, 'retailerrewardplaceOrder']);

//invoice image store
Route::post('retailer/invoice/image', [ASEController::class, 'retailerinvoiceIndex']);

//invoice store
Route::post('retailer/invoice/store', [ASEController::class, 'retailerinvoiceStore']);


//b2b product order

//cart list

Route::get('retailer/b2b/cart/user/{id}', [ASEController::class, 'retailerb2bshowByUser']);
Route::get('retailer/b2b/cart/clear/{id}', [ASEController::class, 'retailerb2bclearCart']);
Route::get('retailer/b2b/cart/qty', [ASEController::class, 'retailerb2bqtyUpdateLatest']);
//cart -delete
Route::get('retailer/b2b/cart/delete/{id}', [ASEController::class, 'retailerb2bcartdelete']);
// cart preview
Route::get('retailer/b2b/cart/pdf/url', [ASEController::class, 'retailerb2bcartPlacePDF_URL']);
Route::get('retailer/b2b/cart/pdf/view', [ASEController::class, 'retailerb2bcartPreviewPDF_view'])->name('retailer.cart.place.pdf');
//multicolor bulk add
Route::post('retailer/b2b/bulkAddTocart', [ASEController::class, 'retailerb2bbulkAddTocart']);
//order
Route::post('retailer/b2b/product-place-order', [ASEController::class, 'retailerb2bplaceOrder']);

Route::get('retailer/b2b/order/list', [ASEController::class, 'retailerb2bOrderlist']);
Route::get('retailer/b2b/order/show/{id}', [ASEController::class, 'retailerb2bOrdershow']);
Route::get('retailer/b2b/order/details/{id}', [ASEController::class, 'retailerb2bOrderDetails']);
Route::get('retailer/b2b/order/place/pdf/url/{id}', [ASEController::class, 'retailerb2bOrderPlacePDF_URL']);
Route::get('retailer/b2b/order/place/pdf/view/{id}', [ASEController::class, 'retailerb2bOrderPlacePDF_view'])->name('retailer.order.place.pdf');

//terms & condition
Route::get('retailer/terms', [ASEController::class, 'retailerterms']);

Route::get('retailer/duplicate/records', [ASEController::class, 'retailerduplicateCheck']);


//monthly scan limit
Route::get('retailer/monthly/scan/limit/{id}', [ASEController::class, 'retailermonthlyScan']);






