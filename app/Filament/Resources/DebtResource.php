<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DebtResource\Pages;
use App\Filament\Resources\DebtResource\RelationManagers;
use App\Models\Debt;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class DebtResource extends Resource
{
    protected static ?string $model = Debt::class;

    protected static ?string $recordTitleAttribute = 'customer_name';

    protected static ?string $navigationLabel = 'Hutang';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', '!=', 'paid')->count();
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withCount('payments')
            ->withSum('payments', 'amount');
    }

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('customer_name')
                    ->required()
                    ->maxLength(255)
                    ->label('Nama Pelanggan'),
                Forms\Components\TextInput::make('amount')
                    ->required()
                    ->numeric()
                    ->label('Jumlah Hutang'),
                Forms\Components\DatePicker::make('due_date')
                    ->label('Jatuh Tempo'),
                Forms\Components\Select::make('status')
                    ->options([
                        'unpaid' => 'Belum Lunas',
                        'partial' => 'Sebagian',
                        'paid' => 'Lunas',
                    ])
                    ->default('unpaid')
                    ->disabled()
                    ->label('Status'),
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
            ->columns([
                Tables\Columns\TextColumn::make('customer_name')
                    ->searchable()
                    ->label('Nama Pelanggan'),
                Tables\Columns\TextColumn::make('amount')
                    ->money('idr')
                    ->label('Jumlah Hutang'),
                Tables\Columns\TextColumn::make('paid_amount')
                    ->money('idr')
                    ->label('Sudah Dibayar'),
                Tables\Columns\TextColumn::make('due_date')
                    ->date()
                    ->label('Jatuh Tempo'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'partial' => 'warning',
                        default => 'danger',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'paid' => 'Lunas',
                        'partial' => 'Sebagian',
                        'unpaid' => 'Belum Lunas',
                        default => $state,
                    }),
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
            'index' => Pages\ListDebts::route('/'),
            'create' => Pages\CreateDebt::route('/create'),
            'edit' => Pages\EditDebt::route('/{record}/edit'),
        ];
    }
}
