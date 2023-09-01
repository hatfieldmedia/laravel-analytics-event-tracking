<?php

namespace ProtoneMedia\AnalyticsEventTracking\Http;

interface SessionIdRepository
{
    public function update(string $sessionId): void;

    public function get(): ?string;
}
