<?php
namespace App\MessageHandler;

use App\Message\NotificationMessage;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class NotificationMessageHandler
{
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function __invoke(NotificationMessage $message): void
    {
        $email = (new Email())
            ->to($message->getEmail())
            ->subject($message->getSubject())
            ->text($message->getContent());

        $this->mailer->send($email);
    }
}