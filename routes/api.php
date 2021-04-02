<?php

use Illuminate\Support\Facades\Route;

Route::get('/chart/comments_chart', 'Backend\Api\ApiController@comments_chart');
Route::get('/chart/users_chart', 'Backend\Api\ApiController@users_chart');

############### API ################
Route::group(['prefix' => 'v1', 'namespace' => 'Api\General'], function () {

    Route::get('/all_posts', 'GeneralController@get_posts');
    Route::get('/post/{slug}', 'GeneralController@show_post');
    Route::post('/post/{slug}', 'GeneralController@store_comment');
    Route::get('/search', 'GeneralController@search');
    Route::get('/category/{category_slug}', 'GeneralController@category');
    Route::get('/archive/{date}', 'GeneralController@archive');
    Route::get('/author/{username}', 'GeneralController@author');
    Route::post('/contact-us', 'GeneralController@do_contact');

});

Route::group(['prefix' => 'v1', 'middleware' => ['auth:api'], 'namespace' => 'Api\Users'], function () {

//    Route::any('/notifications/get', 'UsersController@getNotifications');
//    Route::any('/notifications/read', 'UsersController@markAsRead');

    Route::get('/user_information', 'UsersController@user_information');
    Route::patch('/edit_user_information', 'UsersController@update_user_information');
    Route::patch('/edit_user_password', 'UsersController@update_user_password');

    Route::get('/my_posts', 'UsersController@my_posts');
    Route::get('/my_posts/create', 'UsersController@create_post');
    Route::post('/my_posts/create', 'UsersController@store_post');
    Route::get('/my_posts/{post_id}/edit', 'UsersController@edit_post');
    Route::patch('/my_posts/{post}/edit', 'UsersController@update_post');
    Route::delete('/my_posts/{post_id}', 'UsersController@delete_post');
    Route::post('/delete_post_media/{media_id}', 'UsersController@delete_post_media');

    Route::get('/all_comments', 'UsersController@all_comments');
    Route::get('/comments/{id}/edit', 'UsersController@edit_comment');
    Route::patch('/comments/{comment_id}/edit', 'UsersController@update_comment');
    Route::delete('/comments/{id}', 'UsersController@delete_comment');

    Route::post('/logout', 'UsersController@logout');

});

Route::group(['prefix' => 'v1', 'namespace' => 'Api'], function () {

    Route::post('/register', 'AuthController@register');
    Route::post('/login', 'AuthController@login');

});
