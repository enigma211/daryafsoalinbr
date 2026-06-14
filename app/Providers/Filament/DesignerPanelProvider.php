<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class DesignerPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('designer')
            ->path('designer')
            ->login()
            ->passwordReset(\App\Filament\Pages\Auth\SmsPasswordReset::class)
            ->brandName('داشبورد طراحان سوال')
            ->sidebarCollapsibleOnDesktop()
            ->font('Vazirmatn', asset('fonts/vazirmatn/Vazirmatn-font-face.css'))
            ->defaultAvatarProvider(\App\Providers\Filament\CustomAvatarProvider::class)
            ->renderHook(
                \Filament\View\PanelsRenderHook::HEAD_END,
                fn (): string => '<style>
                    :root { --font-family: "Vazirmatn", tahoma, sans-serif !important; }
                    body, .fi-body { font-family: "Vazirmatn", tahoma, sans-serif !important; }
                </style>'
            )
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Designer/Resources'), for: 'App\Filament\Designer\Resources')
            ->resources([
                \App\Filament\Resources\Questions\QuestionResource::class,
                \App\Filament\Resources\Tickets\TicketResource::class,
            ])
            ->navigationItems([
                \Filament\Navigation\NavigationItem::make('ارسال سوال')
                    ->url(fn (): string => \App\Filament\Resources\Questions\QuestionResource::getUrl('create'))
                    ->icon('heroicon-o-plus-circle')
                    ->isActiveWhen(fn () => request()->routeIs('filament.designer.resources.questions.create'))
                    ->sort(1),
                \Filament\Navigation\NavigationItem::make('مشاهده سوالات')
                    ->url(fn (): string => \App\Filament\Resources\Questions\QuestionResource::getUrl('index'))
                    ->icon('heroicon-o-rectangle-stack')
                    ->isActiveWhen(fn () => request()->routeIs('filament.designer.resources.questions.index') || request()->routeIs('filament.designer.resources.questions.edit') || request()->routeIs('filament.designer.resources.questions.view'))
                    ->sort(2),
            ])
            ->discoverPages(in: app_path('Filament/Designer/Pages'), for: 'App\Filament\Designer\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Designer/Widgets'), for: 'App\Filament\Designer\Widgets')
            ->widgets([
                // Default widgets removed as requested
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
