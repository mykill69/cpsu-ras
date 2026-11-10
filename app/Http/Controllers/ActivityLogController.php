<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Activity;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class ActivityLogController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }
    private function renderPagination($paginator)
{
    return $paginator->links()->render();
}

    public function index(Request $request)
    {
        try {
            $query = Activity::with(['causer', 'subject'])
                ->latest();

            // Search filter
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('description', 'like', "%{$search}%")
                      ->orWhere('event', 'like', "%{$search}%")
                      ->orWhere('log_name', 'like', "%{$search}%");
                });
            }

            // User filter
            if ($request->has('user') && $request->user) {
                $query->where('causer_id', $request->user);
            }

            // Log type filter
            if ($request->has('log_type') && $request->log_type) {
                $query->where('log_name', $request->log_type);
            }

            // Event filter
            if ($request->has('event') && $request->event) {
                $query->where('event', $request->event);
            }

            // Date range filter
            if ($request->has('date_from') && $request->date_from) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }
            if ($request->has('date_to') && $request->date_to) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            $activities = $query->paginate(20);
            
            // Get filter options
            $users = User::where('is_active', true)->orderBy('first_name')->get();
            $logTypes = Activity::distinct()->pluck('log_name')->filter();
            $eventTypes = Activity::distinct()->pluck('event')->filter();

            // Statistics
            $stats = [
                'total' => Activity::count(),
                'today' => Activity::whereDate('created_at', today())->count(),
                'week' => Activity::whereDate('created_at', '>=', now()->subWeek())->count(),
                'month' => Activity::whereDate('created_at', '>=', now()->subMonth())->count(),
                'users' => Activity::distinct('causer_id')->count('causer_id'),
            ];

            // Then use it:
if ($request->ajax()) {
    $tableHtml = view('activity-logs.partials.table', compact('activities'))->render();
    $paginationHtml = $this->renderPagination($activities);
    
    return response()->json([
        'success' => true,
        'table' => $tableHtml,
        'pagination' => $paginationHtml,
    ]);
}

            return view('activity-logs.index', compact(
                'activities', 
                'users', 
                'logTypes',
                'eventTypes',
                'stats'
            ));
        } catch (\Exception $e) {
            Log::error('Activity log index failed: ' . $e->getMessage());
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to load activity logs: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->with('error', 'Failed to load activity logs');
        }
    }

    public function show(Activity $activity)
    {
        try {
            $activity->load(['causer', 'subject']);
            
            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'activity' => $activity
                ]);
            }

            return view('activity-logs.show', compact('activity'));
        } catch (\Exception $e) {
            Log::error('Activity log show failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load activity details'
            ], 500);
        }
    }

    public function destroy(Activity $activity)
    {
        try {
            $activity->delete();

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Activity log deleted successfully'
                ]);
            }

            return redirect()->route('activity-logs.index')
                ->with('success', 'Activity log deleted successfully');
        } catch (\Exception $e) {
            Log::error('Activity log delete failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete activity log'
            ], 500);
        }
    }

    public function clearAll(Request $request)
    {
        try {
            Activity::truncate();

            activity()
                ->causedBy(auth()->user())
                ->withProperties(['action' => 'cleared_all'])
                ->log('All activity logs cleared');

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'All activity logs cleared successfully'
                ]);
            }

            return redirect()->route('activity-logs.index')
                ->with('success', 'All activity logs cleared successfully');
        } catch (\Exception $e) {
            Log::error('Activity log clear failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear activity logs'
            ], 500);
        }
    }
}