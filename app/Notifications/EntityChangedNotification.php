<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

final class EntityChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Model $entity,
        public string $action,
        public string $performedBy
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        class_basename($this->entity);
        $title = $this->getTitle();

        return (new MailMessage)
            ->subject($title)
            ->line($this->getMessage())
            ->action('View Entity', url('/'))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $entityName = class_basename($this->entity);

        return [
            'title' => $this->getTitle(),
            'message' => $this->getMessage(),
            'data' => $this->entity->toArray(),
            'entity' => $entityName,
            'entity_id' => $this->entity->getKey(),
            'action' => $this->action,
            'performed_by' => $this->performedBy,
        ];
    }

    private function getTitle(): string
    {
        $entityName = Str::headline(class_basename($this->entity));
        $identifier = $this->getEntityIdentifier();

        return "{$entityName} {$this->action}" . ($identifier ? ": {$identifier}" : '');
    }

    private function getMessage(): string
    {
        $entityName = Str::headline(class_basename($this->entity));
        $identifier = $this->getEntityIdentifier();

        $entityString = $identifier ? "{$entityName} {$identifier}" : "An entity of type {$entityName}";

        return "{$entityString} has been {$this->action} by {$this->performedBy}.";
    }

    private function getEntityIdentifier(): ?string
    {
        if (isset($this->entity->name)) {
            return $this->entity->name;
        }

        if (isset($this->entity->first_name) && isset($this->entity->last_name)) {
            return "{$this->entity->first_name} {$this->entity->last_name}";
        }

        return $this->entity->title ?? $this->entity->email ?? null;
    }
}
