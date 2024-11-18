<?php

namespace HiPay\Payment\ScheduledTask\UpdatePaymentStatus;

use HiPay\Payment\Service\NotificationService;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskCollection;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(handles: UpdatePaymentStatusTask::class)]
class UpdatePaymentStatusTaskHandler extends ScheduledTaskHandler
{
    /**
     * @param EntityRepository<ScheduledTaskCollection> $scheduledTaskRepository
     */
    public function __construct(
        protected EntityRepository $scheduledTaskRepository,
        private NotificationService $notificationService
    ) {
        parent::__construct($scheduledTaskRepository);
    }

    public function run(): void
    {
        $this->notificationService->dispatchNotifications();
    }
}
