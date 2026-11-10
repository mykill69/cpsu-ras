<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Folder;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_folders' => Folder::count(),
            'total_files' => File::count(),
            'total_users' => User::where('is_active', true)->count(),
            'total_size' => File::sum('size'),
            'recent_uploads' => File::with('uploader', 'folder')
                ->latest()
                ->take(10)
                ->get(),
            'recent_activities' => Activity::latest()
                ->take(20)
                ->get()
                ->map(function($activity) {
        $causer = $activity->causer;
        $subject = $activity->subject;
        return [
            'id' => $activity->id,
            'description' => $activity->description,
            'event' => $activity->event,
            'created_at' => $activity->created_at,
            'causer_name' => $causer ? ($causer->first_name . ' ' . $causer->last_name) : 'System',
            'causer_initials' => $causer ? strtoupper(substr($causer->first_name, 0, 1) . substr($causer->last_name, 0, 1)) : 'SYS',
            'subject_type' => $subject ? class_basename($subject) : null,
            'subject_name' => $subject ? ($subject->name ?? $subject->original_name ?? null) : null,
            'properties' => $activity->properties,
        ];
    }),
            'files_by_type' => File::select('mime_type', DB::raw('count(*) as count'))
                ->groupBy('mime_type')
                ->orderByDesc('count')
                ->take(5)
                ->get(),
            'uploads_by_day' => File::select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('count(*) as count')
                )
                ->where('created_at', '>=', now()->subDays(30))
                ->groupBy('date')
                ->orderBy('date')
                ->get(),
            'storage_usage' => $this->getStorageUsage(),
        ];

        return view('dashboard', compact('stats'));
    }

    /**
     * Get dashboard stats for AJAX refresh
     */

    public function stats()
    {
        $stats = [
            'total_folders' => Folder::count(),
            'total_files' => File::count(),
            'total_users' => User::where('is_active', true)->count(),
            'total_size' => File::sum('size'),
            'storage_usage' => $this->getStorageUsage(),
            'recent_uploads' => File::with('uploader', 'folder')
                ->latest()
                ->take(10)
                ->get()
                ->map(function($file) {
                    return [
                        'id' => $file->id,
                        'uuid' => $file->uuid,
                        'original_name' => $file->original_name,
                        'formatted_size' => $file->formatted_size,
                        'created_at' => $file->created_at,
                        'uploader' => $file->uploader ? $file->uploader->name : null,
                    ];
                }),
            'recent_activities' => Activity::latest()
                ->take(20)
                ->get()
                ->map(function($activity) {
                    return [
                        'id' => $activity->id,
                        'description' => $activity->description,
                        'created_at' => $activity->created_at,
                    ];
                }),
        ];

        return response()->json($stats);
    }


    private function getStorageUsage()
    {
        $totalStorage = config('app.max_storage', 107374182400); // 100GB default
        $usedStorage = File::sum('size');
        $percentage = ($totalStorage > 0) ? round(($usedStorage / $totalStorage) * 100, 2) : 0;

        return [
            'total' => $totalStorage,
            'used' => $usedStorage,
            'percentage' => $percentage,
            'formatted_total' => $this->formatBytes($totalStorage),
            'formatted_used' => $this->formatBytes($usedStorage),
        ];
    }

    private function formatBytes($bytes)
    {
        if ($bytes === 0) return '0 B';
        
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $size = $bytes;
        $unitIndex = 0;
        
        while ($size >= 1024 && $unitIndex < count($units) - 1) {
            $size /= 1024;
            $unitIndex++;
        }
        
        return round($size, 2) . ' ' . $units[$unitIndex];
    }
}
