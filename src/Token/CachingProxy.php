<?php
declare(strict_types = 1);

namespace Promenade\Doctrine\Aws\Auth\Token;

use Psr\Cache\CacheItemPoolInterface;

/**
 * Token caching proxy for performance optimization purposes
 */
class CachingProxy implements TokenProvider
{
    private TokenProvider $subject;

    private CacheItemPoolInterface $cache;
    
    private int $lifetime;

    /**
     * @param TokenProvider $subject
     * @param CacheItemPoolInterface $cache
     * @param int $lifetime Cache TTL in minutes
     */
    public function __construct(
        TokenProvider $subject,
        CacheItemPoolInterface $cache,
        int $lifetime = 10
    ) {
        $this->subject = $subject;
        $this->cache = $cache;
        $this->lifetime = $lifetime;
    }

    public function getToken(string $endpoint, string $region, string $username): string
    {
        $cacheKey = urlencode("rds_token $endpoint $region $username");
        
        $cachedToken = $this->cache->getItem($cacheKey);
        if ($cachedToken->isHit()) {
            $token = (string)$cachedToken->get();
        } else {
            $token = $this->subject->getToken($endpoint, $region, $username);
            
            $cachedToken->set($token);
            $cachedToken->expiresAfter(new \DateInterval("PT{$this->lifetime}M"));
            $this->cache->save($cachedToken);
        }
        
        return $token;
    }
}