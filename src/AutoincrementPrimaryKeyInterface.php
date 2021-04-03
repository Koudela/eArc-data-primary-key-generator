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

use eArc\Data\Entity\Interfaces\PrimaryKey\AutoPrimaryKeyInterface;

/**
 * Interface to autogenerate incremental primary keys
 */
interface AutoincrementPrimaryKeyInterface extends AutoPrimaryKeyInterface
{
}
