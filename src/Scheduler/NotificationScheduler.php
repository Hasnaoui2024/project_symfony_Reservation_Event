<?php
namespace App\Scheduler;

use App\Service\EventReminderService;
use App\Message\NotificationMessage;
use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;

#[AsSchedule]
class NotificationScheduler implements ScheduleProviderInterface
{
    private EventReminderService $reminderService;

    public function __construct(EventReminderService $reminderService)
    {
        $this->reminderService = $reminderService;
    }

    public function getSchedule(): Schedule
    {
        return (new Schedule())
            ->add(RecurringMessage::every('1 day', new NotificationMessage('daily', 'Daily Reminder', 'Check out today\'s events!')));
    }
}