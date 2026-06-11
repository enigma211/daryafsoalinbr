@php
    $textPath = str_replace('.preview', '.text', $getStatePath());
@endphp

<div x-data="{
        text: $wire.$entangle('{{ $textPath }}'),
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
    <div x-ref="previewBox" x-html="text || '<span style=\'color: #94a3b8; font-size: 0.85rem;\'>پیش‌نمایش فرمول‌های گزینه...</span>'" style="padding: 0.75rem; border: 1px dashed #cbd5e1; border-radius: 0.5rem; min-height: 40px; background-color: #f8fafc; color: #1e293b; font-size: 1rem; line-height: 1.8; text-align: justify; direction: rtl;"></div>
</div>
