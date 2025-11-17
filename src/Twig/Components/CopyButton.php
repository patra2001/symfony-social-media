<?php

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('copy_button')]
class CopyButton
{
    public string $type = 'button';
    public string $class = 'btn btn-primary';
    public string $label = 'Button';
    /** 
     * @var array<string,string> 
     */
    public array $dataAttributes = [];
    public string $alertId = 'copy-alert';
    public bool $alertDismissible = true;
}
