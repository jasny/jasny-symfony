<?php

/*
 * This file is part of the Jasny extension on Symfony.
 *
 * (c) Arnold Daniels <arnold@jasny.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jasny\Bundle\ORMBundle\Functions;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\Lexer;

/**
 * GEOM function for querying using geometric objects as parameters
 *
 * Usage: GEOM(:param) where param should be mapped to a Jasny\ORM\Property\Geom object
 *        without any special typing provided (eg. so that it gets converted to string)
 * 
 * @see http://codeutopia.net/blog/2011/02/19/using-spatial-data-in-doctrine-2/
 */
class Geom extends FunctionNode
{
    private $arg;
 
    /**
     * Get SQL function
     * 
     * @param SqlWalker $sqlWalker
     * @return string
     */
    public function getSql(SqlWalker $sqlWalker)
    {
        return 'GeomFromText(' . $this->arg->dispatch($sqlWalker) . ')';
    }
 
    /**
     * Parse DQL function
     * 
     * @param Parser $parser 
     */
    public function parse(Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->arg = $parser->ArithmeticPrimary();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }
}
