<?php
/**
 * Created by PhpStorm.
 * User: Jaeger <JaegerCode@gmail.com>
 * Date: 18/12/11
 * Time: 下午6:39
 */

namespace Jaeger;



use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;

class Cache extends GHttp
{
    public static function remember($name, $arguments)
    {
        $cachePool = null;
        $cacheConfig = self::initCacheConfig($arguments);

        if (empty($cacheConfig['cache'])) {
            return self::$name(...$arguments);
        }
        if (is_string($cacheConfig['cache'])) {
            $cachePool = new FilesystemAdapter();
        }else if ($cacheConfig['cache'] instanceof AdapterInterface) {
            $cachePool = $cacheConfig['cache'];
        }

        $cacheKey = self::getCacheKey($name,$arguments);
        return $cachePool->get($cacheKey,function (ItemInterface $item)use($cacheConfig,$name,$arguments){
             $item->expiresAfter($cacheConfig['cache_ttl']);
             $data = self::$name($arguments);
             $item->set($data);
             return $item;
         });
    }

    protected static function initCacheConfig($arguments): array
    {
        $cacheConfig = [
            'cache' => null,
            'cache_ttl' => null
        ];
        if(!empty($arguments[2])) {
            $cacheConfig = array_merge([
                'cache' => null,
                'cache_ttl' => null
            ],$arguments[2]);
        }
        return $cacheConfig;
    }

    protected static function getCacheKey($name, $arguments): string
    {
        return md5($name.'_'.json_encode($arguments));
    }
}