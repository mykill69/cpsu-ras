<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Folder;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = $this->buildStats();
        $uploadsByDay = $this->uploadsByDay(30);

        return view('dashboard', compact('stats', 'uploadsByDay'));
    }

    /**
     * Get dashboard stats for AJAX refresh.
     * Returns the exact same shape as the initial page payload so the
     * front-end never loses fields (causer names, uploaders) after a refresh.
     */
    public function stats()
    {
        return response()->json($this->buildStats());
    }

    private function buildStats(): array
    {
        return [
            'total_folders' => Folder::count(),
            'total_files' => File::count(),
            'total_users' => User::where('is_active', true)->count(),
            'uploads_this_week' => File::where('created_at', '>=', now()->startOfWeek())->count(),
            'storage_usage' => $this->getStorageUsage(),
            'recent_uploads' => File::with('uploader')
                ->latest()
                ->take(8)
                ->get()
                ->map(fn ($file) => [
                    'id' => $file->id,
                    'original_name' => $file->original_name,
                    'extension' => strtolower(pathinfo($file->original_name, PATHINFO_EXTENSION)),
                    'formatted_size' => $file->formatted_size,
                    'created_at' => $file->created_at,
                    'uploader' => $file->uploader?->name,
                ]),
            'recent_activities' => Activity::with(['causer', 'subject'])
                ->latest()
                ->take(12)
                ->get()
                ->map(function ($activity) {
                    $causer = $activity->causer;
                    $subject = $activity->subject;

                    return [
                        'id' => $activity->id,
                        'description' => $activity->description,
                        'event' => $activity->event,
                        'created_at' => $activity->created_at,
                        'causer_name' => $causer ? trim($causer->first_name . ' ' . $causer->last_name) : 'System',
                        'causer_initials' => $causer
                            ? strtoupper(substr($causer->first_name, 0, 1) . substr($causer->last_name, 0, 1))
                            : 'SY',
                        'subject_type' => $subject ? class_basename($subject) : null,
                        'subject_name' => $subject ? ($subject->name ?? $subject->original_name ?? null) : null,
                    ];
                }),
        ];
    }

    /**
     * Daily upload counts for the last $days days, zero-filled so the chart
     * always has one bar per day.
     */
    private function uploadsByDay(int $days): array
    {
        $start = now()->subDays($days - 1)->startOfDay();

        $counts = File::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->where('created_at', '>=', $start)
            ->groupBy('date')
            ->pluck('count', 'date');

        $series = [];
        for ($i = 0; $i < $days; $i++) {
            $date = $start->copy()->addDays($i);
            $key = $date->toDateString();
            $series[] = [
                'date' => $key,
                'label' => $date->format('M j'),
                'count' => (int) ($counts[$key] ?? 0),
            ];
        }

        return $series;
    }

    private function getStorageUsage()
    {
        $totalStorage = config('app.max_storage', 107374182400); // 100GB default
        $usedStorage = (int) File::sum('size');
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
        if ($bytes == 0) return '0 B';

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
