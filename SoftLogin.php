<?php

namespace SoftLogin;

use Shopware\Components\Console\Application;
use Shopware\Components\Plugin;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Doctrine\ORM\Tools\SchemaTool;
use Shopware\Components\Plugin\Context\InstallContext;
use SoftLogin\Models\SoftLogin as Model;
use Shopware\Components\Plugin\Context\UninstallContext;
use SoftLogin\Commands\RegenerateHashesCommand;

/**
 * Shopware-Plugin SoftLogin.
 */
class SoftLogin extends Plugin
{
//    /**
//     * {@inheritdoc}
//     */
//    public static function getSubscribedEvents()
//    {
//        return [
//            'Enlight_Controller_Action_PostDispatch_Backend_Base' => 'extendExtJS',
//        ];
//    }

    /**
     * {@inheritdoc}
     */
    public function install(InstallContext $installContext)
    {
        $installContext->scheduleClearCache(InstallContext::CACHE_LIST_ALL);

        $this->createSchema();
//        $this->createAttributes();
//        $this->rebuildAttributeModels();
    }

    /**
     * {@inheritdoc}
     */
    public function uninstall(UninstallContext $uninstallContext)
    {
        if (!$uninstallContext->keepUserData()) {
            $this->removeSchema();
        }

        $uninstallContext->scheduleClearCache(InstallContext::CACHE_LIST_ALL);
    }

//    /**
//     * @param \Enlight_Event_EventArgs $eventArgs
//     */
//    public function extendExtJS(\Enlight_Event_EventArgs $eventArgs)
//    {
//        /** @var \Enlight_View_Default $view */
//        $view = $eventArgs->get('subject')->View();
//        $view->addTemplateDir($this->getPath() . '/Resources/views/');
//        $view->extendsTemplate('backend/soft_login/Shopware.attribute.Form.js');
//    }

    /**
    * @param ContainerBuilder $container
    */
    public function build(ContainerBuilder $container)
    {
        $container->setParameter('soft_login.plugin_dir', $this->getPath());
        parent::build($container);
    }

    /**
     * Registers commands.
     *
     * @param Application $application
     */
    public function registerCommands(Application $application)
    {
        $application->add(new RegenerateHashesCommand());
    }

//    /**
//     * Creates attribute for user with soft login.
//     */
//    private function createAttributes()
//    {
//        $crud = Shopware()->Container()->get('shopware_attribute.crud_service');
//        $crud->update('s_user_attributes', 'sl_login_hash', 'text' , [
//            'displayInBackend' => false,
//        ]);
//    }

//    private function rebuildAttributeModels()
//    {
//        $metaDataCache = Shopware()->Models()->getConfiguration()->getMetadataCacheImpl();
//        $metaDataCache->deleteAll();
//        Shopware()->Models()->generateAttributeModels('s_user_attributes');
//    }

    /**
     * Creates database table over attributes on base of doctrine models.
     */
    private function createSchema()
    {
        $modelManager = Shopware()->Models();
        $schemaTool = new SchemaTool($modelManager);
        $schemaTool->updateSchema(
            array(
                $modelManager->getClassMetadata(Model::class)
            ),
            true
        );
    }

    /**
     * Removes schema.
     */
    private function removeSchema()
    {
        $modelManager = $this->container->get('models');
        $schemaTool = new SchemaTool($modelManager);
        $schemaTool->dropSchema(
            array(
                $modelManager->getClassMetadata(Model::class)
            )
        );
    }
}
