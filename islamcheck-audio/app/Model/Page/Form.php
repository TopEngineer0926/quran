<?php

namespace App\Models\Page;

use Illuminate\Database\Eloquent\Model;

class Form extends Model
{
    public $action;
    public $method;
    public $form;

    function __construct($action='',$method='post')
    {
        $this->action = $action;
        $this->method = $method;
    }

    public function render($form)
    {
        $this->form = $form;
    }
}
