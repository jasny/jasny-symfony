<?php

/*
 * This file is part of the Jasny extension on Symfony.
 *
 * (c) Arnold Daniels <arnold@jasny.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jasny\GeneratorBundle\Generator;

use Doctrine\ORM\Tools\EntityRepositoryGenerator as BaseGenerator;

/**
 * { @inheritDoc }
 */
class EntityRepositoryGenerator extends BaseGenerator
{
    protected static $_template =
'<?php

namespace <namespace>;

use Jasny\ORMBundle\EntityRepository;

/**
 * <className>
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class <className> extends EntityRepository
{
}';
    
    public function generateEntityRepositoryClass($fullClassName)
    {
        $namespace = substr($fullClassName, 0, strrpos($fullClassName, '\\'));
        $className = substr($fullClassName, strrpos($fullClassName, '\\') + 1, strlen($fullClassName));

        $variables = array(
            '<namespace>' => $namespace,
            '<className>' => $className
        );
        return str_replace(array_keys($variables), array_values($variables), static::$_template);
    }
}