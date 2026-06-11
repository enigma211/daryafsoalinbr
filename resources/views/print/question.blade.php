<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>چاپ سوال - {{ $question->unique_code }}</title>
    <link href="{{ asset('fonts/vazirmatn/Vazirmatn-font-face.css') }}" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="{{ asset('vendor/katex/katex.min.css') }}">
    <script defer src="{{ asset('vendor/katex/katex.min.js') }}"></script>
    <script defer src="{{ asset('vendor/katex/contrib/auto-render.min.js') }}"></script>
    <style>
        body {
            font-family: 'Vazirmatn', sans-serif;
            background: #fff;
            color: #000;
            padding: 20px;
            margin: 0;
            font-size: 14pt;
            line-height: 2;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        .header {
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header-title {
            font-size: 18pt;
            font-weight: bold;
        }
        .code-box {
            border: 1px solid #000;
            padding: 5px 15px;
            border-radius: 5px;
            font-weight: bold;
            font-family: monospace;
            font-size: 16pt;
        }
        .question-text {
            font-weight: bold;
            margin-bottom: 20px;
            text-align: justify;
        }
        .options {
            margin-bottom: 30px;
        }
        .option {
            margin-bottom: 10px;
        }
        .option.correct {
            font-weight: bold;
            text-decoration: underline;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .meta-table th, .meta-table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: right;
            font-size: 12pt;
        }
        .meta-table th {
            background-color: #f5f5f5;
        }
        .explanatory {
            border: 1px solid #000;
            padding: 15px;
            margin-top: 20px;
            background-color: #fafafa;
        }
        @media print {
            body {
                padding: 0;
            }
            .no-print {
                display: none !important;
            }
        }
        .print-btn {
            display: block;
            width: 200px;
            margin: 20px auto;
            padding: 10px;
            text-align: center;
            background: #2563eb;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            cursor: pointer;
            border: none;
            font-family: inherit;
            font-size: 14pt;
        }
        
        /* Fix KaTeX directionality in RTL context */
        .katex, .katex * {
            direction: ltr !important;
            unicode-bidi: isolate;
        }
        .katex-display {
            direction: ltr !important;
            text-align: center !important;
        }
    </style>
</head>
<body>
    <button class="print-btn no-print" onclick="window.print()">چاپ این صفحه</button>

    <div class="container">
        <div class="header">
            <div class="header-title">مشخصات و صورت سوال</div>
            <div class="code-box">کد یکتا: {{ $question->unique_code }}</div>
        </div>

        <table class="meta-table">
            <tr>
                <th>نام طراح:</th>
                <td>{{ $question->designer->name ?? 'نامشخص' }}</td>
                <th>مبحث / طبقه‌بندی:</th>
                <td>{{ $question->category->topic ?? 'نامشخص' }}</td>
            </tr>
            <tr>
                <th>تاریخ ثبت:</th>
                <td colspan="3">{{ \Morilog\Jalali\Jalalian::fromCarbon($question->created_at)->format('%d %B %Y') }}</td>
            </tr>
        </table>

        <div class="question-text">
            {!! $question->text !!}
        </div>

        <div class="options">
            @foreach($question->options as $index => $option)
                <div class="option">
                    گزینه {{ $index + 1 }}) {!! $option->text !!} 
                </div>
            @endforeach
        </div>

        @if($question->correct_option)
        <div class="correct-answer" style="margin-bottom: 20px; font-weight: bold; color: green; font-size: 16pt;">
            ✅ پاسخ صحیح: گزینه {{ $question->correct_option }}
        </div>
        @endif

        @if($question->descriptive_answer)
        <div class="explanatory">
            <strong>پاسخ تشریحی:</strong>
            <div style="margin-top: 10px;">
                {!! $question->descriptive_answer !!}
            </div>
        </div>
        @endif

        @if($question->exact_source || $question->other_references)
        <div class="explanatory">
            <strong>مستندات و منابع:</strong>
            <div style="margin-top: 10px;">
                @if($question->exact_source)
                    <div><strong>آدرس دقیق مبحث:</strong> {{ $question->exact_source }}</div>
                @endif
                @if($question->other_references)
                    <div style="margin-top: 5px;"><strong>سایر منابع:</strong> {{ $question->other_references }}</div>
                @endif
            </div>
        </div>
        @endif
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            renderMathInElement(document.body, {
                delimiters: [
                    {left: "$$", right: "$$", display: true},
                    {left: "$", right: "$", display: false},
                    {left: "\\(", right: "\\)", display: false},
                    {left: "\\[", right: "\\]", display: true}
                ],
                throwOnError: false
            });
            // Auto trigger print if we are just opening it to print
            // setTimeout(() => window.print(), 1000); // Optional auto-print
        });
    </script>
</body>
</html>
