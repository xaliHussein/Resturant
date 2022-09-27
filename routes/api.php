<?php


use App\Http\Controllers\UsersController;
use App\Http\Controllers\FoodController;
use App\Http\Controllers\OrdersController;
use App\Http\Controllers\ResturantController;
use App\Http\Controllers\SectionsController;
use App\Http\Controllers\NotificationsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Broadcast;
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


Broadcast::routes(['middleware' => ['auth:api']]);
 route::post('add_user',[UsersController::class,'addUser']);
 route::post('login',[UsersController::class,'login']);
 route::post('get_number_phone',[UsersController::class,'getNumberPhone']);
 route::post('verify_authentication',[UsersController::class,'verifyAuthentication']);
 route::post('send_code_again',[UsersController::class,'sendCodeAgain']);


Route::middleware(['auth:api'])->group(function () {
    route::get('get_favorites_list',[UsersController::class,'getFavoriteList']);
    route::put('user_name_change',[UsersController::class,'userNameChange']);
    route::put('password_change',[UsersController::class,'passwordChange']);
    route::put('number_phone_change',[UsersController::class,'numberPhoneChange']);

    route::post('add_resturant',[ResturantController::class,'addResturant']);
    route::get('get_resturant',[ResturantController::class,'getResturants']);

    route::get('client_management_resturant',[ResturantController::class,'clientManagementResturant']);
    route::get('client_management_sections',[SectionsController::class,'clientManagementSections']);
    route::get('client_management_foods',[FoodController::class,'clientManagementFoods']);
    route::delete('client_management_delete_foods',[FoodController::class,'clientManagementDeleteFoods']);
    route::put('client_management_resturant_status',[ResturantController::class,'clientManagementResturantStatus']);
    route::put('client_management_edit_sections',[SectionsController::class,'clientManagemenEditSection']);
    route::put('client_management_edit_restaurant',[ResturantController::class,'clientManagemenEditRestaurant']);

    route::post('add_section',[SectionsController::class,'addSection']);
    route::post('get_sections',[SectionsController::class,'getSections']);
    route::post('get_info_restaurant',[SectionsController::class,'getInfoRestaurant']);
    route::post('add_favorite',[SectionsController::class,'addFavorite']);
    route::post('delete_favorite',[SectionsController::class,'deleteFavorite']);
    route::post('get_favorite',[SectionsController::class,'getFavorite']);
    route::delete('delete_sections',[SectionsController::class,'deleteSections']);

    route::post('add_food',[FoodController::class,'addFood']);
    route::post('get_foods',[FoodController::class,'getFoods']);
    route::post('update_food',[FoodController::class,'updateFood']);
    route::post('get_custom_foods',[FoodController::class,'getCustomFoods']);

    route::post('add_order',[OrdersController::class,'addOrder']);
    route::get('get_orders',[OrdersController::class,'getOrders']);
    route::get('get_orders_resturant',[OrdersController::class,'getOrderResturant']);
    route::post('accept_order',[OrdersController::class,'acceptOrder']);
    route::post('reject_order',[OrdersController::class,'rejectOrder']);

    route::get('get_notifications',[NotificationsController::class,'getNotifications']);
    route::post('seen_notification',[NotificationsController::class,'seenNotification']);

    Route::middleware("admin")->group(function () {
        route::get('get_inactive_resturant',[ResturantController::class,'getInactiveRestaurants']);
        route::get('get_banned_restaurant',[ResturantController::class,'getBannedRestaurants']);
        route::put('restaurant_status_activate',[ResturantController::class,'restaurantStatusActivate']);
        route::put('restaurant_status_shutdown',[ResturantController::class,'restaurantStatusShutdown']);
        route::put('restaurant_cancel_ban',[ResturantController::class,'restaurantCancelBan']);
        route::get('statisticss',[UsersController::class,'statisticss']);
    });
});
