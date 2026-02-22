<?php

namespace JordanLev\TwigTreeTag\Twig\TokenParser;

use JordanLev\TwigTreeTag\Twig\Node\TreeNode;
use Twig\Node\Expression\Variable\AssignContextVariable;
use Twig\TokenParser\AbstractTokenParser;
use Twig\Node\Node;
use Twig\Token;

class TreeTokenParser extends AbstractTokenParser
{
    // {% tree
    public function getTag(): string
    {
        return 'tree';
    }

    public function parse(Token $token): Node
    {
        $lineno   = $token->getLine();
        $stream   = $this->parser->getStream();

        // key, item in items
        $targets  = $this->parseAssignmentExpression();
        $stream->expect(Token::OPERATOR_TYPE, 'in');
        $seq      = $this->parser->parseExpression();

        // as treeA
        $as = 'default';
        if ($stream->nextIf(Token::NAME_TYPE, 'as')) {
            $as = $stream->expect(Token::NAME_TYPE)->getValue();
        }

        // %}
        $stream->expect(Token::BLOCK_END_TYPE);

        $data = [];
        while (true) {

            // backing up tag content
            $data[] = ['type' => 'body', 'node' => $this->parser->subparse(fn(Token $token) => $token->test(['subtree', 'endtree']))];

            // {% subtree
            if ($stream->next()->getValue() == 'subtree') {

                // item
                $child = $this->parser->parseExpression();

                // with treeA
                $with = $as;
                if ($stream->nextIf(Token::NAME_TYPE, 'with')) {
                    $with = $stream->expect(Token::NAME_TYPE)->getValue();
                }

                // %}
                $stream->expect(Token::BLOCK_END_TYPE);

                // backing up subtree details
                $data[] = ['type'  => 'subtree', 'with'  => $with, 'child' => $child];

            // {% endtree
            } else {

                // %}
                $stream->expect(Token::BLOCK_END_TYPE);
                break;
            }
        }

        // key, item
        if (count($targets) > 1) {
            $keyTarget   = $targets->getNode('0');
            $keyTarget   = new AssignContextVariable($keyTarget->getAttribute('name'), $keyTarget->getTemplateLine());
            $valueTarget = $targets->getNode('1');
            $valueTarget = new AssignContextVariable($valueTarget->getAttribute('name'), $valueTarget->getTemplateLine());

        // (implicit _key,) item
        } else {
            $keyTarget   = new AssignContextVariable('_key', $lineno);
            $valueTarget = $targets->getNode('0');
            $valueTarget = new AssignContextVariable($valueTarget->getAttribute('name'), $valueTarget->getTemplateLine());
        }

        return new TreeNode($keyTarget, $valueTarget, $seq, $as, $data, $lineno);
    }
}
