<?php

declare(strict_types=1);

namespace Tests\Feature\Mcp;

use App\Mail\WeeklyDigestMail;
use App\Mcp\LifeOsServer;
use App\Mcp\Tools\Digest\Send as DigestSend;
use App\Models\AgentToken;
use App\Models\DigestLog;
use App\Models\PendingAction;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Agents\PendingActionApplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class Phase10DigestTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        ['user' => $this->user, 'tenant' => $this->tenant] = $this->setupTenantContext();
        [$token] = AgentToken::issue($this->user, $this->tenant, 'phpunit', ['*']);
        App::instance('agent.token', $token);

        Mail::fake();
    }

    private function digestArgs(array $overrides = []): array
    {
        return array_merge([
            'week_starts_on' => '2026-05-04',
            'subject' => 'LifeOS week of 2026-05-04',
            'body_text' => "# Weekly digest\n\nThis week was fine.",
        ], $overrides);
    }

    public function test_digest_send_queues_pending_action_without_sending(): void
    {
        LifeOsServer::tool(DigestSend::class, $this->digestArgs())
            ->assertOk()
            ->assertStructuredContent(function (array $content): bool {
                $this->assertSame(PendingAction::STATUS_PENDING, $content['status']);
                $this->assertFalse($content['auto_applied']);

                return true;
            });

        $this->assertSame(1, PendingAction::query()->where('tool', 'digest.send')->count());
        $this->assertSame(0, DigestLog::query()->count());
        Mail::assertNothingSent();
    }

    public function test_digest_send_idempotent_on_week(): void
    {
        LifeOsServer::tool(DigestSend::class, $this->digestArgs(['subject' => 'first']));
        LifeOsServer::tool(DigestSend::class, $this->digestArgs(['subject' => 'second-different-text']));

        $this->assertSame(1, PendingAction::query()->count());
    }

    public function test_apply_sends_mail_and_records_log(): void
    {
        LifeOsServer::tool(DigestSend::class, $this->digestArgs());

        $action = PendingAction::query()->firstOrFail();
        app(PendingActionApplier::class)->apply($action, $this->user);

        $this->assertSame(1, DigestLog::query()->count());
        $log = DigestLog::query()->first();
        $this->assertSame($this->user->email, $log->recipient_email);
        $this->assertNotNull($log->sent_at);

        Mail::assertSent(WeeklyDigestMail::class, function (WeeklyDigestMail $mail) {
            return $mail->hasTo($this->user->email)
                && $mail->weekStartsOn === '2026-05-04';
        });
    }

    public function test_re_apply_for_same_week_short_circuits_without_double_send(): void
    {
        // First action queued + applied.
        LifeOsServer::tool(DigestSend::class, $this->digestArgs());
        $first = PendingAction::query()->firstOrFail();
        app(PendingActionApplier::class)->apply($first, $this->user);

        // Force a second pending action with a manually different idempotency
        // (simulates a race / manual DB insert) for the same week.
        $duplicate = PendingAction::create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'agent_token_id' => $first->agent_token_id,
            'tool' => 'digest.send',
            'action' => PendingAction::ACTION_CREATE,
            'payload' => $this->digestArgs(['subject' => 'duplicate']),
            'idempotency_key' => hash('sha256', 'manual-duplicate'),
            'status' => PendingAction::STATUS_PENDING,
        ]);

        Mail::fake(); // reset Mail spy
        app(PendingActionApplier::class)->apply($duplicate, $this->user);

        // digest_logs should still be 1 (unique on tenant + week_starts_on),
        // and Mail::send should NOT have been called the second time.
        $this->assertSame(1, DigestLog::query()->count());
        Mail::assertNothingSent();
    }

    public function test_revert_deletes_log_row_within_window(): void
    {
        LifeOsServer::tool(DigestSend::class, $this->digestArgs());
        $action = PendingAction::query()->firstOrFail();
        $applied = app(PendingActionApplier::class)->apply($action, $this->user);
        $this->assertSame(1, DigestLog::query()->count());

        app(PendingActionApplier::class)->revert($applied, $this->user);

        // Email is already sent (can't be unsent). Revert clears the log so
        // the next pending_action for the same week can re-send if needed.
        $this->assertSame(0, DigestLog::query()->count());
    }

    public function test_auto_apply_kicks_in_after_first_approval(): void
    {
        // Tenant opts into auto-apply for digest.send.
        $this->tenant->forceFill([
            'tool_auto_apply' => ['digest.send' => true],
        ])->save();

        // Week 1: still pending (no prior approval).
        LifeOsServer::tool(DigestSend::class, $this->digestArgs(['week_starts_on' => '2026-05-04']));
        $week1 = PendingAction::query()->firstOrFail();
        $this->assertSame(PendingAction::STATUS_PENDING, $week1->status);
        app(PendingActionApplier::class)->apply($week1, $this->user);

        // Week 2: should auto-apply because a prior digest.send was approved
        // within the last 90 days.
        Mail::fake();
        LifeOsServer::tool(DigestSend::class, $this->digestArgs(['week_starts_on' => '2026-05-11']));
        $week2 = PendingAction::query()->where('payload->week_starts_on', '2026-05-11')->firstOrFail();
        $this->assertSame(PendingAction::STATUS_APPLIED, $week2->status);
        Mail::assertSent(WeeklyDigestMail::class);
    }

    public function test_auto_apply_does_not_kick_in_without_prior_approval(): void
    {
        $this->tenant->forceFill([
            'tool_auto_apply' => ['digest.send' => true],
        ])->save();

        LifeOsServer::tool(DigestSend::class, $this->digestArgs());
        $action = PendingAction::query()->firstOrFail();

        $this->assertSame(PendingAction::STATUS_PENDING, $action->status);
        Mail::assertNothingSent();
    }
}
