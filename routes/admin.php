<?php


#######start-authentication#######
Route::group(['prefix' => 'admin', 'namespace' => 'Backend', 'as' => 'admin.'], function () {

    Route::group(['namespace' => 'auth'], function () {

        Route::get('/login', 'LoginController@showLoginForm')->name('show_login_form');
        Route::post('login', 'LoginController@login')->name('login');
        Route::post('logout', 'LoginController@logout')->name('logout');

    });
###########end-authentication########

    Route::group(['middleware' => 'auth', 'roles'], function () {

        Route::any('/notifications/get', 'NotificationsController@getNotifications');
        Route::any('/notifications/read', 'NotificationsController@markAsRead');

        Route::get('/', 'AdminController@index')->name('index_route');
        Route::get('/index', 'AdminController@index')->name('index');

        Route::post('/posts/removeImage/{media_id}', 'PostsController@removeImage')->name('posts.media.destroy');
        Route::resource('/posts', 'PostsController');

        Route::post('/pages/removeImage/{media_id}', 'pagesController@removeImage')->name('pages.media.destroy');
        Route::resource('/pages', 'PagesController');

        Route::resource('/post_comments', 'PostCommentsController');
        Route::resource('/post_categories', 'PostCategoriesController');
        Route::resource('/contact_us', 'ContactUsController');

        Route::post('/users/removeImage', 'UsersController@removeImage')->name('users.remove_image');
        Route::resource('/users', 'UsersController');

    });
});




