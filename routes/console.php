<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('portfolio:about', function () {
    $this->comment('Amanullah - PHP & Laravel Developer Portfolio');
})->purpose('Display portfolio information');
