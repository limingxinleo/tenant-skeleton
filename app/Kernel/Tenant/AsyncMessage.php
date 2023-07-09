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

use Hyperf\AsyncQueue\JobInterface;
use Hyperf\AsyncQueue\JobMessage;
use Hyperf\Contract\CompressInterface;
use Hyperf\Contract\UnCompressInterface;

class AsyncMessage extends JobMessage
{
    public int $id;

    public function __construct(JobInterface $job)
    {
        parent::__construct($job);
        if (empty($this->id)) {
            $this->id = Tenant::instance()->getId();
        }
    }

    public function __serialize(): array
    {
        if ($this->job instanceof CompressInterface) {
            /* @phpstan-ignore-next-line */
            $this->job = $this->job->compress();
        }

        return [$this->job, $this->attempts, $this->id];
    }

    public function __unserialize(array $data): void
    {
        [$job, $attempts, $id] = $data;
        if ($job instanceof UnCompressInterface) {
            $job = $job->uncompress();
        }

        $this->job = $job;
        $this->attempts = $attempts;
        $this->id = $id;
    }
}
