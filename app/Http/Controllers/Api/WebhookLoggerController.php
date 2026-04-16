<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WebhookLog;
use Illuminate\Http\Request;

class WebhookLoggerController extends Controller
{
    public function __invoke(Request $request, $provider = null)
    {
        $log = WebhookLog::create([
            'provider' => $provider,
            'payload' => $request->all(),
        ]);

        return response()->json([
            'message' => 'Webhook received',
            'id' => $log->id,
        ]);
    }
}