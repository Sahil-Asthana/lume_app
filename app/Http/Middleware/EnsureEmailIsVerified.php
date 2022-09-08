<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Auth\Access\AuthorizationException;
class EnsureEmailIsVerified
{
   
    public function handle($request, Closure $next)
    {
        if ( $request->fullUrl() != route('email.request.verification') && 
           ( ! $request->user() || ! $request->user()->hasVerifiedEmail() ) )
        {
            throw new AuthorizationException('Unauthorized, your email address '.$request->user()->email.' is not verified.');
        }
return $next($request);
    }
}