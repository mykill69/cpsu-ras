<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Setting;
use App\Models\User;

class CheckMaintenanceMode
{
    public function handle(Request $request, Closure $next): Response
    {
        // Get maintenance mode from database
        $maintenanceMode = Setting::get('maintenance_mode', false);
        
        if ($maintenanceMode) {
            // Always allow login/logout routes
            if ($request->is('login') || $request->is('login/*') || $request->is('logout')) {
                return $next($request);
            }
            
            // Allow authenticated administrators
            if (Auth::check()) {
                /** @var User $user */
                $user = Auth::user();
                
                // Check if user is administrator
                if ($user->isAdministrator() || $user->hasRole('administrator') || $user->position === 'System Administrator') {
                    // Flash maintenance banner for admins
                    session()->flash('maintenance_mode', true);
                    return $next($request);
                }
            }
            
            // Get maintenance message
            $message = Setting::get('maintenance_message', 'System is under maintenance. Please check back later.');
            
            // Show maintenance page for non-admin users
            return response()->view('errors.maintenance', [
                'message' => $message,
            ], 503);
        }
        
        return $next($request);
    }
}