<?php

namespace timolake\livewireForms\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \timolake\livewireForms\LivewireForm
 */
class LivewireForm extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'livewireForm';
    }
}
