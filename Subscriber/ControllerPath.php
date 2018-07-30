<?php

namespace SoftLogin\Subscriber;

use Enlight\Event\SubscriberInterface;
use Shopware\Components\Form\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ControllerPath implements SubscriberInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param $pluginDir
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Dispatcher_ControllerPath_Widgets_SoftLogin' => 'onGetControllerPathFrontend',
            'Enlight_Controller_Dispatcher_ControllerPath_Backend_SoftLogin' => 'onGetControllerPathBackend',
//            'Enlight_Controller_Dispatcher_ControllerPath_Api_SoftLogin' => 'getApiControllerSoftLogin'
        ];
    }

    /**
     * Register the frontend controller
     *
     * @param   \Enlight_Event_EventArgs $args
     * @return  string
     * @Enlight\Event Enlight_Controller_Dispatcher_ControllerPath_Frontend_PhagPimposzek
     */
    public function onGetControllerPathFrontend(\Enlight_Event_EventArgs $args)
    {
        $this->extendTemplate();
        return __DIR__ . '/../Controllers/Widgets/SoftLogin.php';
    }

    /**
     * Register the backend controller
     *
     * @param   \Enlight_Event_EventArgs $args
     * @return  string
     * @Enlight\Event Enlight_Controller_Dispatcher_ControllerPath_Backend_SoftLogin
     */
    public function onGetControllerPathBackend(\Enlight_Event_EventArgs $args)
    {
        $this->extendTemplate();
        return __DIR__ . '/../Controllers/Backend/SoftLogin.php';
    }

//    public function getApiControllerSoftLogin(\Enlight_Event_EventArgs $args)
//    {
//        return __DIR__ . '/../Controllers/Api/SoftLogin.php';
//    }

    /**
     * Extends the template bootstrap with plugin resources.
     */
    private function extendTemplate()
    {
        return $this->container->get('template')->addTemplateDir(__DIR__ . '/../Resources/views/');
    }
}
