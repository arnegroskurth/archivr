<?php

namespace Archivr\Connection;

interface ConnectionInterface
{
    /**
     * Returns true if the connection currently holds a lock.
     *
     * @return bool
     */
    public function hasLock(): bool;

    /**
     * Tries to acquire a lock.
     * Returns true on success and false on failure.
     *
     * @param bool $wait
     * @param bool $force
     *
     * @return bool
     */
    public function acquireLock(bool $wait = true, bool $force = false);

    /**
     * Releases an acquired lock.
     * Returns true on success and false on failure.
     *
     * @return bool
     */
    public function releaseLock();

    /**
     * Reads and returns the content of a remote stored object.
     *
     * @param string $relativePath
     *
     * @return string
     */
    public function read(string $relativePath): string;

    /**
     * Writes the content of a remote stored object.
     *
     * @param string $relativePath
     * @param string $content
     */
    public function write(string $relativePath, string $content);

    /**
     * Returns true of the remote object exists.
     *
     * @param string $relativePath
     *
     * @return bool
     */
    public function exists(string $relativePath): bool;

    /**
     * Removes the remote stored object.
     *
     * @param string $relativePath
     */
    public function unlink(string $relativePath);

    /**
     * Opens and returns a PHP stream to a remote stored object that can be used to read/write to/from.
     *
     * @param string $relativePath
     * @param string $mode
     *
     * @return resource
     */
    public function getStream(string $relativePath, string $mode);
}
