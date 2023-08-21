<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase\Contract\Auth;
use Kreait\Firebase\Exception\FirebaseException;
use Session;

class MakeAdminController extends Controller
{
    public function index()
    {
        $auth = app('firebase.auth');
        $uid = Session::get('uid');
        $auth->setCustomUserClaims($uid, ['admin' => true]);
        Session::flash('message', 'You are now an Admin');
        return redirect('/home');
    }
}
