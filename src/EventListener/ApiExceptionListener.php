<?php

namespace App\EventListener;

use App\Enum\ApiError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ApiExceptionListener
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $request = $event->getRequest();

        if (!str_starts_with($request->getPathInfo(), '/api')) {
            return;
        }

        $exception = $event->getThrowable();

        [$statusCode, $errors] = match (true) {
            $exception instanceof NotFoundHttpException => [404, [ApiError::NOT_FOUND]],
            $exception instanceof MethodNotAllowedHttpException => [405, [ApiError::METHOD_NOT_ALLOWED]],
            $exception instanceof AccessDeniedHttpException,
            $exception instanceof AccessDeniedException => [403, [ApiError::ACCESS_DENIED]],
            $exception instanceof UnauthorizedHttpException => [401, [ApiError::USER_NOT_AUTHENTICATED]],
            default => [500, [ApiError::INTERNAL_SERVER_ERROR]],
        };

        $event->setResponse(new JsonResponse([
            'code' => $statusCode,
            'errors' => $errors,
        ], $statusCode));
    }
}
