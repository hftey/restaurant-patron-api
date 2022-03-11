<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RestaurantController;

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

Route::get('/restaurant', 'App\Http\Controllers\RestaurantController@index')->name('restaurant');
Route::get('/restaurant/get_qi_ultimate/{place_id}', 'App\Http\Controllers\RestaurantController@get_qi_ultimate')->name('restaurant');
Route::get('/restaurant/get/{place_id}', 'App\Http\Controllers\RestaurantController@get')->name('restaurant');
Route::post('/restaurant/exist', 'App\Http\Controllers\RestaurantController@exist')->name('restaurant');
Route::post('/restaurant/create', 'App\Http\Controllers\RestaurantController@create')->name('restaurant');
Route::post('/restaurant/update', 'App\Http\Controllers\RestaurantController@update')->name('restaurant');
Route::post('/restaurant/update_experience', 'App\Http\Controllers\RestaurantController@update_experience')->name('experience');
Route::post('/restaurant/done_experience', 'App\Http\Controllers\RestaurantController@done_experience')->name('experience');
Route::post('/restaurant/get_restaurant_bound', 'App\Http\Controllers\RestaurantController@get_restaurant_bound')->name('restaurant');


Route::post('/restaurant/update_experience_photo', 'App\Http\Controllers\RestaurantController@update_experience_photo')->name('experience');
Route::get('/restaurant/get_experience/{place_id}/{created_by}/{created_at}', 'App\Http\Controllers\RestaurantController@get_experience')->name('experience');
Route::get('/restaurant/get_experience_by_id/{place_id}/{experience_id}', 'App\Http\Controllers\RestaurantController@get_experience')->name('experience');
Route::get('/restaurant/get_experience_user/{user_id}', 'App\Http\Controllers\RestaurantController@get_experience_user')->name('experience');

Route::get('/restaurant/get_user_top_qi/{user_id}/{type}', 'App\Http\Controllers\RestaurantController@get_user_top_qi')->name('experience');


Route::get('/restaurant/get_experience_restaurant/{place_id}/{user_id}', 'App\Http\Controllers\RestaurantController@get_experience_restaurant')->name('experience');
Route::get('/restaurant/get_experience_photo/{experiences_id}', 'App\Http\Controllers\RestaurantController@get_experience_photo')->name('experience');
Route::get('/restaurant/display_experience_photo/{experiences_photos_id}/{small}', 'App\Http\Controllers\RestaurantController@display_experience_photo')->name('experience');
Route::middleware('cors')->delete('/restaurant/delete_experience_photo/{experiences_photos_id}', 'App\Http\Controllers\RestaurantController@delete_experience_photo')->name('experience');


Route::post('/register', 'App\Http\Controllers\UserController@register')->name('user');
Route::post('/login', 'App\Http\Controllers\UserController@login')->name('user');


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
