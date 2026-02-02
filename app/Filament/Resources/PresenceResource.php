<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PresenceResource\Pages;
use App\Models\Presence;
use Filament\Actions;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PresenceResource extends Resource
{
    protected static ?string $model = Presence::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clock';

    protected static string|\UnitEnum|null $navigationGroup = 'Attendance';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\MorphToSelect::make('user')
                    ->types([
                        Forms\Components\MorphToSelect\Type::make(\App\Models\Employee::class)
                            ->titleAttribute('name'),
                        Forms\Components\MorphToSelect\Type::make(\App\Models\Intern::class)
                            ->titleAttribute('name'),
                    ])
                    ->required(),
                Forms\Components\DatePicker::make('date')
                    ->required(),
                Forms\Components\TimePicker::make('check_in')
                    ->required(),
                Forms\Components\TimePicker::make('check_out'),
                Forms\Components\FileUpload::make('image_capture')
                    ->image()
                    ->directory('presences')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('detected_name')
                    ->maxLength(255),
                Forms\Components\TextInput::make('face_score')
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('status')
                    ->required()
                    ->maxLength(255)
                    ->default('valid'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_capture')
                    ->circular(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('user_type')
                    ->label('Type')
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'App\Models\Employee' => 'Employee',
                        'App\Models\Intern' => 'Intern',
                        default => 'Unknown',
                    })
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'App\Models\Employee' => 'info',
                        'App\Models\Intern' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('check_in')
                    ->time()
                    ->sortable(),
                Tables\Columns\TextColumn::make('check_out')
                    ->time()
                    ->sortable(),
                Tables\Columns\TextColumn::make('face_score')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'valid' => 'success',
                        'invalid' => 'danger',
                        default => 'warning',
                    })
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Actions\EditAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListPresences::route('/'),
            'create' => Pages\CreatePresence::route('/create'),
            'edit' => Pages\EditPresence::route('/{record}/edit'),
        ];
    }
}
