<?php

namespace SoftLogin\Commands;

use Shopware\Commands\ShopwareCommand;
use SoftLogin\Models\SoftLogin;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RegenerateHashesCommand extends ShopwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('sw:softlogin:hashgen')
            ->setDescription('Regenerate hashes for all of the valid users.')
            ->setHelp(<<<EOF
The <info>%command.name%</info> regenerates hashes for the users. 
It skips the entity model relationships and iterates over database.
It may take some time.
EOF
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $repository = Shopware()->Models()->getRepository(SoftLogin::class);

        $processedIdCount = 0;
        $successIdCount = 0;
        $validUserIds = $repository->getValidUserIds();
        $userCount = count($validUserIds);
        $infoPrefix    = '[ INFO  ] ';
        $errorPrefix   = '[ ERROR ] ';
        $startPrefix   = '[ START ] ';
        $endPrefix     = '[ END   ] ';
        $successPrefix = '[ OK    ] ';

        $output->writeln($startPrefix.'<info>'.$userCount.' hashes to be regenerated...</info>');

        if ($userCount > 500000) {
            $output->writeln($errorPrefix.'<error>There is more than 500.000 hashes, please write a paginator...</error>');
            return;
        }

        foreach ($validUserIds as $key => $userId) {
            $userId = (int)$userId;
            $output->writeln($infoPrefix.'<info>Processing user id: '.$userId.' ...</info>');

            $result = $repository->regenerateHash($userId);
            if ($result) {
                $processedIdCount += 1;
            }

            $isRegenerated =
                $result['insertCount'] <= 1 && $result['deleteCount'] <= 1  &&
                $result['insertCount'] >= $result['deleteCount'] &&
                $result['customerId'] >= 0;
            if (!$isRegenerated) {
                $output->writeln($errorPrefix.'<error>Hash for user id '.$userId.' was not regenerated.</error>');
                continue;
            }

            $successIdCount += 1;
            $toProcess = $userCount - $successIdCount;
            $output->writeln($successPrefix.'<info>Hash for user id '.$userId.' was regenerated.</info>');
            $output->writeln($infoPrefix.'<info>'.$toProcess.' hash(es) to be processed...</info>');
        }

        $isSuccess = $processedIdCount === $successIdCount;

        if (!$isSuccess) {
            $output->writeln($errorPrefix.'<error>Processed ids count and hashes count does not match.'
                .'Data consistency or integirty validation may be necessary.'
                .'Check reference methods in '.$this->getName().' command.</error>');
            return;
        }

        $output->writeln($infoPrefix.'<info>Hashes regeneration finished.</info>');
        $output->writeln($endPrefix.'<info>From '.$userCount.' valid user ids, '.$processedIdCount.' was processed,'
            .' and '.$successIdCount.' hashes were regenerated correctly.</info>');
    }
}
