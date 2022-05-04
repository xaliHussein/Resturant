<?php


use App\Http\Controllers\UsersController;
use App\Http\Controllers\FoodController;
use App\Http\Controllers\OrdersController;
use App\Http\Controllers\ResturantController;
use App\Http\Controllers\SectionsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
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
 route::post('add_user',[UsersController::class,'addUser']);
 route::post('login',[UsersController::class,'login']);

Route::middleware(['auth:api'])->group(function () {
    route::post('add_resturant',[ResturantController::class,'addResturant']);
    route::get('get_resturant',[ResturantController::class,'getResturants']);

    route::post('add_section',[SectionsController::class,'addSection']);
    route::get('get_sections',[SectionsController::class,'getSections']);
    route::post('edit_sections',[SectionsController::class,'editSection']);

    route::post('add_food',[FoodController::class,'addFood']);
    route::get('get_food',[FoodController::class,'getFoods']);
    route::post('update_food',[FoodController::class,'updateFood']);

    route::post('add_order',[OrdersController::class,'addOrder']);
    route::get('get_orders',[OrdersController::class,'getOrders']);

    Route::middleware("admin")->group(function () {
        route::get('get_inactive_resturant',[ResturantController::class,'getInactiveRestaurants']);
        route::put('restaurant_status_changed',[ResturantController::class,'restaurantStatusChanged']);
        route::get('count',[UsersController::class,'count']);
    });
});
