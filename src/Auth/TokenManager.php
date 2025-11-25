<?php

namespace SmartDato\Dpd\Auth;

use Illuminate\Contracts\Cache\LockProvider;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use SmartDato\Dpd\Clients\LoginServiceClient;

class TokenManager
{
    public function __construct(
        protected CacheRepository $cache,
        protected LoginServiceClient $loginClient,
        protected Credentials $credentials,
        protected array $cacheConfig = []
    ) {}

    /**
     * Get a valid authentication token (from cache or by authenticating).
     */
    public function getToken(): string
    {
        $cacheKey = $this->getCacheKey();

        // Try to get from cache first
        if ($token = $this->cache->get($cacheKey)) {
            return $token;
        }

        // Use cache lock if available to prevent multiple simultaneous auth requests
        if ($this->cache instanceof LockProvider) {
            return $this->cache->lock($cacheKey.':lock', 10)->block(15, function () use ($cacheKey) {
                // Double-check after acquiring lock
                if ($token = $this->cache->get($cacheKey)) {
                    return $token;
                }

                return $this->authenticateAndCache($cacheKey);
            });
        }

        // Fallback without lock if cache provider doesn't support it
        return $this->authenticateAndCache($cacheKey);
    }

    /**
     * Authenticate with DPD and cache the token.
     */
    protected function authenticateAndCache(string $cacheKey): string
    {
        // Authenticate and cache the token
        $token = $this->loginClient->getAuth(
            $this->credentials->delisId,
            $this->credentials->password
        );

        $ttl = $this->cacheConfig['ttl'] ?? 86400; // 24 hours default

        $this->cache->put($cacheKey, $token, $ttl);

        return $token;
    }

    /**
     * Invalidate the cached token (useful when authentication fails).
     */
    public function invalidateToken(): void
    {
        $this->cache->forget($this->getCacheKey());
    }

    /**
     * Get the credentials associated with this token manager.
     */
    public function getCredentials(): Credentials
    {
        return $this->credentials;
    }

    /**
     * Get the cache key for storing the auth token.
     */
    protected function getCacheKey(): string
    {
        $prefix = $this->cacheConfig['prefix'] ?? 'dpd_auth';

        return sprintf('%s:%s', $prefix, $this->credentials->delisId);
    }
}
