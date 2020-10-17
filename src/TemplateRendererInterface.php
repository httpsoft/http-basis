<?php

declare(strict_types=1);

namespace HttpSoft\Basis;

interface TemplateRendererInterface
{
    /**
     * Gets an instance of the original rendering engine used.
     *
     * @return object
     */
    public function getEngine(): object;

    /**
     * Renders the template/view file by passing parameters to it.
     *
     * @param string $view
     * @param array $params
     * @return string
     */
    public function render(string $view, array $params = []): string;
}
