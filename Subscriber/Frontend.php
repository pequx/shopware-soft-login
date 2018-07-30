<?php

namespace SoftLogin\Subscriber;

use Enlight\Event\SubscriberInterface;
use \SoftLogin\Components\Admin;

class Frontend implements SubscriberInterface
{
    /**
     * @var array
     */
    private $config;

    /**
     * Allowed controllers for event listeners.
     *
     * @var array
     */
    private $controllerWhiteList = [
        'softLogin' => ['account', 'detail', 'index', 'listing'],
        'persistentLogin' => ['account'],
        'hashGeneration' => ['account', 'register'],
    ];

    /**
     * Soft login admin service.
     *
     * @var Admin
     */
    protected $admin;

    /**
     * Frontend constructor.
     */
    public function __construct() {
        try {
            $this->admin = Shopware()->Container()->get('soft_login.admin_service');
            $this->config = Shopware()->Container()
                ->get('shopware.plugin.cached_config_reader')
                ->getByPluginName('SoftLogin');
        } catch (\Exception $exception) {
            return;
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return array(
            'Enlight_Controller_Action_PreDispatch_Frontend' => 'onPreDispatchFrontend',
            'Enlight_Controller_Action_PostDispatch_Frontend_Account' => 'onPostDispatchFrontendAccount',
            'Enlight_Controller_Action_PostDispatch_Frontend_Register' => 'onPostDispatchFrontendRegister',
        );
    }

    /**
     * Handler for soft login.
     *
     * @param \Enlight_Event_EventArgs $args
     */
    public function onPreDispatchFrontend(\Enlight_Event_EventArgs $args)
    {
        /** @var $controller \Enlight_Controller_Action */
        $controller = $args->get('subject');
        /** @var \Enlight_Controller_Request_Request $request */
        $request = $controller->Request();

        $isPasswordRecovery = $request->getActionName() === 'password';

        if ($isPasswordRecovery) {
            $this->admin->setUserHash($request);
        }

        $isControllerAllowed = $this->isControllerAllowed('softLogin', $request);
        $hash = $this->admin->checkHash($request);
        $isLoggedIn = $this->admin->checkUser();

        if (!$isControllerAllowed || !$hash || $isLoggedIn ) {
            return;
        }

        $request->setParam('softLoginHash', $hash);
        $controller->forward('index', 'softLogin', 'widgets');
    }

    /**
     * Handler for persistent login.
     *
     * @param \Enlight_Event_EventArgs $args
     */
    public function onPostDispatchFrontendAccount (\Enlight_Event_EventArgs $args)
    {
        /** @var $controller \Enlight_Controller_Action */
        $controller = $args->get('subject');
        /** @var \Enlight_Controller_Request_Request $request */
        $request = $controller->Request();

        $isControllerAllowed = $this->isControllerAllowed('persistentLogin', $request);
        $action = $request->getActionName();
        $isActionAllowed = $action === 'login' || $action === 'savePassword' || $action === 'saveEmail';
        $isPersistentLogin = $this->config['persistentLogin'] === true &&
            (bool)$request->get('persistent') === true;

        if (!$isControllerAllowed || !$isActionAllowed || !$isPersistentLogin) {
            return;
        }

        switch ($action) {
            case 'login':
                if (!$session = $this->admin->setPersistentSessionOffsets($request)) {
                    return;
                }
                $this->admin->persistentLogin();
                break;
            case 'savePassword' || 'saveEmail':
                $this->admin->setUserHash();
                break;
        }
    }

    /**
     * Handler fir the soft login hash generation after user registration.
     *
     * @param \Enlight_Event_EventArgs $args
     */
    public function onPostDispatchFrontendRegister(\Enlight_Event_EventArgs $args)
    {
        /** @var \Enlight_Controller_Action $controller */
        $controller = $args->get('subject');
        /** @var \Enlight_Controller_Request_Request $request */
        $request = $controller->Request();

        if ($request->getActionName() === 'saveRegister') {
            $this->admin->setUserHash();
        }
    }

    /**
     * Checks if the request entity controller is allowed for a given action.
     *
     * @param string $action
     * @param \Enlight_Controller_Request_Request $request
     * @return boolean
     */
    protected function isControllerAllowed($action, \Enlight_Controller_Request_Request $request): bool
    {
        return \in_array(
            $request->getControllerName(),
            $this->controllerWhiteList[$action],
            false
        );
    }
}
