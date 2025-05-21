<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjetoResource\Pages;
use App\Filament\Resources\ProjetoResource\RelationManagers;
use App\Models\Projeto;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProjetoResource extends Resource
{
    protected static ?string $model = Projeto::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label('Usuário')
                    ->relationship('user', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->placeholder('Selecione um usuário'),
                Forms\Components\TextInput::make('nome')
                    ->label('Nome do Projeto')
                    ->placeholder('Digite o nome do projeto')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('descricao')
                    ->label('Descrição')
                    ->placeholder('Digite a descrição do projeto')
                    ->columnSpanFull(),
                Forms\Components\DatePicker::make('data_inicio')
                    ->label('Data de Início')
                    ->placeholder('Selecione a data de início')
                    ->required(),
                Forms\Components\DatePicker::make('data_termino')
                    ->label('Data de Término')
                    ->placeholder('Selecione a data de término'),
                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->placeholder('Selecione o status')
                    ->required()
                    ->options([
                        'ativo' => 'Ativo',
                        'inativo' => 'Inativo',
                        'concluido' => 'Concluído',
                        'em_andamento' => 'Em Andamento',
                        'cancelado' => 'Cancelado',
                    ])
                    ->searchable()
                    ->default('ativo'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nome')
                    ->searchable(),
                Tables\Columns\TextColumn::make('data_inicio')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('data_termino')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageProjetos::route('/'),
        ];
    }
}
