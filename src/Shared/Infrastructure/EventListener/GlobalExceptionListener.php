<?php

namespace App\Shared\Infrastructure\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;

class GlobalExceptionListener
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
        $message = 'Internal Server Error';
        $errors = null;

        if ($exception instanceof HttpExceptionInterface) {
            $statusCode = $exception->getStatusCode();
            $message = $exception->getMessage();
        }

        $data = [
            'success' => false,
            'error' => $message,
        ];

        if ($errors) {
            $data['errors'] = $errors;
        }

        if ($_ENV['APP_ENV'] === 'dev' && $statusCode === 500) {
            $data['debug'] = [
                'message' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ];
        }

        $event->setResponse(new JsonResponse($data, $statusCode));
    }
}
