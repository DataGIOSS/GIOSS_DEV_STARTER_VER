<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Log;

use App\User;

class usersController extends Controller
{

    /*
    *namasepade para enivar correo
    *
    */

    use SendsPasswordResetEmails;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = DB::table('users')->get();

        return view('auth.register')->with('users', $users);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $data)
    {
        
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {

            $validator = Validator::make(
                $request->all(), 
                [
                    'name' => 'required | max: 255 |',
                    'lastname' => 'required | max: 255',
                    'email' => 'required | email | max:255 | unique:users, email',
                    'password' => 'required | min:6 | alpha_dash',
                    'tipo_usuario' => 'required | integer | between:1, 2 | exists:roles, id'
                ]
            );

            $validator->setAttributeNames([
                'name'=>'Nombres',
                'lastname'=>'Apellidos',
                'email' => "Email",
                'password' => 'Contraseña'
            ]);

            $validator->validate();

            DB::beginTransaction();
            //se crea al usuario
            $newUser = new User();

            $newUser->name = $request->name;
            $newUser->email = $request->email;
            $newUser->password = Hash::make(''.$request->password);
            $newUser->roleid = $request->tipo_usuario;
            $newUser->lastname = $request->lastname;

            $saveUser = $newUser->save();
            if(!$saveUser) throw new \Exception("Error al crear el Usuario");

            DB::commit();

            return back()->with('success', 'El Usuario fue creado con éxito.');

        } catch (\Exception $e) {
            Log::error("Error en el controlador: ".$e->getMessage());
            DB::rollBack();
            return back()->with('error', $e->getMessage())->withErrors($validator)->withInput();
        }

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request)
    {
        //
        $url = $request->edit_url;

        try {

            if ($request->edit_password == "" || is_null($request->edit_password)) {
                
                if ($request->edit_email == "" || is_null($request->edit_email)) {

                    $validator = Validator::make(
                        $request->all(), 
                        [
                            'edit_name' => 'required | max: 255 |',
                            'edit_lastname' => 'required | max: 255',
                            'edit_tipo_usuario' => 'required | integer | between:1,2 | exists:roles,id'
                        ]
                    );

                    $validator->setAttributeNames([
                        'edit_name'=>'Nombres',
                        'edit_lastname'=>'Apellidos'
                    ]);

                } else {
                    $validator = Validator::make(
                        $request->all(),
                        [
                            'edit_name' => 'required | max: 255 |',
                            'edit_lastname' => 'required | max: 255',
                            'edit_email' => 'required | email | max:255 | unique:users, email',
                            'edit_tipo_usuario' => 'required | integer | between:1,2 | exists:roles,id'
                        ]
                    );

                    $validator->setAttributeNames([
                        'edit_name'=>'Nombres',
                        'edit_lastname'=>'Apellidos',
                        'edit_email' => "Email"
                    ]);
                }

            } else {
                
                if ($request->edit_email == "" || is_null($request->edit_email)) {

                    $validator = Validator::make(
                        $request->all(), 
                        [
                            'edit_name' => 'required | max: 255 |',
                            'edit_lastname' => 'required | max: 255',
                            'edit_password' => 'required | min:6 | alpha_dash',
                            'edit_tipo_usuario' => 'required | integer | between:1,2 | exists:roles,id'
                        ]
                    );

                    $validator->setAttributeNames([
                        'edit_name'=>'Nombres',
                        'edit_lastname'=>'Apellidos',
                        'edit_password' => 'Contraseña'
                    ]);

                } else {

                    $validator = Validator::make(
                        $request->all(), 
                        [
                            'edit_name' => 'required | max: 255 |',
                            'edit_lastname' => 'required | max: 255',
                            'edit_email' => 'required | email | max:255 | unique:users,email',
                            'edit_password' => 'required | min:6 | alpha_dash',
                            'edit_tipo_usuario' => 'required | integer | between:1,2 | exists:roles,id'
                        ]
                    );

                    $validator->setAttributeNames([
                        'edit_name'=>'Nombres',
                        'edit_lastname'=>'Apellidos',
                        'edit_email' => "Email",
                        'edit_password' => 'Contraseña'
                    ]);

                }

            }

            $validator->validate();

            DB::beginTransaction();
            //se crea al usuario

            if ($request->edit_password == "" || is_null($request->edit_password)) {
                
                if ($request->edit_email == "" || is_null($request->edit_email)) {
                    $updateUser = DB::table('users')
                    ->where('id', $request->edit_id_user)
                    ->update(array('name' => $request->edit_name, 'roleid' => $request->edit_tipo_usuario, 'lastname' => $request->edit_lastname));
                } else {
                    $updateUser = DB::table('users')
                    ->where('id', $request->edit_id_user)
                    ->update(array('name' => $request->edit_name, 'email' => $request->edit_email, 'roleid' => $request->edit_tipo_usuario, 'lastname' => $request->edit_lastname));
                }
                
            } else {
                if ($request->edit_email == "" || is_null($request->edit_email)) {
                    $updateUser = DB::table('users')
                    ->where('id', $request->edit_id_user)
                    ->update(array('name' => $request->edit_name, 'password' => Hash::make(''.$request->edit_password), 'roleid' => $request->edit_tipo_usuario, 'lastname' => $request->edit_lastname));
                } else {
                    $updateUser = DB::table('users')
                    ->where('id', $request->edit_id_user)
                    ->update(array('name' => $request->edit_name, 'email' => $request->edit_email, 'password' => Hash::make(''.$request->edit_password), 'roleid' => $request->edit_tipo_usuario, 'lastname' => $request->edit_lastname));
                }
            }

            if(!$updateUser) throw new \Exception("Error al modificar el Usuario");

            DB::commit();

            return \Redirect::to($url)->with('edit_success', 'El Usuario fue creado con éxito.');

        } catch (\Exception $e) {
            Log::error("Error en el controlador: ".$e->getMessage());
            DB::rollBack();
            return \Redirect::to($url)->with('edit_error', $e->getMessage())->withErrors($validator);
        }
    }

    public function desactivar_usuario(Request $request)
    {
        $url = $request->disable_url;

        try {

            DB::beginTransaction();
            
            $updateUser = DB::table('users')
                    ->where('id', $request->edit_id_user)
                    ->update(array('status' => $request->edit_status));

            if(!$updateUser) throw new \Exception("Error al desactivar el Usuario");

            DB::commit();

            if ($request->edit_status == 0) {
                return \Redirect::to($url)->with('disable_success', 'El Usuario fue desactivado con éxito.');
            } else {
                return \Redirect::to($url)->with('able_success', 'El Usuario fue activado con éxito.');
            }

        } catch (\Exception $e) {
            Log::error("Error en el controlador: ".$e->getMessage());
            DB::rollBack();
            return \Redirect::to($url)->with('disable_error', 'El Usuario fue creado con éxito.');
        }

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
