<?php

/*-------------------------------Admin Routes------------------------------------*/
/////////////////////////////Routes for Admin/////////////////////////////////////////////////////////////
/////////////////////////////View Routes////////////////////////////////////////////////////////////////
Route::get('/', 'Admin\AdminLoginController@showLoginForm')->name('admin');
Route::get('/', 'Admin\AdminLoginController@showLoginForm')->name('admin.default');
Route::get('/login','Admin\AdminLoginController@showLoginForm')->name('admin.login');
Route::post('/login','Admin\AdminLoginController@login')->name('admin.login');
Route::post('/logout','Admin\AdminLoginController@logout')->name('admin.logout');
///////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Password Reset Routes...
Route::get('password/reset', 'Admin\ForgotPasswordController@showLinkRequestForm')->name('password.reset');
Route::post('password/email', 'Admin\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
Route::get('password/reset', 'Admin\ResetPasswordController@showResetForm')->name('password.reset.token');
Route::post('password/update', 'Admin\ResetPasswordController@reset')->name('password.update');
Route::get('password/redirector', 'Admin\ResetPasswordController@redirector')->name('password.redirector');
///////////////////////////////////////////////////////////////////////////////////////////////////////////////
Route::get('/dashboard', 'Admin\AdminController@index')->name('admin.dashboard');

Route::resource('profile', 'Admin\ProfileController')->only([
    'index', 'update'
]);
Route::resource('section', 'Admin\SectionController')->except([
    'edit','update'
]);

Route::resource('surahs', 'Admin\SurahController');
Route::resource('section', 'Admin\SectionController');
Route::resource('reciter', 'Admin\QariController');
Route::resource('language', 'Admin\LanguageController');

Route::resource('reciter_language', 'Admin\QariLanguageController');
Route::resource('section_language', 'Admin\SectionLanguageController');
Route::resource('translated_language', 'Admin\TranslatedLanguageController');
Route::resource('setting', 'Admin\SettingController');

Route::resource('recitation', 'Admin\RecitationController');
Route::resource('pending-recitation', 'Admin\PendingRecitationController');

Route::resource('bulk_insertion', 'Admin\AllRecitationController');
//Route::get('/vbv/activate/{id}', 'VBVController@activate')->name('vbv.activate');
//Route::get('/vbv/deactivate/{id}', 'VBVController@deactivate')->name('vbv.deactivate');
/*---------------------------------------------------------------------------------------------*/
