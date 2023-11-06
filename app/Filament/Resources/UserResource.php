<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\Pages\CreateUser;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Pages\Page;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $modelLabel = 'пользователь';

    protected static ?string $pluralModelLabel = 'пользователи';

    protected static ?string $navigationGroup = 'Администрирование';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('username')
                    ->required()
                    ->placeholder('Допустимы русские и английские символы')
                    ->label('Логин')
                    ->unique(ignoreRecord: true)
                    ->minLength(2)
                    ->maxLength(255),
                TextInput::make('email')
                    ->placeholder('Введите почту для входа в приложение')
                    ->email()
                    ->required()
                    ->label('Эл. почта')
                    ->unique(ignoreRecord: true),
                DateTimePicker::make('email_verified_at')
                    ->label('Дата верификации'),
                TextInput::make('password')
                    ->label('Пароль')
                    ->password()
                    ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn (Page $livewire) => ($livewire instanceof CreateUser))
                    ->maxLength(255),
                Select::make('roles')
                    ->label('Роли')
                    ->multiple()
                    ->preload()
                    ->relationship('roles', 'name'),
                Select::make('expert_id')
                    ->label('Эксперты')
                    ->preload()
                    ->relationship('expert', 'full_name')
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('expert.full_name')
                    ->label('Эксперт'),
                TextColumn::make('username')
                    ->label('Имя пользователя'),
                TextColumn::make('email'),
                TextColumn::make('roles.name')
                    ->badge()
                    ->label('роли'),
                TextColumn::make('email_verified_at')
                    ->dateTime(),
                TextColumn::make('created_at')
                    ->dateTime(),
                TextColumn::make('updated_at')
                    ->dateTime(),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
