<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace App\Controller;

use App\Job\TenantJob;
use App\Model\User;
use Swoole\Coroutine\Channel;

class IndexController extends Controller
{
    public function index()
    {
        $model = User::query()->find(1);
        $channel = new Channel(1);
        go(function () use ($channel) {
            $model = User::query()->find(1);
            $channel->push($model);
        });

        $model2 = $channel->pop();

        queue_push(new TenantJob());

        return $this->response->success([
            $model->toArray(),
            $model2->toArray(),
        ]);
    }
}
