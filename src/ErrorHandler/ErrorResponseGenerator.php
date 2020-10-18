<?php

declare(strict_types=1);

namespace HttpSoft\Basis\ErrorHandler;

use HttpSoft\Basis\Response\ExtractErrorDataTrait;
use HttpSoft\Basis\TemplateRendererInterface;
use HttpSoft\ErrorHandler\ErrorResponseGeneratorInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

final class ErrorResponseGenerator implements ErrorResponseGeneratorInterface
{
    use ExtractErrorDataTrait;

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
    public function generate(Throwable $error, ServerRequestInterface $request): ResponseInterface
    {
        $response = $this->responseFactory->createResponse($this->extractErrorStatusCode($error));

        $response->getBody()->write($this->template->render($this->view, [
            'response' => $response,
            'request' => $request,
            'exception' => $error,
            'debug' => $this->debug,
        ]));

        return $response;
    }
}
