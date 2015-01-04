<?php
namespace EasyCron\ECLang\AST;

use EasyCron\ECLang\ParseException;

/**
 * Base exception class for AST exceptions.
 */
class ASTException extends ParseException
{
    /**
     * @param Node $node
     *
     * @return ASTException
     */
    public static function noDispatchForNode($node)
    {
        return new self("Double-dispatch for node " . get_class($node) . " is not supported.");
    }
}
