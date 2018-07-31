<?php

namespace SoftLogin\Subscriber;

use Enlight\Event\SubscriberInterface;
use SoftLogin\Models\Repository;

class Backend implements SubscriberInterface
{
    /**
     * Soft login repository.
     *
     * @var Repository
     */
    protected $repository;

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatch_Backend_Customer' => 'onBackendDispatch',
        ];
    }

    /**
     * Handler for hash regeneration after password reset in the backend Customer module.
     *
     * @param \Enlight_Event_EventArgs $args
     */
    public function onBackendDispatch(\Enlight_Event_EventArgs $args)
    {
        /** @var \Shopware_Controllers_Backend_Customer $controller */
        $controller = $args->get('subject');
        $request = $controller->Request();

        $isSaveAction = $request->getActionName() === 'save' && $request->isDispatched() === true;
        $isNewPassword = count($request->getParam('newPassword')) > 0;
        $customerId = (int) $request->getParam('customerID');
        if (!$isSaveAction || !$isNewPassword || $customerId < 0) { return; }

        /**
         * @todo: figure out which customer group should be handled if we add
         * a customer in the backend
         */
        try {
            /**
             * We cannot instantiate here the admin service, so we will use repository.
             * It utilises session components and other thingies native to front-end.
             */
            $this->repository = Shopware()->Models()->getRepository('SoftLogin\Models\SoftLogin');
            $this->config = Shopware()->Container()
                ->get('shopware.plugin.cached_config_reader')
                ->getByPluginName('SoftLogin');
        } catch (\Exception $exception) {
            return;
        }

        $result = $this->repository->regenerateHash($customerId);
        if ($result['insertCount'] === $result['deleteCount']) { return; }
    }
}