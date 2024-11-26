<?php

namespace HiPay\Payment\Controller;

use HiPay\Payment\Logger\HipayLogger;
use HiPay\Payment\Service\NotificationService;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller use to receive notifications from Hipay.
 */
#[Route(defaults: ['_routeScope' => ['api'], 'auth_required' => false])]
class NotificationController
{
    private HipayLogger $logger;

    public function __construct(
        HipayLogger $hipayLogger,
        private NotificationService $notificationService
    ) {
        $this->logger = $hipayLogger->setChannel(HipayLogger::API);
    }

    #[Route('/api/hipay/notify', name: 'store-api.action.hipay.notification', methods: ['POST', 'GET'])]
    public function receiveNotification(Request $request, SalesChannelContext $context): JsonResponse
    {
        try {
            $this->notificationService->saveNotificationRequest($request, $context->getContext());
        } catch (\Throwable $e) {
            $message = 'Notification fail : ' . $e->getMessage();
            $this->logger->error($message);

            $code = Response::HTTP_INTERNAL_SERVER_ERROR;
            if ($e instanceof UnauthorizedHttpException) {
                $code = Response::HTTP_UNAUTHORIZED;
            } elseif ($e instanceof AccessDeniedException) {
                $code = Response::HTTP_FORBIDDEN;
            }

            return new JsonResponse(['success' => false, 'error' => $message], $code);
        }

        return new JsonResponse(['success' => true]);
    }
}
