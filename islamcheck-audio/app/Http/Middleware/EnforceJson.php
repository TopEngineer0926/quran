<?php

namespace App\Http\Middleware;

use Closure;

class EnforceJson
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {


//        print_r($request);exit;
        if(strpos($request->url(),"/api/upload/media/file")){
//                $request->headers->set('Content-type', 'audio/mpeg');
        }else if(strpos($request->url(),"/api/")){
            $request->headers->set('Accept', 'application/json');
            if($request->headers->get('Content-type') == "application/x-www-form-urlencoded"){
                // do nothing
            }else{
                $request->headers->set('Content-type', 'application/json');
            }
        }

//        return $next($request);
        return $next($request)->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods','GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS')
            ->header('Access-Control-Allow-Headers',' Origin, Content-Type, Accept, Authorization, X-Request-With')
            ->header('Access-Control-Allow-Credentials',' true');
    }


}
