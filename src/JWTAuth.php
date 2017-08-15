<?php

namespace JWTAuth;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Hash;
use Event;

use App\User;

use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\ValidationData;

use JWTAuth\Providers\Storage\CacheAdapter;
use JWTAuth\Exceptions\TokenExpiredException;
use JWTAuth\Exceptions\TokenInvalidException;
use JWTAuth\Exceptions\TokenUnavailableException;
use JWTAuth\Exceptions\JWTException;

class JWTAuth
{
    /**
     * @var string
     */
    protected $_secret;
    /**
     * @var string
     */
    protected $_iss;
    /**
     * @var string
     */
    protected $_aud;
    /**
     * @var int
     */
    protected $_expiration;

    /**
     * @var string
     */
    protected $_token_header;

    /**
     * @var string
     */
    protected $_token_name;

    /**
     * @var \Lcobucci\JWT\Token
     */
    protected $_token;

    /**
     * @var string
     */
    protected $_username;

    /**
     * @var \Illuminate\Contracts\Auth\Authenticatable
     */
    protected $_user;

    /**
     * @var \Illuminate\Http\Request
     */
    private $request;
    /**
     * @var \JWTAuth\Providers\Storage\CacheAdapter
     */
    private $cache;

    /**
     * JWTAuth constructor.
     * @param \Illuminate\Http\Request $request
     * @param \JWTAuth\Providers\Storage\CacheAdapter $cache
     */
    public function __construct(Request $request, CacheAdapter $cache)
    {
        $this->_secret = $this->config('secret');
        $this->_iss = $this->config('iss');
        $this->_aud = $this->config('aud');
        $this->_expiration = $this->config('expiration');
        $this->_token_header = $this->config('token_header');
        $this->_token_name = $this->config('token_name');

        $this->_username = $this->config('username');

        $this->request = $request;
        $this->cache = $cache;
    }

    /**
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function user()
    {
        return $this->_user;
    }

    /**
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @return $this
     */
    protected function setUser(User $user)
    {
        $this->_user = $user;
        return $this;
    }

    /**
     * @param  \App\User  $user
     * @return \Lcobucci\JWT\Token
     */
    public function createToken(User $user)
    {
        $signer = new Sha256();

        $token = (new Builder())
            ->setIssuer($this->_iss) // Configures the issuer (iss claim)
            ->setAudience($this->_aud) // Configures the audience (aud claim)
            ->setId(sha1(sprintf('%s|%s|%s', Carbon::now()->timestamp, $user->id, $user->{$this->username()})), true) // Configures the id (jti claim), replicating as a header item
            ->setIssuedAt(Carbon::now()->timestamp) // Configures the time that the token was issue (iat claim)
            ->setNotBefore(Carbon::now()->timestamp) // Configures the time that the token can be used (nbf claim)
            ->setExpiration(Carbon::now()->timestamp + $this->_expiration) // Configures the expiration time of the token (nbf claim)
            ->setSubject($user->id) // Configures a new claim, called "uid"
            ->set($this->username(), $user->{$this->username()})
            ->sign($signer, $this->_secret) // creates a signature using "testing" as key
            ->getToken(); // Retrieves the generated token

        $this->_token = $token;

        return $token;
    }

    /**
     * @param string $token
     * @return \Lcobucci\JWT\Token
     */
    public function parseToken(string $token)
    {
        $token = (new Parser())->parse((string) $token); // Parses from a string
        $token->getHeaders(); // Retrieves the token header
        $token->getClaims(); // Retrieves the token claims

        //echo $token->getHeader('jti'); // will print "4f1g23a12aa"
        //echo $token->getClaim('iss'); // will print "http://example.com"
        //echo $token->getClaim('uid'); // will print "1"
        return $token;
    }

    /**
     * @return \Lcobucci\JWT\Token
     *
     * @throws \JWTAuth\Exceptions\JWTException
     */
    public function getToken()
    {
        if (!empty($this->_token)) {
            return $this->_token;
        }

        if ($this->request->hasHeader($this->_token_header)) {
            $token = $this->request->header($this->_token_header);
        }
        elseif ($this->request->has($this->_token_name)) {
            $token = $this->request->input($this->_token_name);
        }
        else {
            throw new JWTException('A token is required', 400);
        }

        $token = $this->parseToken($token);

        return $token;
    }

    /**
     * @param \Lcobucci\JWT\Token $token
     * @throws \JWTAuth\Exceptions\TokenExpiredException
     * @throws \JWTAuth\Exceptions\TokenInvalidException
     * @throws \JWTAuth\Exceptions\TokenUnavailableException
     */
    public function validateToken(\Lcobucci\JWT\Token $token)
    {
        $signer = new Sha256();
        //var_dump($token->verify($signer, 'testing 1')); // false, because the key is different
        //var_dump($token->verify($signer, $signKey)); // true, because the key is the same
        if (!$token->verify($signer, $this->_secret)) {
            throw new TokenInvalidException('Token Signature could not be verified.');
        }

        if ($token->isExpired(Carbon::now())) {
            throw new TokenExpiredException('Token has expired.');
        }

        if ($this->isUnavailableToken($token)) {
            throw new TokenUnavailableException('The token not available anymore.');
        }

        $requiredClaims = ['sub', $this->username()];
        foreach ($requiredClaims as $claim) {
            if (!$token->hasClaim($claim)) {
                throw new TokenInvalidException('Token contains invalid data.');
            }
        }

        $nbf = (int)$token->getClaim('nbf');
        if ($nbf > Carbon::now()->timestamp) {
            throw new TokenInvalidException('Not Before (nbf) timestamp cannot be in the future', 400);
        }

        $iat = (int)$token->getClaim('iat');
        if ($iat > Carbon::now()->timestamp) {
            throw new TokenInvalidException('Issued At (iat) timestamp cannot be in the future', 400);
        }

        $data = new ValidationData(); // It will use the current time to validate (iat, nbf and exp)
        $data->setIssuer($this->_iss);
        $data->setAudience($this->_aud);
        //$data->setId('4f1g23a12aa');

        //var_dump($token->validate($data)); // false, because we created a token that cannot be used before of `time() + 60`

        //$data->setCurrentTime(time() + 60); // changing the validation time to future

        //var_dump($token->validate($data)); // true, because validation information is equals to data contained on the token

        //$data->setCurrentTime(time() + 4000); // changing the validation time to future

        //var_dump($token->validate($data)); // false, because token is expired since current time is greater than exp

        if (!$token->validate($data)) {
            throw new TokenInvalidException('Token contains invalid data.');
        }
    }

    /**
     * @param array $credentials
     * @param bool $remember
     * @return bool
     */
    public function attempt($credentials = [], $remember = false)
    {
        $this->fireAttemptEvent();

        if (empty($credentials)) {

            $token = $this->getToken();

            if (!$token) {
                return false;
            }

            $user = $this->retrieveByJWT($token);

            $this->login($user, $remember);

            return true;
        }

        $user = $this->retrieveByCredentials($credentials);

        // If an implementation of UserInterface was returned, we'll ask the provider
        // to validate the user against the given credentials, and if they are in
        // fact valid we'll log the users into the application and return true.
        if ($this->hasValidCredentials($user, $credentials)) {
            $this->login($user, $remember);

            return true;
        }

        // If the authentication attempt fails we will fire an event so that the user
        // may be notified of any suspicious attempts to access their account from
        // an unrecognized user. A developer may listen to this event as needed.
        $this->fireFailedEvent();

        return false;
    }

    /**
     * @param \Lcobucci\JWT\Token $token
     * @return $this
     */
    public function forgetToken(\Lcobucci\JWT\Token $token)
    {
        $leftMin = Carbon::now()->diffInMinutes(Carbon::createFromTimestamp($token->getClaim('exp')));
        $this->cache->put(sprintf('jti.%s', $token->getHeader('jti')), true, $leftMin);
        return $this;
    }

    /**
     * @param \Lcobucci\JWT\Token $token
     * @return bool
     */
    public function isUnavailableToken(\Lcobucci\JWT\Token $token)
    {
        return $this->cache->has(sprintf('jti.%s', $token->getHeader('jti')));
    }

    /**
     * Retrieve a user by the given credentials.
     *
     * @param  array  $credentials
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByCredentials(array $credentials)
    {
        if (empty($credentials)) {
            return null;
        }

        return User::where($this->username(), $credentials[$this->username()])->first();
    }

    /**
     * Retrieve a user by the given JWT.
     *
     * @param \Lcobucci\JWT\Token $token
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByJWT(\Lcobucci\JWT\Token $token)
    {
        if (empty($token)) {
            return null;
        }

        return User::where('id', $token->getClaim('sub'))->where($this->username(), $token->getClaim($this->username()))->first();
    }

    /**
     * Determine if the user matches the credentials.
     *
     * @param  mixed  $user
     * @param  array  $credentials
     * @return bool
     */
    protected function hasValidCredentials($user, $credentials)
    {
        return !is_null($user) && $this->validateCredentials($user, $credentials);
    }

    /**
     * Validate a user against the given credentials.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  array  $credentials
     * @return bool
     */
    public function validateCredentials(User $user, array $credentials)
    {
        $plain = $credentials['password'];

        return Hash::check($plain, $user->getAuthPassword());
    }

    /**
     * Log a user into the application.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  bool  $remember
     * @return void
     */
    public function login(User $user, $remember = false)
    {
        // If the user should be permanently "remembered" by the application we will
        // queue a permanent cookie that contains the encrypted copy of the user
        // identifier. We will then decrypt this later to retrieve the users.
        if ($remember) {
            //$this->ensureRememberTokenIsSet($user);

            //$this->queueRecallerCookie($user);
        }

        $this->fireLoginEvent();

        $this->setUser($user);

        $this->createToken($user);
    }

    public function fireAttemptEvent()
    {
        Event::fire(new \JWTAuth\Events\Attempt);
    }

    public function fireFailedEvent()
    {
        Event::fire(new \JWTAuth\Events\Fail);
    }

    public function fireLoginEvent()
    {
        Event::fire(new \JWTAuth\Events\Login);
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function config($name)
    {
        return config(sprintf('jwt.%s', $name));
    }

    /**
     * @return string
     */
    public function username()
    {
        return $this->_username;
    }

    /**
     * @param $username
     * @return $this
     */
    public function setUsername($username)
    {
        $this->_username = $username;
        return $this;
    }
}