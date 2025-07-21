<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Filament\Resources\PaymentResource\RelationManagers;
use App\Models\Payment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['debt']);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::whereDate('created_at', today())->count();
    }

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('debt_id')
                    ->relationship(
                        'debt',
                        'customer_name',
                        fn ($query) => $query->where('status', '!=', 'paid')
                    )
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                        if ($state) {
                            $debt = \App\Models\Debt::find($state);
                            if ($debt) {
                                $remainingAmount = $debt->amount - $debt->paid_amount;
                                $set('amount', $remainingAmount);
                            }
                        }
                    })
                    ->label('Hutang')
                    ->helperText(function ($state) {
                        if ($state) {
                            $debt = \App\Models\Debt::find($state);
                            if ($debt) {
                                $remaining = $debt->amount - $debt->paid_amount;
                                return "Sisa hutang: Rp " . number_format($remaining, 0, ',', '.');
                            }
                        }
                        return null;
                    }),
                Forms\Components\TextInput::make('amount')
                    ->required()
                    ->numeric()
                    ->live()
                    ->label('Jumlah Pembayaran')
                    ->rules([
                        'numeric',
                        'required',
                        function ($get) {
                            return function (string $attribute, $value, \Closure $fail) use ($get) {
                                $debtId = $get('debt_id');
                                if ($debtId) {
                                    $debt = \App\Models\Debt::find($debtId);
                                    if ($debt) {
                                        $remaining = $debt->amount - $debt->paid_amount;
                                        if ($value > $remaining) {
                                            $fail("Jumlah pembayaran tidak boleh melebihi sisa hutang (Rp " . number_format($remaining, 0, ',', '.') . ")");
                                        }
                                    }
                                }
                            };
                        },
                    ]),
                Forms\Components\Select::make('payment_method')
                    ->options([
                        'cash' => 'Tunai',
                        'qris' => 'QRIS',
                        'transfer' => 'Transfer Bank',
                    ])
                    ->required()
                    ->label('Metode Pembayaran'),
                Forms\Components\TextInput::make('reference_number')
                    ->maxLength(255)
                    ->label('Nomor Referensi'),
                Forms\Components\Textarea::make('notes')
                    ->maxLength(65535)
                    ->label('Catatan'),
                Forms\Components\Hidden::make('user_id')
                    ->default(fn () => Auth::id()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('60s')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Tanggal'),
                Tables\Columns\TextColumn::make('debt.customer_name')
                    ->searchable()
                    ->label('Pelanggan'),
                Tables\Columns\TextColumn::make('amount')
                    ->money('idr')
                    ->label('Jumlah'),
                Tables\Columns\TextColumn::make('payment_method')
                    ->badge()
                    ->label('Metode'),
                Tables\Columns\TextColumn::make('reference_number')
                    ->searchable()
                    ->label('No. Ref'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'edit' => Pages\EditPayment::route('/{record}/edit'),
        ];
    }
}
