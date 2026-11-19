<?php

namespace App\Http\Controllers\Wms;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();
        $userId = $request->integer('user');
        $from = $request->input('from');
        $to = $request->input('to');

        return view(wms_view('audit.index'), [
            'logs' => AuditLog::query()
                ->with('user')
                ->when($search !== '', fn (Builder $query) => $query->where('action', 'like', "%{$search}%"))
                ->when($userId > 0, fn (Builder $query) => $query->where('user_id', $userId))
                ->when(is_string($from) && $from !== '', fn (Builder $query) => $query->whereDate('created_at', '>=', $from))
                ->when(is_string($to) && $to !== '', fn (Builder $query) => $query->whereDate('created_at', '<=', $to))
                ->latest('created_at')
                ->paginate(25)
                ->withQueryString(),
            'users' => User::query()->orderBy('name')->get(),
            'search' => $search,
            'userId' => $userId,
            'from' => $from,
            'to' => $to,
        ]);
    }
}
