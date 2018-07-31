<?php

namespace SoftLogin\Subscriber;

use Enlight\Event\SubscriberInterface;

class ExtendCustomer implements SubscriberInterface
{
    /**
     * @var string
     */
    private $pluginDirectory;

    /**
     * @param $pluginDirectory
     * @param \Enlight_Template_Manager $templateManager
     */
    public function __construct
    (
        $pluginDirectory
    )
    {
        $this->pluginDirectory = $pluginDirectory;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatchSecure_Backend_Customer' => 'onCustomerDispatch',
        ];
    }

    /**
     * @param \Enlight_Event_EventArgs $args
     */
    public function onCustomerDispatch(\Enlight_Event_EventArgs $args)
    {
        /** @var \Shopware_Controllers_Backend_Customer $controller */
        $controller = $args->get('subject');
        $view = $controller->View();
        $request = $controller->Request();

        $view->addTemplateDir($this->pluginDirectory . '/Resources/views');

        if ($request->getActionName() == 'index') {
            $view->extendsTemplate('backend/soft_login/app.js');
        }
        if ($request->getActionName() == 'load') {
            $view->extendsTemplate('backend/soft_login/view/detail/window.js');
        }
    }
}
