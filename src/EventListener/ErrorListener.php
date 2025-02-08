<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Twig\Environment;

class ErrorListener
{
    private $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();
        $statusCode = $exception instanceof HttpExceptionInterface ? $exception->getStatusCode() : 500;

        // Vérifie si un template spécifique existe
        $template = "/bundles/TwigBundle/Exception/error{$statusCode}.html.twig";
        if (!$this->twig->getLoader()->exists($template)) {
            $template = "bundles/TwigBundle/Exception/error.html.twig"; // Fallback
        }

        // Rend la page Twig
        $response = new Response($this->twig->render($template, ['status_code' => $statusCode]));
        $response->setStatusCode($statusCode);
        $event->setResponse($response);
    }
}
