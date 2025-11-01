<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingConfirmed extends Notification implements ShouldQueue // <-- Implement ShouldQueue for async sending
{
    use Queueable;

    protected $booking;

    /**
     * Create a new notification instance.
     */
    public function __construct(Booking $booking)
    {
        $this->booking = $booking->load('ticket.event'); // Eager load necessary relations
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $eventTitle = $this->booking->ticket->event->title;

        return (new MailMessage)
                    ->subject('Your Booking for ' . $eventTitle . ' is Confirmed!')
                    ->greeting('Hello ' . $notifiable->name . ',')
                    ->line("We are pleased to confirm your booking (ID: {$this->booking->id}) for the event: **{$eventTitle}**.")
                    ->line("Details:")
                    ->line("- **Ticket Type:** {$this->booking->ticket->type}")
                    ->line("- **Quantity:** {$this->booking->quantity}")
                    ->line("- **Total Paid:** $" . number_format($this->booking->payment->amount, 2))
                    ->action('View Your Booking', url('/bookings/' . $this->booking->id)) // Mock URL
                    ->line('Thank you for using our service!');
    }

    // ... toArray method (not required by assignment)
}