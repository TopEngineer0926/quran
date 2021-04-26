<?php

namespace App\Models\Page;

use Illuminate\Database\Eloquent\Model;
use stdClass;

class TableOption extends Model
{
    public $types = null;

    function __construct()
    {
        $this->types = new stdClass;
        $this->add_types();
    }

    private function add_types()
    {
        $this->types->image = 'image';
    }
}
