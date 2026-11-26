<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Commands\HandlePaymentWebhookCommand;
use App\Contracts\CommandBus;
use App\Models\Payment;
use App\Payments\FakePayProvider;
use Illuminate\Console\Command;

/** Plays the role of the fictional acquirer on a developer machine. */
final class SimulatePaymentCommand extends Command
{
    protected $signature = 'payments:simulate {payment : Payment public id} {--fail : Report a declined payment}';

    protected $description = 'Send a signed FakePay webhook for a pending payment (local development only)';

    public function handle(FakePayProvider $provider, CommandBus $bus): int
    {
        if (!app()->environment('local', 'testing')) {
            $this->error('Only available in the local environment.');

            return self::FAILURE;
        }

        $payment = Payment::query()->where('public_id', $this->argument('payment'))->first();
        if ($payment === null) {
            $this->error('Payment not found.');

            return self::FAILURE;
        }

        $body = (string)json_encode([
            'payment_id'   => $payment->public_id,
            'status'       => $this->option('fail') ? 'failed' : 'succeeded',
            'amount_cents' => $payment->amount_cents,
        ]);

        $result = $bus->dispatch(new HandlePaymentWebhookCommand(FakePayProvider::NAME, $body, $provider->sign($body)));
        $this->info("Payment is now '{$result->status->value}'.");

        return self::SUCCESS;
    }
}
