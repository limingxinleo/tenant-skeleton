<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace App\Kernel\Tenant;

use Hyperf\Context\ApplicationContext;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Support\Traits\StaticInstance;
use Psr\Container\ContainerInterface;

class Tenant
{
    use StaticInstance;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var int
     */
    protected $id = 1;

    public function __construct()
    {
        $this->container = ApplicationContext::getContainer();
    }

    public function init($id = null)
    {
        if (empty($id)) {
            $request = $this->container->get(RequestInterface::class);
            $id = $request->header('X-TENANT-ID');
            $id = $id % 2 + 1;
        }

        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }
}
