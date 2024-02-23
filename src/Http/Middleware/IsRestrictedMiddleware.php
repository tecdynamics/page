<?php
namespace Tec\Page\Http\Middleware;
/**
 *  ****************************************************************
 *  *** DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS HEADER. ***
 *  ****************************************************************
 *  Copyright © 2023 TEC-Dynamics LTD <support@tecdynamics.org>.
 *  All rights reserved.
 *  This software contains confidential proprietary information belonging
 *  to Tec-Dynamics Software Limited. No part of this information may be used, reproduced,
 *  or stored without prior written consent of Tec-Dynamics Software Limited.
 * @Author    : Michail Fragkiskos
 * @Created at: 08/12/2023 at 17:28
 * @Interface     : ${NAME}
 * @Package   : Tec_Eshop
 */

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tec\Page\Models\Page;

class IsRestrictedMiddleware{

    public function handle(Request $request, Closure $next)
{
    if (!auth()->check() && ! auth('customer')->check() && \Route::current()->parameter('slug') !=null){
        $result = DB::table('pages')->join('slugs', 'pages.id','=','slugs.reference_id')
       ->where('slugs.key','=',\Route::current()->parameter('slug'))
       ->where('slugs.reference_type',Page::class)
       ->first();
         if(!empty($result) && $result->is_restricted !=0  && $result->is_restricted != \Route::current()->parameter('slug')) {
             return redirect(route('public.single',$result->is_restricted));
           }
    }
    return $next($request);
    }
}
