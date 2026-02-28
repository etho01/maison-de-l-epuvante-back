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
    public function __construct(
        private string $environment
    ) {}

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

        $responseData = [
            'code' => $statusCode,
            'errors' => $errors,
        ];

        // Ajouter le message et le traceback en mode dev
        if ($this->environment === 'dev') {
            $responseData['debug'] = [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $this->formatTrace($exception->getTrace()),
            ];
        } else {
            // En production, ajouter seulement le message pour les erreurs 500
            if ($statusCode === 500) {
                $responseData['debug'] = [
                    'message' => $exception->getMessage(),
                ];
            }
        }

        $event->setResponse(new JsonResponse($responseData, $statusCode));
    }

    private function formatTrace(array $trace): array
    {
        return array_map(function ($frame) {
            return [
                'file' => $frame['file'] ?? 'unknown',
                'line' => $frame['line'] ?? 0,
                'function' => $frame['function'] ?? 'unknown',
                'class' => $frame['class'] ?? null,
            ];
        }, array_slice($trace, 0, 10)); // Limiter à 10 frames pour éviter des réponses trop grandes
    }
}
