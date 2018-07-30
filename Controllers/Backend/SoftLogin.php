<?php

class Shopware_Controllers_Backend_SoftLogin extends Shopware_Controllers_Backend_Application
{
    protected $model = \SoftLogin\Models\SoftLogin::class;
    protected $alias = 'SoftLogin';

    /**
     * @var \SoftLogin\Models\Repository
     */
    protected $repository;

    /**
     * @return void
     */
    public function preDispatch()
    {
        parent::preDispatch();

        $this->repository = Shopware()->Models()->getRepository($this->model);
    }

    /**
     * Joins a customer entity to the list query.
     *
     * @return \Shopware\Components\Model\QueryBuilder
     */
    public function getListQuery()
    {
        $builder = parent::getListQuery();
        $builder->addSelect(['customer'])
            ->leftJoin('SoftLogin.customer', 'customer');

        return $builder;
    }

    /**
     * Joins a customer etntity to the detail query.
     *
     * @param $id
     * @return \Doctrine\ORM\QueryBuilder|\Shopware\Components\Model\QueryBuilder
     */
    public function getDetailQuery($id)
    {
        $builder = parent::getDetailQuery($id);
        $builder->addSelect(['customer'])
            ->leftJoin('SoftLogin.customer', 'customer');
        return $builder;
    }

    /**
     * Adds a customer entity to the result data.
     *
     * @param array $data
     * @return array
     */
    public function save($data)
    {
        $data['customer'] = $this->repository->getCustomer($data['customerId']);

        return parent::save($data);
    }

    /**
     * @return void
     */
    public function regenerateHashAction()
    {
        $id = (int)$this->Request()->getParam('customerId');
        $result = $this->repository->regenerateHash($id);

        if (!$result || $result['insertCount'] != $result['deleteCount']) {
            $this->View()->assign([
               'success' => false,
               'message' => 'Meh, consistency check failed.',
            ]);
        }

        try {
            $result = $this->repository->getSoftLogin($result['customerId'])
                ->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
        } catch (Exception $exception) {
            $this->View()->assign([
                'success' => false,
                'message' => 'Meh, exception: '. $exception->getMessage(),
            ]);
        }

        $this->View()->assign([
            'success' => true,
            'data' => [
                'id' => $result['id'],
                'customerId' => $result['customerId'],
                'loginHash' => $result['loginHash'],
                'firstLogin' => $result['firstLogin'],
                'lastLogin' => $result['lastLogin'],
                'updatedAt' => $result['updatedAt'],
                'isActive' => $result['isActive'],
            ]
        ]);
    }
}
