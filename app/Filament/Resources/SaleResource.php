<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SaleResource\Pages;
use App\Filament\Resources\SaleResource\RelationManagers;
use App\Models\Sale;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class SaleResource extends Resource
{
    protected static ?string $model = Sale::class;
    
    protected static ?string $recordTitleAttribute = 'product_name';

    protected static ?string $navigationLabel = 'Penjualan';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::whereDate('created_at', today())->count();
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['product_name', 'notes'];
    }

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('product_name')
                    ->required()
                    ->maxLength(255)
                    ->label('Nama Produk'),
                Forms\Components\TextInput::make('quantity')
                    ->required()
                    ->numeric()
                    ->default(1)
                    ->label('Jumlah'),
                Forms\Components\TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->label('Harga Satuan'),
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
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Tanggal'),
                Tables\Columns\TextColumn::make('product_name')
                    ->searchable()
                    ->label('Nama Produk'),
                Tables\Columns\TextColumn::make('quantity')
                    ->numeric()
                    ->label('Jumlah'),
                Tables\Columns\TextColumn::make('price')
                    ->money('idr')
                    ->label('Harga Satuan'),
                Tables\Columns\TextColumn::make('total')
                    ->money('idr')
                    ->label('Total'),
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
            'index' => Pages\ListSales::route('/'),
            'create' => Pages\CreateSale::route('/create'),
            'edit' => Pages\EditSale::route('/{record}/edit'),
        ];
    }
}
