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

use Hyperf\Context\Context;
use Hyperf\Coroutine\Coroutine;
use Hyperf\Database\ConnectionInterface;
use Hyperf\Stringable\Str;

class ConnectionResolver extends \Hyperf\DbConnection\ConnectionResolver
{
    /**
     * Get a database connection instance.
     */
    public function connection(?string $name = null): ConnectionInterface
    {
        if (is_null($name)) {
            $name = $this->getDefaultConnection();
        }

        $id = Tenant::instance()->getId();
        if (! Str::contains($name, '_')) {
            $name = $name . '_' . $id;
        }

        $connection = null;
        $id = $this->getContextKey($name);
        if (Context::has($id)) {
            $connection = Context::get($id);
        }

        if (! $connection instanceof ConnectionInterface) {
            $pool = $this->factory->getPool($name);
            $connection = $pool->get();
            try {
                // PDO is initialized as an anonymous function, so there is no IO exception,
                // but if other exceptions are thrown, the connection will not return to the connection pool properly.
                $connection = $connection->getConnection();
                Context::set($id, $connection);
            } finally {
                if (Coroutine::inCoroutine()) {
                    defer(function () use ($connection) {
                        $connection->release();
                    });
                }
            }
        }

        return $connection;
    }

    /**
     * The key to identify the connection object in coroutine context.
     * @param mixed $name
     */
    private function getContextKey($name): string
    {
        return sprintf('database.connection.%s', $name);
    }
}
