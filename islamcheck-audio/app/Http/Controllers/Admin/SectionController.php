<?php

namespace App\Http\Controllers\Admin;

use App\Models\Section;
use Illuminate\Http\Request;
use XMLWriter;
use App\Models\Page\Page;
use App\Models\Resource;
use App\Models\Source;
use App\Models\Enum;
use App\Models\Search;
use Exception;
use League\Csv\Reader;
use League\Csv\Statement;
use Illuminate\Support\Facades\Validator;


class SectionController extends CrudController
{
    public $model = 'Section';
    public $route = 'section';
    public $view = 'admin.view';
    private $title = 'Sections';

    function __construct()
    {
        $this->middleware('auth:admin');
        parent::__construct($this->model,$this->route);
    }

    public function index()
    {
        $resource = Section::select('id','name' )->orderBy('id', 'desc')->get();

        $page = new Page($this->title,$this->title);
        $page->create_button('Add',$this->route.'.create','add');
        $table = $page->table(['ID', 'Name'  ],$this->route);
        $table->add_actions([ 'edit','delete']);
        //$table->hide_columns(['ID']);
        $table->render($resource);
        $page->add($table);
        return view($this->view)->with(['page'=>$page]);
    }

    public function create()
    {

        $page = new Page($this->title,$this->title);
        $form = $page->form(route($this->route.'.store'));

        $status =  array(1=>'Yes',0=>'No');

        $form->render([

            ['label' => 'Name', 'type' => 'input', 'input-type' => 'text', 'name' => 'name', 'class' => 'form-control form-control-alternative', 'placeholder' => 'Name', 'required' => true],
            ['label' => 'Status', 'type' => 'select', 'name' => 'status', 'class' => 'form-control search-select', 'options' => $status],
            ['type'=>'input','input-type'=>'submit','class'=>'form-control form-control-alternative btn btn-success','name'=>'submit','val'=>'Submit']

        ]);

        $page->add($form);
        return view($this->view)->with(['page' => $page]);
    }

    public function store(Request $request)
    {

        try {

            $validator = Validator::make($request->all(), [
                'name' => ['required'],
                'status' => ['required'],

            ]);
            if ($validator->fails()) {
                return back()->withErrors([Enum::fail => $validator->errors()->first()]);
            }

            $section = new Section();

            $section->name = $request->name;
             $section->status = $request->status;

            $section->save();

            return back()->withErrors([Enum::success => [Enum::success_add]]);

        } catch (Exception $e) {
            return back()->withErrors([Enum::fail => $e->getMessage()]);
        }
    }

    public function edit($id)
    {
        $page = new Page($this->title,$this->title);
        $data = parent::edit($id);

        $status =  array(1=>'Yes',0=>'No');

        $form = $page->form(route($this->route.'.update',[$data->id]),'PATCH');
        $form->render([

            ['label' => 'Name', 'type' => 'input', 'input-type' => 'text', 'name' => 'name', 'class' => 'form-control form-control-alternative', 'placeholder' => 'Name', 'required' => true,'val'=>$data->name],
            ['label' => 'Status', 'type' => 'select', 'name' => 'status', 'class' => 'form-control search-select', 'options' => $status,'val'=>$data->status],
            ['type'=>'input','input-type'=>'submit','class'=>'form-control form-control-alternative btn btn-success','name'=>'submit','val'=>'Update']
        ]);
        $page->add($form);
        return view($this->view)->with(['page'=>$page]);
    }

    public function update(Request $request,$id,$table=null)
    {
        $this->attributes([
            'name',
            'status',

        ]);
        return parent::update($request,$id);
    }

    public function show($id)
    {
        $page = new Page($this->title,$this->title);
        $data = parent::edit($id);
        //$data = Qari::with('section')->where('id',$id)->orderBy('created_at', 'desc')->get();
        $section=Section::select('name')->find($data->section_id);


        $form = $page->form(route($this->route.'.update',[$data->id]));
        $form->render([
            ['label'=>'Name','type'=>'text','name'=>'name','class'=>'form-control form-control-alternative','placeholder'=>'Name' ,'val'=>$data->name],
            ['label'=>'Arabic Name','type'=>'text','name'=>'arabic_name','class'=>'form-control form-control-alternative','placeholder'=>'Arabic Name','val'=>$data->arabic_name],
            ['label'=>'Description','type'=>'text','name'=>'description','class'=>'form-control form-control-alternative','placeholder'=>'Description','val'=>$data->description],
            ['label'=>'Section','type'=>'text','name'=>'section','class'=>'form-control form-control-alternative','placeholder'=>'Description','val'=>$section->name],
            ['label'=>'Status','type'=>'text','name'=>'status','class'=>'form-control form-control-alternative','placeholder'=>'Description','val'=>$data->status==1?"Yes":"No"],

        ]);
        $page->add($form);
        return view($this->view)->with(['page'=>$page]);
    }


}
