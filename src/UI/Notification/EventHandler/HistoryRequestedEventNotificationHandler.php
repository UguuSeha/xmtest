<?php
declare(strict_types=1);

namespace App\UI\Notification\EventHandler;

use App\UI\Event\HistoryRequestedEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mime\Email;

#[AsMessageHandler]
class HistoryRequestedEventNotificationHandler
{
    public function __construct(
        private MailerInterface $mailer,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(HistoryRequestedEvent $event)
    {
        $email = (new Email())
            ->to($event->email)
            ->subject($event->company->getName())
            ->text(sprintf(
                "From %s to %s",
                $event->start->format('Y-m-d'),
                $event->end->format('Y-m-d'),
            ));

        try {
            $this->mailer->send($email);
        } catch (\Exception $exception) {
            $this->logger->critical('Failed to send email', [
                'exception' => $exception,
            ]);
        }
    }
}
