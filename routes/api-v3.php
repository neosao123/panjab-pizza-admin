<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V3\AuthController;
use App\Http\Controllers\Api\V3\CartController;
use App\Http\Controllers\Api\V3\CommonController;
use App\Http\Controllers\Api\V3\CashierController;
use App\Http\Controllers\Api\V3\CustomerController;
use App\Http\Controllers\Api\V3\SpecialOfferController;
use App\Http\Controllers\Api\V3\SignaturePizzaController;
use App\Http\Controllers\Api\V3\CashierOrderController;
use App\Http\Controllers\Api\V3\InvoicesController;
use App\Http\Controllers\Api\V3\CustomerOrderController;
use App\Http\Controllers\Api\V3\PaymentController;
use App\Http\Controllers\Api\V3\StoreReportsController;
use App\Http\Controllers\Api\V3\PizzasController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::get('/latest/version', [CommonController::class, 'get_latest_version']);
Route::post('/set/version', [CommonController::class, 'set_version']);

Route::get('/truncate/orders', [CommonController::class, 'truncateOrders']);

Route::get('/trial-notify', [CashierController::class, 'send_trial_notification']);

Route::group(['middleware' => 'cors'], function () {

    //Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/dynamic-slider/app', [CommonController::class, 'dynamicSliderApp']);
    Route::get('/dynamic-slider/web', [CommonController::class, 'dynamicSliderWeb']);

    Route::get('/dynamic-slider', [CommonController::class, 'dynamicSlider']);
    Route::get('/home/pizzas', [CommonController::class, 'homePagePizzas']);
    Route::get('/storelocation/order/{code}', [CommonController::class, 'order_list']);
    Route::get('/pizza/sizes-prices', [CommonController::class, 'pizzaSizesAndPrices']);
    Route::get('/cheese', [CommonController::class, 'cheese']);
    Route::get('/settings', [CommonController::class, 'settings']);
    Route::get('/crust', [CommonController::class, 'crust']);
    Route::get('/dips', [CommonController::class, 'dips']);
    Route::get('/soft-drinks', [CommonController::class, 'softDrinks']);
    Route::get('/special-bases', [CommonController::class, 'specialBases']);
    Route::get('/toppings', [CommonController::class, 'toppings']);
    Route::get('/all-ingredients', [CommonController::class, 'allIngredients']);
    Route::get('/sides', [CommonController::class, 'sides']);
    Route::get('/type-wise-searchable-sides', [CommonController::class, 'typeWiseSearchableSides']);
    Route::get('/store-locations', [CommonController::class, 'storeLocations']);
    Route::get('/special-offers', [SpecialOfferController::class, 'list']);
    Route::get('/searchable-special-deal', [SpecialOfferController::class, 'searchableSpecialDeals']);
    Route::get('/special-offers/{code}', [SpecialOfferController::class, 'show']);
    // nearest store by lat long
    Route::get('/nearest-store', [CommonController::class, 'nearestStoreByLatLng']);

    Route::get('/signature-pizzas', [SignaturePizzaController::class, 'list']);
    Route::get('/signature-pizzas/{code}', [SignaturePizzaController::class, 'show']);

    Route::get('/pizzas', [PizzasController::class, 'list']);
    Route::get('/pizzas/{code}', [PizzasController::class, 'show']);

    //for signature pizza and other pizza
    Route::get('/pizza/toppings/{code}/{section}', [CommonController::class, 'pizzaToppingsConfig']);

    Route::post('/zipcode/check/deliverable', [CommonController::class, 'check_zipcode_deliverable']);
    Route::post('/zipcode/list', [CommonController::class, 'zipcode_deliverable_list']);
    Route::get('/pizzaPrice', [CommonController::class, 'pizzaPrices']);

    Route::get('/zipcode/serviceable', [CommonController::class, 'check_zipcode_serviceable']);

    // customer  (client website)
    Route::group(['prefix' => 'customer'], function () {
        // register
        Route::post('/register', [CustomerController::class, 'customer_register']);
        // auth
        Route::post('/login', [CustomerController::class, 'customer_login']);
        Route::post('/reset/password', [CustomerController::class, 'customer_reset_password']);
		Route::get('/verify', [CustomerController::class, 'verify_customer_token']);
		Route::post('/update/password', [CustomerController::class, 'update_customer_password']);
        
		Route::post('/logout', [CustomerController::class, 'customer_logout']);
        // profile
        Route::post('/profile', [CustomerController::class, 'get_customer_info']);
        Route::get('/my-profile/{code}', [CustomerController::class, 'customer_profile']);
        Route::get('/detailsByToken', [CustomerController::class, 'customer_details_by_token']);
        Route::post('/updateProfile', [CustomerController::class, 'update_customer_info']);
        Route::post('/addAddress', [CustomerController::class, 'add_customer_address']);
        Route::post('/updateAddress', [CustomerController::class, 'update_customer_address']);
        Route::post('/deleteAddress', [CustomerController::class, 'delete_customer_address']);
        Route::get('/getstorelocationbycity', [CustomerController::class, 'getStoreLocationByCity']);
        Route::get('/previousorder', [CashierOrderController::class, 'get_previous_order']);
        Route::post('/payment/callback',  [CustomerOrderController::class, 'webhook']);
        Route::get('/payment/success',  [CustomerOrderController::class, 'payment_success']);
        Route::get('/payment/failed',  [CustomerOrderController::class, 'payment_failed']);
        Route::post('/changepassword', [CustomerController::class, 'change_password']);
        // orders
        Route::group(['prefix' => '/order'], function () {
            Route::post('/place',  [CustomerOrderController::class, 'order_place']);
            Route::post('/list',  [CustomerOrderController::class, 'get_order_list']);
            Route::post('/details',  [CustomerOrderController::class, 'get_order_details']);
            Route::post('/getlist',  [CustomerOrderController::class, 'customer_order_list']);
        });
    });

    // in-store (cashier website)
    Route::group(['prefix' => 'cashier'], function () {
        // auth
        Route::post('/login', [CashierController::class, 'cashier_login']);
        Route::post('/resetPassword', [CashierController::class, 'cashier_reset_password']);
        Route::get('/verifyToken', [CashierController::class, 'verify_cashier_token']);
        Route::post('/updatePassword', [CashierController::class, 'update_cashier_password']);
        Route::post('/change-password', [CashierController::class, 'change_password']);
        Route::post('/logout', [CashierController::class, 'cashier_logout']);
        // profile
        Route::post('/profile', [CashierController::class, 'get_cashier_info']);
        Route::post('/updateProfile', [CashierController::class, 'update_cashier_info']);
        Route::post('/updateFirebaseId', [CashierController::class, 'update_firebase_token']);
        Route::post('/getPrevAddress', [CashierController::class, 'getPrevAddress']);
        Route::group(['prefix' => 'order'], function () {
            Route::post('status-change', [CashierOrderController::class, 'update_order_status']);
            Route::post('place', [CashierOrderController::class, 'order_place']);
            Route::post('edit', [CashierOrderController::class, 'order_edit']);
            Route::post('details', [CashierOrderController::class, 'get_order_details']);
            Route::post('assignDeliveryExecutive', [CashierOrderController::class, 'delivery_executive_assign']);
            Route::post('list', [CashierOrderController::class, 'get_order_list']);
            Route::post('deliveryTypeChange', [CashierOrderController::class, 'update_delivery_type']);
            Route::post('direct/change-delivery-type', [CashierOrderController::class, 'direct_update_delivery_type']);
            Route::post('notificationList', [CashierOrderController::class, 'get_notification_order_list']);
            Route::post('addCreditComments', [CashierOrderController::class, 'add_credit_comments']);
            Route::get('date_time', [CashierOrderController::class, 'date_time']);
        });
        Route::get('detailsByToken', [CashierController::class, 'cashier_details_by_token']);
        Route::get('highlight/screen', [CashierOrderController::class, 'get_placed_or_ready_orders']);
        Route::get('bellringer/screen', [CashierOrderController::class, 'get_orders_for_accept_screen']);
        Route::get('store/summary', [CashierOrderController::class, 'order_summary']);
    });

    //cashier order
    Route::get('/delivery-executive', [CashierOrderController::class, 'get_delivery_executive_list']);
    Route::get('/deliveryExecutive/{storeCode}', [CashierOrderController::class, 'get_delivery_executive_by_storecode']);

    Route::group(['prefix' => '/payment'], function () {
        Route::post('/verify', [PaymentController::class, 'verify']);
        Route::post('/cancel', [PaymentController::class, 'cancel']);
    });

    // invoice api
    Route::group(['prefix' => '/invoice'], function () {
        Route::post('/list', [InvoicesController::class, 'getInvoice']);
    });
    Route::get('/invoices', [InvoicesController::class, 'getListByMobileNumber']);


    Route::group(['prefix' => '/storeReport'], function () {
        Route::get('/downloadReports', [StoreReportsController::class, 'downloadReports']);
        Route::get('/sendReportToEmail', [StoreReportsController::class, 'sendReportToEmail']);
    });

    Route::get('send/daily/store/summary', [StoreReportsController::class, 'daily_store_summary_mail']);
    Route::post('sendContactUsEmail', [CommonController::class, 'sendContactUsEmail']);

    // Cart Apis
    Route::group(['prefix' => 'cart'], function () {
        Route::get('/', [CartController::class, 'get_cart']);
        Route::post('/verify-cart', [CartController::class, 'verify_cart']);
        Route::post('/store', [CartController::class, 'store']);
        Route::post('/update', [CartController::class, 'update']);
        Route::get('/delete/{id}/{device_id}', [CartController::class, 'delete']);
    });

    Route::group(['prefix' => 'user'], function () {
        Route::post('/registration', [AuthController::class, 'user_registration']);
        Route::post('/login', [AuthController::class, 'user_login']);
        Route::post('/logout', [AuthController::class, 'user_logout']);
        Route::post('/resetPassword', [AuthController::class, 'user_reset_password']);
    });
});
