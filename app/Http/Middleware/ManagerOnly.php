<?php

namespace App\Http\Middleware;

use App\Traits\HttpResponses;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ManagerOnly
{
    use HttpResponses;
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure(Request): (Response|RedirectResponse) $next
     * @return JsonResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (!Auth()->user()?->is_manager) {
            return $this->failure(
                'Unauthorized for this action!',
                Response::HTTP_UNAUTHORIZED
            );
        }
        return $next($request);
    }
}
