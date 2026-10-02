<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $adminEmails = config('app.admin_emails', []);
        $userEmail = strtolower((string) $request->user()?->email);

        abort_unless($userEmail !== '' && in_array($userEmail, $adminEmails, true), 403);

        return $next($request);
    }
}