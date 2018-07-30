<?php

namespace SoftLogin\Models;

use Doctrine\DBAL\DBALException;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query;
use Shopware\Components\Form\Field\Date;
use Shopware\Components\Model\ModelRepository;
use Shopware\Components\Model\QueryBuilder;
use Shopware\Models\Category\Category;
use Shopware\Models\Customer\Customer;
use Shopware\Components\Password\Manager as Encoder;

class Repository extends ModelRepository
{
    /**
     * @var string
     */
    protected $encoderName;

    /**
     * @var Encoder
     */
    protected $encoder;

    /**
     * Repository constructor.
     * @param EntityManager $em
     * @param Mapping\ClassMetadata $class
     */
    public function __construct(EntityManager $em, Mapping\ClassMetadata $class)
    {
        parent::__construct($em, $class);

        try {
            $this->encoderName = Shopware()->PasswordEncoder()->getDefaultPasswordEncoderName();
            $this->encoder = Shopware()->PasswordEncoder();
        } catch (\Exception $exception) {
            return;
        }
    }

    public function getSoftLoginQueryBuilder(int $id)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder
            ->select('soft_login')
            ->from(SoftLogin::class, 'soft_login')
            ->where('soft_login.id = :id')
            ->setParameter('id', $id);
        return $builder;
    }

    public function getSoftLoginQuery(int $customerId)
    {
        $builder = $this->getSoftLoginQueryBuilder($customerId);
        return $builder->getQuery();
    }

    /**
     * OLD STUFF
     */

    /**
     * Returns user entity associated with soft login over hash.
     *
     * @param $hash
     * @return Customer|boolean
     */
    public function getUser($hash)
    {
       try {
           /** @var SoftLogin $softLogin */
           $softLogin = $this->getEntityManager()->createQueryBuilder()
               ->select(['softLogin'])
               ->from(SoftLogin::class, 'softLogin')
               ->where('softLogin.loginHash = :hash')
               ->setParameter('hash', $hash)
               ->getQuery()
               ->getOneOrNullResult(AbstractQuery::HYDRATE_OBJECT);
       } catch (NonUniqueResultException $exception) {
           return false;
       }

        if (!$softLogin) {
            return false;
        }

        return $this->getCustomer($softLogin->getCustomer()->getId());
    }

    public function getCustomer(int $userId)
    {
        try {
            $user = $this->getEntityManager()->createQueryBuilder()
                ->select(['customer'])
                ->from(\Shopware\Models\Customer\Customer::class, 'customer')
                ->where('customer.id = :userId')
                ->andWhere('customer.active = 1')
                ->andWhere('customer.accountMode != 1')
                ->setParameter('userId', $userId)
                ->getQuery()
                ->getOneOrNullResult(AbstractQuery::HYDRATE_OBJECT);
        } catch (NonUniqueResultException $exception) {
            return false;
        }
        return $user;
    }

    /**
     * Provides valid user ids for hash generation.
     *
     * @return array|boolean
     */
    public function getValidUserIds()
    {
        $connection = $this->getEntityManager()->getConnection();
        try {
            return $connection->executeQuery('
                SELECT customer.id FROM s_user AS customer WHERE (
                    customer.email IS NOT NULL AND
                    customer.active=1 AND
                    customer.lockedUntil IS NULL
                );
            ')->fetchAll(\PDO::FETCH_COLUMN);
        } catch (DBALException $exception) {
            return false;
        }
    }

    /**
     * Regenerate hash over user id.
     *
     * @param integer $userId
     * @return array|boolean
     */
    public function regenerateHash(int $userId)
    {
        $connection = $this->getEntityManager()->getConnection();
        try {
            $userData = $connection->executeQuery('
                SELECT user.email, user.password, user.lastname 
                FROM s_user AS user WHERE user.id=:id;
            ', ['id' => $userId,])->fetchAll(\PDO::FETCH_NUM);

            $deleteCount = $connection->delete(
                's_plugin_soft_login',
                ['customer_id' => $userId],
                [\PDO::PARAM_INT]
            );
        } catch (\Exception $exception) {
            return false;
        }

        $isUnique = count($userData) === 1;
        $isSoftLogin = $deleteCount <= 1;
        $loginHash = $this->generateHash(implode($userData[0]));

        if (!$isUnique || !$isSoftLogin || !$loginHash) {
            return false;
        }

        $insertCount = $connection->insert(
            's_plugin_soft_login',
            [
                'customer_id' => $userId,
                'login_hash' => $loginHash,
                'updated_at' =>  new \DateTime('now'),
                'is_active' => 1,
                'category_id' => 1
            ],
            [
                \PDO::PARAM_INT,
                \PDO::PARAM_STR,
                'datetime',
                \PDO::PARAM_INT,
                \PDO::PARAM_INT,
            ]
        );

        return [
            'insertCount' => $insertCount,
            'deleteCount' => $deleteCount,
            'customerId' => $userId,
        ];
    }

    /**
     * Generate hash for the account over user id.
     * @param integer $userId
     * @return boolean
     */
    public function generateAccountHash(int $userId)
    {
        $customer = $this->getValidUser($userId);
        $defaultRootCategory = $this->getCategory(1);
        $userData = $this->getUserData($customer);
        $loginHash = $this->generateHash($userData);

        if (!$customer || !$defaultRootCategory || !$userData || !$loginHash) {
            return false;
        }

        try {
            $modelManager = $this->getEntityManager();

            $softLogin = $this->getSoftLogin($userId)->getOneOrNullResult(AbstractQuery::HYDRATE_OBJECT);
            if (!$softLogin) {
                $softLogin = new SoftLogin();
                $softLogin->setCustomer($customer);
                $softLogin->setCategory($defaultRootCategory);
                $softLogin->setActive(true);
            }

            $softLogin->setLoginHash($loginHash);

            $modelManager->persist($softLogin);
            $modelManager->flush();
            return true;
        } catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * Handles soft login action.
     *
     * @param integer $userId
     * @return boolean
     */
    public function softLogin(int $userId): bool
    {
        $now = new \DateTime();

        try {
            /** @var SoftLogin $softLogin */
            $softLogin = $this->getEntityManager()->createQueryBuilder()
                ->select(['soft_login'])
                ->from(SoftLogin::class, 'soft_login')
                ->where('soft_login.customerId = :userId')
                ->setParameter('userId', $userId)
                ->getQuery()
                ->getOneOrNullResult(AbstractQuery::HYDRATE_OBJECT);

            $softLogin->setLastLogin($now);

            $firstLogin = $softLogin->getFirstLogin();
            if (!$firstLogin) {
                $softLogin->setFirstLogin($now);
            }

            $modelManager = $this->getEntityManager();
            $modelManager->persist($softLogin);
            $modelManager->flush();
            return true;
        } catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * Get user id over email.
     *
     * @param string $email
     * @return integer|boolean
     */
    public function getUserId(string $email)
    {
        try {
            $userId = $this->getEntityManager()->createQueryBuilder()
                ->select('customer.id')
                ->from(Customer::class, 'customer')
                ->where('customer.email = :email')
                ->setParameter('email', $email)
                ->getQuery()
                ->getOneOrNullResult(AbstractQuery::HYDRATE_ARRAY);
            if (count($userId) > 1) {
                return false;
            }
            return $userId['id'];
        } catch (NonUniqueResultException $exception) {
            return false;
        }
    }

    /**
     * Provides soft login etntity over user id.
     *
     * @param integer $userId
     * @return Query|Query
     */
    public function getSoftLogin(int $userId)
    {
        try {
            return $this->getEntityManager()->createQueryBuilder()
                ->select('soft_login')
                ->from(SoftLogin::class, 'soft_login')
                ->where('soft_login.customerId = :id')
                ->setParameter('id', $userId)
                ->getQuery();
        } catch (NonUniqueResultException $exception) {
            return false;
        }
    }

    /**
     * @param $id
     * @return Customer|boolean
     */
    protected function getValidUser($id)
    {
        try {
            return $this->getEntityManager()->createQueryBuilder()
                ->select('customer')
                ->from('Shopware\Models\Customer\Customer', 'customer')
                ->where('customer.id = :id')
                ->setParameter('id', $id)
                ->getQuery()
                ->getOneOrNullResult(AbstractQuery::HYDRATE_OBJECT);
        } catch (NonUniqueResultException $exception) {
            return false;
        }
    }

    /**
     * @param integer $id
     * @return Category|boolean
     */
    protected function getCategory(int $id)
    {
        try {
            $category = $this->getEntityManager()->createQueryBuilder()
                ->select(['category'])
                ->from(Category::class, 'category')
                ->where('category.id = :categoryId')
                ->setParameter('categoryId', $id)
                ->getQuery()
                ->getOneOrNullResult(AbstractQuery::HYDRATE_OBJECT);
            if (!$category) {
                return false;
            }
            return $category;
        } catch (NonUniqueResultException $exception) {
            return false;
        }
    }

    /**
     * Getter method for soft login category redirection.
     *
     * @param Customer $user
     * @return Category|boolean
     */
    public function getCategoryRedirection(Customer $user)
    {
        try {
            /** @var SoftLogin $softLogin */
            $softLogin = $this->getEntityManager()->createQueryBuilder()
                ->select(['soft_login'])
                ->from(SoftLogin::class, 'soft_login')
                ->where('soft_login.customerId = :id')
                ->setParameter('id', $user->getId())
                ->getQuery()
                ->getOneOrNullResult(AbstractQuery::HYDRATE_OBJECT);

            return $softLogin->getCategory();
        } catch (NonUniqueResultException $exception) {
            return false;
        }
    }

    /**
     * Generate safe hash by given user entity id and encode it.
     *
     * @param string $userData
     * @return string|boolean
     */
    private function generateHash(string $userData)
    {
        if (!$userData || !$this->encoderName) {
            return false;
        }

        return base64_encode(
            $this->encoder->encodePassword($userData, $this->encoderName)
        );
    }

    /**
     * Provides user data string for hash generation.
     *
     * @param Customer $user
     * @return string|boolean
     */
    protected function getUserData(Customer $user)
    {
        if (!$user) {
            return false;
        }

        $userData = [
            $user->getEmail(),
            $user->getPassword(),
            $user->getLastname()
        ];

        return implode($userData);
    }
}
