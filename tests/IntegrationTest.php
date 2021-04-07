<?php declare(strict_types=1);
/**
 * e-Arc Framework - the explicit Architecture Framework
 *
 * @package earc/data-redis
 * @link https://github.com/Koudela/eArc-data-redis/
 * @copyright Copyright (c) 2019-2021 Thomas Koudela
 * @license http://opensource.org/licenses/MIT MIT License
 */

namespace eArc\DataPrimaryKeyGeneratorTests;

use eArc\Data\Initializer;
use eArc\Data\ParameterInterface as DataParameter;
use eArc\DataFilesystem\FilesystemDataBridge;
use eArc\DataPrimaryKeyGenerator\AutoincrementPrimaryKeyInterface;
use eArc\DataPrimaryKeyGenerator\AutoUUIDPrimaryKeyInterface;
use eArc\DataPrimaryKeyGenerator\ParameterInterface;
use eArc\DataPrimaryKeyGenerator\PrimaryKeyGenerator;
use PHPUnit\Framework\TestCase;

class IntegrationTest extends TestCase
{
    public function init(): void
    {
        Initializer::init();

        di_tag(DataParameter::TAG_ON_AUTO_PRIMARY_KEY, PrimaryKeyGenerator::class);
        di_tag(DataParameter::TAG_ON_LOAD, FilesystemDataBridge::class);
        di_tag(DataParameter::TAG_ON_PERSIST, FilesystemDataBridge::class);
        di_tag(DataParameter::TAG_ON_REMOVE, FilesystemDataBridge::class);
        di_tag(DataParameter::TAG_ON_FIND, FilesystemDataBridge::class);
        di_set_param(\eArc\DataFilesystem\ParameterInterface::DATA_PATH, __DIR__.'/data');
        exec('rm -rf '.__DIR__.'/data/eArc');
    }

    public function testUUIDStrategy(): void
    {
        $this->init();

        $myUUIDEntity = new MyUUIDEntity();
        data_persist($myUUIDEntity);
        self::assertNotNull($myUUIDEntity->getPrimaryKey());
        self::assertTrue($this->isValidUUID($myUUIDEntity->getPrimaryKey()));
    }

    public function testUUIDDefaultStrategy(): void
    {
        $this->init();

        di_set_param(ParameterInterface::DEFAULT_INTERFACE, AutoUUIDPrimaryKeyInterface::class);

        $myEntity = new MyEntity();
        data_persist($myEntity);
        self::assertNotNull($myEntity->getPrimaryKey());
        self::assertTrue($this->isValidUUID($myEntity->getPrimaryKey()));
    }

    public function testRedisAutoincrementStrategy(): void
    {
        $this->init();

        di_set_param(ParameterInterface::INFRASTRUCTURE, PrimaryKeyGenerator::USE_REDIS);

        $myIncrementPKEntity_1 = new MyIncrementPKEntity();
        data_persist($myIncrementPKEntity_1);
        self::assertNotNull($myIncrementPKEntity_1->getPrimaryKey());

        $myIncrementPKEntity_2 = new MyIncrementPKEntity();
        data_persist($myIncrementPKEntity_2);
        self::assertNotNull($myIncrementPKEntity_2->getPrimaryKey());

        self::assertTrue($myIncrementPKEntity_1->getPrimaryKey() + 1===$myIncrementPKEntity_2->getPrimaryKey() + 0);
        self::assertEquals((int)$myIncrementPKEntity_2->getPrimaryKey(), max(data_find(MyIncrementPKEntity::class, [])));
    }

    public function testRedisAutoincrementDefaultStrategy(): void
    {
        $this->init();

        di_set_param(ParameterInterface::INFRASTRUCTURE, PrimaryKeyGenerator::USE_REDIS);
        di_set_param(ParameterInterface::DEFAULT_INTERFACE, AutoincrementPrimaryKeyInterface::class);

        $myEntity_1 = new MyEntity();
        data_persist($myEntity_1);
        self::assertNotNull($myEntity_1->getPrimaryKey());

        $myEntity_2 = new MyEntity();
        data_persist($myEntity_2);
        self::assertNotNull($myEntity_2->getPrimaryKey());

        self::assertTrue($myEntity_1->getPrimaryKey()+1 === $myEntity_2->getPrimaryKey()+0);
        self::assertEquals((int) $myEntity_2->getPrimaryKey(), max(data_find(MyEntity::class, [])));
    }

    public function testFilesystemAutoincrementStrategy(): void
    {
        $this->init();

        di_set_param(ParameterInterface::INFRASTRUCTURE, PrimaryKeyGenerator::USE_FILESYSTEM);

        $myIncrementPKEntity_1 = new MyIncrementPKEntity();
        data_persist($myIncrementPKEntity_1);
        self::assertNotNull($myIncrementPKEntity_1->getPrimaryKey());

        $myIncrementPKEntity_2 = new MyIncrementPKEntity();
        data_persist($myIncrementPKEntity_2);
        self::assertNotNull($myIncrementPKEntity_2->getPrimaryKey());

        self::assertTrue($myIncrementPKEntity_1->getPrimaryKey() + 1===$myIncrementPKEntity_2->getPrimaryKey() + 0);
        self::assertEquals((int)$myIncrementPKEntity_2->getPrimaryKey(), max(data_find(MyIncrementPKEntity::class, [])));
    }

    public function testFilesystemAutoincrementDefaultStrategy(): void
    {
        $this->init();

        di_set_param(ParameterInterface::INFRASTRUCTURE, PrimaryKeyGenerator::USE_FILESYSTEM);
        di_set_param(ParameterInterface::DEFAULT_INTERFACE, AutoincrementPrimaryKeyInterface::class);

        $myEntity_1 = new MyEntity();
        data_persist($myEntity_1);
        self::assertNotNull($myEntity_1->getPrimaryKey());

        $myEntity_2 = new MyEntity();
        data_persist($myEntity_2);
        self::assertNotNull($myEntity_2->getPrimaryKey());

        self::assertTrue($myEntity_1->getPrimaryKey()+1 === $myEntity_2->getPrimaryKey()+0);
        self::assertEquals((int) $myEntity_2->getPrimaryKey(), max(data_find(MyEntity::class, [])));
    }

    protected function isValidUUID(mixed $uuid): bool
    {
        return is_string($uuid) &&
            preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $uuid) === 1;
    }
}
