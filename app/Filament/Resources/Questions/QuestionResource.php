<?php

namespace App\Filament\Resources\Questions;

use App\Filament\Resources\Questions\Pages\CreateQuestion;
use App\Filament\Resources\Questions\Pages\EditQuestion;
use App\Filament\Resources\Questions\Pages\ListQuestions;
use App\Filament\Resources\Questions\Pages\ViewQuestion;
use App\Filament\Resources\Questions\Schemas\QuestionForm;
use App\Filament\Resources\Questions\Schemas\QuestionInfolist;
use App\Filament\Resources\Questions\Tables\QuestionsTable;
use App\Models\Question;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class QuestionResource extends Resource
{
    protected static ?string $model = Question::class;

    protected static ?string $modelLabel = 'سوال';
    public static function getPluralModelLabel(): string
    {
        return filament()->getCurrentPanel()->getId() === 'designer' ? 'سوالات من' : 'بانک سوالات';
    }

    public static function getNavigationLabel(): string
    {
        return filament()->getCurrentPanel()->getId() === 'designer' ? 'مشاهده سوالات' : 'بانک سوالات';
    }

    public static function shouldRegisterNavigation(): bool
    {
        if (filament()->getCurrentPanel()->getId() === 'designer') {
            return false;
        }

        return parent::shouldRegisterNavigation();
    }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'unique_code';

    public static function getGloballySearchableAttributes(): array
    {
        return ['unique_code', 'text', 'designer.name'];
    }

    public static function getGlobalSearchResultTitle(\Illuminate\Database\Eloquent\Model $record): string
    {
        return 'سوال ' . $record->unique_code;
    }

    public static function getGlobalSearchResultDetails(\Illuminate\Database\Eloquent\Model $record): array
    {
        return [
            'طراح' => $record->designer->name ?? 'نامشخص',
            'وضعیت' => match ($record->current_status) {
                'approved' => 'تایید شده',
                'awaiting_review' => 'منتظر داوری',
                'scientific_review' => 'داوری علمی',
                'regulations_review' => 'داوری مقررات',
                'needs_revision' => 'نیاز به اصلاح',
                default => 'پیش‌نویس',
            },
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return QuestionForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return QuestionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return QuestionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\Questions\RelationManagers\CommentsRelationManager::class,
            \App\Filament\Resources\Questions\RelationManagers\ActivitiesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListQuestions::route('/'),
            'create' => CreateQuestion::route('/create'),
            'view' => ViewQuestion::route('/{record}'),
            'edit' => EditQuestion::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);

        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if (Auth::check()) {
            // Super Admin and Exam Manager see all
            if ($user->hasRole(['Super Admin', 'Exam Manager'])) {
                return $query;
            }

            // Designers see only their own questions
            if ($user->hasRole('Question Designer') && !$user->hasRole(['Scientific Reviewer', 'Regulations Reviewer', 'Field Secretary'])) {
                $query->where('user_id', $user->id);
            }

            // Reviewers and Field Secretary see only questions in their assigned categories
            // unless they are also the designer of a question
            if ($user->hasRole(['Scientific Reviewer', 'Regulations Reviewer', 'Field Secretary'])) {
                $assignedCategoryIds = $user->categories()->pluck('categories.id')->toArray();
                
                $query->where(function ($q) use ($user, $assignedCategoryIds) {
                    if (!empty($assignedCategoryIds)) {
                        $q->whereIn('category_id', $assignedCategoryIds);
                    } else {
                        // If a reviewer has no categories assigned, maybe they see none, or we fallback to false
                        // For safety, they shouldn't see anything unless assigned, except their own designs
                        $q->whereRaw('1 = 0'); 
                    }

                    // Also let them see their own designed questions regardless of category
                    if ($user->hasRole('Question Designer')) {
                        $q->orWhere('user_id', $user->id);
                    }
                });
            }
        }

        return $query;
    }
}
