<?php

use Shopware\Models\Customer\Customer;
use SoftLogin\Models\SoftLogin as Model;

class AdminTest extends PHPUnit\Framework\TestCase
{
    /**
     * @var \SoftLogin\Components\Admin
     */
    private $component;

    /**
     * @var Shopware\Components\Model\ModelManager
     */
    protected $modelManager;

    /**
     * @var \SoftLogin\Models\Repository
     */
    protected $modelRepository;

    /**
     * @var \Shopware\Models\Customer\Repository;
     */
    protected $customerRepository;

    /**
     * @var Enlight_Components_Session_Namespace
     */
    private $session;

    /**
     * @var Enlight_Controller_Front
     */
    private $front;

    /**
     * @var Customer
     */
    public $customerModel;

    /**
     * @var sAdmin;
     */
    protected $sAdmin;

    /**
     * @var \SoftLogin\Models\SoftLogin
     */
    protected $softLoginModel;

    public function setUp()
    {
        parent::setUp();

        Shopware()->Container()->get('models')->clear();
        Shopware()->Front()->setRequest(new Enlight_Controller_Request_RequestHttp());

        $this->component = Shopware()->Container()->get('soft_login.admin_service');
        $this->modelManager = Shopware()->Models();
        $this->modelRepository = Shopware()->Models()->getRepository('SoftLogin\Models\SoftLogin');
        $this->customerRepository = Shopware()->Models()->getRepository('Shopware\Models\Customer\Customer');
        $this->session = Shopware()->Session();
        $this->front = Shopware()->Front();
        $this->session->offsetSet('sessionId', null);
        $this->customerModel = $this->createCustomer();
        $this->softLoginModel = $this->createSoftLogin($this->customerModel);
        $this->sAdmin = Shopware()->Modules()->Admin();
    }

    /**
     * Tear down
     */
    public function tearDown()
    {
        $this->deleteCustomer($this->customerModel);
        $this->deleteSoftLogin($this->softLoginModel);
        Shopware()->Container()->get('models')->clear();
        parent::tearDown();
    }

    /**
     * @covers \SoftLogin\Components\Admin::checkHash()
     */
    public function testCheckHash()
    {
        $request = $this->front->Request()->setRequestUri('?'.$this->softLoginModel->getLoginHash());
        $result = $this->component->checkHash($request);
        $this->assertEquals(
            $this->softLoginModel->getLoginHash(),
            $result
        );
    }

    /**
     * @covers \SoftLogin\Components\Admin::checkHash()
     */
    public function testCheckSoftLogin()
    {
        $request = $this->front->Request()->setParam('softLoginHash', $this->softLoginModel->getLoginHash());
        $result = $this->component->checkSoftLogin($request);
        $this->assertEquals($this->customerModel, $result);
    }

    /**
     * @covers \SoftLogin\Components\Admin::setPersistentSessionOffsets()
     */
    public function testSetPersistentSessionOffsets()
    {
        $request = $this->front->Request()->setPost('persistent', true);
        $result = [
            'return' => $this->component->setPersistentSessionOffsets($request),
            'session' => $this->session->offsetGet('persistent')
        ];

        $this->assertEquals(true, $result['return']);
        $this->assertEquals(true, $result['session']);

        $this->unsetPersistent();
    }

    /**
     * @covers \SoftLogin\Components\Admin::setUserHash()
     */
    public function testUserHashPasswordRecovery()
    {
        $request = $this->front->Request()->setPost('email', $this->customerModel->getEmail());
        $result = $this->component->setUserHash($request);

        $this->assertEquals(true, $result);
    }

    /**
     * @covers \SoftLogin\Components\Admin::setUserHash()
     */
    public function testUserHashRegistration()
    {
        $this->session->offsetSet('sUserId', $this->customerModel->getId());
        $result = $this->component->setUserHash();

        $this->assertEquals(true, $result);
    }

    /**
     * @covers \SoftLogin\Components\Admin::checkUser()
     */
    public function testCheckUserIsLoggedIn()
    {
        $this->logOutUser();
        $this->logInUser();
        $result = $this->component->checkUser();
        $this->assertEquals(true, $result);
    }

    /**
     * @covers \SoftLogin\Components\Admin::checkUser()
     */
    public function testCheckUserIsNotLoggedIn()
    {
        $this->logOutUser();
        $result = $this->component->checkUser();
        $this->assertEquals(false, $result);
    }

    /**
     * @covers \SoftLogin\Components\Admin::softLogin()
     */
    public function testSoftLogin()
    {
        $this->logInUser();
        $request = $this->front->Request();
        $result = $this->component->softLogin($request);

        $this->assertEquals(true, $result);
    }

    /**
     * @covers \SoftLogin\Components\Admin::persistentLogin()
     */
    public function testPersistentLogin()
    {
        $this->unsetPersistent();
        $this->setPersistent();

        $result = $this->component->persistentLogin();
        $this->assertEquals(true, $result);
    }

    /**
     * @covers \SoftLogin\Components\Admin::setLoginSessionOffsets()
     */
    public function testSetLoginSessionOffsets()
    {
        $result = $this->component->setLoginSessionOffsets($this->customerModel);
        $this->assertEquals(true, $result);
    }

    /**
     * Helper methods
     */

    public function setPersistent()
    {
        $this->session->offsetSet('persistent', true);
    }

    public function unsetPersistent()
    {
        $this->session->offsetUnset('persistent');
    }

    public function logInUser()
    {
        $this->front->Request()->setPost('email', $this->customerModel->getEmail());
        $this->front->Request()->setPost('password', 'fooobar');
        try {
            $this->sAdmin->sLogin();
        } catch (Exception $exception) {
            return false;
        }
    }

    public function logOutUser()
    {
        $this->sAdmin->logout();
    }

    /**
     * Create dummy customer entity
     *
     * @return \Shopware\Models\Customer\Customer
     */
    public function createCustomer()
    {
        $date = new DateTime();
        $date->modify('-8 days');
        $lastLogin = $date->format(DateTime::ISO8601);

        $birthday = DateTime::createFromFormat('Y-m-d', '1986-12-20')->format(DateTime::ISO8601);

        $randomPrefix = rand(1, 10000);

        $testData = [
            'password' => 'fooobar',
            'email' => 'test'.$randomPrefix.'@soft_login.com',
            'customernumber' => $randomPrefix,
            'lastlogin' => $lastLogin,

            'salutation' => 'mr',
            'firstname' => 'Max',
            'lastname' => 'Mustermann',
            'birthday' => $birthday,

            'billing' => [
                'salutation' => 'mr',
                'firstname' => 'Max',
                'lastname' => 'Mustermann',
                'attribute' => [
                    'text1' => 'Freitext1',
                    'text2' => 'Freitext2',
                ],
                'zipcode' => '12345',
                'city' => 'Musterhausen',
                'street' => 'Kraftweg, 22',
                'country' => '2',
                'additionalAddressLine1' => 'IT-Department',
                'additionalAddressLine2' => 'Second Floor',
            ],

            'shipping' => [
                'salutation' => 'Mr',
                'company' => 'Widgets Inc.',
                'firstname' => 'Max',
                'lastname' => 'Mustermann',
                'zipcode' => '98765',
                'city' => 'Musterhausen',
                'street' => 'Merkel Strasse, 10',
                'country' => '4',
                'attribute' => [
                    'text1' => 'Freitext1',
                    'text2' => 'Freitext2',
                ],
                'additionalAddressLine1' => 'Sales-Department',
                'additionalAddressLine2' => 'Third Floor',
            ],

            'debit' => [
                'account' => 'Fake Account',
                'bankCode' => $randomPrefix . '55555555',
                'bankName' => 'Fake Bank',
                'accountHolder' => 'Max Mustermann',
            ],
        ];

        $customerResource = new \Shopware\Components\Api\Resource\Customer();
        $customerResource->setManager(Shopware()->Models());

        return $customerResource->create($testData);
    }

    /**
     * Deletes all dummy customer entity
     */
    public function deleteCustomer(Customer $customer)
    {
        $billingId = Shopware()->Db()->fetchOne('SELECT id FROM s_user_billingaddress WHERE userID = ?', [$customer->getId()]);
        $shippingId = Shopware()->Db()->fetchOne('SELECT id FROM s_user_shippingaddress WHERE userID = ?', [$customer->getId()]);

        if ($billingId) {
            Shopware()->Db()->delete('s_user_billingaddress_attributes', 'billingID = ' . $billingId);
            Shopware()->Db()->delete('s_user_billingaddress', 'id = ' . $billingId);
        }
        if ($shippingId) {
            Shopware()->Db()->delete('s_user_shippingaddress_attributes', 'shippingID = ' . $shippingId);
            Shopware()->Db()->delete('s_user_shippingaddress', 'id = ' . $shippingId);
        }
        Shopware()->Db()->delete('s_core_payment_data', 'user_id = ' . $customer->getId());
        Shopware()->Db()->delete('s_user_attributes', 'userID = ' . $customer->getId());
        Shopware()->Db()->delete('s_user', 'id = ' . $customer->getId());
    }

    /**
     * @param Customer $customer
     * @return \SoftLogin\Models\SoftLogin|boolean
     */
    public function createSoftLogin(Customer $customer)
    {
        $id = $customer->getId();
        $softLogin = $this->modelRepository->generateAccountHash($id);
        if (!$softLogin) { return false; }
        return $this->getSoftLogin($customer);
    }

    /**
     * @return \Shopware\Models\Category\Category|boolean
     */
    public function getCategory()
    {
        try {
            $category = Shopware()->Models()->createQueryBuilder()
                ->select('category')
                ->from(\Shopware\Models\Category\Category::class, 'category')
                ->where('category.id =:id')
                ->setParameter('id', $this->softLoginModel->getCategory())
                ->getQuery()
                ->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_OBJECT);
            return $category;
        } catch (\Doctrine\ORM\NonUniqueResultException $exception) {
            return false;
        }
    }

    /**
     * @param Customer $customer
     * @return mixed
     */
    public function getSoftLogin(Customer $customer)
    {
        try {
            $softLogin = $this->modelManager->createQueryBuilder()
                ->select(['soft_login'])
                ->from(\SoftLogin\Models\SoftLogin::class, 'soft_login')
                ->where('soft_login.customerId = :userId')
                ->setParameter('userId', $customer->getId())
                ->getQuery()
                ->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_OBJECT);
        } catch (\Doctrine\ORM\NonUniqueResultException $exception) {
            return false;
        }
        return $softLogin;
    }

    /**
     * @param Model $softLogin
     */
    public function deleteSoftLogin(\SoftLogin\Models\SoftLogin $softLogin)
    {
        Shopware()->Db()->delete('s_plugin_soft_login', 'id = '.$softLogin->getId());
    }
}
