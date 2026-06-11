<?php

namespace App\Filament\Resources\Questions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class QuestionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('unique_code')
                    ->label('کد یکتا')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category.topic')
                    ->label('مبحث مقررات')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('text')
                    ->label('متن سوال')
                    ->formatStateUsing(fn ($state) => \Illuminate\Support\Str::limit(strip_tags($state), 60))
                    ->searchable(),

                TextColumn::make('current_status')
                    ->label('وضعیت')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'پیش‌نویس',
                        'awaiting_review' => 'در انتظار داوری',
                        'scientific_review' => 'داوری علمی',
                        'regulations_review' => 'داوری مقررات',
                        'needs_revision' => 'نیازمند اصلاح',
                        'approved' => 'تایید شده',
                        'rejected' => 'رد شده',
                        'archived' => 'آرشیو شده',
                        default => $state,
                    })
                    ->badge(),
                TextColumn::make('designer.name')
                    ->label('طراح')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('تاریخ ایجاد')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->relationship('category', 'topic')
                    ->label('مبحث مقررات ملی'),
                SelectFilter::make('difficulty_level')
                    ->options([
                        'easy' => 'آسان',
                        'medium' => 'متوسط',
                        'hard' => 'سخت',
                    ])
                    ->label('درجه سختی'),
                SelectFilter::make('current_status')
                    ->options([
                        'draft' => 'پیش‌نویس',
                        'awaiting_review' => 'در انتظار داوری',
                        'scientific_review' => 'داوری علمی',
                        'regulations_review' => 'داوری مقررات',
                        'needs_revision' => 'نیازمند اصلاح',
                        'approved' => 'تایید شده',
                        'rejected' => 'رد شده',
                    ])
                    ->label('وضعیت'),
                TrashedFilter::make(),
            ])
            ->recordActions([
                \Filament\Actions\Action::make('send_for_review')
                    ->label('ارسال برای داوری')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('info')
                    ->requiresConfirmation()
                    ->visible(function ($record) {
                        /** @var \App\Models\User $user */
                        $user = \Illuminate\Support\Facades\Auth::user();
                        return in_array($record->current_status, ['draft', 'needs_revision']) && $user->can('update', $record);
                    })
                    ->action(fn ($record) => $record->update(['current_status' => 'awaiting_review'])),

                \Filament\Actions\Action::make('scientific_review_approve')
                    ->label('تایید علمی')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(function ($record) {
                        /** @var \App\Models\User $user */
                        $user = \Illuminate\Support\Facades\Auth::user();
                        return in_array($record->current_status, ['awaiting_review', 'scientific_review']) && $user->hasRole(['Super Admin', 'Exam Manager', 'Scientific Reviewer']);
                    })
                    ->action(fn ($record) => $record->update(['current_status' => 'regulations_review'])),

                \Filament\Actions\Action::make('regulations_review_approve')
                    ->label('تایید مقررات (تایید نهایی)')
                    ->icon('heroicon-o-shield-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(function ($record) {
                        /** @var \App\Models\User $user */
                        $user = \Illuminate\Support\Facades\Auth::user();
                        return $record->current_status === 'regulations_review' && $user->hasRole(['Super Admin', 'Exam Manager', 'Regulations Reviewer']);
                    })
                    ->action(function ($record) {
                        $record->update(['current_status' => 'approved']);
                        \Filament\Notifications\Notification::make()
                            ->title('سوال شما تایید نهایی شد')
                            ->body('سوال با کد ' . $record->unique_code . ' با موفقیت تایید نهایی شد.')
                            ->success()
                            ->sendToDatabase($record->designer);
                    }),

                \Filament\Actions\Action::make('reject_or_revise')
                    ->label('رد / نیاز به اصلاح')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->form([
                        \Filament\Forms\Components\Textarea::make('comment')
                            ->label('دلیل رد یا اصلاحات مورد نیاز')
                            ->required(),
                    ])
                    ->visible(function ($record) {
                        /** @var \App\Models\User $user */
                        $user = \Illuminate\Support\Facades\Auth::user();
                        return in_array($record->current_status, ['awaiting_review', 'scientific_review', 'regulations_review']) && $user->hasRole(['Super Admin', 'Exam Manager', 'Scientific Reviewer', 'Regulations Reviewer']);
                    })
                    ->action(function (array $data, $record) {
                        // Add comment
                        $record->comments()->create([
                            'user_id' => \Illuminate\Support\Facades\Auth::id(),
                            'comment' => $data['comment'],
                        ]);
                        // Change status
                        $record->update(['current_status' => 'needs_revision']);
                        
                        // Notify designer
                        \Filament\Notifications\Notification::make()
                            ->title('سوال نیاز به اصلاح دارد')
                            ->body('داور برای سوال ' . $record->unique_code . ' اصلاحیه ثبت کرد: ' . \Illuminate\Support\Str::limit($data['comment'], 50))
                            ->danger()
                            ->sendToDatabase($record->designer);
                    }),

                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    \Filament\Actions\ExportBulkAction::make()
                        ->label('خروجی اکسل / CSV')
                        ->icon('heroicon-o-document-arrow-down')
                        ->exporter(\App\Filament\Exports\QuestionExporter::class)
                        ->color('warning')
                        ->visible(fn () => filament()->getCurrentPanel()->getId() !== 'designer'),

                    \Filament\Actions\BulkAction::make('export_exam')
                        ->label('چاپ برگه آزمون (PDF)')
                        ->icon('heroicon-o-printer')
                        ->color('success')
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                            $ids = $records->pluck('id')->join(',');
                            return redirect()->route('exam.print', ['ids' => $ids]);
                        })
                        ->deselectRecordsAfterCompletion()
                        ->visible(fn () => filament()->getCurrentPanel()->getId() !== 'designer'),
                        
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
