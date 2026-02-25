<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with('user');

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by action
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // Filter by model type
        if ($request->filled('model_type')) {
            $query->where('model_type', 'like', "%{$request->model_type}%");
        }

        // Filter by date range
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $sortBy = $request->get('sort_by', 'created_at');
        $sortDir = $request->get('sort_dir', 'desc');
        $allowedSorts = ['created_at', 'action', 'model_type'];
        if (!in_array($sortBy, $allowedSorts)) { $sortBy = 'created_at'; }
        if (!in_array($sortDir, ['asc', 'desc'])) { $sortDir = 'desc'; }

        $logs = $query->orderBy($sortBy, $sortDir)->paginate(50);

        // Get filter options
        $users = User::orderBy('name')->get(['id', 'name']);
        $actions = AuditLog::distinct()->pluck('action');
        $modelTypes = AuditLog::distinct()
            ->whereNotNull('model_type')
            ->pluck('model_type')
            ->map(fn($type) => class_basename($type));

        return view('admin.audit-logs.index', compact('logs', 'users', 'actions', 'modelTypes'));
    }

    public function show(AuditLog $auditLog)
    {
        $auditLog->load('user');

        return view('admin.audit-logs.show', compact('auditLog'));
    }
}

