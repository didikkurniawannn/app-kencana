<?php

namespace App\Filament\Resources\DetailBelanjaResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RevisionsRelationManager extends RelationManager
{
    protected static string $relationship = 'revisions';

    public function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('pagu_baru')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d/m/Y H:i'),
                Tables\Columns\TextColumn::make('pagu_lama')
                    ->label('Pagu Lama')
                    ->money('IDR'),
                Tables\Columns\TextColumn::make('kuefisien_lama')
                    ->label('Kuefisien Lama')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('kuefisien_baru')
                    ->label('Kuefisien Baru')
                    ->alignCenter()
                    ->color('warning'),
                Tables\Columns\TextColumn::make('perubahan')
                    ->label('Perubahan Pagu')
                    ->money('IDR')
                    ->color(fn($state) => $state >= 0 ? 'success' : 'danger')
                    ->formatStateUsing(fn($state) => ($state >= 0 ? '+' : '') . number_format($state, 0, ',', '.')),
                Tables\Columns\TextColumn::make('pagu_baru')
                    ->label('Pagu Baru')
                    ->money('IDR'),
                Tables\Columns\TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->wrap(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Oleh'),
            ])
            ->filters([])
            ->headerActions([])
            ->actions([])
            ->bulkActions([]);
    }
}
