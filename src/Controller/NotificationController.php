<?php

namespace HiPay\Payment\Controller;

use HiPay\Payment\Logger\HipayLogger;
use HiPay\Payment\Service\NotificationService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Controller use to receive notifications from Hipay.
 */
#[Route(defaults: ['_routeScope' => 'api', 'auth_required' => false])]
class NotificationController
{
    private HipayLogger $logger;

    private NotificationService $notificationService;

    public function __construct(
        HipayLogger $hipayLogger,
        NotificationService $notificationService
    ) {
        $this->logger = $hipayLogger->setChannel(HipayLogger::API);
        $this->notificationService = $notificationService;
    }

    #[Route('/api/hipay/notify', name: 'store-api.action.hipay.notification', methods: ['POST', 'GET'])]
    public function receiveNotification(Request $request): JsonResponse
    {
        try {
            $this->notificationService->saveNotificationRequest($request);
        } catch (\Throwable $e) {
            $message = 'Notification fail : '.$e->getMessage();
            $this->logger->error($message);

            $code = 500;
            if ($e instanceof UnauthorizedHttpException) {
                $code = 401;
            } elseif ($e instanceof AccessDeniedException) {
                $code = 403;
            }

            return new JsonResponse(['success' => false, 'error' => $message], $code);
        }

        return new JsonResponse(['success' => true]);
    }
}
