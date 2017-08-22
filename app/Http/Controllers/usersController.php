<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Support\Facades\Password;

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
        return view('auth.register');
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
                    'email' => 'required | email | max:255 | unique:users,email',
                    'password' => 'required|min:6',
                    'tipo_usuario' => 'required | integer | between:1,2 | exists:roles,id',
                ]
            );

            $validator->setAttributeNames([
                'name'=>'Nombres',
                'email' => "Email",
                'password' => 'Password',
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
            if(!$saveUser) throw new Exception("Error al crear el Usuario");

            DB::commit();

            return back()->with('success', 'El Usuario fue creado con éxito.');

        } catch (\Exception $e) {
            Log::error("Error en el controlador: ".$e->getMessage());   
            DB::rollBack();
            return back()->with('error', $e->getMessage());
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
    public function edit($id)
    {
        //
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
