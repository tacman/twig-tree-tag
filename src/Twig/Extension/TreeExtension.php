<?php

namespace JordanLev\TwigTreeTag\Twig\Extension;

use JordanLev\TwigTreeTag\Twig\TokenParser\TreeTokenParser;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\Extension\ExtensionInterface;

class TreeExtension extends AbstractExtension
{
    public function __construct()
    {
        if (version_compare(PHP_VERSION, '8.1.0', '<')) {
            throw new \LogicException('The {%tree%} Twig tag requires PHP version 8.1 or higher');
        }
    }

    public function getTokenParsers(): array
    {
        return [new TreeTokenParser()];
    }

}
