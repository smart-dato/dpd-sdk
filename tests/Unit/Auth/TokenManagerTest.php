<?php

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use SmartDato\Dpd\Auth\Credentials;
use SmartDato\Dpd\Auth\TokenManager;
use SmartDato\Dpd\Clients\LoginServiceClient;

beforeEach(function () {
    $this->mockCache = Mockery::mock(CacheRepository::class);
    $this->mockLoginClient = Mockery::mock(LoginServiceClient::class);
    $this->credentials = new Credentials('test_delis_id', 'test_password');
    $this->cacheConfig = [
        'prefix' => 'dpd_auth',
        'ttl' => 86400,
    ];
});

afterEach(function () {
    Mockery::close();
});

it('returns cached token when available', function () {
    $this->mockCache->shouldReceive('get')
        ->with('dpd_auth:test_delis_id')
        ->once()
        ->andReturn('cached_token');

    $tokenManager = new TokenManager(
        $this->mockCache,
        $this->mockLoginClient,
        $this->credentials,
        $this->cacheConfig
    );

    $token = $tokenManager->getToken();

    expect($token)->toBe('cached_token');
});

it('authenticates and caches token when not cached', function () {
    $this->mockCache->shouldReceive('get')
        ->with('dpd_auth:test_delis_id')
        ->once()
        ->andReturn(null);

    $this->mockLoginClient->shouldReceive('getAuth')
        ->with('test_delis_id', 'test_password')
        ->once()
        ->andReturn('new_token');

    $this->mockCache->shouldReceive('put')
        ->with('dpd_auth:test_delis_id', 'new_token', 86400)
        ->once();

    $tokenManager = new TokenManager(
        $this->mockCache,
        $this->mockLoginClient,
        $this->credentials,
        $this->cacheConfig
    );

    $token = $tokenManager->getToken();

    expect($token)->toBe('new_token');
});

it('can invalidate cached token', function () {
    $this->mockCache->shouldReceive('forget')
        ->with('dpd_auth:test_delis_id')
        ->once();

    $tokenManager = new TokenManager(
        $this->mockCache,
        $this->mockLoginClient,
        $this->credentials,
        $this->cacheConfig
    );

    $tokenManager->invalidateToken();

    expect(true)->toBeTrue();
});

it('returns credentials', function () {
    $tokenManager = new TokenManager(
        $this->mockCache,
        $this->mockLoginClient,
        $this->credentials,
        $this->cacheConfig
    );

    $credentials = $tokenManager->getCredentials();

    expect($credentials)->toBe($this->credentials)
        ->and($credentials->delisId)->toBe('test_delis_id')
        ->and($credentials->password)->toBe('test_password');
});

it('uses default TTL when not configured', function () {
    $this->mockCache->shouldReceive('get')
        ->with('dpd_auth:test_delis_id')
        ->once()
        ->andReturn(null);

    $this->mockLoginClient->shouldReceive('getAuth')
        ->with('test_delis_id', 'test_password')
        ->once()
        ->andReturn('new_token');

    $this->mockCache->shouldReceive('put')
        ->with('dpd_auth:test_delis_id', 'new_token', 86400)
        ->once();

    $tokenManager = new TokenManager(
        $this->mockCache,
        $this->mockLoginClient,
        $this->credentials,
        [] // empty config
    );

    $token = $tokenManager->getToken();

    expect($token)->toBe('new_token');
});

it('generates correct cache key', function () {
    $this->mockCache->shouldReceive('get')
        ->with('dpd_auth:test_delis_id')
        ->once()
        ->andReturn('cached_token');

    $tokenManager = new TokenManager(
        $this->mockCache,
        $this->mockLoginClient,
        $this->credentials,
        $this->cacheConfig
    );

    $tokenManager->getToken();
});

it('uses custom cache prefix when configured', function () {
    $customConfig = [
        'prefix' => 'custom_prefix',
        'ttl' => 3600,
    ];

    $this->mockCache->shouldReceive('get')
        ->with('custom_prefix:test_delis_id')
        ->once()
        ->andReturn('cached_token');

    $tokenManager = new TokenManager(
        $this->mockCache,
        $this->mockLoginClient,
        $this->credentials,
        $customConfig
    );

    $token = $tokenManager->getToken();

    expect($token)->toBe('cached_token');
});
