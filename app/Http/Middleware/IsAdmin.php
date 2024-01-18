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

        // Check if $uid is not null before making the getUser call
        if ($uid !== null) {
            $userClaim = app('firebase.auth')->getUser($uid)->customClaims["admin"];

            if ($userClaim) {
                return $next($request);
            } else {
                return redirect('/home')->with('error', 'You are not admin');
            }
        } else {
            return redirect('/home')->with('message', 'You are not admin');
        }
    }
}
