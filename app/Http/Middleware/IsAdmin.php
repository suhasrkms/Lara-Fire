<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Session;

class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $uid = Session::get('uid');
        $userClaim = app('firebase.auth')->getUser($uid)->customClaims["admin"];

        if ($userClaim) {
            return $next($request);
        }
        else {
            return redirect('/home');
        }
    }
}
