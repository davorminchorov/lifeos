<?php

namespace Tests\Feature;

use App\Jobs\ImportInvestmentsCsv;
use App\Models\Investment;
use App\Models\InvestmentTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class InvestmentCsvImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_imports_investment_transactions_from_csv()
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $this->actingAs($user);

        $csv = implode("\n", [
            'Action,Time,ISIN,Ticker,Name,Notes,ID,No. of shares,Price / share,Currency (Price / share),Exchange rate,Currency (Result),Total,Currency (Total) Deposit',
            'Buy,2025-08-20,US0378331005,AAPL,Apple Inc.,First buy,ORD-1,10,200,USD,,USD,2000,USD',
            'Sell,2025-09-01,US0378331005,AAPL,Apple Inc.,Partial sell,ORD-2,2,210,USD,,USD,420,USD',
        ]);

        $file = UploadedFile::fake()->createWithContent('investments.csv', $csv);

        Queue::fake();

        $response = $this->post(route('investments.import'), [
            'file' => $file,
        ]);

        $response->assertRedirect(route('investments.index'));

        $capturedJob = null;
        Queue::assertPushed(ImportInvestmentsCsv::class, function ($job) use ($user, &$capturedJob) {
            $capturedJob = $job;

            return $job->userId === $user->id;
        });

        // Run the job synchronously to verify DB side-effects
        $this->assertNotNull($capturedJob, 'Job was not captured for execution');

        // Store the CSV in fake storage at the path the job expects
        Storage::put($capturedJob->storedPath, $csv);

        $capturedJob->handle();

        $this->assertDatabaseHas('investments', [
            'user_id' => $user->id,
            'symbol_identifier' => 'AAPL',
            'name' => 'Apple Inc.',
        ]);

        $investment = Investment::where('user_id', $user->id)->where('symbol_identifier', 'AAPL')->first();
        $this->assertNotNull($investment);

        // Two transactions created
        $this->assertEquals(2, InvestmentTransaction::where('investment_id', $investment->id)->count());

        // Quantity should reflect buy 10 then sell 2 => 8
        $investment->refresh();
        $this->assertEquals(8.0, (float) $investment->quantity);
    }
}
