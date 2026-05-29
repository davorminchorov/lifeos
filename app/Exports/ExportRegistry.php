<?php

declare(strict_types=1);

namespace App\Exports;

use App\Exports\Contracts\ModuleExporter;
use App\Exports\Modules\BudgetExporter;
use App\Exports\Modules\ContractExporter;
use App\Exports\Modules\CreditNoteExporter;
use App\Exports\Modules\CustomerExporter;
use App\Exports\Modules\CycleMenuExporter;
use App\Exports\Modules\ExpenseExporter;
use App\Exports\Modules\HolidayExporter;
use App\Exports\Modules\InvestmentDividendExporter;
use App\Exports\Modules\InvestmentExporter;
use App\Exports\Modules\InvestmentTransactionExporter;
use App\Exports\Modules\InvoiceExporter;
use App\Exports\Modules\IouExporter;
use App\Exports\Modules\JobApplicationExporter;
use App\Exports\Modules\JobApplicationInterviewExporter;
use App\Exports\Modules\JobApplicationOfferExporter;
use App\Exports\Modules\PaymentExporter;
use App\Exports\Modules\SubscriptionExporter;
use App\Exports\Modules\UtilityBillExporter;
use App\Exports\Modules\WarrantyExporter;
use InvalidArgumentException;

class ExportRegistry
{
    /**
     * Exporter classes in the order they should appear in the UI and ZIP.
     *
     * @var array<int, class-string<ModuleExporter>>
     */
    private const EXPORTERS = [
        ExpenseExporter::class,
        BudgetExporter::class,
        SubscriptionExporter::class,
        UtilityBillExporter::class,
        InvestmentExporter::class,
        InvestmentTransactionExporter::class,
        InvestmentDividendExporter::class,
        InvoiceExporter::class,
        PaymentExporter::class,
        CreditNoteExporter::class,
        CustomerExporter::class,
        ContractExporter::class,
        WarrantyExporter::class,
        IouExporter::class,
        JobApplicationExporter::class,
        JobApplicationInterviewExporter::class,
        JobApplicationOfferExporter::class,
        CycleMenuExporter::class,
        HolidayExporter::class,
    ];

    /** @var array<string, ModuleExporter>|null */
    private ?array $resolved = null;

    /**
     * @return array<string, ModuleExporter>
     */
    public function all(): array
    {
        if ($this->resolved === null) {
            $this->resolved = [];

            foreach (self::EXPORTERS as $class) {
                $exporter = new $class;
                $this->resolved[$exporter->key()] = $exporter;
            }
        }

        return $this->resolved;
    }

    /**
     * @return array<int, string>
     */
    public function keys(): array
    {
        return array_keys($this->all());
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->all());
    }

    public function get(string $key): ModuleExporter
    {
        if (! $this->has($key)) {
            throw new InvalidArgumentException("Unknown export module [{$key}].");
        }

        return $this->all()[$key];
    }

    /**
     * Module metadata for the UI: [['key' => ..., 'label' => ...], ...].
     *
     * @return array<int, array{key: string, label: string}>
     */
    public function toOptions(): array
    {
        return array_map(
            fn (ModuleExporter $exporter): array => [
                'key' => $exporter->key(),
                'label' => $exporter->label(),
            ],
            array_values($this->all()),
        );
    }
}
