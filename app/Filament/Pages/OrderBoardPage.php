<?php

namespace App\Filament\Pages;

use Carbon\Carbon;
use App\Models\Order;
use Filament\Pages\Page;
use Filament\Actions\Action;

use Filament\Facades\Filament;
use Filament\Support\Enums\MaxWidth;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Actions\Contracts\HasActions;
use Filament\Pages\Concerns\InteractsWithFullPage;
use Filament\Actions\Concerns\InteractsWithActions;



class OrderBoardPage extends Page implements HasActions
{
    use InteractsWithActions;
    // protected static string $layout = 'layouts.filament-no-sidebar'; 

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'filament.pages.order-board-page';



    // public static function shouldRegisterNavigation(): bool
    // {
    //     return true;
    // }

    public static function shouldRegisterNavigation(): bool
    {
        if (auth()->guard('admin')->user()->super_admin == 1) {
            return false;
        }
        return true;
    }


    public static function getNavigationLabel(): string
    {
        return __('strings.order_board');
    }

    public function getTitle(): string
    {
        return __('strings.order_board');
    }


    public function getMaxContentWidth(): MaxWidth
    {
        return MaxWidth::Full;
    }

  public function getViewData(): array
{
    $auth = auth()->guard('admin')->user();

    $query = Order::with(['user', 'branch']);

    

    if ($auth->super_admin != 1) {
        $query->where('branch_id', $auth->branch_id);
    }

    $branch = $auth->super_admin != 1
        ? $auth->branch
        : null;

    $today = Carbon::today();

    $openingDateTime = $branch
        ? $today->copy()->setTimeFromTimeString($branch->opening_date)
        : null;

    $closingDateTime = $branch
        ? $today->copy()->setTimeFromTimeString($branch->close_date)
        : null;

    $queryForToday = clone $query;

    

    if ($openingDateTime && $closingDateTime) {
        if ($closingDateTime->lessThan($openingDateTime)) {
            // حالة: الفرع بيقفل تاني يوم (مثال: 20:00 → 12:00)
            $queryForToday->where(function ($q) use ($openingDateTime, $closingDateTime) {
                $q->whereBetween('created_at', [
                        $openingDateTime->copy()->utc(),
                        $openingDateTime->copy()->endOfDay()->utc()
                    ])
                  ->orWhereBetween('created_at', [
                        $closingDateTime->copy()->startOfDay()->utc(),
                        $closingDateTime->copy()->utc()
                    ]);
            });
        } else {
            // حالة: الفرع بيقفل في نفس اليوم (مثال: 08:00 → 23:00)
            $queryForToday->whereBetween('created_at', [
                $openingDateTime->copy()->utc(),
                $closingDateTime->copy()->utc()
            ]);
        }
    } else {
        // fallback لو مش فيه opening/closing محددين
        $queryForToday->whereDate('created_at', $today);
    }

$todayCairo = Carbon::today('Africa/Cairo');

$start = $todayCairo->copy()->startOfDay()->timezone('UTC');
$end   = $todayCairo->copy()->endOfDay()->timezone('UTC');

$testOrders = $query->clone()
    ->whereBetween('created_at', [$start, $end])
    ->get();

// dd([
//     'start' => $start->toDateTimeString(),
//     'end' => $end->toDateTimeString(),
//     'orders' => $testOrders->pluck('created_at')->toArray(),
// ]);



    $pendingOrders = $queryForToday->clone()->whereIn('status', [
        'pending',
        'under_receipt',
        'under_review',
        'in_preparation'
    ])->latest()->get();

    $completedOrders = $queryForToday->clone()->whereIn('status', [
        'prepared',
        'shipped',
        'arrived',
        'completed'
    ])->latest()->get();

    return [
        'pendingOrders'   => $pendingOrders,
        'completedOrders' => $completedOrders,
    ];
}
    public function changeStatusAction(): Action
    {
        return Action::make('changeStatus')
            ->label(__('strings.change_status'))
            ->color('primary')
            ->icon('heroicon-m-pencil-square')
            ->size('sm')
            ->form([
                Select::make('status')
                    ->label(__('strings.new_status'))
                    ->required()
                    ->options([
                        'pending' => __('strings.pending'),
                        'under_receipt' => __('strings.under_receipt'),
                        'under_review' => __('strings.under_review'),
                        'in_preparation' => __('strings.in_preparation'),
                        'prepared' => __('strings.prepared'),
                        'shipped' => __('strings.shipped'),
                        'arrived' => __('strings.arrived'),
                        'completed' => __('strings.completed'),
                        'canceled' => __('strings.canceled'),
                    ])
                    ->live()
                    ->columnSpanFull(),

                Textarea::make('cancelled_reason')
                    ->label(__('strings.cancel_reason'))
                    ->rows(3)
                    ->visible(fn($get) => $get('status') === 'canceled')
                    ->required(fn($get) => $get('status') === 'canceled')
                    ->columnSpanFull(),
            ])
            ->action(function (array $data, array $arguments): void {
                $order = Order::findOrFail($arguments['order_id']);

                $order->update([
                    'status' => $data['status'],
                    'cancelled_reason' => $data['status'] === 'canceled' ? $data['cancelled_reason'] : null,
                ]);

                Notification::make()
                    ->title(__('strings.order_status_updated_successfully'))
                    ->success()
                    ->send();
            })
            ->fillForm(function (array $arguments): array {
                return [
                    'status' => $arguments['current_status'] ?? 'pending',
                ];
            })
            ->modalHeading(__('strings.change_order_status'))
            ->modalSubmitActionLabel(__('strings.save_changes'))
            ->modalCancelActionLabel(__('strings.cancel'))
            ->modalWidth('md');
    }
}
