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
ini_set('max_execution_time', 0);
ini_set('memory_limit', -1);

Route::get('/', function () {
    return view('auth.login');
});

Route::get('/quienes', function () {
    return view('welcome');
});

Route::get('/help', function () {
    return view('help');
});


Route::get('/contactenos', function () {
    return view('welcome');
});

Route::get('/resoluciones', function () {
    return view('welcome');
});

Auth::routes();

Route::group(['middleware' => 'auth'], function(){
	Route::get('/home', 'HomeController@index');
	
	Route::get('/', function () {
    	return view('home');
	});
		
	Route::resource('/registro', 'usersController');
	Route::post('/editar', 'usersController@edit');
	Route::post('/desactivar_usuario', 'usersController@desactivar_usuario');
	Route::get('/upload_files', 'filesController@upload');
	Route::get('/generar_reportes', 'reportsController@upload');
	Route::name('uploading')->post('/upload_files','filesController@store');
	Route::name('status_files')->get('/status_files','filesController@show');
	Route::name('indexing')->get('/upload_files','filesController@index');
	Route::name('getMunicipios')->get('/departamento/getmunicipio', 'DepartamentoController@getMunicipios');
	//Route::post('/registrar', 'usersController@create');
});

Route::group(['middleware' => 'admin'], function(){
	//Route::name('getMunicipios')->get('/departamento/getmunicipio', 'DepartamentoController@getMunicipios');
	//Route::post('/registrar', 'usersController@create');
});



//nombreclase::Funcion-Metodohttp ( 'nombreRuta','nombrecontrolador@funcion')

//otra forma es con el metodo resource //name' => 'required | max: 255 |',
Route::resource('/formulario', 'formulario@upload');
//Route::resource('url-defecto', 'nombre controlador')