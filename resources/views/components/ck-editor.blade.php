<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div
        dir="rtl"
        x-data="{ state: $wire.$entangle('{{ $getStatePath() }}') }"
        x-init="
            const tryInit = () => {
                if (typeof window.initCKEditor !== 'undefined') {
                    window.initCKEditor($refs.editor).then(editor => {
                        // Set initial data
                        if (state) {
                            editor.setData(state);
                        }

                        // Sync editor data to Livewire
                        editor.model.document.on('change:data', () => {
                            state = editor.getData();
                        });

                        // Sync Livewire data to editor
                        $watch('state', (value) => {
                            if (value !== editor.getData()) {
                                editor.setData(value || '');
                            }
                        });
                    }).catch(err => console.error(err));
                } else {
                    setTimeout(tryInit, 100);
                }
            };
            tryInit();
        "
    >
        <div wire:ignore>
            <div x-ref="editor" class="text-right" style="direction: rtl; text-align: right; min-height: 200px;"></div>
        </div>
    </div>
    <style>
        .ck-editor__editable_inline {
            min-height: 250px !important;
        }
    </style>
</x-dynamic-component>
