<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Map extends Component
{
    public $center;
    public $zoom;
    public $markers;
    public $currentLocation;
    public $fullscreen;
    public $class;
    public $mapId;
    public $clickable;
    public $onClick;

    public function __construct(
        $center = null,
        $zoom = 10,
        $markers = [],
        $currentLocation = false,
        $fullscreen = false,
        $class = '',
        $mapId = 'map',
        $clickable = false,
        $onClick = null
    ) {
        $this->center = $center ?? ['lat' => 20.5937, 'lon' => 78.9629]; // Default to India
        $this->zoom = $zoom;
        $this->markers = $markers;
        $this->currentLocation = $currentLocation;
        $this->fullscreen = $fullscreen;
        $this->class = $class;
        $this->mapId = $mapId;
        $this->clickable = $clickable;
        $this->onClick = $onClick;
    }

    public function render(): View|Closure|string
    {
        return view('components.map');
    }
}
