<?php

namespace App\Http\Controllers\Admin;

use App\Models\Recitation;
use App\Models\Surah;
use App\Models\Qari;
use Illuminate\Http\Request;
use App\Models\Page\Page;
//use App\Models\Resource;
use App\Models\Enum;

use Illuminate\Support\Facades\Storage;
use Exception;
use Illuminate\Support\Facades\Validator;

class RecitationController extends CrudController
{
    public $model = 'Recitation';
    public $route = 'recitation';
    public $view = 'admin.view';
    private $title = 'Recitations';

    function __construct()
    {
        $this->middleware('auth:admin');
        parent::__construct($this->model,$this->route);
    }

    public function index()
    {
        $qaris=Qari::select('id as code','name')->orderby('relative_path','asc')->get()->toArray();

        $resource = Recitation::select('id',  'surah_id', 'name',  'qari_id'  )->where('qari_id',$qaris[0]['code'])->where('surah_id','<>',0)->with('qari')->orderBy('surah_id', 'asc')->get();

        $page = new Page($this->title,$this->title);
        $page->create_filter($qaris,route($this->route.'.index'),'filter',$qaris[0]['code']);
        $page->create_button('Add',$this->route.'.create','add');
        $table = $page->table(['id','ID', 'Name','Reciter'  ],$this->route);
        $table->add_actions([ 'edit' ]);
        $table->replace_column('qari_id','qari','name');

        $table->hide_columns(['id']);
        $table->render($resource);
        //$table->add_actions(['view','edit','delete']);
        $page->add($table);
        return view($this->view)->with(['page'=>$page]);
    }

    public function create()
    {

        $page = new Page($this->title,$this->title);
        $form = $page->form(route('recitation.store'));

        $surahs = Surah::orderby('id', 'asc') ->get();
        $surahs_list = $surahs->mapWithKeys(function ($item) {
            return [$item['id'] => $item['simple_name']  ];
        });

        $qaris = Qari::orderby('name', 'asc') ->get();
        $qari_list = $qaris->mapWithKeys(function ($item) {
            return [$item['id'] => $item['name']  ];
        });

        $form->render([
            ['label' => 'Surah', 'type' => 'select', 'name' => 'surah_id', 'class' => 'form-control search-select', 'options' => $surahs_list],
            ['label' => 'Qari', 'type' => 'select', 'name' => 'qari_id', 'class' => 'form-control search-select', 'options' => $qari_list],
            ['label' => 'Upload MP3', 'type' => 'file', 'name' => 'file', 'required' => true],
            ['type' => 'input', 'input-type' => 'submit', 'class' => 'form-control form-control-alternative btn btn-success', 'name' => 'submit', 'val' => 'Submit']
        ]);

        $page->add($form);
        return view($this->view)->with(['page' => $page]);
    }

    public function store(Request $request)
    {

        $start = microtime(true); //start timer count
        try {

            $validator = Validator::make($request->all(), [
                'surah_id' => ['required'],
                'qari_id' => ['required'],
                'file' => ['required', function ($attribute, $value, $fail) {
                    if ($value->getClientOriginalExtension() != 'mp3') {
                        $fail(':attribute must be .mp3!');
                    }

                }],
            ]);

            if ($validator->fails()) {
                return back()->withErrors([Enum::fail => $validator->errors()->first()]);
            }

            $qari=Qari::find($request->qari_id);

            if (!Storage::disk('s3')->exists($qari->relative_path)) {
                Storage::disk('s3')->makeDirectory($qari->relative_path, 0775, true);
            }

            $file = $request->file('file');
            $fileName =  $file->getClientOriginalName();

            $path = Storage::disk('s3')->put(
                $qari->relative_path.'/'.$fileName,
                file_get_contents($request->file('file')),
                'public'
            );

            if($path){

                $recitation = new Recitation();

                $recitation->surah_id = $request->surah_id;
                $recitation->qari_id = $request->qari_id;
                $recitation->file_name = $fileName;
                $recitation->extension =  $file->getClientOriginalExtension();
                $recitation->format_long_name = $file->getMimeType();
                $recitation->size = $file->getSize();
                $recitation->stream_count = $file->getClientOriginalExtension();
                $recitation->duration = 0;
                $recitation->bit_rate = $file->getSize();
                $recitation->probe_score = 0;
                $recitation->start_time = '0';
                $recitation->save();

                return back()->withErrors([Enum::success => [Enum::success_add]]);
            }else{
                return back()->withErrors([Enum::fail =>  'There is some issue with this file uploadig']);
            }

        } catch (Exception $e) {
            return back()->withErrors([Enum::fail => $e->getMessage()]);
        }

    }

    public function edit($id)
    {
        $page = new Page($this->title,$this->title);
        $data = parent::edit($id);
        $data = Recitation::find($id) ;

        $surahs = Surah::orderby('id', 'asc') ->get();
        $surahs_list = $surahs->mapWithKeys(function ($item) {
            return [$item['id'] => $item['simple_name']  ];
        });

        $qaris = Qari::orderby('name', 'asc') ->get();
        $qari_list = $qaris->mapWithKeys(function ($item) {
            return [$item['id'] => $item['name']  ];
        });

        $form = $page->form(route($this->route.'.update',[$data->id]),'PATCH');
        $form->render([
            ['label' => 'Surah', 'type' => 'select', 'name' => 'surah_id', 'class' => 'form-control search-select', 'options' => $surahs_list,'val'=>$data->surah_id],
            ['label' => 'Qari', 'type' => 'select', 'name' => 'qari_id', 'class' => 'form-control search-select', 'options' => $qari_list,'val'=>$data->qari_id],
            ['label' => 'Upload MP3', 'type' => 'file', 'name' => 'file_name', 'val'=>$data->file_name],
            ['type'=>'input','input-type'=>'submit','class'=>'form-control form-control-alternative btn btn-success','name'=>'submit','val'=>'Update']
        ]);

        $page->add($form);
        return view('admin.view')->with(['page'=>$page]);
    }

    public function update(Request $request,$id,$table=null)
    {

        $validator = Validator::make($request->all(), [
            'surah_id' => ['required'],
            'qari_id' => ['required'],
            //'file_name' => ['required', 'mimes:mp3'],
        ]);

        if ($validator->fails()) {
            return back()->withErrors([Enum::fail => $validator->errors()->first()]);
        }

        $this->attributes([
            'surah_id',
            'qari_id',
            'file_name',
            'extension',
        ]);


        if($request->file){

            $qari=Qari::find($request->qari_id);

            if (!Storage::disk('s3')->exists($qari->relative_path)) {
                Storage::disk('s3')->makeDirectory($qari->relative_path, 0775, true);
            }

            $file = $request->file('file_name');
            $fileName =  $file->getClientOriginalName();

            $path = Storage::disk('s3')->put(
                $qari->relative_path.'/'.$fileName,
                file_get_contents($request->file('file_name')),
                'public'
            );

            $request['file_name']=$fileName;
            $request['extension']=$file->getClientOriginalExtension();

        }

        return parent::update($request,$id);
    }
	
	 

    public function show($qari)
    {
        $qaris=Qari::select('id as code','name')->orderby('relative_path','asc')->get()->toArray();

        $resource = Recitation::select('id', 'surah_id',  'name',  'qari_id'  )->where('qari_id',$qari)->where('surah_id','<>',0)->with('qari')->orderBy('surah_id', 'asc')->get();

        $page = new Page($this->title,$this->title);
        $page->create_filter($qaris,route($this->route.'.index'),'filter',$qari);
        $page->create_button('Add',$this->route.'.create','add');
        $table = $page->table(['id','ID', 'Name','Reciter'  ],$this->route);
        $table->add_actions([ 'edit' ]);
        $table->replace_column('qari_id','qari','name');

        $table->hide_columns(['id']);
        $table->render($resource);
        //$table->add_actions(['view','edit','delete']);
        $page->add($table);
        return view($this->view)->with(['page'=>$page]);
    }


}
