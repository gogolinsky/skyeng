<?php

namespace src\Decorator;

use DateTime;
use Exception;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use src\Integration\IDataProvider;

class CachingDataProvider implements IDataProvider
{
    public $cache;
    /** @var LoggerInterface */
    public $logger;
    /** @var IDataProvider */
    private $dataProvider;

    /**
     * @param IDataProvider $dataProvider
     * @param CacheItemPoolInterface $cache
     * @param LoggerInterface $logger
     */
    public function __construct(IDataProvider $dataProvider, CacheItemPoolInterface $cache, LoggerInterface $logger)
    {
        $this->dataProvider = $dataProvider;
        $this->cache = $cache;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function get(array $request)
    {
        try {
            $cacheKey = $this->getCacheKey($request);
            $cacheItem = $this->cache->getItem($cacheKey);

            if ($cacheItem->isHit()) {
                return $cacheItem->get();
            }

            $result = $this->dataProvider->get($request);
            $cacheItem->set($result)->expiresAt((new DateTime())->modify('+1 day'));

            if (!$this->cache->save($cacheItem)) {
                $this->logger->warning('Warning');
            }

            return $result;
        } catch (Exception $e) {
            $this->logger->critical('Error');
            throw $e;
        }

        return [];
    }

    public function getCacheKey(array $input)
    {
        return md5(json_encode($input));
    }
}