<?php

namespace ProtoneMedia\AnalyticsEventTracking\Http;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StoreSessionIdInSession
{
    /**
     * Stores the posted Client ID in the session.
     */
    public function __invoke(Request $request, SessionIdSession $sessionIdSession): JsonResponse
    {
        $data = $request->validate(['id' => 'required|string|max:255']);

        $sessionIdSession->update($data['id']);

        return response()->json();
    }
}
