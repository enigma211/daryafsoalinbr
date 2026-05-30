import './bootstrap';

// Import CKEditor 5 and plugins
import { 
    ClassicEditor, Essentials, Paragraph, Bold, Italic, 
    Heading, List, Alignment, Underline, Strikethrough, BlockQuote, 
    Table, TableToolbar, Link, Indent, IndentBlock
} from 'ckeditor5';
import { PasteFromOffice } from '@ckeditor/ckeditor5-paste-from-office';
import 'ckeditor5/ckeditor5.css';
import 'ckeditor5/translations/fa.js';

// Import KaTeX
import katex from 'katex';
import renderMathInElement from 'katex/dist/contrib/auto-render.mjs';
import 'katex/dist/katex.min.css';

window.katex = katex;
window.renderMathInElement = renderMathInElement;

window.ClassicEditor = ClassicEditor;
window.PasteFromOffice = PasteFromOffice;

window.renderMath = () => {
    renderMathInElement(document.body, {
        delimiters: [
            {left: '$$', right: '$$', display: true},
            {left: '$', right: '$', display: false},
            {left: '\\(', right: '\\)', display: false},
            {left: '\\[', right: '\\]', display: true}
        ],
        ignoredClasses: ['ck', 'ck-content', 'ck-editor__editable'],
        throwOnError: false
    });
};

// Global initializer for CKEditor
window.initCKEditor = function (elementSelector) {
    const el = typeof elementSelector === 'string' ? document.querySelector(elementSelector) : elementSelector;
    if (!el) return;

    return ClassicEditor.create(el, {
        licenseKey: 'GPL',
        language: 'fa',
        plugins: [ 
            Essentials, Paragraph, Bold, Italic, Underline, Strikethrough,
            Heading, List, Alignment, BlockQuote, Table, TableToolbar, Link,
            Indent, IndentBlock, PasteFromOffice 
        ],
        toolbar: [
            'heading', '|',
            'bold', 'italic', 'underline', 'strikethrough', '|',
            'alignment', 'bulletedList', 'numberedList', '|',
            'outdent', 'indent', '|',
            'insertTable', 'blockQuote', 'link', '|',
            'undo', 'redo'
        ],
        table: {
            contentToolbar: [ 'tableColumn', 'tableRow', 'mergeTableCells' ]
        }
    }).then(editor => {
        // Optional: Render math after paste or data change if needed inside the editor
        // editor.model.document.on('change:data', () => { window.renderMath(); });
        return editor;
    }).catch(error => {
        console.error("CKEditor Init Error:", error);
    });
};

document.addEventListener("DOMContentLoaded", window.renderMath);
document.addEventListener("livewire:navigated", window.renderMath);

// Render math when Livewire updates the DOM (Livewire 3 compatibility)
document.addEventListener("livewire:init", () => {
    Livewire.hook('commit', ({ succeed }) => {
        succeed(() => {
            setTimeout(() => {
                window.renderMath();
            }, 50);
        });
    });
});
