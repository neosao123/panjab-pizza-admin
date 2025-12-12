<?php

use App\Http\Controllers\DynamicSliderController;
use App\Http\Controllers\PaymentSettingsController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\SidesToppingsController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\RoleWiseRightsController;
use App\Http\Controllers\CheeseController;
use App\Http\Controllers\CookController;
use App\Http\Controllers\CrustsController;
use App\Http\Controllers\CrustTypeController;
use App\Http\Controllers\DipsController;
use App\Http\Controllers\SpecialbasesController;
use App\Http\Controllers\SoftDrinkController;
use App\Http\Controllers\ToppingsController;
use App\Http\Controllers\SidesController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\SpecialOfferController;
use App\Http\Controllers\StoreLocationController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\DeliverZipcodeController;
use App\Http\Controllers\OrdersController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\SauceController;
use App\Http\Controllers\SpicyController;
use App\Http\Controllers\PizzaPriceController;
use App\Http\Controllers\SignaturePizzaCategoryController;
use App\Http\Controllers\SignaturePizzaController;
use App\Http\Controllers\PizzasCategoryController;
use App\Http\Controllers\PizzasController;
use App\Http\Controllers\PictureController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\BackgroundImageController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\SMSController;
use App\Http\Controllers\SmsTemplateController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;

Route::get("/clear", function () {
    sleep(1);
    Artisan::call("cache:clear");
    sleep(1);
    Artisan::call("view:clear");
    sleep(1);
    Artisan::call("config:clear");
    sleep(1);
    Artisan::call("optimize");
    sleep(1);
    Artisan::call("optimize:clear");
    sleep(1);
    echo "Cleared";
});

Route::post("/send-notification", [NotificationController::class, 'sendNotification'])->name('send-notification');

Route::get("/hash", function () {
    echo Hash::make('S!ngh2025');
});

Route::get('/user/deleteuserprocess', [HomeController::class, 'delete_user_process']);
Route::get("", [LoginController::class, 'index']);
//Route::get("", [HomeController::class, 'index']);
//Route::get("/", [HomeController::class, 'index']);
Route::get("/", [LoginController::class, 'index']);
Route::get("/toastr", [HomeController::class, 'test_toastr']);
Route::get('/login', [LoginController::class, 'index'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::get("/logout", [LoginController::class, 'logout']);
Route::get('/updatepassword', [LoginController::class, 'updatePassword']);

Route::get('/reset-password', [LoginController::class, 'reset']);
Route::post('/forgot-password', [LoginController::class, 'resetPassword']);
Route::get('/recoverPassword/{token}', [LoginController::class, 'verifyTokenLink']);
Route::post('/recover-password', [LoginController::class, 'updateMemberPassword']);

Route::group(['middleware' => 'admin'], function () {
    Route::get("/dashboard", [DashboardController::class, 'index']);
    Route::get('/profile/{id}', [ProfileController::class, 'index']);
    Route::get('/profileshow/{id}', [ProfileController::class, 'show']);
    Route::post('profile-update/{id}', [ProfileController::class, 'updateprofile'])->name('updateprofile');

    Route::get('/access/denied', [HomeController::class, 'accessdenied']);

    //role wise rights
    Route::group(['prefix' => '/rolewiserights'], function () {
        Route::get('/list', [RoleWiseRightsController::class, 'index']);
        Route::get('/getMenuList/{role}', [RoleWiseRightsController::class, 'getMenuList']);
        Route::post('/saveMenu', [RoleWiseRightsController::class, 'saveMenu']);
    });

    //Users
    Route::get('getuserslist', [UsersController::class, 'getUserList']);
    Route::group(['prefix' => '/users'], function () {
        Route::get('/list', [UsersController::class, 'index']);
        Route::get('/add', [UsersController::class, 'add']);
        Route::post('/store', [UsersController::class, 'store']);
        Route::get('/delete', [UsersController::class, 'delete']);
        Route::get('/edit/{code}', [UsersController::class, 'edit']);
        Route::post('/update', [UsersController::class, 'update']);
        Route::get('/view/{code}', [UsersController::class, 'view']);
        Route::get('/deleteImage', [UsersController::class, 'deleteImage']);
    });

    //Cheese
    Route::get('getCheese', [CheeseController::class, 'getCheese']);
    Route::get('getCheeseList', [CheeseController::class, 'getCheeseList']);
    Route::group(['prefix' => '/cheese'], function () {
        Route::get('/list', [CheeseController::class, 'index']);
        Route::get('/edit/{code}', [CheeseController::class, 'edit']);
        Route::post('/update', [CheeseController::class, 'update']);
        Route::get('/view/{code}', [CheeseController::class, 'view']);
        Route::get('/add', [CheeseController::class, 'add'])->name('cheese.add');
        Route::post('/store', [CheeseController::class, 'store'])->name('cheese.store');
        Route::get('/delete', [CheeseController::class, 'delete']);
    });

    //Crust
    Route::get('getCrust', [CrustsController::class, 'getCrusts']);
    Route::get('getCrustList', [CrustsController::class, 'getCrustList']);
    Route::group(['prefix' => '/crust'], function () {
        Route::get('/list', [CrustsController::class, 'index']);
        Route::get('/edit/{code}', [CrustsController::class, 'edit']);
        Route::post('/update', [CrustsController::class, 'update']);
        Route::get('/view/{code}', [CrustsController::class, 'view']);
        Route::get('/add', [CrustsController::class, 'add']);
        Route::post('/store', [CrustsController::class, 'store']);
        Route::get('/delete', [CrustsController::class, 'delete']);
    });

    //Crust_Type
    Route::get('getCrustType', [CrustTypeController::class, 'getCrustType']);
    Route::get('getCrustTypeList', [CrustTypeController::class, 'getCrustTypeList']);
    Route::group(['prefix' => '/crust-type'], function () {
        Route::get('/list', [CrustTypeController::class, 'index']);
        Route::get('/edit/{code}', [CrustTypeController::class, 'edit']);
        Route::post('/update', [CrustTypeController::class, 'update']);
        Route::get('/view/{code}', [CrustTypeController::class, 'view']);
        Route::get('/add', [CrustTypeController::class, 'add']);
        Route::post('/store', [CrustTypeController::class, 'store']);
        Route::get('/delete', [CrustTypeController::class, 'delete']);
    });

    //Special Bases
    Route::get('getSpecialBases', [SpecialbasesController::class, 'getSpecialBases']);
    Route::get('getSpecialBasesList', [SpecialbasesController::class, 'getSpecialBasesList']);
    Route::group(['prefix' => '/specialbases'], function () {
        Route::get('/list', [SpecialbasesController::class, 'index']);
        Route::get('/edit/{code}', [SpecialbasesController::class, 'edit']);
        Route::post('/update', [SpecialbasesController::class, 'update']);
        Route::get('/view/{code}', [SpecialbasesController::class, 'view']);
        Route::get('/add', [SpecialbasesController::class, 'add']);
        Route::post('/store', [SpecialbasesController::class, 'store']);
        Route::get('/delete', [SpecialbasesController::class, 'delete']);
    });

    // Spicy
    Route::get('getSpicy', [SpicyController::class, 'getSpicy']);
    Route::get('getSpicyList', [SpicyController::class, 'getSpicyList']);
    Route::group(['prefix' => '/spicy'], function () {
        Route::get('/list', [SpicyController::class, 'index']);
        Route::get('/edit/{code}', [SpicyController::class, 'edit']);
        Route::post('/update', [SpicyController::class, 'update']);
        Route::get('/view/{code}', [SpicyController::class, 'view']);
        Route::get('/add', [SpicyController::class, 'add']);
        Route::post('/store', [SpicyController::class, 'store']);
        Route::get('/delete', [SpicyController::class, 'delete']);
    });

    // Sauce
    Route::get('getSauce', [SauceController::class, 'getSauce']);
    Route::get('getSauceList', [SauceController::class, 'getSauceList']);
    Route::group(['prefix' => '/sauce'], function () {
        Route::get('/list', [SauceController::class, 'index']);
        Route::get('/edit/{code}', [SauceController::class, 'edit']);
        Route::post('/update', [SauceController::class, 'update']);
        Route::get('/view/{code}', [SauceController::class, 'view']);
        Route::get('/add', [SauceController::class, 'add']);
        Route::post('/store', [SauceController::class, 'store']);
        Route::get('/delete', [SauceController::class, 'delete']);
    });

    // Cook
    Route::get('getCook', [CookController::class, 'getCook']);
    Route::get('getCookList', [CookController::class, 'getCookList']);
    Route::group(['prefix' => '/cook'], function () {
        Route::get('/list', [CookController::class, 'index']);
        Route::get('/edit/{code}', [CookController::class, 'edit']);
        Route::post('/update', [CookController::class, 'update']);
        Route::get('/view/{code}', [CookController::class, 'view']);
        Route::get('/add', [CookController::class, 'add']);
        Route::post('/store', [CookController::class, 'store']);
        Route::get('/delete', [CookController::class, 'delete']);
    });

    //dips
    Route::get('getDips', [DipsController::class, 'getDips']);
    Route::get('getDipsList', [DipsController::class, 'getDipsList']);
    Route::group(['prefix' => '/dips'], function () {
        Route::get('/list', [DipsController::class, 'index']);
        Route::get('/edit/{code}', [DipsController::class, 'edit']);
        Route::post('/update', [DipsController::class, 'update']);
        Route::get('/view/{code}', [DipsController::class, 'view']);
        Route::get('/deleteImage', [DipsController::class, 'deleteImage']);
        Route::get('/add', [DipsController::class, 'add']);
        Route::post('/store', [DipsController::class, 'store']);
        Route::get('/delete', [DipsController::class, 'delete']);
    });

    //softdrink controller
    Route::get('getSoftDrink', [SoftDrinkController::class, 'getSoftDrink']);
    Route::get('getSoftDrinkList', [SoftDrinkController::class, 'getSoftDrinkList']);
    Route::group(['prefix' => '/softdrinks'], function () {
        Route::get('/list', [SoftDrinkController::class, 'index']);
        Route::get('/edit/{code}', [SoftDrinkController::class, 'edit']);
        Route::post('/update', [SoftDrinkController::class, 'update']);
        Route::get('/view/{code}', [SoftDrinkController::class, 'view']);
        Route::get('/deleteImage', [SoftDrinkController::class, 'deleteImage']);
        Route::get('/add', [SoftDrinkController::class, 'add']);
        Route::post('/store', [SoftDrinkController::class, 'store']);
        Route::get('/delete', [SoftDrinkController::class, 'delete']);
    });


    //Topppings
    Route::get('getToppings', [ToppingsController::class, 'getToppings']);
    Route::get('getToppingsList', [ToppingsController::class, 'getToppingsList']);
    Route::group(['prefix' => '/toppings'], function () {
        Route::get('/list', [ToppingsController::class, 'index']);
        Route::get('/edit/{code}', [ToppingsController::class, 'edit']);
        Route::post('/update', [ToppingsController::class, 'update']);
        Route::get('/view/{code}', [ToppingsController::class, 'view']);
        Route::get('/deleteImage', [ToppingsController::class, 'deleteImage']);
        Route::get('/add', [ToppingsController::class, 'add']); // ShreyasM - Add Toppings
        Route::post('/store', [ToppingsController::class, 'store']); // ShreyasM - Store Toppings
        Route::get('/delete', [ToppingsController::class, 'delete']);
    });

    //Sides - Topppings
    Route::get('getSidesToppings', [SidesToppingsController::class, 'getSidesToppings']);
    Route::get('getSidesToppingsList', [SidesToppingsController::class, 'getSidesToppingsList']);
    Route::group(['prefix' => '/sides-toppings'], function () {
        Route::get('/list', [SidesToppingsController::class, 'index']);
        Route::get('/edit/{code}', [SidesToppingsController::class, 'edit']);
        Route::post('/update', [SidesToppingsController::class, 'update']);
        Route::get('/view/{code}', [SidesToppingsController::class, 'view']);
        Route::get('/deleteImage', [SidesToppingsController::class, 'deleteImage']);
        Route::get('/add', [SidesToppingsController::class, 'add']); // ShreyasM - Add Toppings
        Route::post('/store', [SidesToppingsController::class, 'store']); // ShreyasM - Store Toppings
    });

    //sides
    Route::get('getSides', [SidesController::class, 'getSides']);
    Route::get('getSidesList', [SidesController::class, 'getSidesList']);
    Route::group(['prefix' => '/sides'], function () {
        Route::get('/list', [SidesController::class, 'index']);
        Route::get('/edit/{code}', [SidesController::class, 'edit']);
        Route::post('/update', [SidesController::class, 'update']);
        Route::get('/view/{code}', [SidesController::class, 'view']);
        Route::get('/deleteImage', [SidesController::class, 'deleteImage']);
        Route::get('/add', [SidesController::class, 'add']);
        Route::post('/store', [SidesController::class, 'store']);
        Route::get('/delete', [SidesController::class, 'delete']);
    });

    //customer
    Route::get('getCustomerList', [CustomerController::class, 'getCustomerList']);
    Route::group(['prefix' => '/customers'], function () {
        Route::get('/list', [CustomerController::class, 'index']);
        Route::get('/view/{code}', [CustomerController::class, 'view']);
        Route::get('/getCustomer', [CustomerController::class, 'getCustomer']);
        Route::get('/getEmail', [CustomerController::class, 'getEmail']);
        Route::get('/getMobile', [CustomerController::class, 'getMobile']);
    });

    //special offer
    Route::get('getSpecialOffers', [SpecialOfferController::class, 'getSpecialOffers']);
    Route::get('getSpecialoffersList', [SpecialOfferController::class, 'getSpecialOfferList']);
    Route::group(['prefix' => '/specialoffer'], function () {
        Route::get('/list', [SpecialOfferController::class, 'index']);
        Route::get('/view/{code}', [SpecialOfferController::class, 'view']);
        Route::get('/edit/{code}', [SpecialOfferController::class, 'edit']);
        Route::get('/add', [SpecialOfferController::class, 'add']);
        Route::post('/store', [SpecialOfferController::class, 'store']);
        Route::post('/update', [SpecialOfferController::class, 'update']);
        Route::get('/deleteImage', [SpecialOfferController::class, 'deleteImage']);
        Route::get('/delete', [SpecialOfferController::class, 'delete']);
        Route::get('/sides', [SpecialOfferController::class, 'getSpecialOfferByType']);
        Route::get('/size', [SpecialOfferController::class, 'getSize']);
        Route::get('/line/delete', [SpecialOfferController::class, 'deleteSpecialOfferLine']);
        Route::get('/all/delete', [SpecialOfferController::class, 'deleteAllSpecialOfferLine']);
    });

    // Reports
    Route::get('getReportsList', [ReportsController::class, 'getReportsList']);
    Route::get('getReportsListByStoreLocation', [ReportsController::class, 'getReportsListByStoreLocation']);
    Route::group(['prefix' => '/reports'], function () {
        Route::get('/list-all', [ReportsController::class, 'index']);
        Route::get('/list-store-location', [ReportsController::class, 'reportsByStoreLocation']);
        Route::get('/getStoreLocation', [ReportsController::class, 'getStoreLocation']);
        Route::get('/store-summary', [ReportsController::class, 'storeSummary']);
        Route::get('/store-summary/data', [ReportsController::class, 'storeSummaryList']);
    });

    // Reports
    Route::get('getDynamicSliderList', [DynamicSliderController::class, 'getDynamicSliderList']);
    Route::group(['prefix' => '/dynamic-sliders'], function () {
        Route::get('getDynamicSliderTitle', [DynamicSliderController::class, 'getDynamicSliderTitle']);
        Route::get('/list', [DynamicSliderController::class, 'index']);
        Route::get('/view/{code}', [DynamicSliderController::class, 'view']);
        Route::get('/edit/{code}', [DynamicSliderController::class, 'edit']);
        Route::get('/add', [DynamicSliderController::class, 'add']);
        Route::post('/store', [DynamicSliderController::class, 'store']);
        Route::post('/update', [DynamicSliderController::class, 'update']);
        Route::get('/delete/{code}', [DynamicSliderController::class, 'delete']);
        Route::get('/deleteImage', [DynamicSliderController::class, 'deleteImage']);
        Route::get('/deleteLineentries/{code}', [DynamicSliderController::class, 'deleteLineentries']);
    });


    //store location

    Route::get('getStorelocationList', [StoreLocationController::class, 'getStoreLocationList']);
    Route::group(['prefix' => '/storelocation'], function () {
        Route::get('getStoreLocation', [StoreLocationController::class, 'getStoreLocation']);
        Route::get('/list', [StoreLocationController::class, 'index']);
        Route::get('/view/{code}', [StoreLocationController::class, 'view']);
        Route::get('/edit/{code}', [StoreLocationController::class, 'edit']);
        Route::get('/add', [StoreLocationController::class, 'add']);
        Route::post('/store', [StoreLocationController::class, 'store']);
        Route::post('/update', [StoreLocationController::class, 'update']);
        Route::get('/delete', [StoreLocationController::class, 'delete']);
    });

    // Developer - Shreyas Mahamuni
    // Working Date - 22-11-2023
    // Pizza Price
    Route::get('getPizzaPrice', [PizzaPriceController::class, 'getPizzaPrice']);
    Route::group(['prefix' => '/pizzaprice'], function () {
        Route::get('/list', [PizzaPriceController::class, 'index']);
        Route::get('/edit/{id}', [PizzaPriceController::class, 'edit']);
        Route::post('/update', [PizzaPriceController::class, 'update']);
        Route::get('/view/{id}', [PizzaPriceController::class, 'view']);
    });

    //settings
    Route::get('getSettingList', [SettingController::class, 'getSettingList'])->name('admin.getSettingList');
    Route::group(['prefix' => '/setting'], function () {
        Route::get('/list', [SettingController::class, 'index']);
        Route::get('/edit/{code}', [SettingController::class, 'edit']);
        Route::post('/update', [SettingController::class, 'update']);
        Route::get('/view/{code}', [SettingController::class, 'view']);
    });

    //orders
    Route::get('getOrders', [OrdersController::class, 'getOrders']);
    Route::get('getOrdersList', [OrdersController::class, 'getOrdersList']);
    Route::group(['prefix' => '/orders'], function () {
        Route::get('/list', [OrdersController::class, 'index']);
        Route::get('/view/{code}', [OrdersController::class, 'view']);
        Route::get('/updateOrderStatus', [OrdersController::class, 'updateOrderStatus']);
        Route::get('/invoice/{code}', [OrdersController::class, 'getInvoice']);
    });

    Route::get('/deliverable/zipcode', [DeliverZipcodeController::class, 'index']);
    Route::post('/deliverable/zipcode/store', [DeliverZipcodeController::class, 'store']);
    Route::get('/getZipcodeList', [DeliverZipcodeController::class, 'getZipcodeList']);
    Route::get('/zipcode/edit', [DeliverZipcodeController::class, 'edit']);
    Route::get('/zipcode/delete', [DeliverZipcodeController::class, 'delete']);
    Route::get('/zipcode/fetch/get-store-location', [DeliverZipcodeController::class, 'getStoreLocation']);
    Route::get('/zipcode/import', [DeliverZipcodeController::class, 'importZipcodes']);
    Route::post('/zipcode/upload', [DeliverZipcodeController::class, 'uploadZipcodes']);


    //seemashelar@neosao
    //Date:12-11-2024

    //Signature Pizza Category
    Route::get('getCategories', [SignaturePizzaCategoryController::class, 'getCategories']);
    Route::get('getCategoriesList', [SignaturePizzaCategoryController::class, 'getCategoriesList']);
    Route::group(['prefix' => '/signature-pizza-category'], function () {
        Route::get('/list', [SignaturePizzaCategoryController::class, 'index']);
        Route::get('/edit/{code}', [SignaturePizzaCategoryController::class, 'edit']);
        Route::post('/update', [SignaturePizzaCategoryController::class, 'update']);
        Route::get('/view/{code}', [SignaturePizzaCategoryController::class, 'view']);
        Route::get('/delete', [SignaturePizzaCategoryController::class, 'delete']);
        Route::get('/deleteImage', [SignaturePizzaCategoryController::class, 'deleteImage']);
        Route::get('/add', [SignaturePizzaCategoryController::class, 'add']);
        Route::post('/store', [SignaturePizzaCategoryController::class, 'store']);
    });

    //Signature Pizza
    Route::get('getSignaturePizza', [SignaturePizzaController::class, 'getSignaturePizza']);
    Route::get('/getCheese', [SignaturePizzaController::class, 'getCheese']);
    Route::get('/getCrust', [SignaturePizzaController::class, 'getCrust']);
    Route::get('/getCrustType', [SignaturePizzaController::class, 'getCrustType']);
    Route::get('/getSpecialBase', [SignaturePizzaController::class, 'getSpecialBase']);
    Route::get('/getSpices', [SignaturePizzaController::class, 'getSpices']);
    Route::get('/getSauce', [SignaturePizzaController::class, 'getSauce']);
    Route::get('/getCook', [SignaturePizzaController::class, 'getCook']);
    Route::get('getSignaturePizzaList', [SignaturePizzaController::class, 'getSignaturePizzaList']);
    Route::group(['prefix' => '/signature-pizza'], function () {
        Route::get('/list', [SignaturePizzaController::class, 'index']);
        Route::get('/edit/{code}', [SignaturePizzaController::class, 'edit']);
        Route::post('/update', [SignaturePizzaController::class, 'update']);
        Route::get('/view/{code}', [SignaturePizzaController::class, 'view']);
        Route::get('/delete', [SignaturePizzaController::class, 'delete']);
        Route::get('/deleteImage', [SignaturePizzaController::class, 'deleteImage']);
        Route::get('/add', [SignaturePizzaController::class, 'add']);
        Route::post('/store', [SignaturePizzaController::class, 'store']);
    });


    //Pizzas Category
    Route::get('getPizzasCategories', [PizzasCategoryController::class, 'getCategories']);
    Route::get('getPizzasCategoriesList', [PizzasCategoryController::class, 'getCategoriesList']);
    Route::group(['prefix' => '/pizzas-category'], function () {
        Route::get('/list', [PizzasCategoryController::class, 'index']);
        Route::get('/edit/{code}', [PizzasCategoryController::class, 'edit']);
        Route::post('/update', [PizzasCategoryController::class, 'update']);
        Route::get('/view/{code}', [PizzasCategoryController::class, 'view']);
        Route::get('/delete', [PizzasCategoryController::class, 'delete']);
        Route::get('/deleteImage', [PizzasCategoryController::class, 'deleteImage']);
        Route::get('/add', [PizzasCategoryController::class, 'add']);
        Route::post('/store', [PizzasCategoryController::class, 'store']);
    });


    //Pizzas
    Route::get('getPizzas', [PizzasController::class, 'getPizzas']);
    Route::get('/getPizzasCheese', [PizzasController::class, 'getCheese']);
    Route::get('/getPizzasCrust', [PizzasController::class, 'getCrust']);
    Route::get('/getPizzasCrustType', [PizzasController::class, 'getCrustType']);
    Route::get('/getPizzasSpecialBase', [PizzasController::class, 'getSpecialBase']);
    Route::get('/getPizzasSpices', [PizzasController::class, 'getSpices']);
    Route::get('/getPizzasSauce', [PizzasController::class, 'getSauce']);
    Route::get('/getPizzasCook', [PizzasController::class, 'getCook']);
    Route::get('getPizzasList', [PizzasController::class, 'getPizzasList']);
    Route::group(['prefix' => '/pizzas'], function () {
        Route::get('/list', [PizzasController::class, 'index']);
        Route::get('/edit/{code}', [PizzasController::class, 'edit']);
        Route::post('/update', [PizzasController::class, 'update']);
        Route::get('/view/{code}', [PizzasController::class, 'view']);
        Route::get('/delete', [PizzasController::class, 'delete']);
        Route::get('/deleteImage', [PizzasController::class, 'deleteImage']);
        Route::get('/add', [PizzasController::class, 'add']);
        Route::post('/store', [PizzasController::class, 'store']);
    });
    // Picture
    Route::get('getPicture', [PictureController::class, 'getPicture']);
    Route::get('getPictureList', [App\Http\Controllers\PictureController::class, 'getPictureList']);
    Route::group(['prefix' => 'pictures'], function () {
        Route::get('/list', [PictureController::class, 'index']);
        Route::get('/edit/{code}', [App\Http\Controllers\PictureController::class, 'edit']);
        Route::post('/update', [App\Http\Controllers\PictureController::class, 'update']);
        Route::get('/view/{code}', [App\Http\Controllers\PictureController::class, 'view']);
        Route::post('/deleteImage', [App\Http\Controllers\PictureController::class, 'deleteImage']);
        Route::get('/add', [App\Http\Controllers\PictureController::class, 'add']);
        Route::post('/store', [App\Http\Controllers\PictureController::class, 'store']);
        Route::get('/delete', [App\Http\Controllers\PictureController::class, 'delete']);

        Route::get('/get-products', [PictureController::class, 'get_products']);

    });


    Route::get('getBackgroundImage', [BackgroundImageController::class, 'getBackgroundImageList']);
    Route::group(['prefix' => '/background-image'], function () {

        Route::get('/list', [BackgroundImageController::class, 'index']);
        Route::get('/view/{id}', [BackgroundImageController::class, 'view']);
        Route::get('/edit/{id}', [BackgroundImageController::class, 'edit']);
        Route::post('/update', [BackgroundImageController::class, 'update']);
        Route::get('/delete/{id}', [BackgroundImageController::class, 'delete']);
        Route::post('/delete-image', [BackgroundImageController::class, 'deleteImage']);
    });


    // Section Routes
    Route::get('getSectionList', [SectionController::class, 'getSectionList']);

    Route::group(['prefix' => '/sections'], function () {
        Route::get('/list', [SectionController::class, 'index']);
        Route::get('/view/{code}', [SectionController::class, 'view']);
        Route::get('/edit/{code}', [SectionController::class, 'edit']);
        Route::get('/add', [SectionController::class, 'add']);
        Route::post('/store', [SectionController::class, 'store']);
        Route::post('/update', [SectionController::class, 'update']);
        Route::get('/delete/{code}', [SectionController::class, 'delete']);
        Route::get('/deleteImage', [SectionController::class, 'deleteImage']);
        Route::get('/deleteLineentries/{code}', [SectionController::class, 'deleteLineentries']);
    });

    // SMS Template Routes
    Route::get('getSmsTemplateList', [SmsTemplateController::class, 'getSmsTemplateList']);

    Route::group(['prefix' => '/sms-templates'], function () {
        Route::get('/list', [SmsTemplateController::class, 'index']);
        Route::get('/view/{id}', [SmsTemplateController::class, 'view']);
        Route::get('/edit/{id}', [SmsTemplateController::class, 'edit']);
        Route::get('/add', [SmsTemplateController::class, 'add']);
        Route::post('/store', [SmsTemplateController::class, 'store']);
        Route::post('/update', [SmsTemplateController::class, 'update']);
        Route::get('/delete/{id}', [SmsTemplateController::class, 'delete']);
    });



    //sms sending

    // SMS Send Page
    Route::get('/sms', [SMSController::class, 'index'])->name('sms.index');


    // SMS Routes
    Route::prefix('customers')->middleware('auth:admin')->group(function () {


        // Get Templates (AJAX with limit/offset/exclude)
        Route::get('/templates/get', [SMSController::class, 'getTemplates']);

        // Get Template Preview (AJAX)
        Route::get('/templates/preview/{id}', [SMSController::class, 'getTemplatePreview']);
        Route::post('/validate-sms', [SMSController::class, 'validateSMS']);
        // Send SMS
        Route::post('/send-sms', [SMSController::class, 'sendSMS']);
        Route::get('/sms-logs/list', [SMSController::class, 'getSmsLogs']);
        // Twilio Settings
        Route::post('/twillio-settings-save', [SMSController::class, 'saveTwilioSettings'])->name('twilio.settings.save');
        Route::get('/twillio-settings-get', [SMSController::class, 'getTwilioSettings'])->name('twilio.settings.get');
    });

    // Site CSM Settings

    Route::prefix('settings')->group(function () {
        Route::get('/about', [SettingsController::class, 'about'])->name('settings.about');
        Route::post('/about', [SettingsController::class, 'updateAbout'])->name('settings.about.update');

        Route::get('/contact', [SettingsController::class, 'contact'])->name('settings.contact');
        Route::post('/contact', [SettingsController::class, 'updateContact'])->name('settings.contact.update');

        //terms and conditions
        Route::get('/terms', [SettingsController::class, 'terms'])->name('settings.terms');
        Route::post('/terms', [SettingsController::class, 'updateTerms'])->name('settings.terms.update');

        // Privacy Policy
        Route::get('/privacyPolicy', [SettingsController::class, 'privacyPolicy'])->name('settings.privacyPolicy');
        Route::post('/privacyPolicy', [SettingsController::class, 'updatePrivacyPolicyContent'])->name('settings.privacyPolicy.update');

        Route::get('/refund', [SettingsController::class, 'refund'])->name('settings.refund');
        Route::post('/refund-update', [SettingsController::class, 'updateRefundContent'])->name('settings.refundPolicy.update');


        // Email Settings
        Route::get('/email', [SettingsController::class, 'email'])->name('settings.email');
        Route::post('/email/store', [SettingsController::class, 'storeEmail'])->name('settings.email.store');
        Route::post('/email/update/{id?}', [SettingsController::class, 'updateEmail'])->name('settings.email.update');

        // Logo routes
        Route::get('/logo/update', [SettingsController::class, 'logoUpdate'])->name('settings.logo.update');
        Route::post('/logo/update', [SettingsController::class, 'updateLogo'])->name('settings.logo.save');
        Route::post('/logo/delete', [SettingsController::class, 'logoDelete'])->name('settings.delete.logo');

        // Favicon routes
        Route::get('/favicon/update', [SettingsController::class, 'logoUpdate'])->name('settings.favicon.update');
        Route::post('/favicon/update', [SettingsController::class, 'updateFavicon'])->name('settings.favicon.save');
        Route::post('/favicon/delete', [SettingsController::class, 'faviconDelete'])->name('settings.delete.favicon');

        // Barcode routes
        Route::get('/barcode/update', [SettingsController::class, 'logoUpdate'])->name('settings.barcode.update');
        Route::post('/barcode/update', [SettingsController::class, 'updateBarcode'])->name('settings.barcode.save');
        Route::post('/barcode/delete', [SettingsController::class, 'barcodeDelete'])->name('settings.delete.barcode');

        Route::get('/logoUpdate', [SettingsController::class, 'logoUpdate'])->name('settings.logoUpdate');
        Route::post('/logoUpdate', [SettingsController::class, 'updateLogoSettings'])->name('settings.logoSave');

        // image delete
        Route::post('/delete-image', [SettingsController::class, 'deleteImage'])->name('settings.deleteImage');

        //Social Media
        Route::get('/social-media', [SettingsController::class, 'socialMedia'])->name('settings.social');
        Route::post('/social-media/update', [SettingsController::class, 'updateSocialMedia'])->name('settings.social.update');

        Route::post('/site-details/update', [SettingsController::class, 'updateSiteDetails'])->name('settings.sites.update');

    });


     //Payment Settings
        Route::get('/payment-settings',[PaymentSettingsController::class, 'index']);
        Route::post('/payment-gateway/store', [PaymentSettingsController::class, 'store'])
    ->name('payment-gateway.store');

});
