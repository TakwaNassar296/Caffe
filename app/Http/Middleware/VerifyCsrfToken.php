<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
   
   
    
    protected $except = [
        
             'updateCartByIPN/*','rating/*',
             'api/callback/*',
             '/status/*/success',
             '/status/*/error',
             'callback',
             'return',

         
      
    ];
}
