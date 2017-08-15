<?php
/**
 * Created by PhpStorm.
 * User: Exia
 * Date: 15.08.2017
 * Time: 18:21
 */

namespace JWTAuth\Providers\Storage;
use Illuminate\Support\Facades\Cache;


class CacheAdapter implements StorageInterface
{
    /**
     * @var \Illuminate\Cache\CacheManager
     */
    protected $cache;

    /**
     * @var string
     */
    protected $tag = 'jwt.auth';

    /**
     * @param string $store
     */
    public function __construct($store)
    {
        $this->cache = Cache::store($store);;
    }

    /**
     * Add a new item into storage.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @param  int  $minutes
     * @return void
     */
    public function put($key, $value, $minutes)
    {
        $this->cache()->put($key, $value, $minutes);
    }

    /**
     * Add a new item into storage.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @param  int  $minutes
     * @return void
     */
    public function add($key, $value, $minutes)
    {
        $this->cache()->add($key, $value, $minutes);
    }

    /**
     * Check whether a key exists in storage.
     *
     * @param  string  $key
     * @return bool
     */
    public function has($key)
    {
        return $this->cache()->has($key);
    }

    /**
     * Get value by key from storage.
     *
     * @param  string  $key
     * @return bool
     */
    public function get($key)
    {
        return $this->cache()->get($key);
    }

    /**
     * Increment value by key from storage.
     *
     * @param  string  $key
     * @param int $amount
     * @return bool
     */
    public function increment($key, $amount = 1)
    {
        return $this->cache()->increment($key, $amount);
    }

    /**
     * Decrement value by key from storage.
     *
     * @param  string  $key
     * @param int $amount
     * @return bool
     */
    public function decrement($key, $amount = 1)
    {
        return $this->cache()->decrement($key, $amount);
    }

    /**
     * Remove an item from storage.
     *
     * @param  string  $key
     * @return bool
     */
    public function forget($key)
    {
        return $this->cache()->forget($key);
    }

    /**
     * Remove all items associated with the tag.
     *
     * @return void
     */
    public function flush()
    {
        $this->cache()->flush();
    }

    /**
     * Return the cache instance with tags attached.
     *
     * @return \Illuminate\Cache\CacheManager
     */
    protected function cache()
    {
        if (! method_exists($this->cache, 'tags')) {
            return $this->cache;
        }

        return $this->cache->tags($this->tag);
    }

}