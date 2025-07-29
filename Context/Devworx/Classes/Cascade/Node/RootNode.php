<?php

namespace Cascade\Node;

use Cascade\Runtime\Context;

class RootNode extends AbstractNode
{
    /**
     * @var AbstractNode[]
     */
    protected array $children = [];

    public function __construct(array $children = [])
    {
        $this->children = $children;
    }

    public function addChild(AbstractNode $node): void
    {
        $this->children[] = $node;
    }

    public function evaluate(Context|array &$context,mixed $input=null): string
    {
		return implode('',array_map(
			fn($child) => $child->evaluate($context,$input),
			$this->children
		));
    }
	
	public function compile(string $contextVar='$context',string $input='null'): string
    {
		$compiledParts = array_map(
			fn($child) => $child->compile($contextVar,$input),
			$this->children
		);
		$compiledParts = array_filter($compiledParts,fn($row)=>!empty($row));
		
		if( count($compiledParts) > 1)
			$compiledParts = implode(', ', $compiledParts);
		else
			$compiledParts = $compiledParts[0] ?? '';
		return $compiledParts;
    }
}
