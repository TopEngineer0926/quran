<?php

namespace App\Http\Controllers\Admin;

use App\Models\Section;
use App\Models\Surah;
use Illuminate\Http\Request;
use XMLWriter;
use App\Models\Page\Page;
use App\Models\Resource;
use App\Models\Language;
use App\Models\Qari;
use App\Models\Source;
use App\Models\Enum;
use App\Models\Translation;
use App\Models\Search;
use Illuminate\Support\Facades\DB;
use Exception;
use League\Csv\Reader;
use League\Csv\Statement;
use Illuminate\Support\Facades\Validator;


class QariController extends CrudController
{
    public $model = 'Qari';
    public $route = 'reciter';
    public $view = 'admin.view';
    private $title = 'Reciters';

    function __construct()
    {
        $this->middleware('auth:admin');
        parent::__construct($this->model,$this->route);
    }

    public function index()
    {
        $resource = Qari::select('id',   'name',  'section_id'  )->with('section')->orderBy('created_at', 'desc')->get();

        $page = new Page($this->title,$this->title);
        $page->create_button('Add',$this->route.'.create','add');
        $table = $page->table(['ID', 'Name', 'Section' ],$this->route);
        $table->add_actions([ 'edit','delete']);
        $table->replace_column('section_id','section','name');
        //$table->replace_column('author_id','author','name');
        //$table->hide_columns(['ID']);
        $table->render($resource);
        $page->add($table);
        return view($this->view)->with(['page'=>$page]);
    }

    public function create()
    {

        $page = new Page($this->title,$this->title);
        $form = $page->form(route($this->route.'.store'));

        $sections = Section::orderby('name', 'asc')->get();
        $sections_list = $sections->mapWithKeys(function ($item) {
            return [$item['id'] => $item['name']  ];
        });

        $status =  array(1=>'Yes',0=>'No');

        $form->render([

            ['label' => 'Section', 'type' => 'select', 'name' => 'section_id', 'class' => 'form-control search-select', 'options' => $sections_list],
            ['label' => 'Simple Name', 'type' => 'input', 'input-type' => 'text', 'name' => 'name', 'class' => 'form-control form-control-alternative', 'placeholder' => 'Name', 'required' => true],
            //['label' => 'Arabic Name', 'type' => 'input', 'input-type' => 'text', 'name' => 'arabic_name', 'class' => 'form-control form-control-alternative', 'placeholder' => 'Arabic Name', 'required' => true],
            ['label' => 'Status', 'type' => 'select', 'name' => 'status', 'class' => 'form-control search-select', 'options' => $status],
            ['label'=>'Description','type'=>'input','input-type'=>'text','name'=>'description','class'=>'form-control form-control-alternative','placeholder'=>'Description','clip'=>'50'],

            ['type'=>'input','input-type'=>'submit','class'=>'form-control form-control-alternative btn btn-success','name'=>'submit','val'=>'Submit']

        ]);

        $page->add($form);
        return view($this->view)->with(['page' => $page]);
    }

    public function store(Request $request)
    {

        try {

            $validator = Validator::make($request->all(), [
                'section_id' => ['required'],
                'name' => ['required'],
                //'arabic_name' => ['required'],
                'status' => ['required'],

            ]);
            if ($validator->fails()) {
                return back()->withErrors([Enum::fail => $validator->errors()->first()]);
            }

            $qari = new Qari();

            $qari->section_id = $request->section_id;
            $qari->name = $request->name;
            $qari->arabic_name = '';
            $qari->status = $request->status;
            $qari->home = 1;
            $qari->file_formats = 'mp3';
            $qari->relative_path = file_format($request->name);
            $qari->description = $request->description;

            $qari->save();

            return back()->withErrors([Enum::success => [Enum::success_add]]);

        } catch (Exception $e) {
            return back()->withErrors([Enum::fail => $e->getMessage()]);
        }
    }

    public function edit($id)
    {
        $page = new Page($this->title,$this->title);
        $data = parent::edit($id);

        $sections = Section::orderby('name', 'asc')->get();
        $sections_list = $sections->mapWithKeys(function ($item) {
            return [$item['id'] => $item['name']  ];
        });

        $status =  array(1=>'Yes',0=>'No');

        $form = $page->form(route($this->route.'.update',[$data->id]),'PATCH');
        $form->render([

            ['label' => 'Section', 'type' => 'select', 'name' => 'section_id', 'class' => 'form-control search-select', 'options' => $sections_list,'val'=>$data->section_id],
            ['label' => 'Simple Name', 'type' => 'input', 'input-type' => 'text', 'name' => 'name', 'class' => 'form-control form-control-alternative', 'placeholder' => 'Name', 'required' => true,'val'=>$data->name],
            //['label' => 'Arabic Name', 'type' => 'input', 'input-type' => 'text', 'name' => 'arabic_name', 'class' => 'form-control form-control-alternative', 'placeholder' => 'Arabic Name', 'required' => true,'val'=>$data->arabic_name],
            ['label' => 'Status', 'type' => 'select', 'name' => 'status', 'class' => 'form-control search-select', 'options' => $status,'val'=>$data->status],
            ['label'=>'Description','type'=>'input','input-type'=>'text','name'=>'description','class'=>'form-control form-control-alternative','placeholder'=>'Description','clip'=>'50','val'=>$data->description],

            ['type'=>'input','input-type'=>'submit','class'=>'form-control form-control-alternative btn btn-success','name'=>'submit','val'=>'Update']
        ]);
        $page->add($form);
        return view($this->view)->with(['page'=>$page]);
    }

    public function update(Request $request,$id,$table=null)
    {
        $this->attributes([
            'section_id',
            'name',
            'arabic_name',
            'description',
            'status',

        ]);
        return parent::update($request,$id);
    }

    public function destroy($id)
    {

        $model = $this->model::find($id);

        $folder=str_replace('/','',$model->relative_path);

        if($this->model::destroy($id)){

			DB::table('recitations')->where('qari_id', $id)->delete();
            DB::table('files')->where('folder', $folder)->delete();
            DB::table('directories')->where('name', $folder)->delete();
			
        }
        return redirect(route($this->route.'.index'))->withErrors([Enum::success => [Enum::success_delete]]);
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
            //['label'=>'Arabic Name','type'=>'text','name'=>'arabic_name','class'=>'form-control form-control-alternative','placeholder'=>'Arabic Name','val'=>$data->arabic_name],
            ['label'=>'Description','type'=>'text','name'=>'description','class'=>'form-control form-control-alternative','placeholder'=>'Description','val'=>$data->description],
            ['label'=>'Section','type'=>'text','name'=>'section','class'=>'form-control form-control-alternative','placeholder'=>'Description','val'=>$section->name],
            ['label'=>'Status','type'=>'text','name'=>'status','class'=>'form-control form-control-alternative','placeholder'=>'Description','val'=>$data->status==1?"Yes":"No"],

        ]);
        $page->add($form);
        return view($this->view)->with(['page'=>$page]);
    }


}
