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

namespace App\Kernel\Tenant;

use Hyperf\AsyncQueue\JobInterface;
use Hyperf\AsyncQueue\Message;

class AsyncMessage extends Message
{
    /**
     * @var int
     */
    public $id;

    public function __construct(JobInterface $job)
    {
        parent::__construct($job);
        if (empty($this->id)) {
            $this->id = Tenant::instance()->getId();
        }
    }

    public function serialize()
    {
        return serialize([
            $this->job,
            $this->attempts,
            $this->id,
        ]);
    }

    public function unserialize($serialized)
    {
        [$job, $attempts, $id] = unserialize($serialized);

        $this->job = $job;
        $this->attempts = $attempts;
        $this->id = $id;
    }
}
