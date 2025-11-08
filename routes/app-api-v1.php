<?php

use Illuminate\Support\Facades\Route;
// Import Controllers
use App\Http\Controllers\Api\AppV1\CartController;
use App\Http\Controllers\Api\AppV1\OrderController;
use App\Http\Controllers\Api\AppV1\SpecialOfferController;
use App\Http\Controllers\Api\AppV1\CommonController;
use App\Http\Controllers\Api\AppV1\AuthController;

Route::group(['middleware' => 'cors'], function () {
  // For SpecailPizza
  Route::get('/getSpecials', [SpecialOfferController::class, 'get_specials']);
  Route::post('/getSpecialDetails', [SpecialOfferController::class, 'get_specials_details']);
  // For Customized Pizza
  Route::get('/getAllIngredients', [CommonController::class, 'get_all_ingredients']);
  // For List with Limit 10
  Route::get('/sidesList', [CommonController::class, 'get_sides_list']);
  Route::get('/dipsList', [CommonController::class, 'get_dips_list']);
  Route::get('/softdrinksList', [CommonController::class, 'get_softdrinks_list']);
  // Common Api's
  Route::get('/sides', [CommonController::class, 'get_sides']);
  Route::get('/dips', [CommonController::class, 'get_dips']);
  Route::get('/softdrinks', [CommonController::class, 'get_softdrinks']);
  Route::get('/cheese', [CommonController::class, 'get_cheese']);
  Route::get('/crust', [CommonController::class, 'get_crust']);
  Route::get('/specialbases', [CommonController::class, 'get_specialbases']);
  Route::get('/toppings', [CommonController::class, 'get_toppings']);
  Route::get('/pizzaPrice', [CommonController::class, 'get_pizza_price']);
  //
  Route::get('/storelocation', [CommonController::class, 'get_storeLocation']);
  Route::get('/getstorelocationbycity', [CommonController::class, 'getStoreLocationByCity']);
  Route::get('/nearest-store', [CommonController::class, 'nearestStoreByLatLng']);
  //
  Route::get('/settings', [CommonController::class, 'get_settings']);
  //
  Route::post('/zipcode/check/deliverable', [CommonController::class, 'check_zipcode_deliverable']);
  Route::post('/zipcode/list', [CommonController::class, 'zipcode_deliverable_list']);
  Route::get('/getDynamicSlider', [CommonController::class, 'getDynamicSlider']);

  // Server Side Cart
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
    Route::group(['prefix' => '/order'], function () {
      Route::post('/place',  [OrderController::class, 'order_place']);
      Route::post('/details',  [OrderController::class, 'get_order_details']);
      Route::post('/getlist',  [OrderController::class, 'user_order_list']);
    });
  });
  Route::group(['prefix' => 'guest-user'], function () {
    Route::group(['prefix' => '/order'], function () {
      Route::post('/place',  [OrderController::class, 'guest_order_place']);
    });
  });
  // For Contact Us Page
  Route::post('sendContactUsEmail', [CommonController::class, 'sendContactUsEmail']);
});
