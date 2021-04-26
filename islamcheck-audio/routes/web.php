<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

include 'admin.php';

Route::get('/', function () {
    return redirect('admin');
});

Route::get('/cacheclear/', function () {


    $exitCode = Artisan::call('cache:clear');
    $exitCode = Artisan::call('view:clear');
    $exitCode = Artisan::call('config:clear');
    $exitCode = Artisan::call('config:cache');
    //$exitCode=Artisan::call('Composer dump-autoload');
    return '<h2>Cache Cleared...</h2>';
});

Route::get('api', 'API\APIController@apiList');
Route::get('api/languages/{language_code?}', 'API\APIController@languages');
Route::get('api/surahs/{language_code?}', 'API\APIController@surahs');
Route::get('api/sections/{language_code?}', 'API\APIController@sections');
Route::get('api/qaris/{section_id}/{language_code?}', 'API\APIController@qaris');
Route::get('api/allqaris/{language_code?}', 'API\APIController@allQaris');

Route::get('api/surahs_list/{qari_id}/{language_code?}', 'API\APIController@surahsList');

Route::get('api/add-surahs', 'import\ImportController@insertSurahs');
Route::get('api/add-section', 'import\ImportController@insertSections');
Route::get('api/add-qaris', 'import\ImportController@insertQaris');
Route::get('api/add-recitations/{start}/{end}', 'import\ImportController@insertRecitation');
Route::get('api/add-qari-data/{path}/{id}', 'import\ImportController@insertRecitationQari');
Route::get('api/download_files', 'import\ImportController@downloadFiles');
Route::get('api/insert-only-data', 'import\ImportController@insertDataOnly');
//Route::get('api/scheduled-cron', 'import\ImportController@scheduledCron');
Route::get('newfolder', 'import\ImportController@addNewFolder');
Route::get('updatefiles', 'import\ImportController@updateFiles');
Route::get('updatefilesqari', 'import\ImportController@updateFilesQari');


