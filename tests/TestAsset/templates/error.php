<?php

declare(strict_types=1);

/** @var Devanych\View\Renderer $this */
/** @var Psr\Http\Message\ResponseInterface $response */
/** @var Psr\Http\Message\ServerRequestInterface $request */
/** @var HttpSoft\Basis\Exception\HttpException $exception */
/** @var bool $debug */
?>

<?php if ($debug) : ?>
    <?=get_class($this) . ':' . get_class($response) . ':' . get_class($exception) . ':' . get_class($request)?>
<?php else : ?>
    <?=$exception->getStatusCode() . ' ' . $exception->getReasonPhrase()?>
<?php endif;
