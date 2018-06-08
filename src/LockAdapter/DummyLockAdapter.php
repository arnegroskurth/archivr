<?php

namespace Storeman\LockAdapter;

class DummyLockAdapter extends AbstractLockAdapter
{
    /**
     * @var Lock[]
     */
    protected $lockMap = [];

    protected function doGetExistingLockNames(): array
    {
        return array_keys($this->lockMap);
    }

    protected function doGetLock(string $name): ?Lock
    {
        return isset($this->lockMap[$name]) ? $this->lockMap[$name] : null;
    }

    protected function doAcquireLock(string $name, int $timeout = null): bool
    {
        $this->lockMap[$name] = new Lock($name, $this->identity);

        return true;
    }

    protected function doReleaseLock(string $name): void
    {
        unset($this->lockMap[$name]);
    }
}
