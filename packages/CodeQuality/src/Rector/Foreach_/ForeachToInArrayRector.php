<?php declare(strict_types=1);

namespace Rector\CodeQuality\Rector\Foreach_;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Equal;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class ForeachToInArrayRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Simplify `foreach` loops into `in_array` when possible',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
foreach ($items as $item) {
    if ($item === "something") {
        return true;
    }
}

return false;
CODE_SAMPLE
                    ,
                    'in_array("something", $items, true);'
                ),
            ]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Foreach_::class];
    }

    /**
     * @param Foreach_ $foreachNode
     */
    public function refactor(Node $foreachNode): ?Node
    {
        if (! $this->isAForeachCandidate($foreachNode)) {
            return null;
        }

        $firstNodeInsideForeach = $foreachNode->stmts[0];

        if (! $firstNodeInsideForeach instanceof If_) {
            return null;
        }

        $ifCondition = $firstNodeInsideForeach->cond;

        if (! $ifCondition instanceof Identical && ! $ifCondition instanceof Equal) {
            return null;
        }

        $leftVariable = $ifCondition->left;
        $rightVariable = $ifCondition->right;

        if (! $leftVariable instanceof Variable && ! $rightVariable instanceof Variable) {
            return null;
        }

        $condition = $this->normalizeYodaComparison($leftVariable, $rightVariable, $foreachNode);

        if (! $this->isIfBodyABoolReturnNode($firstNodeInsideForeach)) {
            return null;
        }

        $inArrayFunctionCall = $this->createInArrayFunction($condition, $ifCondition, $foreachNode);

        $returnNodeToRemove = $foreachNode->getAttribute(Attribute::NEXT_NODE);
        $this->removeNode($returnNodeToRemove);

        /** @var Return_ $returnNode */
        $returnNode = $firstNodeInsideForeach->stmts[0];

        $negativeReturn = $this->isFalse($returnNode->expr);

        return new Return_($negativeReturn ? new BooleanNot($inArrayFunctionCall) : $inArrayFunctionCall);
    }

    private function isAForeachCandidate(Foreach_ $foreachNode): bool
    {
        if (isset($foreachNode->keyVar)) {
            return false;
        }

        $nextNode = $foreachNode->getAttribute(Attribute::NEXT_NODE);
        if ($nextNode === null || ! $nextNode instanceof Return_) {
            return false;
        }

        $returnExpression = $nextNode->expr;

        return $returnExpression !== null && $this->isBool($returnExpression);
    }

    /**
     * @param mixed $leftValue
     * @param mixed $rightValue
     *
     * @return mixed
     */
    private function normalizeYodaComparison($leftValue, $rightValue, Foreach_ $foreachNode)
    {
        /** @var Variable $foreachVariable */
        $foreachVariable = $foreachNode->valueVar;
        if ($leftValue instanceof Variable) {
            if ($this->areNodesEqual($leftValue, $foreachVariable)) {
                return $rightValue;
            }
        }

        if ($this->areNodesEqual($rightValue, $foreachVariable)) {
            return $leftValue;
        }
    }

    private function isIfBodyABoolReturnNode(If_ $firstNodeInsideForeach): bool
    {
        $ifStatment = $firstNodeInsideForeach->stmts[0];

        if (! $ifStatment instanceof Return_) {
            return false;
        }

        return $this->isBool($ifStatment->expr);
    }

    /**
     * @param mixed $condition
     * @param Identical|Equal $ifCondition
     */
    private function createInArrayFunction($condition, $ifCondition, Foreach_ $foreachNode): FuncCall
    {
        $arguments = $this->createArgs([$condition, $foreachNode->expr]);

        if ($ifCondition instanceof Identical) {
            $arguments[] = $this->createArg($this->createTrue());
        }

        return $this->createFunction('in_array', $arguments);
    }
}
