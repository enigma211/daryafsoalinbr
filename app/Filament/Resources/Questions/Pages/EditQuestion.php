<?php

namespace App\Filament\Resources\Questions\Pages;

use App\Filament\Resources\Questions\QuestionResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditQuestion extends EditRecord
{
    protected static string $resource = QuestionResource::class;


    protected function getHeaderActions(): array
    {
        /** @var \App\Models\User $user */
        $user = \Illuminate\Support\Facades\Auth::user();

        return [
            \Filament\Actions\Action::make('send_for_review')
                ->label('ارسال برای داوری')
                ->icon('heroicon-o-paper-airplane')
                ->color('info')
                ->requiresConfirmation()
                ->visible(fn () => in_array($this->record->current_status, ['draft', 'needs_revision']) && $user->can('update', $this->record))
                ->action(function () {
                    $this->record->update(['current_status' => 'awaiting_review']);
                    $this->refreshFormData(['current_status']);
                }),

            \Filament\Actions\Action::make('scientific_review_approve')
                ->label('تایید علمی')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn () => in_array($this->record->current_status, ['awaiting_review', 'scientific_review']) && $user->hasRole(['Super Admin', 'Exam Manager', 'Scientific Reviewer']))
                ->action(function () {
                    $this->record->update(['current_status' => 'regulations_review']);
                    $this->refreshFormData(['current_status']);
                }),

            \Filament\Actions\Action::make('regulations_review_approve')
                ->label('تایید مقررات (تایید نهایی)')
                ->icon('heroicon-o-shield-check')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn () => $this->record->current_status === 'regulations_review' && $user->hasRole(['Super Admin', 'Exam Manager', 'Regulations Reviewer']))
                ->action(function () {
                    $this->record->update(['current_status' => 'approved']);
                    $this->refreshFormData(['current_status']);
                    \Filament\Notifications\Notification::make()
                        ->title('سوال شما تایید نهایی شد')
                        ->body('سوال با کد ' . $this->record->unique_code . ' با موفقیت تایید نهایی شد.')
                        ->success()
                        ->sendToDatabase($this->record->designer);
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
                ->visible(fn () => in_array($this->record->current_status, ['awaiting_review', 'scientific_review', 'regulations_review']) && $user->hasRole(['Super Admin', 'Exam Manager', 'Scientific Reviewer', 'Regulations Reviewer']))
                ->action(function (array $data) {
                    $this->record->comments()->create([
                        'user_id' => \Illuminate\Support\Facades\Auth::id(),
                        'comment' => $data['comment'],
                    ]);
                    $this->record->update(['current_status' => 'needs_revision']);
                    $this->refreshFormData(['current_status']);
                    
                    \Filament\Notifications\Notification::make()
                        ->title('سوال نیاز به اصلاح دارد')
                        ->body('داور برای سوال ' . $this->record->unique_code . ' اصلاحیه ثبت کرد: ' . \Illuminate\Support\Str::limit($data['comment'], 50))
                        ->danger()
                        ->sendToDatabase($this->record->designer);
                }),

            \Filament\Actions\Action::make('print')
                ->label('چاپ')
                ->icon('heroicon-o-printer')
                ->color('gray')
                ->url(fn () => route('print.question', $this->record))
                ->openUrlInNewTab(),

            ViewAction::make(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
