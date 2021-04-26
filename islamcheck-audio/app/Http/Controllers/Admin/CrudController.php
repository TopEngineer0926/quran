<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Enum;

class CrudController extends Controller
{
    public $model = null;
    public $model_path = 'App\Models\\';
    public $route = null;
    public $attributes;

    function __construct($model,$route)
    {
        $this->model = $this->model_path.$model;
        $this->route = $route;
    }

    public function viewall()
    {

        return $this->model::all();
    }

    public function index()
    {
        return $this->model::all();
    }

    public function destroy($id)
    {
        $this->model::destroy($id);
        return redirect(route($this->route.'.index'))->withErrors([Enum::success => [Enum::success_delete]]);
    }

    public function update(Request $request,$id,$table=null)
    {
        if($table!=null){
            $this->model=$table;
        }

        $model = $this->model::find($id);
        foreach($this->attributes as $attribute)
        {
            $model->$attribute = $request->get($attribute);
        }
        $model->save();
        return redirect(route($this->route.'.index'))->withErrors([Enum::success => [Enum::success_update]]);
    }

    public function store(Request $request)
    {
        $model = new $this->model;
        foreach($this->attributes as $attribute)
        {
            $model->$attribute = $request->get($attribute);
        }
        $model->save();
        return redirect(route($this->route.'.index'))->withErrors([Enum::success => [Enum::success_add]]);
    }

    public function create()
    {
        //
    }

    public function edit($id)
    {
        return $this->model::find($id);
    }

    public function show($id)
    {
        return $this->model::find($id);
    }

    public function attributes($attributes)
    {
        $this->attributes = $attributes;
    }

}
