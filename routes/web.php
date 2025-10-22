<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StateController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\DistributorController;
use App\Http\Controllers\LostBookController;
use App\Http\Controllers\CollectionController;
use App\Http\Controllers\Lms\IssueController;
use App\Http\Controllers\Facility\CabBookingController;
use App\Http\Controllers\Facility\TrainBookingController;
use App\Http\Controllers\Facility\FlightBookingController;
use App\Http\Controllers\Facility\HotelBookingController;
use App\Http\Controllers\Facility\PropertyController;
use App\Http\Controllers\Cave\CaveFormController;
use App\Http\Controllers\Cave\CaveLocationController;
use App\Http\Controllers\Cave\CaveCategoryController;
use App\Http\Controllers\RetailerProductController;
use App\Http\Controllers\TermsController;
use App\Http\Controllers\RetailerOrderController;
use App\Http\Controllers\CatalogueController;
use App\Http\Controllers\SchemeController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\SizeController;
use App\Http\Controllers\ColorController;
use App\Http\Controllers\CategoryController;

use Illuminate\Support\Facades\Route;

Route::get('/cache-clear', function() {
	// \Artisan::call('route:cache');
	\Artisan::call('config:cache');
	\Artisan::call('permission:cache-reset');
   //	\Artisan::call('cache:clear');
	\Artisan::call('view:clear');
	\Artisan::call('config:clear');
	\Artisan::call('view:cache');
	\Artisan::call('route:clear');
	dd('Cache cleared');
});
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
//Route::group(['middleware' => ['role:super-admin|lms-admin']], function() {
Route::group(['middleware' => ['auth']], function() {

    Route::resource('permissions', App\Http\Controllers\PermissionController::class);
    Route::get('permissions/{permissionId}/delete', [App\Http\Controllers\PermissionController::class, 'destroy']);

    Route::resource('roles', App\Http\Controllers\RoleController::class);
    Route::get('roles/{roleId}/delete', [App\Http\Controllers\RoleController::class, 'destroy']);
    Route::get('roles/{roleId}/give-permissions', [App\Http\Controllers\RoleController::class, 'addPermissionToRole']);
    Route::put('roles/{roleId}/give-permissions', [App\Http\Controllers\RoleController::class, 'givePermissionToRole']);

    Route::resource('users', App\Http\Controllers\UserController::class);
    Route::get('users/{userId}/delete', [App\Http\Controllers\UserController::class, 'destroy']);
    
    
    //states
    
    Route::resource('states', StateController::class);
    Route::get('states/{userId}/delete', [StateController::class, 'destroy']);
    Route::get('states/{userId}/status/change', [StateController::class, 'status']);
    Route::post('states/bulk/upload', [StateController::class, 'bulkUpload']);
    Route::get('states/csv/export', [StateController::class, 'stateExport']);
    
    //area
    Route::resource('areas', AreaController::class);
    Route::get('areas/{userId}/delete', [AreaController::class, 'destroy']);
    Route::get('areas/{userId}/status/change', [AreaController::class, 'status']);
    Route::post('areas/bulk/upload', [AreaController::class, 'bulkUpload']);
    Route::get('areas/csv/export', [AreaController::class, 'areaExport']);
    Route::get('areas/state/wise/{id}', [AreaController::class, 'areaStateWise']);
    
    //employee
    Route::resource('employees', EmployeeController::class);
    Route::get('employees/{userId}/delete', [EmployeeController::class, 'destroy']);
    Route::get('employees/{userId}/status/change', [AreaController::class, 'status']);
    Route::get('employees/csv/export', [EmployeeController::class, 'employeeExport']);
    Route::post('employees/bulk/upload', [EmployeeController::class, 'bulkUpload']);
    Route::get('employees/state/{state}', [EmployeeController::class, 'state'])->name('users.state');
    Route::get('employees/hierarchy', [EmployeeController::class, 'hierarchy'])->name('employees.hierarchy');
    Route::get('employees/notifications', [EmployeeController::class, 'notifications'])->name('notifications.index');
    Route::get('employees/filter-by-brand', [EmployeeController::class, 'hierarchy'])->name('employees.filter-by-brand');
    Route::post('employee/area/save', [EmployeeController::class, 'addArea'])->name('employee.area.store');
    Route::get('employee/area/delete/{id}', [EmployeeController::class, 'deleteArea'])->name('employee.area.delete');

    //team show
    Route::get('vp/brand/wise/{id}', [EmployeeController::class, 'vpBrandWise'])->name('vp.brand.wise');
    Route::get('state/vp/wise/{id}', [EmployeeController::class, 'stateVpWise'])->name('state.vp.wise');

    Route::get('rsm/state/wise/{id}', [EmployeeController::class, 'rsmStateWise'])->name('rsm.state.wise');

    Route::get('asm/rsm/wise/{id}', [EmployeeController::class, 'asmRsmWise'])->name('asm.rsm.wise');


    Route::get('ase/asm/wise/{id}', [EmployeeController::class, 'aseAsmWise'])->name('ase.asm.wise');
    //activity
     Route::get('activity', [ActivityController::class, 'index'])->name('activities.index');
    
    //distributor
    Route::resource('distributors', DistributorController::class);
    Route::get('distributors/{userId}/delete', [DistributorController::class, 'destroy']);
    Route::get('distributors/{userId}/status/change', [DistributorController::class, 'status']);
    Route::get('distributors/csv/export', [DistributorController::class, 'employeeExport']);
    Route::post('distributors/bulk/upload', [DistributorController::class, 'bulkUpload']);
    Route::get('distributors/note', [DistributorController::class, 'note'])->name('distributors.note');
    
    //collections
     Route::resource('collections', CollectionController::class);
     Route::get('collections/{userId}/delete', [CollectionController::class, 'destroy']);
     Route::get('collections/{userId}/status/change', [CollectionController::class, 'status']);
     Route::get('collections/export/csv', [CollectionController::class, 'csvExport']);
     Route::post('collections/upload/csv', [CollectionController::class, 'csvImport']);
     
     //colors
     Route::resource('colors', ColorController::class);
     Route::get('colors/{userId}/edit', [ColorController::class, 'edit'])->name('colors.edit');
     Route::post('colors/{userId}/update', [ColorController::class, 'update'])->name('colors.update');
     Route::get('colors/{userId}/view', [ColorController::class, 'show'])->name('colors.view');
     Route::get('colors/{userId}/delete', [ColorController::class, 'destroy'])->name('colors.delete');
     Route::get('colors/{userId}/status/change', [ColorController::class, 'status'])->name('colors.status');

     //size
     Route::resource('sizes', SizeController::class);
     Route::get('sizes/{userId}/edit', [SizeController::class, 'edit'])->name('sizes.edit');
     Route::post('sizes/{userId}/update', [SizeController::class, 'update'])->name('sizes.update');
     Route::get('sizes/{userId}/view', [SizeController::class, 'show'])->name('sizes.view');
     Route::get('sizes/{userId}/delete', [SizeController::class, 'destroy'])->name('sizes.delete');
     Route::get('sizes/{userId}/status/change', [SizeController::class, 'status'])->name('sizes.status');
    
    //categories
    Route::resource('categories', CategoryController::class);
    Route::get('categories/{userId}/delete', [CategoryController::class, 'destroy']);
    Route::get('categories/{userId}/status/change', [CategoryController::class, 'status']);
    Route::get('categories/export/csv', [CategoryController::class, 'csvExport']);
    Route::post('categories/upload/csv', [CategoryController::class, 'csvImport']);

    //products
    Route::resource('products', ProductController::class);
    Route::get('products/{userId}/delete', [ProductController::class, 'destroy']);
    Route::get('products/{userId}/status/change', [ProductController::class, 'status']);
    Route::get('products/export/csv', [ProductController::class, 'csvExport']);
    Route::post('products/upload/csv', [ProductController::class, 'csvImport']);
    
    
    //catalogues
     Route::resource('catalogues', CatalogueController::class);
     Route::get('catalogues/{userId}/edit', [CatalogueController::class, 'edit'])->name('catalogues.edit');
     Route::post('catalogues/{userId}/update', [CatalogueController::class, 'update'])->name('catalogues.update');
     Route::get('catalogues/{userId}/view', [CatalogueController::class, 'show'])->name('catalogues.view');
     Route::get('catalogues/{userId}/delete', [CatalogueController::class, 'destroy'])->name('catalogues.delete');
     Route::get('catalogues/{userId}/status/change', [CatalogueController::class, 'status'])->name('catalogues.status');
     Route::get('catalogues/export/csv', [CatalogueController::class, 'exportCSV'])->name('catalogues.exportCSV');

     //schemes
     Route::resource('schemes', SchemeController::class);
     Route::get('schemes/{userId}/edit', [SchemeController::class, 'edit'])->name('schemes.edit');
     Route::post('schemes/{userId}/update', [SchemeController::class, 'update'])->name('schemes.update');
     Route::get('schemes/{userId}/view', [SchemeController::class, 'show'])->name('schemes.view');
     Route::get('schemes/{userId}/delete', [SchemeController::class, 'destroy'])->name('schemes.delete');
     Route::get('schemes/{userId}/status/change', [SchemeController::class, 'status'])->name('schemes.status');

      //news
     Route::resource('news', NewsController::class);
     Route::get('news/{userId}/edit', [NewsController::class, 'edit'])->name('news.edit');
     Route::post('news/{userId}/update', [NewsController::class, 'update'])->name('news.update');
     Route::get('news/{userId}/view', [NewsController::class, 'show'])->name('news.view');
     Route::get('news/{userId}/delete', [NewsController::class, 'destroy'])->name('news.delete');
     Route::get('news/{userId}/status/change', [NewsController::class, 'status'])->name('news.status');

     
     //stores
    Route::resource('stores', StoreController::class);
    Route::get('stores/{userId}/delete', [StoreController::class, 'destroy']);
    Route::get('stores/{userId}/status/change', [StoreController::class, 'status']);
    Route::get('stores/csv/export', [StoreController::class, 'employeeExport']);
    Route::post('stores/bulk/upload', [StoreController::class, 'bulkUpload']);
    Route::get('stores/noorderreason', [StoreController::class, 'noorderreason'])->name('stores.noorderreason');
    Route::post('/stores/transfer/to/ase', [StoreController::class, 'bulkASEDistributorransfer'])->name('stores.transfer');

    //orders
    Route::get('primary/order', [OrderController::class, 'primaryOrder'])->name('primary.orders.index');
    Route::get('primary/order/csv/export', [OrderController::class, 'primaryOrderExport'])->name('primary.orders.export');

    Route::get('primary/order/report', [OrderController::class, 'primaryOrderReport'])->name('primary.order.report');
    Route::get('primary/order/report/csv/export', [OrderController::class, 'primaryOrderReportExport'])->name('primary.order.report.export');


    Route::get('secondary/order', [OrderController::class, 'secondaryOrder'])->name('secondary.orders.index');
    Route::get('secondary/order/csv/export', [OrderController::class, 'secondaryOrderExport'])->name('secondary.orders.export');

    Route::get('secondary/order/report', [OrderController::class, 'secondaryOrderReport'])->name('secondary.order.report');
    Route::get('secondary/order/report/csv/export', [OrderController::class, 'secondaryOrderReportExport'])->name('secondary.order.report.export');


    //attendance
    Route::get('attendance/report', [EmployeeController::class, 'attendanceReport'])->name('attendance.report');
    Route::get('attendance/report/csv/export', [EmployeeController::class, 'attendanceReportExport'])->name('attendance.report.export');



    Route::prefix('reward')->name('reward.')->group(function () {
        Route::prefix('/user')->name('retailer.user.')->group(function () {
            Route::get('/', [RetailerUserController::class, 'index'])->name('index');
            Route::get('/create', [RetailerUserController::class, 'create'])->name('create');
            Route::post('/store', [RetailerUserController::class, 'store'])->name('store');
            Route::get('/{id}/view', [RetailerUserController::class, 'show'])->name('view');
            Route::get('/{id}/edit', [RetailerUserController::class, 'edit'])->name('edit');
            Route::post('/{id}/update', [RetailerUserController::class, 'update'])->name('update');
            Route::get('/{id}/status', [RetailerUserController::class, 'status'])->name('status');
            Route::get('/{id}/verification', [RetailerUserController::class, 'verification'])->name('verification');
            Route::get('/{id}/delete', [RetailerUserController::class, 'destroy'])->name('delete');
        	Route::get('/export/csv', [RetailerUserController::class, 'exportCSV'])->name('export.csv');
			Route::get('/login/count', [RetailerUserController::class, 'loginCount'])->name('login.count');
			Route::get('/login/count/export/csv', [RetailerUserController::class, 'loginCountexportCSV'])->name('login.count.export.csv');
			Route::get('/login/store/count/{state}', [RetailerUserController::class, 'loginStoreCount'])->name('login.store.count');
			Route::get('/login/store/count/export/csv/{state}', [RetailerUserController::class, 'loginStoreCountCsv'])->name('login.store.export.csv');
        });
		    // product
        Route::prefix('/product')->name('retailer.product.')->group(function () {
            Route::get('/', [RetailerProductController::class, 'index'])->name('index');
            Route::get('/create', [RetailerProductController::class, 'create'])->name('create');
            Route::post('/store', [RetailerProductController::class, 'store'])->name('store');
            Route::get('/{id}/view', [RetailerProductController::class, 'show'])->name('view');
            Route::get('/{id}/edit', [RetailerProductController::class, 'edit'])->name('edit');
            Route::post('/update/{id}', [RetailerProductController::class, 'update'])->name('update');
            Route::get('/{id}/status', [RetailerProductController::class, 'status'])->name('status');
            Route::get('/{id}/delete', [RetailerProductController::class, 'destroy'])->name('delete');
            Route::post('/bulk/upload', [RetailerProductController::class, 'bulkUpload'])->name('bulkUpload');
            Route::get('/export/csv', [RetailerProductController::class, 'exportCSV'])->name('export.csv');
			Route::post('/specification/add', [RetailerProductController::class, 'specificationAdd'])->name('specification.add');
            Route::get('/specification/{id}/delete', [RetailerProductController::class, 'specificationDestroy'])->name('specification.delete');
            Route::post('/specification/{id}/edit', [RetailerProductController::class, 'specificationEdit'])->name('specification.edit');

        });

        // product
        Route::prefix('/qrcode')->name('retailer.barcode.')->group(function () {
            Route::get('/', [BarcodeController::class, 'index'])->name('index');
            Route::get('/create', [BarcodeController::class, 'create'])->name('create');
            Route::get('/csv/export', [BarcodeController::class, 'csvExport'])->name('csv.export');
            Route::get('{slug}/csv/export', [BarcodeController::class, 'csvExportSlug'])->name('detail.csv.export');
            Route::post('/store', [BarcodeController::class, 'store'])->name('store');
            Route::get('/{id}/view', [BarcodeController::class, 'show'])->name('view');
		    Route::get('/{id}/detail', [BarcodeController::class, 'view'])->name('show');
			Route::get('/{id}/used/qrcode', [BarcodeController::class, 'useqrcode'])->name('useqrcode');
			Route::get('/{id}/edit', [BarcodeController::class, 'edit'])->name('edit');
            Route::post('/{id}/update', [BarcodeController::class, 'update'])->name('update');
            Route::get('/{id}/status', [BarcodeController::class, 'status'])->name('status');
            Route::get('/{id}/delete', [BarcodeController::class, 'destroy'])->name('delete');
            Route::get('/bulkDelete', [BarcodeController::class, 'bulkDestroy'])->name('bulkDestroy');
			Route::get('qr/csv/export/page', [BarcodeController::class, 'qrcsvExport'])->name('qr.details.csv.export');
			Route::post('/sequence/save', [BarcodeController::class, 'sequenceSave'])->name('sequence.save');
			Route::get('/sequence/csv/download', [BarcodeController::class, 'sequenceCsv'])->name('sequence.csv.download');
			Route::post('/sequence/csv/upload', [BarcodeController::class, 'sequenceCsvUpload'])->name('sequence.csv.upload');
			Route::get('/error/log/Report', [BarcodeController::class, 'errorlogCsv'])->name('error.log.report.csv.export');
        });
  			// invoice
          Route::prefix('/qrcode/redeem')->name('qrcode.redeem.')->group(function () {
            Route::get('/', [BarcodeController::class, 'qrRedeem'])->name('index');
            Route::get('/list/csv/export', [BarcodeController::class, 'qrRedeemcsvExport'])->name('csv.export');
             Route::post('/csv/upload', [BarcodeController::class, 'areaCSVUpload'])->name('csv.upload');
              Route::get('/retailer/wise/report', [BarcodeController::class, 'retailerwiseReport'])->name('retailer.wise.report');
            Route::get('/retailer/list/csv/export', [BarcodeController::class, 'retailerReportcsvExport'])->name('retailer.csv.export');
            Route::get('retailer/product/list/csv/export', [BarcodeController::class, 'retailerProductReportcsvExport'])->name('retailer.product.csv.export');
            Route::get('/fetch-stores', [BarcodeController::class, 'fetchStores'])->name('fetch.stores');
             Route::get('/retailer/scan/report/monthly', [BarcodeController::class, 'retailerscanReport'])->name('retailer.scan.report');
              Route::get('/retailer/scan/report/monthly/csv/export', [BarcodeController::class, 'retailerscanReportCsv'])->name('retailer.scan.report.csv.export');
               Route::get('/State/Distributor/Wise/Mismatch/Coupon/Report', [BarcodeController::class, 'couponmismatchCsv'])->name('mismatch.csv.export');
                
        });
		
		// terms
          Route::prefix('/terms')->name('retailer.terms.')->group(function () {
            Route::get('/', [TermsController::class, 'index'])->name('index');
            Route::post('/store', [TermsController::class, 'store'])->name('store');
            Route::post('/update', [TermsController::class, 'update'])->name('update');
        });

        // product
        Route::prefix('/order')->name('retailer.order.')->group(function () {
            Route::get('/', [RetailerOrderController::class, 'index'])->name('index');
            Route::get('/{id}/view', [RetailerOrderController::class, 'show'])->name('view');
            Route::get('/export/csv', [RetailerOrderController::class, 'exportCSV'])->name('export.csv');
			Route::get('/{id}/approval/{status}', [RetailerOrderController::class, 'approval'])->name('approval');
			Route::get('/{id}/status/{status}', [RetailerOrderController::class, 'status'])->name('status');
			Route::get('/{id}/product/status/{status}', [RetailerOrderController::class, 'orderProductStatus'])->name('product.status');
			Route::post('/save/note', [RetailerOrderController::class, 'saveNote'])->name('note.save');
        });
    });
});
