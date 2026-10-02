<?php

namespace App\Http\Controllers;

use App\Models\Aquarium;
use App\Models\AquariumActuatorCommand;
use App\Models\AquariumTelemetry;
use App\Models\VivoUsers;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index(Request $request)
    {
        $search = mb_substr(trim((string) $request->query('q', '')), 0, 100);

        $users = VivoUsers::query()
            ->with('aquarium')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('username', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhereHas('aquarium', fn ($aquarium) => $aquarium->where('sain', 'like', "%{$search}%"));
                });
            })
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $aquariumIds = $users->getCollection()->pluck('aquarium.id')->filter()->all();
        $latestTelemetry = AquariumTelemetry::query()
            ->whereIn('id', AquariumTelemetry::query()
                ->selectRaw('MAX(id)')
                ->whereIn('aquarium_id', $aquariumIds)
                ->groupBy('aquarium_id'))
            ->get()
            ->keyBy('aquarium_id');

        return view('admin.dashboard', [
            'users' => $users,
            'latestTelemetry' => $latestTelemetry,
            'search' => $search,
            'stats' => [
                'users' => VivoUsers::count(),
                'aquariums' => Aquarium::count(),
                'paired' => Aquarium::whereNotNull('device_token_hash')->count(),
                'pendingCommands' => AquariumActuatorCommand::where('status', 'pending')->count(),
                'telemetry' => AquariumTelemetry::count(),
            ],
            'recentCommands' => AquariumActuatorCommand::with('aquarium.owner')
                ->latest()
                ->limit(8)
                ->get(),
        ]);
    }
}