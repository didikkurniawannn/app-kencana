<?php

namespace App\Filament\Resources\KegiatanResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SubKegiatansRelationManager extends RelationManager
{
    protected static string $relationship = 'subKegiatans';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nama_sub_kegiatan')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nama_sub_kegiatan')
            ->columns([
                Tables\Columns\TextColumn::make('kode_sub_kegiatan')->label('Kode'),
                Tables\Columns\TextColumn::make('nama_sub_kegiatan')->label('Nama Sub Kegiatan')->wrap(),
                Tables\Columns\TextColumn::make('total_pagu')
                    ->label('Total Pagu')
                    ->money('IDR'),
                Tables\Columns\TextColumn::make('total_realisasi')
                    ->label('Total Realisasi')
                    ->money('IDR'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->url(fn($record) => \App\Filament\Resources\SubKegiatanResource::getUrl('view', ['record' => $record])),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
