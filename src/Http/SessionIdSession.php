<?php

namespace ProtoneMedia\AnalyticsEventTracking\Http;

use Illuminate\Session\Store;
use Illuminate\Support\Str;

class SessionIdSession implements SessionIdRepository
{
    private Store $session;
    private string $key;

    public function __construct(Store $session, string $key)
    {
        $this->session = $session;
        $this->key     = $key;
    }

    /**
     * Stores the Session ID in the session.
     */
    public function update(string $sessionId): void
    {
        $this->session->put($this->key, $sessionId);
    }

    /**
     * Gets the Client ID from the session or generates one.
     */
    public function get(): ?string
    {
        return $this->session->get($this->key);
    }
}
