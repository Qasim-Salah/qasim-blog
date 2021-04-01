<?php


#######start-authentication#######
Route::group(['prefix' => 'admin', 'namespace' => 'Backend', 'as' => 'admin.'], function () {

    Route::group(['namespace' => 'Auth', ], function () {

        Route::get('/login', 'LoginController@showLoginForm')->name('show_login_form');
        Route::post('login', 'LoginController@login')->name('login');

    });
###########end-authentication########

    Route::group(['middleware' => 'auth:admin'], function () {

        Route::post('logout', 'Auth\LoginController@logout')->name('logout');

        Route::get('/index', 'AdminController@index')->name('index');

        Route::any('/notifications/get', 'NotificationsController@getNotifications');
        Route::any('/notifications/read', 'NotificationsController@markAsRead');

        Route::resource('/posts', 'PostsController')->middleware('can:posts');

        Route::resource('/pages', 'PagesController')->middleware('can:pages');

        Route::resource('/post_comments', 'PostCommentsController')->middleware('can:post_comments');
        Route::resource('/post_categories', 'PostCategoriesController')->middleware('can:post_categories');
        Route::resource('/contact_us', 'ContactUsController')->middleware('can:contact_us');

        Route::resource('/users', 'UsersController')->middleware('can:users');

        Route::group(['prefix' => 'roles','middleware' => 'can:roles'], function () {
            Route::get('/', 'RolesController@index')->name('roles.index');
            Route::get('create', 'RolesController@create')->name('roles.create');
            Route::post('store', 'RolesController@store')->name('roles.store');
            Route::get('/edit/{id}', 'RolesController@edit')->name('roles.edit');
            Route::patch('update/{id}', 'RolesController@update')->name('roles.update');
        });
        Route::group(['prefix' => 'admin','middleware' => 'can:admins'], function () {
            Route::get('/', 'AdminsController@index')->name('admin.index');
            Route::get('/create', 'AdminsController@create')->name('admin.create');
            Route::post('/store', 'AdminsController@store')->name('admin.store');
        });
    });
});




