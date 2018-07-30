<?php

use Shopware\Models\Customer\Customer;
use SoftLogin\Models\SoftLogin as Model;

class SoftLoginTest extends Enlight_Components_Test_TestCase
{
    /**
     * @var \Shopware\Components\Model\ModelManager
     */
    protected $modelManager;

    /**
     * @var \SoftLogin\Models\Repository
     */
    protected $repository;

    /**
     * Array representation of the model
     * @var array
     */
    protected $testData;

    /**
     * @var AdminTest
     */
    protected $adminTest;

    /**
     * @var Customer
     */
    protected $customer;

    /**
     * @var \Shopware\Models\Category\Category
     */
    protected $category;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->modelManager = Shopware()->Models();
        $this->repository = Shopware()->Models()->getRepository(
            \SoftLogin\Models\SoftLogin::class
        );
        $this->adminTest = new AdminTest();
        $this->adminTest->setUp();
        $this->customer = $this->adminTest->customerModel;
        $this->category = $this->adminTest->getCategory();

        $this->testData = [
            'customer' => $this->customer,
            'loginHash' => 'JDJ5JDEwJHhrUFYzZWxRNnREMVE1dlZud1V2OE84T2RseWZUVjc2YmF0cGNKYjFGUFAzcjJFRENSVEtx',
            'firstLogin' => new DateTime('now'),
            'active' => 1,
            'lastLogin' => new DateTime('now'),
            'category' => $this->category,
        ];
    }

    protected function tearDown()
    {
        $this->adminTest->deleteCustomer($this->customer);
        $this->adminTest->tearDown();
        parent::tearDown();
    }

    public function testGetterAndSetter()
    {
        $softLogin = new Model();

        foreach ($this->testData as $field => $value) {
            $setMethod = 'set' . ucfirst($field);

            if (substr($field, 0, 2) === 'is') {
                $getMethod = $field;
            } else {
                $getMethod = 'get' . ucfirst($field);
            }

            $softLogin->$setMethod($value);

            $this->assertEquals($softLogin->$getMethod(), $value);
        }
    }

    /**
     * Testcase
     */
    public function testFromArrayWorks()
    {
        $softLogin = new Model();
        $softLogin->fromArray($this->testData);

        foreach ($this->testData as $fieldName => $value) {
            if (substr($fieldName, 0, 2) === 'is') {
                $getMethod = $fieldName;
            } else {
                $getMethod = 'get' . ucfirst($fieldName);
            }

            $this->assertEquals($softLogin->$getMethod(), $value);
        }
    }

    /**
     * Testcase
     */
    public function testShouldBePersisted()
    {
        $oldUser = $this->testData['customer'];
        $newUser = $this->adminTest->createCustomer();
        $this->testData['customer'] = $newUser;

        $softLogin = new Model();
        $softLogin->fromArray($this->testData);

        $this->modelManager->persist($softLogin);
        try {
            $this->modelManager->flush();
        } catch (\Doctrine\ORM\OptimisticLockException $exception) {
            return false;
        }

        $softLoginId = $softLogin->getId();

        $this->modelManager->detach($softLogin);
        unset($softLogin);

        $softLogin = $this->repository->find($softLoginId);

        foreach ($this->testData as $fieldName => $value) {
            if (substr($fieldName, 0, 2) === 'is') {
                $getMethod = $fieldName;
            } else {
                $getMethod = 'get' . ucfirst($fieldName);
            }

            $this->assertEquals($softLogin->$getMethod(), $value);
        }

        $this->testData['customer'] = $oldUser;
        $this->adminTest->deleteCustomer($newUser);
        $this->adminTest->deleteCustomer($oldUser);
        $this->adminTest->deleteSoftLogin($softLogin);
    }
}
