<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Shopware\Kernel;
use Shopware\Models\Shop\Shop;

require __DIR__ . '/../../../../../autoload.php';

/**
 * @link https://stackoverflow.com/questions/5505130/phpunit-output-causing-zend-session-exceptions
 */
Zend_Session::$_unitTestEnabled = true;

class SoftLoginTestKernel extends Kernel
{
    public static function start()
    {
        $kernel = new self(getenv('SHOPWARE_ENV') ?: 'testing', true);
        $kernel->boot();

        $container = $kernel->getContainer();
        $container->get('plugins')->Core()->ErrorHandler()->registerErrorHandler(E_ALL | E_STRICT);

        /** @var $repository \Shopware\Models\Shop\Repository */
        $repository = $container->get('models')->getRepository(Shop::class);

        $shop = $repository->getActiveDefault();
        $shop->registerResources();

        if (!self::assertPlugin('SoftLogin')) {
            throw new \RuntimeException('Plugin SoftLogin must be installed and activated.');
        }
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    private static function assertPlugin($name)
    {
        $sql = 'SELECT 1 FROM s_core_plugins WHERE name = ? AND active = 1';

        return (bool) Shopware()->Container()->get('dbal_connection')->fetchColumn($sql, [$name]);
    }
}

SoftLoginTestKernel::start();
