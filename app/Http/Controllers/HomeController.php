<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;
use Kreait\Firebase\Contract\Auth;
use Kreait\Firebase\Exception\FirebaseException;
use Carbon\Carbon;

use Session;

class HomeController extends Controller
{
  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct()
  {
    $this->middleware('auth');
  }

  /**
   * Show the application dashboard.
   *
   * @return \Illuminate\Contracts\Support\Renderable
   */
  public function index()
  {
    // FirebaseAuth.getInstance().getCurrentUser();
    try {

      $uid = Session::get('uid');
      $user = app('firebase.auth')->getUser($uid);
      $users = app('firebase.auth')->listUsers($defaultMaxResults = 1000, $defaultBatchSize = 1000);

      $usersArray = iterator_to_array($users);
      $totalUsers = count($usersArray);

      return view('home', compact('user','totalUsers'));
    } catch (\Exception $e) {
      return $e->getmessage();
    }
  }
}
