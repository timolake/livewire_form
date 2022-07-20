<?php

namespace timolake\livewireForms\Commands;

use Illuminate\Console\Command;

class LivewireFormCommand extends Command
{
    public $signature = 'livewireForm';

    public $description = '';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
