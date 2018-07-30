<?php

namespace SoftLogin\Components;

use Shopware\Components\CustomerStream\CookieSubscriber;
use Enlight_Components_Session_Namespace as Session;
use Shopware\Models\Customer\Customer;
use SoftLogin\Models\Repository;
use SoftLogin\Models\SoftLogin;

/**
 * Class Admin
 * @package SoftLogin\Components
 */
class Admin
{
    /**
     * @var \sAdmin
     */
    protected $sAdmin;

    /**
     * @var Repository
     */
    private $modelRepository;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var array
     */
    private $config;

    /**
     * @var CookieSubscriber
     */
    private $cookieSubscriber;

    /**
     * Admin constructor.
     */
    public function __construct() {
        $this->sAdmin = Shopware()->Modules()->Admin();
        $this->session = Shopware()->Session();
        $this->modelRepository = Shopware()->Models()
             ->getRepository(SoftLogin::class);
        try {
            $this->config = Shopware()->Container()
                ->get('shopware.plugin.cached_config_reader')
                ->getByPluginName('SoftLogin');
            $this->cookieSubscriber = Shopware()->Container()->get(
                'shopware.customer_stream.cookie_subscriber'
            );
        } catch (\Exception $exception) { return; }
    }

    /**
     * Checks request for hash  matching encoding pattern.
     *
     * @param \Enlight_Controller_Request_Request $request
     * @return string|boolean
     */
    public function checkHash(\Enlight_Controller_Request_Request $request) {
        $pathInfo = $request->getRequestUri();
        preg_match(
            '/[A-Za-z0-9+\/=]{80}/', //first group match of base64 encoded hash
            $pathInfo, $match
        );

        if (count($match) === 0) { return false; }

        return $match[0];
    }

    /**
     * Checks if soft login is possible over request with valid hash match.
     *
     * @param \Enlight_Controller_Request_Request $request
     * @return Customer|boolean
     */
    public function checkSoftLogin(\Enlight_Controller_Request_Request $request)
    {
        $hash = $request->getParam('softLoginHash');
        $user = $this->modelRepository->getUser($hash);
        if (!$hash && !$user) {
            return false;
        }

        return $user;
    }

    /**
     * Setter method of persistent login session.
     *
     * @param \Enlight_Controller_Request_Request $request
     * @return boolean
     */
    public function setPersistentSessionOffsets(\Enlight_Controller_Request_Request $request): bool
    {
        $persistent = $request->getPost('persistent');
        if (!$persistent) { return false; }

        $this->session->offsetSet('persistent', true);
        return true;
    }

    /**
     * Setter method of soft login user hash.
     *
     * @param \Enlight_Controller_Request_RequestHttp $request
     * @return bool
     */
    public function setUserHash(\Enlight_Controller_Request_RequestHttp $request = null): bool
    {
        if ($request) {
           if (!$email = $request->getPost('email')) { return false; }
           if (!$userId = $this->modelRepository->getUserId($email)) { return false; }

           return $this->modelRepository->generateAccountHash($userId);
        } //password recovery

        if (!$userId = $this->session->offsetGet('sUserId')) { return false; } //registration

        return $this->modelRepository->generateAccountHash($userId);
    }

    /**
     * Checks if user is correctly logged in. Also checks session timeout.
     *
     * @return boolean
     */
    public function checkUser(): bool
    {
        $login = $this->sAdmin->sCheckUser();
        if ($login) { return true; }

        return false;
    }

    /**
     * Logs in a soft login user.
     *
     * @return boolean
     */
    public function softLogin(\Enlight_Controller_Request_Request $request): bool
    {
        $request->setPost([
            'email' => $this->session->offsetGet('sUserMail'),
            'passwordMD5' => $this->session->offsetGet('sUserPassword'),
        ]);

        try {
            $login = $this->sAdmin->sLogin(true);
            if ($login['sErrorFlag'] || $login['sErrorMessages'] || !$login) { return false;}
        } catch (\Exception $exception) { return false; }

        $userId = (int)$this->session->offsetGet('sUserId'); //user id is set after login
        if (!$userId) { return false; }

        return $this->modelRepository->softLogin($userId);
    }

    /**
     * Persistent login.
     *
     * @return boolean
     */
    public function persistentLogin(): bool
    {
        $isPersistent = $this->session->offsetGet('persistent');
        if ($isPersistent) {
            try {
                $expiration = $this->config['persistentSessionTimeout'];
                if ($expiration <= 0) { return false; }
                $this->session->setExpirationSeconds($expiration);
                $cookie = $this->setCookie();
                if (!$cookie) { return false; }
            } catch (\Zend_Session_Exception $session_Exception) { return false; }
        }
        return true;
    }

    /**
     * Set session cookie in frontend.
     *
     * @return boolean
     */
    protected function setCookie(): bool
    {
        try {
            $config = Shopware()->Container()->get('config');
            if (!$config->get('useSltCookie')) { return false; }
        } catch (\Exception $exception) { return false; }

        /** @var \Enlight_Controller_Front $controller */
        $controller = Shopware()->Front();
        $response = $controller->Response();

        $cookies = $response->getCookies();
        $cookieData = null;
        foreach ($cookies as $name => $cookie) {
            if (substr($name, 0, 3) === 'slt') {
                $cookieData = [
                    'name' => $cookie['name'],
                    'value' => $cookie['value'],
                    'expire' => $cookie['expire'],
                    'path' => $cookie['path'],
                ];
            }
        }

        if (!$cookieData) { return false; }

        $cookieData['expire'] = time() + $this->config['persistentSessionTimeout'];

        $cookie = $response->setCookie(
            $cookieData['name'],
            $cookieData['value'],
            $cookieData['expire'],
            $cookieData['path']
        );

        if(!$cookie) { return false; }

        return true;
    }


    /**
     * Setter for the soft login session offsets.
     *
     * @param Customer $user
     * @return boolean
     */
    public function setLoginSessionOffsets(Customer $user): bool
    {
        $category = $this->modelRepository->getCategoryRedirection($user);
        if(!$category) { return false; }

        $this->session->offsetSet('sUserMail', $user->getEmail());
        $this->session->offsetSet('sUserPassword', $user->getPassword());
        $this->session->offsetSet('categoryIdRedirection', (int)$category->getId());

        if (!$this->session->offsetGet('sUserMail')  &&
            !$this->session->offsetGet('sUserPassword') &&
            !$this->session->offsetGet('categoryIdRedirection')) {
            return false;
        }

        return true;
    }
}
