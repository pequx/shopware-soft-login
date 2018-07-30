<?php

namespace SoftLogin\Subscriber;

use Enlight\Event\SubscriberInterface;

class Backend implements SubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
//            'Enlight_Controller_Action_PostDispatchSecure_Backend_Customer' => 'onBackendDispatch',
        ];
    }

    public function onBackendDispatch(\Enlight_Event_EventArgs $args)
    {
        /** @var \Shopware_Controllers_Backend_Customer $controller */
        $controller = $args->get('subject');
        $view = $controller->View();
        $request = $controller->Request();
    }
}