<?php

namespace App\View\Components;

use Illuminate\View\Component;
use DateTimeZone;

class TimezoneSelect extends Component
{
    public $name; // The name of the select dropdown

    public $selected; // Pre-selected timezone (optional)

    public $timezones; // List of timezones

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($name = 'timezone', $selected = null)
    {
        $this->name = $name;
        $this->selected = $selected;

        // Get the list of all PHP supported timezones
        $this->timezones = DateTimeZone::listIdentifiers();
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.timezone-select');
    }
}
