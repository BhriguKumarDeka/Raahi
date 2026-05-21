<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OnboardedMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user) {
            $isOnboardingPage = $request->is('onboarding') || $request->routeIs('onboarding');

            if (!$user->onboarded && !$isOnboardingPage) {
                return redirect()->route('onboarding');
            }

            if ($user->onboarded && $isOnboardingPage) {
                return redirect()->route('dashboard');
            }
        }

        return $next($request);
    }
}
