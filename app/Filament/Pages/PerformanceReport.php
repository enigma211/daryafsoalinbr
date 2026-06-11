<?php

namespace App\Filament\Pages;

use App\Models\User;
use Filament\Pages\Page;
use Filament\Tables\Table;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;

class PerformanceReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-presentation-chart-line';
    protected static ?string $navigationLabel = 'گزارش عملکرد پرسنل';
    protected static ?string $title = 'گزارش جامع عملکرد طراحان و داوران';
    protected static string | \UnitEnum | null $navigationGroup = 'مدیریت';
    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.pages.performance-report';

    public static function shouldRegisterNavigation(): bool
    {
        // Only Super Admin and Exam Manager can see reports
        /** @var \App\Models\User $user */
        $user = \Illuminate\Support\Facades\Auth::user();
        return $user && $user->hasRole(['Super Admin', 'Exam Manager']);
    }

    public function mount()
    {
        /** @var \App\Models\User $user */
        $user = \Illuminate\Support\Facades\Auth::user();
        if (!$user || !$user->hasRole(['Super Admin', 'Exam Manager'])) {
            abort(403);
        }
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(User::query())
            ->modifyQueryUsing(function (Builder $query) {
                $filters = $this->tableFilters['date_range'] ?? [];
                $from = $filters['from_date'] ?? null;
                $to = $filters['to_date'] ?? null;

                $query->withCount([
                    'questions as designed_total_count' => function (Builder $q) use ($from, $to) {
                        if ($from) $q->whereDate('created_at', '>=', $from);
                        if ($to) $q->whereDate('created_at', '<=', $to);
                    },
                    'questions as designed_approved_count' => function (Builder $q) use ($from, $to) {
                        $q->where('current_status', 'approved');
                        if ($from) $q->whereDate('created_at', '>=', $from);
                        if ($to) $q->whereDate('created_at', '<=', $to);
                    },
                    'questions as designed_rejected_count' => function (Builder $q) use ($from, $to) {
                        $q->where('current_status', 'needs_revision');
                        if ($from) $q->whereDate('created_at', '>=', $from);
                        if ($to) $q->whereDate('created_at', '<=', $to);
                    },
                    'comments as reviewed_total_count' => function (Builder $q) use ($from, $to) {
                        if ($from) $q->whereDate('created_at', '>=', $from);
                        if ($to) $q->whereDate('created_at', '<=', $to);
                    }
                ]);
            })
            ->columns([
                TextColumn::make('name')
                    ->label('نام کاربر')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                
                TextColumn::make('roles.name')
                    ->label('نقش‌ها')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'Super Admin' => 'مدیر کل',
                        'Exam Manager' => 'مدیر آزمون',
                        'Field Secretary' => 'دبیر رشته',
                        'Operator' => 'اپراتور',
                        'Question Designer' => 'طراح سوال',
                        'Regulations Reviewer' => 'ناظر مقررات ملی',
                        'Scientific Reviewer' => 'ناظر علمی',
                        default => $state,
                    }),

                TextColumn::make('designed_total_count')
                    ->label('کل طراحی‌ها')
                    ->sortable()
                    ->alignCenter(),
                    
                TextColumn::make('designed_approved_count')
                    ->label('تایید نهایی شده')
                    ->sortable()
                    ->color('success')
                    ->alignCenter()
                    ->badge(),

                TextColumn::make('designed_rejected_count')
                    ->label('رد / نیاز به اصلاح')
                    ->sortable()
                    ->color('danger')
                    ->alignCenter(),

                TextColumn::make('reviewed_total_count')
                    ->label('مجموع داوری‌ها (نظرات)')
                    ->sortable()
                    ->color('info')
                    ->alignCenter()
                    ->badge(),
            ])
            ->filters([
                \Filament\Tables\Filters\Filter::make('date_range')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('from_date')->label('از تاریخ'),
                        \Filament\Forms\Components\DatePicker::make('to_date')->label('تا تاریخ'),
                    ])
                    ->query(function (Builder $query) {
                        // The actual logic is inside modifyQueryUsing, we just return the query here
                        return $query;
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['from_date'] ?? null) {
                            $indicators['from_date'] = 'از: ' . \Morilog\Jalali\Jalalian::fromCarbon(\Carbon\Carbon::parse($data['from_date']))->format('Y/m/d');
                        }
                        if ($data['to_date'] ?? null) {
                            $indicators['to_date'] = 'تا: ' . \Morilog\Jalali\Jalalian::fromCarbon(\Carbon\Carbon::parse($data['to_date']))->format('Y/m/d');
                        }
                        return $indicators;
                    })
            ])
            ->defaultSort('designed_total_count', 'desc');
    }
}
