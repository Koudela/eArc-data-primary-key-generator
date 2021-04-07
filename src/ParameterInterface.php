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

interface ParameterInterface
{
    const HASH_KEY_NAME = 'earc.data_primary_key_generator.hash_key_name'; // default 'earc-data-pk-gen'
    const DIR_NAME_POSTFIX = 'earc.data_primary_key_generator.dir_name_postfix'; // default '@earc-data-pk-gen'
    // may be set PrimaryKeyGenerator::USE_REDIS or PrimaryKeyGenerator::USE_FILESYSTEM
    const INFRASTRUCTURE = 'earc.data_primary_key_generator.infrastructure'; // default USE_FILESYSTEM
    const REDIS_CONNECTION = 'earc.data_primary_key_generator.redis_connection'; // default ['localhost']
    const DEFAULT_INTERFACE = 'earc.data_primary_key_generator.default_interface'; // default AutoUUIDPrimaryKeyInterface::class
}
