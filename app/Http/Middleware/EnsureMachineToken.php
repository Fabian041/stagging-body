<?php

namespace App\Http\Middleware;

use App\Models\Machine;
use Closure;
use Illuminate\Http\Request;

class EnsureMachineToken
{
    public function handle(Request $request, Closure $next)
    {
        $machine = $request->user();

        if (! $machine instanceof Machine) {
            return response()->json([
                'message' => 'Token harus mewakili mesin.',
                'reason' => 'INVALID_MACHINE_TOKEN',
            ], 403);
        }

        if (! $machine->tokenCan(config('machine_auth.token_ability', 'machine-auth'))) {
            return response()->json([
                'message' => 'Token tidak memiliki ability machine-auth.',
                'reason' => 'INVALID_MACHINE_TOKEN_ABILITY',
            ], 403);
        }

        return $next($request);
    }
}
