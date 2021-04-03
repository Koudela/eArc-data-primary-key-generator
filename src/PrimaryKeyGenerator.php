<?php /** @noinspection PhpComposerExtensionStubsInspection */ declare(strict_types=1);
/**
 * e-Arc Framework - the explicit Architecture Framework
 *
 * @package earc/data-primary-key-generator
 * @link https://github.com/Koudela/eArc-data-primary-key-generator/
 * @copyright Copyright (c) 2019-2021 Thomas Koudela
 * @license http://opensource.org/licenses/MIT MIT License
 */

namespace eArc\DataPrimaryKeyGenerator;

use eArc\Data\Entity\Interfaces\PrimaryKey\AutoPrimaryKeyInterface;
use eArc\Data\Exceptions\DataException;
use eArc\Data\Exceptions\Interfaces\DataExceptionInterface;
use eArc\Data\Manager\Interfaces\Events\OnAutoPrimaryKeyInterface;
use eArc\DataFilesystem\Services\StaticDirectoryService;
use Redis;

class PrimaryKeyGenerator implements OnAutoPrimaryKeyInterface
{
    const USE_REDIS = 'redis';
    const USE_FILESYSTEM = 'fs';

    protected Redis|null $redis = null;
    protected string $name;
    protected string $infrastructure;

    public function __construct()
    {
        $this->name = di_param(ParameterInterface::NAME, 'earc-data-pk-gen');
        $this->infrastructure = di_param(ParameterInterface::INFRASTRUCTURE, self::USE_FILESYSTEM);

        if ($this->infrastructure === self::USE_REDIS) {
            $this->redis = new Redis();
        }
    }

    public function onAutoPrimaryKey(AutoPrimaryKeyInterface $entity): string|null
    {
        if ($entity instanceof AutoUUIDPrimaryKeyInterface) {
            return $this->UUIDv4();
        }

        if ($entity instanceof AutoincrementPrimaryKeyInterface) {
            if ($this->infrastructure === self::USE_FILESYSTEM) {
                return $this->getAutoIncrementIdFS($entity::class);
            }

            if ($this->infrastructure === self::USE_REDIS) {
                return $this->getAutoIncrementIdRedis($entity::class);
            }
        }

        return null;
    }

    /**
     * @param string $fQCN
     *
     * @return string
     *
     * @throws DataExceptionInterface
     */
    public function getAutoIncrementIdFS(string $fQCN): string
    {
        di_static(StaticDirectoryService::class)::forceChdir($fQCN, '@'.$this->name);

        $filename = 'auto-increment-id.txt';

        if (!$handle = fopen($filename, 'c+')) {
            throw new DataException(sprintf(
                '{6e5a24c5-853c-4bf4-a7b7-b95a7a60df0d} Cannot open file %s.',
                getcwd().'/'.$filename
            ));
        }

        if (!flock($handle, LOCK_EX)) {
            throw new DataException(sprintf(
                '{59fb64fc-acaf-4ec4-8cc0-d10724909f30} Cannot acquire lock for %s.',
                getcwd().'/'.$filename
            ));
        }

        $content = (string) fgets($handle);
        $id = !strlen($content) ? '0' : (string) ((int) $content + 1);

        rewind($handle);
        fputs($handle, $id);

        flock($handle, LOCK_UN);
        fclose($handle);

        return $id;
    }

    /**
     * @param string $fQCN
     *
     * @return string
     *
     * @throws DataExceptionInterface
     */
    public function getAutoIncrementIdRedis(string $fQCN): string
    {
        if (!$this->redis->hExists($this->name, $fQCN)) {
            $max = 0;
            foreach (data_find($fQCN, []) as $primaryKey) {
                $max = max($max, (int) $primaryKey);
            }
            $this->redis->hSet($this->name, $fQCN, $max+1);

            return $max+1;
        }

        return (string) $this->redis->hIncrBy($this->name, $fQCN, 1);
    }

    public function UUIDv4(): string
    {
        $randomHex = '';
        for ($i = 0; $i < 32; $i++) {
            $randomHex .= (string)dechex(rand(0, 15));
        }
        $data = hex2bin($randomHex);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
