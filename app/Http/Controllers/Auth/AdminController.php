<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Kreait\Firebase\Contract\Auth;
use Kreait\Firebase\Exception\FirebaseException;
use App\Http\Controllers\Controller;
use Session;

class AdminController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $currentUser = Session::get('uid');
        $users = app('firebase.auth')->listUsers($defaultMaxResults = 1000, $defaultBatchSize = 1000);
        $usersArray = iterator_to_array($users);
        return view('auth.admin', ['users' => $usersArray, 'currentUser' => $currentUser]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try{

            $userProperties = [
                'email' => $request->email,
                'emailVerified' => false,
                'password' => $request->password,
                'displayName' => $request->displayName,
                'disabled' => false,
            ];

            $createdUser = app('firebase.auth')->createUser($userProperties);

            // return back()->withInput();
            // return $request->all();;
            Session::flash('message', 'New User Created');
            return back()->withInput();
        }
        catch (FirebaseException $e) {
          throw ValidationException::withMessages([$this->username() => [trans('auth.failed')],]);
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
        $uid = $id;
        $properties = [
            'email' => $request->email,
            'emailVerified' => false,
            'displayName' => $request->displayName,
        ];

        $updatedUser = app('firebase.auth')->updateUser($uid, $properties);
        // $input = $request->all();
        // $request->validate([
        // 'displayName' => 'required|min:4|max:20',
        // 'email' => 'regex:/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix',
        // ]);
        // $user = User::findOrFail($id);
        // $user->update($input);
        Session::flash('message', 'User Data Updated');
        return back()->withInput();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $auth = app('firebase.auth');
            $user = $auth->getUser($id);
            if($user->disabled) {
                $updatedUser = $auth->enableUser($id);
                return back()->with('message','The User has been enabled');
            }
            else {
                $updatedUser = $auth->disableUser($id);
                return back()->with('delete', 'The User has been disabled');
            }
        }
        catch (\Kreait\Firebase\Exception\Auth\UserNotFound $e) {
            return  back()->with('error', $e->getMessage());
        }
    }
}
