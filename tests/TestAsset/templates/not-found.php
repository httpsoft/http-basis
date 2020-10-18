<?php

declare(strict_types=1);

/** @var Devanych\View\Renderer $this */
/** @var Psr\Http\Message\ServerRequestInterface $request */
/** @var bool $debug */
?>

<?php if ($debug) : ?>
    <?=get_class($this) . ':' . get_class($request)?>
<?php else : ?>
    <?='404 Not Found'?>
<?php endif;
