<?php

namespace App\Filament\Company\Pages\Reports;

use App\Contracts\ExportableReport;
use App\DTO\ReportDTO;
use App\Filament\Forms\Components\DateRangeSelect;
use App\Services\ExportService;
use App\Services\ReportService;
use App\Support\Column;
use App\Transformers\BalanceSheetReportTransformer;
use Filament\Forms\Form;
use Filament\Support\Enums\Alignment;
use Livewire\Attributes\Url;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BalanceSheet extends BaseReportPage
{
    protected static string $view = 'filament.company.pages.reports.balance-sheet';

    protected static bool $shouldRegisterNavigation = false;

    protected ReportService $reportService;

    protected ExportService $exportService;

    #[Url]
    public ?string $activeTab = 'summary';

    public function boot(ReportService $reportService, ExportService $exportService): void
    {
        $this->reportService = $reportService;
        $this->exportService = $exportService;
    }

    public function getTable(): array
    {
        return [
            Column::make('account_code')
                ->label('Account Code')
                ->toggleable()
                ->alignment(Alignment::Center),
            Column::make('account_name')
                ->label('Account')
                ->alignment(Alignment::Left),
            Column::make('ending_balance')
                ->label('Amount')
                ->alignment(Alignment::Right),
        ];
    }

    public function filtersForm(Form $form): Form
    {
        return $form
            ->inlineLabel()
            ->columns(3)
            ->schema([
                DateRangeSelect::make('dateRange')
                    ->label('As of')
                    ->selectablePlaceholder(false)
                    ->endDateField('asOfDate'),
                $this->getAsOfDateFormComponent()
                    ->hiddenLabel()
                    ->extraFieldWrapperAttributes([]),
            ]);
    }

    protected function buildReport(array $columns): ReportDTO
    {
        return $this->reportService->buildBalanceSheetReport($this->getFormattedAsOfDate(), $columns);
    }

    protected function getTransformer(ReportDTO $reportDTO): ExportableReport
    {
        return new BalanceSheetReportTransformer($reportDTO);
    }

    public function exportCSV(): StreamedResponse
    {
        return $this->exportService->exportToCsv($this->company, $this->report, endDate: $this->getFilterState('asOfDate'));
    }

    public function exportPDF(): StreamedResponse
    {
        return $this->exportService->exportToPdf($this->company, $this->report, endDate: $this->getFilterState('asOfDate'));
    }
}
