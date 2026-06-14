<?php

namespace App\Filament\Resources\Questions\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class QuestionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('یادداشت‌ها و بازخورد ناظر')
                    ->description('دلایل رد شدن یا اصلاحات مورد نیاز در این بخش ثبت می‌شود.')
                    ->schema([
                        Textarea::make('reviewer_notes')
                            ->hiddenLabel()
                            ->disabled(fn () => filament()->getCurrentPanel()?->getId() === 'designer')
                            ->columnSpanFull()
                            ->placeholder('یادداشتی از سوی ناظر ثبت نشده است...'),
                    ])
                    ->icon('heroicon-o-chat-bubble-bottom-center-text')
                    ->collapsible()
                    ->collapsed(fn ($record) => empty($record?->reviewer_notes))
                    ->visible(fn ($context) => $context === 'edit' || filament()->getCurrentPanel()?->getId() === 'admin')
                    ->columnSpanFull(),

                Section::make('متن سوال و پاسخ')
                    ->description('محتوای سوال و گزینه‌ها')
                    ->columnSpanFull()
                    ->schema([
                            \Filament\Forms\Components\Placeholder::make('math_hint')
                                ->hiddenLabel()
                                ->content(new \Illuminate\Support\HtmlString(<<<HTML
                                    <div style="background-color: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; padding: 1rem; border-radius: 0.5rem; margin-bottom: 0.5rem; font-size: 0.875rem;">
                                        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem; font-weight: bold;">
                                            <svg style="width: 20px; height: 20px; flex-shrink: 0;" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
                                            </svg>
                                            <span style="font-size: 1rem;">راهنمای درج فرمول‌های ریاضی</span>
                                        </div>
                                        <div style="line-height: 1.8; text-align: justify;">
                                            کاربر گرامی؛ در محیط ویرایشگر زیر، فرمول‌ها عمداً به شکل <strong>متن خام</strong> (مثلاً <code>\$ V_u = 320 \$</code>) نمایش داده می‌شوند تا بتوانید اعداد و متغیرها را با کیبورد ویرایش کنید.
                                        </div>
                                    </div>
HTML
                                ))->columnSpanFull(),


                            \Filament\Forms\Components\ViewField::make('text')
                                ->view('components.ck-editor')
                                ->label('متن سوال')
                                ->required()
                                ->columnSpanFull(),

                            \Filament\Forms\Components\Placeholder::make('text_preview')
                                ->hiddenLabel()
                                ->content(new \Illuminate\Support\HtmlString(<<<'HTML'
                                    <div style="margin-bottom: 0.5rem; font-weight: bold; color: #374151;">پیش‌نمایش زنده خروجی سوال (با اعمال فرمول‌های ریاضی):</div>
                                    <div x-data="{
                                            text: $wire.$entangle('data.text', true),
                                            renderKaTeX() {
                                                if(window.renderMathInElement) {
                                                    window.renderMathInElement(this.$refs.previewBox, {
                                                        delimiters: [
                                                            {left: '$$', right: '$$', display: true},
                                                            {left: '$', right: '$', display: false},
                                                            {left: '\\(', right: '\\)', display: false},
                                                            {left: '\\[', right: '\\]', display: true}
                                                        ],
                                                        throwOnError: false
                                                    });
                                                }
                                            }
                                        }"
                                        x-init="
                                            $watch('text', value => {
                                                $nextTick(() => renderKaTeX());
                                            });
                                            setTimeout(() => renderKaTeX(), 500);
                                        "
                                    >
                                        <div x-ref="previewBox" x-html="text || '<span style=\'color: #94a3b8;\'>در حال تایپ...</span>'" style="padding: 1.5rem; border: 2px dashed #cbd5e1; border-radius: 0.5rem; min-height: 80px; background-color: #f8fafc; color: #1e293b; font-size: 1rem; line-height: 2; text-align: justify; direction: rtl;"></div>
                                    </div>
HTML
                                ))->columnSpanFull(),
                                
                            Repeater::make('options')
                                ->relationship('options')
                                ->label('گزینه‌های سوال')
                                ->schema([
                                    \Filament\Forms\Components\Hidden::make('option_number'),
                                    Textarea::make('text')
                                        ->hiddenLabel()
                                        ->placeholder('متن گزینه...')
                                        ->required()
                                        ->live(debounce: 500)
                                        ->rows(1)
                                        ->columnSpanFull(),
                                    \Filament\Forms\Components\Placeholder::make('preview')
                                        ->hiddenLabel()
                                        ->view('components.option-preview')
                                        ->columnSpanFull(),
                                ])
                                ->grid(2)
                                ->minItems(4)
                                ->maxItems(4)
                                ->default([
                                    ['option_number' => 1, 'text' => ''],
                                    ['option_number' => 2, 'text' => ''],
                                    ['option_number' => 3, 'text' => ''],
                                    ['option_number' => 4, 'text' => ''],
                                ])
                                ->addable(false)
                                ->deletable(false)
                                ->reorderable(false)
                                ->itemLabel(fn (array $state): ?string => $state['option_number'] ? 'گزینه ' . $state['option_number'] : null)
                                ->columnSpanFull(),

                            Select::make('correct_option')
                                ->label('شماره گزینه صحیح')
                                ->options([
                                    1 => 'گزینه ۱',
                                    2 => 'گزینه ۲',
                                    3 => 'گزینه ۳',
                                    4 => 'گزینه ۴',
                                ])
                                ->required(),

                            \Filament\Forms\Components\ViewField::make('descriptive_answer')
                                ->view('components.ck-editor')
                                ->label('توضیحات پاسخ (اختیاری)')
                                ->columnSpanFull(),

                            \Filament\Forms\Components\Placeholder::make('descriptive_answer_preview')
                                ->hiddenLabel()
                                ->content(new \Illuminate\Support\HtmlString(<<<'HTML'
                                    <div style="margin-bottom: 0.5rem; font-weight: bold; color: #374151;">پیش‌نمایش توضیحات پاسخ (با اعمال فرمول‌های ریاضی):</div>
                                    <div x-data="{
                                            text: $wire.$entangle('data.descriptive_answer', true),
                                            renderKaTeX() {
                                                if(window.renderMathInElement) {
                                                    window.renderMathInElement(this.$refs.previewBox, {
                                                        delimiters: [
                                                            {left: '$$', right: '$$', display: true},
                                                            {left: '$', right: '$', display: false},
                                                            {left: '\\(', right: '\\)', display: false},
                                                            {left: '\\[', right: '\\]', display: true}
                                                        ],
                                                        throwOnError: false
                                                    });
                                                }
                                            }
                                        }"
                                        x-init="
                                            $watch('text', value => {
                                                $nextTick(() => renderKaTeX());
                                            });
                                            setTimeout(() => renderKaTeX(), 500);
                                        "
                                    >
                                        <div x-ref="previewBox" x-html="text || '<span style=\'color: #94a3b8;\'>توضیحاتی وارد نشده است...</span>'" style="padding: 1.5rem; border: 2px dashed #cbd5e1; border-radius: 0.5rem; min-height: 80px; background-color: #f8fafc; color: #1e293b; font-size: 1rem; line-height: 2; text-align: justify; direction: rtl;"></div>
                                    </div>
HTML
                                ))->columnSpanFull(),
                                

                            Repeater::make('attachments')
                                ->relationship('attachments')
                                ->label('پیوست‌ها (تصویر)')
                                ->schema([
                                    FileUpload::make('file_path')
                                        ->hiddenLabel()
                                        ->directory('question-attachments')
                                        ->preserveFilenames()
                                        ->image() // For images/diagrams
                                        ->maxSize(5120) // 5MB limit
                                        ->required()
                                        ->columnSpanFull(),
                                ])
                                ->grid(2)
                                ->defaultItems(0)
                                ->columnSpanFull(),
                        ])->columns(2),

                Section::make('اطلاعات اولیه')
                    ->description('مبحث، نوع سوال و طبقه‌بندی')
                    ->columnSpanFull()
                    ->schema([
                            TextInput::make('unique_code')
                                ->label('کد یکتا')
                                ->default(fn () => str_pad(mt_rand(100000, 999999), 6, '0', STR_PAD_LEFT))
                                ->readOnly()
                                ->dehydrated()
                                ->required()
                                ->unique(ignoreRecord: true),
                            Select::make('category_id')
                                ->label('مبحث مقررات ملی')
                                ->options(fn () => \App\Models\Category::all()->sortBy(function($category) {
                                    preg_match('/\d+/', $category->topic, $matches);
                                    return $matches ? (int)$matches[0] : 999;
                                })->pluck('topic', 'id')->toArray())
                                ->searchable()
                                ->preload()
                                ->required(),
                            Select::make('edition')
                                ->label('ویرایش مبحث')
                                ->options([
                                    'اول' => 'ویرایش اول',
                                    'دوم' => 'ویرایش دوم',
                                    'سوم' => 'ویرایش سوم',
                                    'چهارم' => 'ویرایش چهارم',
                                    'پنجم' => 'ویرایش پنجم',
                                ])
                                ->required(),
                            Select::make('discipline')
                                ->label('رشته آزمون')
                                ->options([
                                    'civil' => 'عمران',
                                    'architecture' => 'معماری',
                                    'electrical' => 'تاسیسات برقی',
                                    'mechanical' => 'تاسیسات مکانیکی',
                                    'surveying' => 'نقشه‌برداری',
                                    'traffic' => 'ترافیک',
                                    'urbanism' => 'شهرسازی',
                                ])
                                ->required(),
                            Select::make('qualification')
                                ->label('صلاحیت / گرایش')
                                ->options([
                                    'design' => 'طراحی / محاسبات',
                                    'supervision' => 'نظارت',
                                    'execution' => 'اجرا',
                                ])
                                ->required(),
                            TextInput::make('exact_source')
                                ->label('محل دقیق مرجع')
                                ->placeholder('مثلاً: مبحث 19 ویرایش 5 صفحه 73')
                                ->required()
                                ->columnSpanFull(),
                    ])->columns(2),


            ]);
    }
}
