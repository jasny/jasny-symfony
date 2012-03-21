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
 * DQL function for calculating distances between two points
 *
 * Example: DISTANCE(foo.point, GEOM(:param))
 *          where param should be mapped to a Jasny\ORM\Property\Point object
 */
class Distance extends FunctionNode
{
    private $firstArg;
    private $secondArg;
 
    /**
     * Get SQL function
     * 
     * @param SqlWalker $sqlWalker
     * @return string
     */
    public function getSql(SqlWalker $sqlWalker)
    {
        // Need to do this hacky linestring length thing because
        // despite what MySQL manual claims, DISTANCE isn't actually implemented...
        return 'GLength(LineString(' .
               $this->firstArg->dispatch($sqlWalker) .
               ', ' .
               $this->secondArg->dispatch($sqlWalker) .
           '))';
    }

    /**
     * Get DQL function
     * 
     * @param Parser $parser 
     * @return string
     */
    public function parse(Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->firstArg = $parser->ArithmeticPrimary();
        $parser->match(Lexer::T_COMMA);
        $this->secondArg = $parser->ArithmeticPrimary();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }
}
