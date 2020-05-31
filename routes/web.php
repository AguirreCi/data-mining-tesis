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

Route::get('/', 'dataset@index');

Route::get('tags', 'dataset@obtener_tags');

Route::get('whois', 'dataset@obtener_whois');

Route::get('titulos/{url}', 'dataset@get_titulo');

Route::get('html/{url}', 'dataset@html_doc');

Route::get('subdom', 'dataset@subdominios');

Route::get('largos', 'dataset@analizar_longitud');

Route::get('guiones', 'dataset@guiones');

Route::get('rank','dataset@ranking');

Route::get('ssl','dataset@ssl');

Route::get('dias','dataset@dias_desde_reg');




//MINERIA

Route::get('gen_dataset','mineriaController@armado_datasets');

Route::get('gen_result_naive','mineriaController@naivebayes_proc');

Route::get('gen_result_dt','mineriaController@tree_proc');

Route::get('gen_result_svm','mineriaController@svm_proc');

Route::get('gen_result_k','mineriaController@k_proc');

Route::get('accuracy_naive','mineriaController@accuracy_naive')->name('accuracy_naive');

Route::get('accuracy_dt','mineriaController@accuracy_tree')->name('accuracy_dt');

Route::get('accuracy_svm','mineriaController@accuracy_svm')->name('accuracy_svm');

Route::get('accuracy_kn','mineriaController@accuracy_kn')->name('accuracy_kn');

Route::get('obtener_arbol','mineriaController@obtener_arbol');






//API


Route::group(
        [           
            'namespace' => 'api',
            'prefix' => 'api',
        ], function(){

            Route::get('analizar_url/{id}', ['middleware'=>'cors', 'uses'=>'analisisController@analizar_url']);
            Route::get('obtener_datos/{url}', ['middleware'=>'cors', 'uses'=>'urlController@obtener_datos']);
            Route::get('analisis_virusTotal/{id}',['middleware'=>'cors', 'uses'=>'analisisController@analisis_virusTotal']);
            

    });