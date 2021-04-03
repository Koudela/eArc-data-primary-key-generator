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
    // will be used hash key (USE_REDIS) or as last dirname (USE_FILESYSTEM);
    const NAME = 'earc.data_primary_key_generator.name'; // default 'earc-data-pk-gen'
    // may be set PrimaryKeyGenerator::USE_REDIS or PrimaryKeyGenerator::USE_FILESYSTEM
    const INFRASTRUCTURE = 'earc.data_primary_key_generator.infrastructure'; // default USE_FILESYSTEM
}
