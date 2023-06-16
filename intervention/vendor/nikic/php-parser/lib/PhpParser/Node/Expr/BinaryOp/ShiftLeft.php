<?php

declare (strict_types=1);
namespace Jacoby\Intervention\PhpParser\Node\Expr\BinaryOp;

use Jacoby\Intervention\PhpParser\Node\Expr\BinaryOp;
class ShiftLeft extends BinaryOp
{
    public function getOperatorSigil() : string
    {
        return '<<';
    }
    public function getType() : string
    {
        return 'Expr_BinaryOp_ShiftLeft';
    }
}
