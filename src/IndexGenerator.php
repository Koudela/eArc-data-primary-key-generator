<?php declare(strict_types=1);
/**
 * e-Arc Framework - the explicit Architecture Framework
 *
 * @package earc/data-primary-key-generator
 * @link https://github.com/Koudela/eArc-data-primary-key-generator/
 * @copyright Copyright (c) 2019-2021 Thomas Koudela
 * @license http://opensource.org/licenses/MIT MIT License
 */

namespace eArc\DataPrimaryKeyGenerator;

use eArc\Data\Entity\Interfaces\EntityInterface;
use eArc\Data\Exceptions\DataException;
use eArc\DataFilesystem\Services\StaticDirectoryService;

/**
 * @deprecated This is old code and not used in the repository anymore. Use at your own risk.
 */
abstract class IndexGenerator
{
    const TYPE_UNIQUE = 'unique';

    public static function updateIndex(string $type, EntityInterface $entity, string $propertyName, $value): void
    {
        di_static(StaticDirectoryService::class)::forceChdir($entity::class, '@'.$type);
        $filename = $propertyName.'.txt';

        if (!$handle = fopen($filename, 'c^+')) {
            throw new DataException(sprintf(
                '{260de63e-a0e9-4d2d-9891-88f9f42986e8} Cannot open file %s.',
                getcwd().'/'.$filename
            ));
        }

        if (!flock($handle, LOCK_EX)) {
            throw new DataException(sprintf(
                '{fddf546a-478c-490e-b8a4-8b1163510a4b} Cannot acquire lock for %s.',
                getcwd().'/'.$filename
            ));
        }

        static::updateIndexCSV($handle,$type === static::TYPE_UNIQUE, $propertyName, $entity->getPrimaryKey(), $value);

        flock($handle, LOCK_UN);
        fclose($handle);
    }

    protected static function updateIndexCSV($handle, bool $unique, string $propertyName, string $primaryKey, ?string $value)
    {
        $index = [];

        while ($array = fgetcsv($handle)) {
            foreach (array_keys($array, $primaryKey, true) as $key) {
                if ($key !== 0) {
                    unset($array[$key]);
                }
            }

            if ($array[0] === $value) {
                $array[] = $primaryKey;
            }

            if (count($array) > 1) {
                $index[$array[0]] = $array;
            }

            if ($unique && count($array) > 2) {
                throw new DataException(sprintf(
                    '{276acc96-ab96-4d94-ae95-41ff42463a1b} Unique index %s for value %s is violated by primary key %s (old) and %s (new)',
                    $propertyName,
                    $value,
                    $array[1],
                    $primaryKey
                ));
            }
        }

        if (null !== $value && !array_key_exists($value, $index)) {
            $index[$value] = [$value, $primaryKey];
        }

        ftruncate($handle, 0);
        rewind($handle);

        foreach ($index as $line) {
            fputcsv($handle, $line);
        }
    }
}
