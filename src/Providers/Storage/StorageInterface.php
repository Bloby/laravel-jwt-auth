<?php
/**
 * Created by PhpStorm.
 * User: Exia
 * Date: 15.08.2017
 * Time: 18:17
 */

namespace JWTAuth\Providers\Storage;


interface StorageInterface
{
    /**
     * @param string $key
     * @param mix $value
     * @param int $minutes
     * @return void
     */
    public function put($key, $value, $minutes);
    /**
     * @param string $key
     * @param mix $value
     * @param int $minutes
     * @return void
     */
    public function add($key, $value, $minutes);

    /**
     * @param string $key
     * @return bool
     */
    public function has($key);

    /**
     * @param string $key
     * @return mixed
     */
    public function get($key);

    /**
     * @param string $key
     * @param int $amount
     * @return mixed
     */
    public function increment($key, $amount = 1);

    /**
     * @param string $key
     * @param int $amount
     * @return mixed
     */
    public function decrement($key, $amount = 1);

    /**
     * @param string $key
     * @return bool
     */
    public function forget($key);

    /**
     * @return void
     */
    public function flush();
}