<?php

namespace Storeman\Index;

use Storeman\FilesystemUtility;
use Storeman\Hash\HashContainer;

/**
 * An index object is the representation of one of this filesystem primitives contained in the index.
 */
class IndexObject implements \ArrayAccess
{
    public const TYPE_DIR = 1;
    public const TYPE_FILE = 2;
    public const TYPE_LINK = 3;

    public const CMP_IGNORE_BLOBID = 1;
    public const CMP_IGNORE_INODE = 2;
    public const CMP_IGNORE_CTIME = 4;


    /**
     * @var string
     */
    protected $relativePath;

    /**
     * @var int
     */
    protected $type;

    /**
     * @var float
     */
    protected $mtime;

    /**
     * @var float
     */
    protected $ctime;

    /**
     * mode & 0777
     *
     * @var int
     */
    protected $permissions;

    /**
     * @var int
     */
    protected $size;

    /**
     * @var int
     */
    protected $inode;

    /**
     * @var string
     */
    protected $linkTarget;

    /**
     * Content hashes for file index objects.
     *
     * @var HashContainer
     */
    protected $hashes;

    /**
     * @var string
     */
    protected $blobId;

    public function __construct(string $relativePath, int $type, float $mtime, ?float $ctime, int $permissions, ?int $size, ?int $inode, ?string $linkTarget, ?string $blobId, ?HashContainer $hashContainer)
    {
        assert(($type === static::TYPE_FILE) ^ ($size === null));
        assert(($type === static::TYPE_FILE) || ($blobId === null));
        assert(($type === static::TYPE_FILE) ^ ($hashContainer === null));
        assert(($type === static::TYPE_LINK) ^ ($linkTarget === null));
        assert(!($permissions & ~0777));

        $this->relativePath = $relativePath;
        $this->type = $type;
        $this->mtime = $mtime;
        $this->ctime = $ctime;
        $this->permissions = $permissions;
        $this->size = $size;
        $this->inode = $inode;
        $this->linkTarget = $linkTarget;
        $this->blobId = $blobId;
        $this->hashes = $hashContainer;
    }

    public function getRelativePath(): string
    {
        return $this->relativePath;
    }

    public function getBasename(): string
    {
        $pos = strrpos($this->relativePath, '/');

        return ($pos === false) ? $this->relativePath : substr($this->relativePath, $pos + 1);
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function getTypeName(): string
    {
        return static::getTypeNameMap()[$this->type];
    }

    public function isDirectory(): bool
    {
        return $this->type === static::TYPE_DIR;
    }

    public function isFile(): bool
    {
        return $this->type === static::TYPE_FILE;
    }

    public function isLink(): bool
    {
        return $this->type === static::TYPE_LINK;
    }

    public function getMtime(): float
    {
        return $this->mtime;
    }

    public function getCtime(): ?float
    {
        return $this->ctime;
    }

    public function setCtime(float $ctime): IndexObject
    {
        $this->ctime = $ctime;

        return $this;
    }

    public function getPermissions(): int
    {
        return $this->permissions;
    }

    public function getPermissionsString(): string
    {
        return decoct($this->permissions);
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function getInode(): ?int
    {
        return $this->inode;
    }

    public function getLinkTarget(): ?string
    {
        return $this->linkTarget;
    }

    public function getHashes(): ?HashContainer
    {
        return $this->hashes;
    }

    public function getBlobId(): ?string
    {
        return $this->blobId;
    }

    public function setBlobId(string $blobId): IndexObject
    {
        assert($this->blobId === null);

        $this->blobId = $blobId;

        return $this;
    }

    /**
     * Equality check with all attributes.
     *
     * @param IndexObject $other
     * @param int $options
     * @return bool
     */
    public function equals(?IndexObject $other, int $options = 0): bool
    {
        if ($other === null)
        {
            return false;
        }

        $equals = true;
        $equals &= $this->relativePath === $other->relativePath;
        $equals &= $this->type === $other->type;
        $equals &= $this->mtime === $other->mtime;
        $equals &= ($options & static::CMP_IGNORE_CTIME) || ($this->ctime === $other->ctime);
        $equals &= $this->permissions === $other->permissions;
        $equals &= $this->size === $other->size;
        $equals &= ($options & static::CMP_IGNORE_INODE) || ($this->inode === $other->inode);
        $equals &= $this->linkTarget === $other->linkTarget;
        $equals &= ($options & static::CMP_IGNORE_BLOBID) || ($this->blobId === $other->blobId);

        if ($this->hashes && $other->hashes)
        {
            $equals &= $this->hashes->equals($other->hashes);
        }

        return $equals;
    }

    /**
     * @inheritdoc
     */
    public function offsetExists($offset)
    {
        return method_exists($this, 'get' . ucfirst($offset));
    }

    /**
     * @inheritdoc
     */
    public function offsetGet($offset)
    {
        assert(method_exists($this, 'get' . ucfirst($offset)));

        return call_user_func([$this, 'get' . ucfirst($offset)]);
    }

    /**
     * @inheritdoc
     */
    public function offsetSet($offset, $value)
    {
        throw new \LogicException();
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset($offset)
    {
        throw new \LogicException();
    }

    public function __clone()
    {
        if ($this->hashes !== null)
        {
            $this->hashes = clone $this->hashes;
        }
    }

    public function __toString(): string
    {
        $parts = [
            $this->getTypeName(),
            "mtime: " . ($this->mtime === null ? '-' : FilesystemUtility::buildTime($this->mtime)),
            "ctime: " . ($this->ctime === null ? '-' : FilesystemUtility::buildTime($this->ctime)),
            "permissions: 0{$this->getPermissionsString()}",
            "inode: " . $this->inode ?: '-',
        ];

        if ($this->isFile())
        {
            $blobId = $this->blobId ?: '-';

            $parts = array_merge($parts, [
                "size: {$this->size} B",
                "blobId: {$blobId}",
            ]);
        }
        elseif ($this->isLink())
        {
            $parts = array_merge($parts, [
                "target: {$this->linkTarget}",
            ]);
        }

        $attributesString = implode(', ', $parts);

        return "{$this->relativePath} ({$attributesString})";
    }

    public static function getTypeNameMap(): array
    {
        return [
            static::TYPE_DIR => 'DIR',
            static::TYPE_FILE => 'FILE',
            static::TYPE_LINK => 'LINK',
        ];
    }
}
