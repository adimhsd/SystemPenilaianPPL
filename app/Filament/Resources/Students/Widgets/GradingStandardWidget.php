<?php

namespace App\Filament\Resources\Students\Widgets;

use Filament\Widgets\Widget;

class GradingStandardWidget extends Widget
{
    protected string $view = 'filament.resources.students.widgets.grading-standard-widget';

    protected int | string | array $columnSpan = 'full';
}
