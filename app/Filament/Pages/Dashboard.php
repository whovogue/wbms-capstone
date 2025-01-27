<?php

namespace App\Filament\Pages;

use App\Livewire\BarangayClearanceChart;
use App\Livewire\BarangayRequestChart;
use App\Livewire\BillerActiveWaterConnection;
use App\Livewire\ConsumersPerPurokChart;
use App\Livewire\LatestUsers;
use App\Livewire\RevenueChart;
use App\Livewire\StatsOverview;
use App\Livewire\UsersPerPurokChart;
use App\Livewire\WaterConsumptionChart;
use App\Models\Announcement;
use App\Models\Bill;
use App\Models\User;
use App\Models\WaterConnection;
use Closure;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class Dashboard extends Page implements HasForms, HasTable
{
    use InteractsWithForms, InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static string $view = 'filament.pages.dashboard';

    public $bill;

    public $labels;

    public $data;

    public $spending;

    public ?array $waterConnectionData = [];

    public ?array $requestwaterConnectionData = [];

    public $userWaterConnection;

    public $disconnectType;

    public $notification = [];

    public function mount()
    {
        $auth = auth()->user();

        if ($auth->isConsumer() && $auth->waterConnections()->exists()) {
            $this->userWaterConnection = $auth->waterConnections()->first()?->id;
            $this->getConsumptionChart();
            $this->getSpendingChart();
        }

        $billQuery = Bill::query();
        $firstConnection = $auth->waterConnections()->first();

        if ($firstConnection) {
            if ($firstConnection['pivot']['status'] === 'disconnected') {

                $disconnectedAt = $auth->disconnectedUsers()->first()?->disconnected_at;

                $this->bill = $billQuery->where('water_connection_id', $firstConnection->id)
                    ->where('status', 'pending')
                    ->where('created_at', '<', $disconnectedAt)
                    ->sum('billing_amount');
            } else {

                $this->bill = $billQuery->where('water_connection_id', $auth->waterConnections()->first()?->id)->where('status', 'pending')->sum('billing_amount');
            }
        }
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Announcement::orderBy('created_at', 'desc')
            )
            ->columns([
                TextColumn::make('title')->searchable(),
                TextColumn::make('description')->searchable(),
                TextColumn::make('created_at')->dateTime('F d Y h:i A')->searchable(),
            ])
            ->filters([
                // ...
            ])
            ->actions([])
            ->bulkActions([
                // ...
            ])
            ->heading('Announcements');
    }

    public function getConsumptionChart()
    {
        $user = auth()->user();
        $firstConnection = $user->waterConnections()->first();
        $disconnectedAt = null;

        if ($firstConnection && $firstConnection['pivot']['status'] === 'disconnected') {
            // If the connection's status is disconnected, get the disconnected_at timestamp
            $disconnectedAt = $user->disconnectedUsers()->first()?->disconnected_at;
        }

        $monthlyConsumption = DB::table(DB::raw('(SELECT 1 AS month UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9 UNION ALL SELECT 10 UNION ALL SELECT 11 UNION ALL SELECT 12) AS months'))
            ->leftJoin(
                DB::raw('(
            SELECT 
                MONTH(created_at) as month, 
                SUM(total_consumption) as total 
            FROM readings 
            WHERE YEAR(created_at) = YEAR(CURRENT_DATE) 
            AND water_connection_id = '.$firstConnection->id.'
            '.($disconnectedAt ? 'AND created_at < "'.$disconnectedAt.'"' : '').'
            GROUP BY month
        ) AS consumption'),
                'months.month',
                '=',
                'consumption.month'
            )
            ->select('months.month', DB::raw('COALESCE(consumption.total, 0) as total'))
            ->orderBy('months.month')
            ->get()
            ->map(fn ($item) => $item->total);

        $this->labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $this->data = $monthlyConsumption;
    }

    public function getSpendingChart()
    {
        $user = auth()->user();
        $firstConnection = $user->waterConnections()->first();
        $disconnectedAt = null;

        if ($firstConnection && $firstConnection['pivot']['status'] === 'disconnected') {
            // If the connection's status is disconnected, get the disconnected_at timestamp
            $disconnectedAt = $user->disconnectedUsers()->first()?->disconnected_at;
        }

        $monthlySpending = DB::table(DB::raw('(SELECT 1 AS month UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9 UNION ALL SELECT 10 UNION ALL SELECT 11 UNION ALL SELECT 12) AS months'))
            ->leftJoin(
                DB::raw('(
            SELECT 
                MONTH(created_at) as month, 
                SUM(partial_payment) as total 
            FROM payments 
            WHERE YEAR(created_at) = YEAR(CURRENT_DATE) 
            AND water_connection_id = '.$firstConnection->id.'
            '.($disconnectedAt ? 'AND created_at < "'.$disconnectedAt.'"' : '').'
            GROUP BY month
        ) AS spending'),
                'months.month',
                '=',
                'spending.month'
            )
            ->select('months.month', DB::raw('COALESCE(spending.total, 0) as total'))
            ->orderBy('months.month')
            ->get()
            ->map(fn ($item) => $item->total);

        $this->spending = $monthlySpending;
    }

    protected function getHeaderWidgets(): array
    {

        $adminWidgets = [
            StatsOverview::class,
            // LatestUsers::class,
            WaterConsumptionChart::class,
            RevenueChart::class,
            ConsumersPerPurokChart::class,
            UsersPerPurokChart::class,
            BarangayRequestChart::class,
            BarangayClearanceChart::class,
        ];

        $billerWidgets = [
            BillerActiveWaterConnection::class,
        ];

        switch (auth()->user()->role) {
            case 'admin':
                return $adminWidgets;
            case 'reader':
                return $billerWidgets;
            case 'consumers':
                return [];
            case 'clerk':
                return [
                    UsersPerPurokChart::class,
                    BarangayRequestChart::class,
                    BarangayClearanceChart::class,
                ];
            default:
                return [];
        }
    }

    public function disconnectRequest()
    {

        if ($this->disconnectType == 'disconnect') {
            $connection = WaterConnection::where('reference_id', auth()->user()->waterConnections()->first()?->reference_id)->first();

            Notification::make()
                ->title(auth()->user()->name.' wants to disconnect from '.$connection['reference_id'].' water connection')
                ->success()
                ->actions([
                    Action::make('view')
                        ->button()
                        ->markAsRead()
                        ->url('/app/water-connections/'.$connection->id.'/edit?activeRelationManager=2'),
                ])
                ->sendToDatabase(User::where('role', 'admin')->whereNot('id', auth()->user()->id)->get());
        } else {
            // $data = auth()->user()->waterConnections()
            //     ->update([
            //         'water_connections_users.status' => 'disconnect',
            //     ]);

            Notification::make()
                ->title(auth()->user()->name.' wants to disconnect '.auth()->user()->waterConnections()->first()['reference_id'].' water connection app')
                ->success()
                ->actions([
                    Action::make('view')
                        ->button()
                        ->markAsRead()
                        ->url('/app/water-connections/'.auth()->user()->waterConnections()->first()['id'].'/edit'),
                ])
                ->sendToDatabase(User::where('role', 'admin')->whereNot('id', auth()->user()->id)->get());
        }
        Notification::make()
            ->title('Disconnection Request Submitted')
            ->success()
            ->send();

        redirect('/app');
    }

    protected function getForms(): array
    {
        return [
            'joinRequestForm',
            'requesWaterConnectionForm',
        ];
    }

    public function joinRequestForm(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('reference_id')
                    ->required()
                    ->label('Reference ID')
                    ->rules([
                        fn (): Closure => function (string $attribute, $value, Closure $fail) {

                            $data = WaterConnection::where('status', 'active')
                                ->pluck('reference_id');

                            $matchFound = false;

                            foreach ($data as $ref) {
                                if ($ref === $value) {
                                    $matchFound = true;
                                    break;
                                }
                            }

                            if (! $matchFound) {
                                $fail('The :attribute is invalid!');
                            }
                        },
                    ]),
            ])
            ->statePath('waterConnectionData');
    }

    public function requesWaterConnectionForm(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')->required(),
                TextInput::make('address')->required(),
                Select::make('purok')
                    ->options([
                        '1' => '1',
                        '1A' => '1A',
                        '2' => '2',
                        '3A' => '3A',
                        '3B' => '3B',
                        '4A' => '4A',
                        '4B' => '4B',
                        '5A' => '5A',
                        '5B' => '5B',
                        '6' => '6',
                        '6A1' => '6A1',
                        '6B' => '6B',
                    ])
                    ->label('Purok')
                    ->required(),
                TextInput::make('phone_number')->tel()->telRegex('/^(0|63)\d{10}$/'),
            ])
            ->statePath('requestwaterConnectionData');
    }

    public function submitConnection()
    {
        $data = $this->joinRequestForm->getState();

        $connection = WaterConnection::where('reference_id', $data['reference_id'])->first();

        $connection->users()->attach(auth()->user()->id, [
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Notification::make()
            ->title('Request Submitted')
            ->success()
            ->send();

        Notification::make()
            ->title('User '.auth()->user()->name.' Request to be connected to '.$data['reference_id'])
            ->success()
            ->actions([
                Action::make('view')
                    ->button()
                    ->markAsRead()
                    ->url('/app/water-connections/'.$connection->id.'/edit?activeRelationManager=2'),
            ])
            ->sendToDatabase(User::where('role', 'admin')->whereNot('id', auth()->user()->id)->get());

        redirect('/app');
    }

    public function requestsubmitConnection()
    {
        $data = $this->requesWaterConnectionForm->getState();
        $data['reference_id'] = $this->generateReferenceId();
        $data['status'] = 'pending';
        $connection = WaterConnection::create($data);

        $connection->users()->attach(auth()->user()->id, [
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Notification::make()
            ->title('Request Submitted')
            ->success()
            ->send();

        Notification::make()
            ->title('User '.auth()->user()->name.' Request a new water connection')
            ->success()
            ->actions([
                Action::make('view')
                    ->button()
                    ->markAsRead()
                    ->url('/app/water-connections/'.$connection->id.'/edit'),
            ])
            ->sendToDatabase(User::where('role', 'admin')->whereNot('id', auth()->user()->id)->get());

        redirect('/app');
    }

    private function generateReferenceId(): string
    {
        do {
            // Generate a random 10-digit number
            $referenceId = 'WBMS'.random_int(1000000, 9999999); // Adjust the range as needed
        } while (DB::table('water_connections')->where('reference_id', $referenceId)->exists());

        return $referenceId;
    }
}
