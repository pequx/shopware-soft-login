<?php

namespace SoftLogin\Models;

use Shopware\Components\Model\ModelEntity;
use Doctrine\ORM\Mapping AS ORM;
use Shopware\Models\Category\Category;
use Shopware\Models\Customer\Customer;

/**
 * @ORM\Entity
 * @ORM\Table(name="s_plugin_soft_login")
 * @ORM\Entity(repositoryClass="Repository")
 */
class SoftLogin extends ModelEntity
{
    /**
     * @var integer $id
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(name="id", type="integer", nullable=false)
     */
    private $id;

    /**
     * @var integer $customerId
     * @ORM\Column(name="customer_id", type="integer")
     */
    protected $customerId;

    /**
     * @var Customer $customer
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Customer\Customer")
     * @ORM\JoinColumn(name="customer_id", referencedColumnName="id")
     */
    protected $customer;

    /**
     * @var string $loginHash
     *
     * @ORM\Column(name="login_hash", type="text", nullable=true)
     */
    private $loginHash;

    /**
     * @var \DateTime $firstLogin
     * @ORM\Column(name="first_login", type="datetime", nullable=true)
     */
    private $firstLogin;

    /**
     * @var \DateTime $updatedAt
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @var integer $isActive
     * @ORM\Column(name="is_active", type="boolean")
     */
    private $isActive;

    /**
     * @var \DateTime $lastLogin
     *
     * @ORM\Column(name="last_login", type="datetime", nullable=true)
     */
    private $lastLogin;

    /**
     * @var
     * @ORM\Column(name="category_id", type="integer")
     */
    protected $categoryId;

    /**
     * @var Category $category
     *
     * @ORM\ManyToOne(
     *     targetEntity="Shopware\Models\Category\Category"
     * )
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id")
     */
    protected $category;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return Customer
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @param mixed $customer
     */
    public function setCustomer($customer)
    {
        $this->customer = $customer;
    }

    /**
     * @return mixed
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param mixed $category
     */
    public function setCategory($category)
    {
        $this->category = $category;
    }

    /**
     * @return string
     */
    public function getLoginHash()
    {
        return $this->loginHash;
    }

    /**
     * @param $loginHash
     * @return $this
     */
    public function setLoginHash($loginHash)
    {
        $this->loginHash = $loginHash;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getFirstLogin()
    {
        return $this->firstLogin;
    }

    /**
     * @param $firstLogin
     */
    public function setFirstLogin($firstLogin)
    {
        $this->firstLogin = $firstLogin;
    }

    /**
     * @return \DateTime
     */
    public function getLastLogin()
    {
        return $this->lastLogin;
    }

    /**
     * @param $lastLogin
     * @return $this
     */
    public function setLastLogin($lastLogin)
    {
        $this->lastLogin = $lastLogin;
        return $this;
    }

    /**
     * @param int $active
     */
    public function setActive($active)
    {
        $this->isActive = $active;
    }

    /**
     * @param int $active
     */
    public function setIsActive($active)
    {
        $this->isActive = $active;
    }

    /**
     * @return int
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * @return int
     */
    public function getActive()
    {
        return $this->isActive;
    }
}
