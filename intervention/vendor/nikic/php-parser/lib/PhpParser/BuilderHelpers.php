<?php

declare (strict_types=1);
namespace Jacoby\Intervention\PhpParser;

use Jacoby\Intervention\PhpParser\Node\Expr;
use Jacoby\Intervention\PhpParser\Node\Identifier;
use Jacoby\Intervention\PhpParser\Node\Name;
use Jacoby\Intervention\PhpParser\Node\NullableType;
use Jacoby\Intervention\PhpParser\Node\Scalar;
use Jacoby\Intervention\PhpParser\Node\Stmt;
/**
 * This class defines helpers used in the implementation of builders. Don't use it directly.
 *
 * @internal
 */
final class BuilderHelpers
{
    /**
     * Normalizes a node: Converts builder objects to nodes.
     *
     * @param Node|Builder $node The node to normalize
     *
     * @return Node The normalized node
     */
    public static function normalizeNode($node) : Node
    {
        if ($node instanceof Builder) {
            return $node->getNode();
        } elseif ($node instanceof Node) {
            return $node;
        }
        throw new \LogicException('Expected node or builder object');
    }
    /**
     * Normalizes a node to a statement.
     *
     * Expressions are wrapped in a Stmt\Expression node.
     *
     * @param Node|Builder $node The node to normalize
     *
     * @return Stmt The normalized statement node
     */
    public static function normalizeStmt($node) : Stmt
    {
        $node = self::normalizeNode($node);
        if ($node instanceof Stmt) {
            return $node;
        }
        if ($node instanceof Expr) {
            return new Stmt\Expression($node);
        }
        throw new \LogicException('Expected statement or expression node');
    }
    /**
     * Normalizes a name: Converts plain string names to PhpParser\Node\Name.
     *
     * @param Name|string $name The name to normalize
     *
     * @return Name The normalized name
     */
    public static function normalizeName($name) : Name
    {
        if ($name instanceof Name) {
            return $name;
        } elseif (\is_string($name)) {
            if (!$name) {
                throw new \LogicException('Name cannot be empty');
            }
            if ($name[0] === '\\') {
                return new Name\FullyQualified(\substr($name, 1));
            } elseif (0 === \strpos($name, 'namespace\\')) {
                return new Name\Relative(\substr($name, \strlen('namespace\\')));
            } else {
                return new Name($name);
            }
        }
        throw new \LogicException('Jacoby\\Intervention\\Name must be a string or an instance of PhpParser\\Node\\Name');
    }
    /**
     * Normalizes a type: Converts plain-text type names into proper AST representation.
     *
     * In particular, builtin types become Identifiers, custom types become Names and nullables
     * are wrapped in NullableType nodes.
     *
     * @param string|Name|Identifier|NullableType $type The type to normalize
     *
     * @return Name|Identifier|NullableType The normalized type
     */
    public static function normalizeType($type)
    {
        if (!\is_string($type)) {
            if (!$type instanceof Name && !$type instanceof Identifier && !$type instanceof NullableType) {
                throw new \LogicException('Type must be a string, or an instance of Name, Identifier or NullableType');
            }
            return $type;
        }
        $nullable = \false;
        if (\strlen($type) > 0 && $type[0] === '?') {
            $nullable = \true;
            $type = \substr($type, 1);
        }
        $builtinTypes = ['array', 'callable', 'string', 'int', 'float', 'bool', 'iterable', 'void', 'object'];
        $lowerType = \strtolower($type);
        if (\in_array($lowerType, $builtinTypes)) {
            $type = new Identifier($lowerType);
        } else {
            $type = self::normalizeName($type);
        }
        if ($nullable && (string) $type === 'void') {
            throw new \LogicException('void type cannot be nullable');
        }
        return $nullable ? new Node\NullableType($type) : $type;
    }
    /**
     * Normalizes a value: Converts nulls, booleans, integers,
     * floats, strings and arrays into their respective nodes
     *
     * @param Node\Expr|bool|null|int|float|string|array $value The value to normalize
     *
     * @return Expr The normalized value
     */
    public static function normalizeValue($value) : Expr
    {
        if ($value instanceof Node\Expr) {
            return $value;
        } elseif (\is_null($value)) {
            return new Expr\ConstFetch(new Name('null'));
        } elseif (\is_bool($value)) {
            return new Expr\ConstFetch(new Name($value ? 'true' : 'false'));
        } elseif (\is_int($value)) {
            return new Scalar\LNumber($value);
        } elseif (\is_float($value)) {
            return new Scalar\DNumber($value);
        } elseif (\is_string($value)) {
            return new Scalar\String_($value);
        } elseif (\is_array($value)) {
            $items = [];
            $lastKey = -1;
            foreach ($value as $itemKey => $itemValue) {
                // for consecutive, numeric keys don't generate keys
                if (null !== $lastKey && ++$lastKey === $itemKey) {
                    $items[] = new Expr\ArrayItem(self::normalizeValue($itemValue));
                } else {
                    $lastKey = null;
                    $items[] = new Expr\ArrayItem(self::normalizeValue($itemValue), self::normalizeValue($itemKey));
                }
            }
            return new Expr\Array_($items);
        } else {
            throw new \LogicException('Invalid value');
        }
    }
    /**
     * Normalizes a doc comment: Converts plain strings to PhpParser\Comment\Doc.
     *
     * @param Comment\Doc|string $docComment The doc comment to normalize
     *
     * @return Comment\Doc The normalized doc comment
     */
    public static function normalizeDocComment($docComment) : Comment\Doc
    {
        if ($docComment instanceof Comment\Doc) {
            return $docComment;
        } elseif (\is_string($docComment)) {
            return new Comment\Doc($docComment);
        } else {
            throw new \LogicException('Jacoby\\Intervention\\Doc comment must be a string or an instance of PhpParser\\Comment\\Doc');
        }
    }
    /**
     * Adds a modifier and returns new modifier bitmask.
     *
     * @param int $modifiers Existing modifiers
     * @param int $modifier  Modifier to set
     *
     * @return int New modifiers
     */
    public static function addModifier(int $modifiers, int $modifier) : int
    {
        Stmt\Class_::verifyModifier($modifiers, $modifier);
        return $modifiers | $modifier;
    }
}
