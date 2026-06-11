<?php

namespace App\Providers\Filament;

use Filament\AvatarProviders\Contracts\AvatarProvider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable;

class CustomAvatarProvider implements AvatarProvider
{
    public function get(Model | Authenticatable $record): string
    {
        // Return local SVG file
        return asset('images/default-avatar.svg');
    }
}
