<?php

declare(strict_types=1);

namespace HttpSoft\Basis\Handler;

use HttpSoft\Basis\TemplateRendererInterface;
use HttpSoft\ErrorHandler\ErrorResponseGeneratorInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class NotFoundHandler implements RequestHandlerInterface
{
    /**
     * @var ResponseFactoryInterface
     */
    private ResponseFactoryInterface $responseFactory;

    /**
     * @var TemplateRendererInterface
     */
    private TemplateRendererInterface $template;

    /**
     * @var string
     */
    private string $view;

    /**
     * @var bool
     */
    private bool $debug;

    /**
     * @param ResponseFactoryInterface $responseFactory
     * @param TemplateRendererInterface $template
     * @param string $view
     * @param bool $debug
     */
    public function __construct(
        ResponseFactoryInterface $responseFactory,
        TemplateRendererInterface $template,
        string $view,
        bool $debug = false
    ) {
        $this->responseFactory = $responseFactory;
        $this->template = $template;
        $this->view = $view;
        $this->debug = $debug;
    }

    /**
     * {@inheritDoc}
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $response = $this->responseFactory->createResponse(ErrorResponseGeneratorInterface::STATUS_NOT_FOUND);

        $response->getBody()->write($this->template->render($this->view, [
            'debug' => $this->debug,
            'request' => $request,
        ]));

        return $response;
    }
}
