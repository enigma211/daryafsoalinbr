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
                Section::make('ثبت سریع از طریق فایل Word')
                    ->description('در صورتی که مایلید سوال خود را با استفاده از قالب Word آپلود کنید، ابتدا قالب زیر را دانلود کرده و پس از تکمیل، آن را بارگذاری نمایید.')
                    ->schema([
                        \Filament\Forms\Components\Placeholder::make('word_template_download')
                            ->hiddenLabel()
                            ->content(new \Illuminate\Support\HtmlString(sprintf(<<<HTML
                                <div style="display: flex; gap: 1rem; align-items: center; background-color: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; padding: 1rem; border-radius: 0.5rem; margin-bottom: 0.5rem;">
                                    <svg style="width: 24px; height: 24px; flex-shrink: 0;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <div>
                                        <span style="font-weight: bold; font-size: 1rem; display: block; margin-bottom: 0.25rem;">قالب استاندارد ثبت سوال (Word)</span>
                                        <a href="%s" download style="color: #15803d; text-decoration: underline; font-weight: bold; font-size: 0.875rem;">دانلود فایل قالب خام (DOCX)</a>
                                    </div>
                                </div>
HTML
                            , asset('templates/question_template.docx')))),

                        FileUpload::make('word_file')
                            ->label('بارگذاری فایل Word تکمیل‌شده')
                            ->maxSize(10240)
                            ->reactive()
                            ->afterStateUpdated(function ($state, $set, $get, $livewire) {
                                if (blank($state)) {
                                    return;
                                }

                                $file = is_array($state) ? reset($state) : $state;
                                $filePath = null;

                                if ($file instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile) {
                                    $filePath = $file->getRealPath();
                                } elseif (is_string($file)) {
                                    $disk = \Illuminate\Support\Facades\Storage::disk('public');
                                    if ($disk->exists($file)) {
                                        $filePath = $disk->path($file);
                                    } else {
                                        $filePath = storage_path('app/' . $file);
                                    }
                                }

                                if (!$filePath || !file_exists($filePath)) {
                                    return;
                                }

                                // Resolve Pandoc executable path dynamically (handles both local XAMPP and Linux production)
                                $pandocPath = 'C:\\Users\\mohsen\\AppData\\Local\\Pandoc\\pandoc.exe';
                                if (PHP_OS_FAMILY !== 'Windows' || !file_exists($pandocPath)) {
                                    $pandocPath = 'pandoc';
                                }

                                $tempMdFile = tempnam(sys_get_temp_dir(), 'pandoc_');

                                // Convert Word to Markdown with LaTeX math dollar delimiters
                                $cmd = '"' . $pandocPath . '" ' . escapeshellarg($filePath) . ' -t markdown-bracketed_spans-native_spans-raw_html+tex_math_dollars --wrap=none -o ' . escapeshellarg($tempMdFile);
                                exec($cmd, $output, $returnCode);

                                if ($returnCode !== 0) {
                                    \Filament\Notifications\Notification::make()
                                        ->title('خطا در تبدیل فایل Word')
                                        ->body('لطفاً مطمئن شوید نرم‌افزار Pandoc روی سرور نصب است.')
                                        ->danger()
                                        ->send();
                                    return;
                                }

                                $markdown = file_get_contents($tempMdFile);
                                @unlink($tempMdFile);

                                if (blank($markdown)) {
                                    return;
                                }

                                // Normalize line endings
                                $markdown = str_replace("\r\n", "\n", $markdown);

                                // Split into sections based on brackets [متن سوال], [گزینه ۱], etc. (handles optional escapes like \[...\])
                                $sections = preg_split('/^\\\\?\[(.*?)\\\\?\]/mi', $markdown, -1, PREG_SPLIT_DELIM_CAPTURE);

                                if (count($sections) < 2) {
                                    \Filament\Notifications\Notification::make()
                                        ->title('ساختار فایل Word نامعتبر است')
                                        ->body('لطفاً ساختار و تگ‌های قالب را تغییر ندهید.')
                                        ->warning()
                                        ->send();
                                    return;
                                }

                                $data = [];
                                for ($i = 1; $i < count($sections); $i += 2) {
                                    $tagName = trim($sections[$i]);
                                    $tagContent = trim($sections[$i + 1]);

                                    // Normalize Persian/English digits in option names
                                    $tagName = str_replace(
                                        ['1', '2', '3', '4'],
                                        ['۱', '۲', '۳', '۴'],
                                        $tagName
                                    );

                                    $data[$tagName] = preg_replace('/^\s*[\r\n]+|[\r\n]+\s*$/', '', $tagContent);
                                }

                                // Fill Question Text (Rich Text CKEditor expects HTML)
                                if (isset($data['متن سوال'])) {
                                    $set('text', (string) \Illuminate\Support\Str::markdown($data['متن سوال']));
                                }

                                // Fill Options (Repeater of plain textareas expects plain markdown/text)
                                $options = [];
                                for ($o = 1; $o <= 4; $o++) {
                                    $persianDigit = str_replace(['1', '2', '3', '4'], ['۱', '۲', '۳', '۴'], (string)$o);
                                    $options[] = [
                                        'option_number' => $o,
                                        'text' => $data["گزینه {$persianDigit}"] ?? ''
                                    ];
                                }
                                $set('options', $options);

                                // Fill Descriptive Answer (Rich Text CKEditor expects HTML)
                                if (isset($data['پاسخ تشریحی'])) {
                                    $set('descriptive_answer', (string) \Illuminate\Support\Str::markdown($data['پاسخ تشریحی']));
                                }

                                \Illuminate\Support\Facades\Log::info('Word Extraction Result:', $data);

                                \Filament\Notifications\Notification::make()
                                    ->title('محتوای فایل Word با موفقیت استخراج شد')
                                    ->success()
                                    ->send();
                            })
                    ])
                    ->icon('heroicon-o-document-arrow-up')
                    ->collapsible()
                    ->columnSpanFull()
                    ->visible(fn ($context) => $context === 'create'),

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
