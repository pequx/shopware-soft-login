<?php

use Shopware\Components\CSRFWhitelistAware;
use SoftLogin\Components\Admin;

/**
 * Frontend controller
 */
class Shopware_Controllers_Widgets_SoftLogin extends Enlight_Controller_Action implements CSRFWhitelistAware
{
    /**
     * @var Enlight_Components_Session_Namespace
     */
    private $session;

    /**
     * Soft login admin service.
     *
     * @var Admin;
     */
    protected $admin;

    /**
     * The snippet manager.
     *
     * @var Shopware_Components_Snippet_Manager
     */
    private $snippetManager;

    /**
     * @var array
     */
    private $config;

    /**
     * @return array
     */
    public function getWhitelistedCSRFActions()
    {
        return [
            'redirect',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function preDispatch()
    {
        $this->session = Shopware()->Session();
        $this->admin = Shopware()->Container()->get('soft_login.admin_service');
        $this->snippetManager = Shopware()->Snippets();
        $this->config = Shopware()->Container()
            ->get('shopware.plugin.cached_config_reader')
            ->getByPluginName('SoftLogin');
//        $this->View()->addTemplateDir(
//            $this->container->getParameter('soft_login.view_dir')
//        );

        parent::preDispatch();
    }

    /**
     * Action for soft login.
     */
    public function indexAction()
    {
        $this->View()->assign('name', 'softLogin');

        $isSoftLoginEnabled = $this->config['softLogin'];
        $sessionOffsetEmail = $this->session->offsetGet('sUserMail');
        $sessionOffsetPassword = $this->session->offsetGet('sUserPassword');

        if (!$isSoftLoginEnabled || $sessionOffsetEmail || $sessionOffsetPassword) {
            return;
        }

        $user = $this->admin->checkSoftLogin($this->Request());
        if (!$user) {
            return;
        }
        $sessionOffsets = $this->admin->setLoginSessionOffsets($user);
        if (!$sessionOffsets) {
            return;
        }

        $softLogin = $this->admin->softLogin($this->Request());
        if ($softLogin) {
            $isSoftLoginPersistent = $this->config['persistentLogin'] && $this->config['softLoginIsPersistent'];
            if ($isSoftLoginPersistent) {
                $this->Request()->setPost('persistent', true);

                $isSoftLoginPersistent = $this->admin->setPersistentSessionOffsets($this->Request());
                if ($isSoftLoginPersistent) {
                    $this->admin->persistentLogin();
                }
            }
            $this->redirectSoftLogin();
        }
    }

    /**
     * Soft login redirection with category reference.
     */
    protected function redirectSoftLogin()
    {
        $isCategoryRedirection = $this->config['softLoginCategoryRedirection'];

        if ($isCategoryRedirection) {
            $categoryId = $this->session->offsetGet('categoryIdRedirection');
            $isRootCategory = $categoryId === 1;
            if (!$categoryId || $isRootCategory) {
                $categoryId = $this->config['softLoginCategoryRedirectionDefault'];
            }

            $this->redirectSoftLoginCategory($categoryId);
        }

        $url = $this->Front()->Router()
            ->assemble(['module' => 'frontend', 'controller' => 'account']);
        $this->redirect($url);
    }

    /**
     * Redirect soft login after success to a given category id.
     *
     * @param integer $categoryId
     */
    protected function redirectSoftLoginCategory($categoryId)
    {
        $this->request->setParam('sCategory', (int)$categoryId);
        $this->forward('index', 'listing', 'frontend');
    }
}
