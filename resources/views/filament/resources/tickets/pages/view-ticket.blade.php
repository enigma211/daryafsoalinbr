<x-filament-panels::page>
    @if ($this->hasInfolist())
        {{ $this->infolist }}
    @endif

    @if($record->status !== 'closed' || filament()->getCurrentPanel()->getId() === 'admin')
        <x-filament::section>
            <x-slot name="heading">ارسال پیام جدید</x-slot>
            
            <form wire:submit="reply">
                {{ $this->replyForm }}

                <div style="margin-top: 1rem; text-align: left;" dir="ltr">
                    <x-filament::button type="submit" color="primary">
                        ارسال پاسخ
                    </x-filament::button>
                </div>
            </form>
        </x-filament::section>
    @elseif($record->status === 'closed')
        <x-filament::section>
            <div style="text-align: center; color: gray;">
                این تیکت بسته شده است و امکان ارسال پیام جدید وجود ندارد.
            </div>
        </x-filament::section>
    @endif
</x-filament-panels::page>
