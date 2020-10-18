<?php

declare(strict_types=1);

namespace HttpSoft\Tests\Basis\TestAsset;

use Devanych\View\Renderer;
use HttpSoft\Basis\TemplateRendererInterface;

use function realpath;

class TemplateRenderer implements TemplateRendererInterface
{
    private Renderer $render;

    public function __construct()
    {
        $this->render = new Renderer(realpath(__DIR__ . '/templates'));
    }

    public function getEngine(): Renderer
    {
        return $this->render;
    }

    public function render(string $view, array $params = []): string
    {
        return $this->render->render($view, $params);
    }
}
