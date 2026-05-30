<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>چاپ سوالات آزمون</title>
    <link href="{{ asset('fonts/vazirmatn/Vazirmatn-font-face.css') }}" rel="stylesheet">
    <style>
        body {
            font-family: 'Vazirmatn', Arial, sans-serif;
            font-size: 14pt;
            line-height: 2;
            margin: 0;
            padding: 20px;
            background: #fff;
            color: #000;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 30px;
        }
        .question-block {
            margin-bottom: 40px;
            page-break-inside: avoid;
        }
        .question-text {
            font-weight: bold;
            margin-bottom: 15px;
            text-align: justify;
        }
        .options-container {
            display: flex;
            flex-wrap: wrap;
            margin-right: 20px;
        }
        .option {
            width: 50%; /* دو ستونه */
            margin-bottom: 10px;
            box-sizing: border-box;
            padding-left: 10px;
        }
        .attachments img {
            max-width: 80%;
            height: auto;
            display: block;
            margin: 15px auto;
        }
        @media print {
            body {
                padding: 0;
            }
            .no-print {
                display: none;
            }
        }
        .print-btn {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            font-family: inherit;
            margin-bottom: 20px;
            display: inline-block;
        }
        .print-btn:hover {
            background-color: #45a049;
        }
        .metadata-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 11pt;
            background: #f9f9f9;
        }
        .metadata-table th, .metadata-table td {
            border: 1px solid #ccc;
            padding: 5px;
            text-align: right;
        }
        .metadata-table th {
            background: #eee;
            width: 20%;
        }
    </style>
</head>
<body>

    <div class="no-print" style="text-align: left;">
        <button class="print-btn" onclick="window.print()">🖨️ چاپ این صفحه (PDF / پرینتر)</button>
    </div>

    <div class="header">
        <h1>بسمه تعالی</h1>
        <h2>دفترچه سوالات آزمون مقررات ملی ساختمان</h2>
        <p>تعداد سوالات: {{ $questions->count() }} سوال</p>
    </div>

    @php
        $disciplines = [
            'civil' => 'عمران',
            'architecture' => 'معماری',
            'electrical' => 'تاسیسات برقی',
            'mechanical' => 'تاسیسات مکانیکی',
            'surveying' => 'نقشه‌برداری',
            'traffic' => 'ترافیک',
            'urbanism' => 'شهرسازی',
        ];
        $qualifications = [
            'design' => 'طراحی / محاسبات',
            'supervision' => 'نظارت',
            'execution' => 'اجرا',
        ];
        $skillTypes = [
            'analysis' => 'تحلیل',
            'calculation' => 'محاسبه',
            'regulation_recognition' => 'تشخیص ضابطه',
            'combined' => 'ترکیبی',
        ];
        $difficulties = [
            'easy' => 'آسان',
            'medium' => 'متوسط',
            'hard' => 'دشوار',
        ];
    @endphp

    @foreach($questions as $index => $question)
        <div class="question-block">
            <div style="font-size: 11pt; color: #555; background: #f9f9f9; padding: 10px; border: 1px solid #ddd; margin-bottom: 15px; border-radius: 5px;">
                <strong>رشته:</strong> {{ $disciplines[$question->discipline] ?? '-' }} |
                <strong>صلاحیت:</strong> {{ $qualifications[$question->qualification] ?? '-' }} |
                <strong>مبحث اصلی:</strong> {{ $question->category->topic ?? '-' }} (ویرایش {{ $question->reference_year ?? '-' }}) |
                <strong>فصل:</strong> {{ $question->chapter ?? '-' }} <br>
                <strong>موضوع:</strong> {{ $question->topic_details ?? '-' }} |
                <strong>مهارت:</strong> {{ $skillTypes[$question->skill_type] ?? '-' }} |
                <strong>درجه سختی:</strong> {{ $difficulties[$question->difficulty_level] ?? '-' }} |
                <strong>زمان:</strong> {{ $question->estimated_time ?? '-' }} دقیقه
                @if($question->other_references)
                    <br><strong>سایر منابع:</strong> {{ $question->other_references }}
                @endif
                @if($question->exact_source)
                    <br><strong>منبع دقیق:</strong> {{ $question->exact_source }}
                @endif
            </div>

            <div class="question-text">
                {{ $index + 1 }}- {!! $question->text !!}
            </div>

            @if($question->attachments && $question->attachments->count() > 0)
                <div class="attachments">
                    @foreach($question->attachments as $attachment)
                        <img src="{{ asset('storage/' . $attachment->file_path) }}" alt="پیوست سوال">
                    @endforeach
                </div>
            @endif

            @if($question->type === 'multiple_choice' && $question->options)
                <div class="options-container">
                    @foreach($question->options as $option)
                        <div class="option">
                            {{ $option->option_number }}) {{ $option->text }}
                        </div>
                    @endforeach
                </div>
            @endif
            
            @if($question->type === 'descriptive')
                <div style="margin-top: 10px; border: 1px dashed #ccc; height: 150px; padding: 10px; color: #888;">
                    (محل پاسخ تشریحی)
                </div>
            @endif
        </div>
    @endforeach

</body>
</html>
