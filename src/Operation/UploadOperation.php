<?php

namespace Archivr\Operation;

use Archivr\Connection\ConnectionInterface;

class UploadOperation implements OperationInterface
{
    protected $absolutePath;
    protected $blobId;
    protected $vaultConnection;

    public function __construct(string $absolutePath, string $blobId, ConnectionInterface $vaultConnection)
    {
        $this->absolutePath = $absolutePath;
        $this->blobId = $blobId;
        $this->vaultConnection = $vaultConnection;
    }

    public function execute(): bool
    {
        $localStream = fopen($this->absolutePath, 'r');
        $remoteStream = $this->vaultConnection->getStream($this->blobId, 'w');

        $bytesCopied = stream_copy_to_stream($localStream, $remoteStream);

        fclose($remoteStream);
        fclose($localStream);

        return $bytesCopied !== false;
    }

    /**
     * @codeCoverageIgnore
     */
    public function __toString(): string
    {
        return sprintf('Upload %s (blobId %s)', $this->absolutePath, $this->blobId);
    }
}
