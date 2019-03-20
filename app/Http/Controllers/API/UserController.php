<?php

namespace App\Http\Controllers\API;

use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Intervention\Image\Facades\Image;

class UserController extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        return User::latest()->paginate(10);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->authorize('isAdmin');
        //Validation on the server
        $this->validate($request,[
            'name'      => 'required|string|max:191',
            'email'     => 'required|string|email|max:191|unique:users',
            'password'  => 'required|string|min:6'

        ]);
       return User::create([
           'name'      => $request['name'],
           'email'     => $request['email'],
           'type'      => $request['type'],
           'bio'       => $request['bio'],
           'photo'     => $request['photo'],
           'password'  => Hash::make($request['password'])
       ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

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
        $user = User::findOrFail($id);

        //Validation on the server
        $this->validate($request,[
            'name'      => 'required|string|max:191',
            'email'     => 'required|string|email|max:191|unique:users,email,' .$user->id,
            'password'  => 'sometimes|required|min:6'

        ]);

        $user->update($request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->authorize('isAdmin');

        $user  = User::findOrFail($id);

        $user->delete();


        return ['message' => 'User Deleted'];
    }


    public  function  profile()
    {
        return auth('api')->user();
        //return Auth::user();
    }

    public  function  updateProfile(Request $request)
    {
        $user =  auth('api')->user();

        //Validation on the server
        $this->validate($request,[
            'name'      => 'required|string|max:191',
            'email'     => 'required|string|email|max:191|unique:users,email,' .$user->id,
            'password'  => 'sometimes|required|min:6'

        ]);

        //Photo in user profile
        $currentPhoto = $user->photo;

        if ($request->photo  !== $currentPhoto){
             //unique name data:image/jpeg;
            $name = time().'.'. explode('/', explode(':' , substr($request->photo, 0 , strpos($request->photo, ';')))[1])[1];

            Image::make($request->photo)->save(public_path('image/profile/').$name);

            $request->merge(['photo' => $name]);

            //To delete the old image
            $userPhoto = public_path('image/profile/').$currentPhoto;
            if(file_exists($userPhoto)){

                //delete
                @unlink($userPhoto);
            }
        }

        //Hash Password

        if (!empty($request->password)){
            $request->merge(['password' =>  Hash::make($request['password'])]);
        }


        //Save

        $user->update($request->all());

        return ['message' => 'Successs'];
    }
}
