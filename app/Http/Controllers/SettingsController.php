<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;
use App\Models\Setting;
use App\Models\User;

class SettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    public function index()
    {
        /** @var User $user */
        $user = auth()->user();
        
        // Only allow administrators
        if (!$user->isAdministrator()) {
            abort(403, 'Unauthorized access');
        }
        
        try {
            $settings = [
                'maintenance_mode' => Setting::get('maintenance_mode', false),
                'maintenance_message' => Setting::get('maintenance_message', 'System is under maintenance. Please check back later.'),
                'system_name' => Setting::get('system_name', config('app.name', 'CPSU RAS')),
                'system_email' => Setting::get('system_email', 'cpsu@cpsu.edu.ph'),
                'max_file_size' => Setting::get('max_file_size', 102400),
                'allowed_file_types' => Setting::get('allowed_file_types', 'pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif'),
                'session_lifetime' => Setting::get('session_lifetime', 120),
            ];

            return view('settings.index', compact('settings'));
        } catch (\Exception $e) {
            Log::error('Settings index failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to load settings');
        }
    }

    public function update(Request $request)
    {
        try {
            $validated = $request->validate([
                'system_name' => 'required|string|max:255',
                'system_email' => 'required|email',
                'max_file_size' => 'required|integer|min:1',
                'allowed_file_types' => 'required|string',
                'session_lifetime' => 'required|integer|min:5|max:1440',
            ]);

            Setting::set('system_name', $validated['system_name']);
            Setting::set('system_email', $validated['system_email']);
            Setting::set('max_file_size', $validated['max_file_size'], 'integer');
            Setting::set('allowed_file_types', $validated['allowed_file_types']);
            Setting::set('session_lifetime', $validated['session_lifetime'], 'integer');

            activity()
                ->causedBy(auth()->user())
                ->withProperties(['action' => 'updated_settings'])
                ->log('System settings updated');

            return response()->json([
                'success' => true,
                'message' => 'Settings updated successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Settings update failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update settings: ' . $e->getMessage()
            ], 500);
        }
    }

    public function toggleMaintenance(Request $request)
    {
        try {
            $mode = $request->input('mode', 'down');
            $message = $request->input('message', 'System is under maintenance. Please check back later.');
            
            if ($mode === 'down') {
                Setting::set('maintenance_mode', true, 'boolean');
                Setting::set('maintenance_message', $message);
                $status = 'enabled';
                $maintenanceMode = true;
            } else {
                Setting::set('maintenance_mode', false, 'boolean');
                $status = 'disabled';
                $maintenanceMode = false;
            }

            activity()
                ->causedBy(auth()->user())
                ->withProperties(['action' => "maintenance_{$status}"])
                ->log("Maintenance mode {$status}");

            return response()->json([
                'success' => true,
                'message' => "Maintenance mode {$status} successfully",
                'maintenance_mode' => $maintenanceMode,
            ]);
        } catch (\Exception $e) {
            Log::error('Toggle maintenance failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle maintenance mode: ' . $e->getMessage()
            ], 500);
        }
    }

    public function clearCache()
    {
        try {
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');

            activity()
                ->causedBy(auth()->user())
                ->withProperties(['action' => 'cleared_cache'])
                ->log('System cache cleared');

            return response()->json([
                'success' => true,
                'message' => 'System cache cleared successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Clear cache failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear cache'
            ], 500);
        }
    }
}