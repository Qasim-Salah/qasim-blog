<?php



#########start-authentication########ي
Route::group(['prefix' => 'Frontend', 'namespace' => 'Frontend\Auth'], function () {

    Route::get('/login', 'LoginController@showLoginForm')->name('Frontend.show_login_form');

    Route::post('login', 'LoginController@login')->name('Frontend.login');
    Route::post('logout', 'LoginController@logout')->name('Frontend.logout');
    Route::get('register', 'RegisterController@showRegistrationForm')->name('Frontend.show_register_form');
    Route::post('register', 'RegisterController@register')->name('Frontend.register');
    Route::get('password/reset', 'ForgotPasswordController@showLinkRequestForm')->name('password.request');
    Route::post('password/email', 'ForgotPasswordController@sendResetLinkEmail')->name('password.email');
    Route::get('password/reset/{token}', 'ResetPasswordController@showResetForm')->name('password.reset');
    Route::post('password/reset', 'ResetPasswordController@reset')->name('password.update');
    Route::get('email/verify', 'VerificationController@show')->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', 'VerificationController@verify')->name('verification.verify');
    Route::post('email/resend', 'VerificationController@resend')->name('verification.resend');
});

###########end-authentication#########

##############start-view_dash_user########
Route::group(['prefix' => 'user', 'namespace' => 'Frontend', 'middleware' => ['verified', 'auth'], 'as' => 'users.'], function () {

    Route::post('logout', 'Auth\LoginController@logout')->name('Frontend.logout');


    Route::get('/dashboard', 'UsersController@index')->name('dashboard');

    Route::any('/notifications/get', 'NotificationsController@getNotifications');
    Route::any('/notifications/read', 'NotificationsController@markAsRead');

    Route::get('/edit-info', 'UsersController@edit_info')->name('edit_info');
    Route::post('/edit-info', 'UsersController@update_info')->name('update_info');
    Route::post('/edit-password', 'UsersController@update_password')->name('update_password');

    Route::get('/create-post', 'UsersController@create_post')->name('post.create');
    Route::post('/create-post', 'UsersController@store_post')->name('post.store');
    Route::get('/edit-post/{post_id}', 'UsersController@edit_post')->name('post.edit');
    Route::put('/edit-post/{post_id}', 'UsersController@update_post')->name('post.update');
    Route::delete('/delete-post/{post_id}', 'UsersController@destroy_post')->name('post.destroy');

    Route::get('/comments', 'UsersController@show_comments')->name('comments');
    Route::get('/edit-comment/{comment_id}', 'UsersController@edit_comment')->name('comment.edit');
    Route::put('/edit-comment/{comment_id}', 'UsersController@update_comment')->name('comment.update');
    Route::delete('/delete-comment/{comment_id}', 'UsersController@destroy_comment')->name('comment.destroy');
});

#########end-view_dash_user######

#############start-view#############
Route::group(['namespace' => 'Frontend', 'as' => 'Frontend.'], function () {

    Route::get('/', 'IndexController@index')->name('index');
    Route::get('/contact-us', 'IndexController@contact')->name('contact');
    Route::post('/contact-us', 'IndexController@do_contact')->name('do_contact');
    Route::get('/category/{category_slug}', 'IndexController@category')->name('category.posts');
    Route::get('/archive/{date}', 'IndexController@archive')->name('archive.posts');
    Route::get('/author/{username}', 'IndexController@author')->name('author.posts');
    Route::get('/search', 'IndexController@search')->name('search');
    Route::get('/{post_slug}', 'IndexController@post_show')->name('posts.show');
    Route::post('/{slug}', 'IndexController@store_comment')->name('posts.add_comment');
});

#########end-view#########
