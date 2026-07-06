<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Rbac\ActiveRoleService;
use App\Domain\Rbac\LoginRoleActivationService;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Discovery\FeatureRegistry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeatureDiscoveryController extends Controller
{
    public function search(Request $request, FeatureRegistry $registry): JsonResponse
    {
        $user = $request->user();

        if ($user instanceof User && ! app(ActiveRoleService::class)->getActiveRecord($user)) {
            app(LoginRoleActivationService::class)->activateForSession($user, false);
        }

        $query = trim((string) $request->query('q', ''));

        if ($query === '') {
            return response()->json(['results' => []]);
        }

        return response()->json([
            'results' => $registry->searchForClient($query),
        ]);
    }
}
