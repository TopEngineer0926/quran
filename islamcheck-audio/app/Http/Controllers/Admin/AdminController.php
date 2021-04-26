<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page\Page;
use App\Models\Resource;

class AdminController extends Controller
{
    private $title = 'Dashboard';
    public $view = 'admin.home';

    public function __construct()
    {
        $this->middleware(['auth:admin']);
    }
    public function index()
    {
        $page = new Page($this->title,$this->title);
        return view($this->view)->with(['page'=>$page]);

    }

}
