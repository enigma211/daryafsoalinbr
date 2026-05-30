<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div
        x-data="{ state: $wire.$entangle('{{ $getStatePath() }}') }"
        x-init="
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
            });
        "
        x-ignore
        wire:ignore
    >
        <div x-ref="editor"></div>
    </div>
</x-dynamic-component>
