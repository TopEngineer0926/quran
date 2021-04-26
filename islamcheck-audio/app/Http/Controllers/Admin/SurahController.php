<?php

namespace App\Http\Controllers\Admin;

use App\Models\Surah;
use App\Models\Translation;
use Illuminate\Http\Request;
use App\Models\Page\Page;
use App\Models\Resource;
use App\Models\Language;
use App\Models\Enum;
use League\Csv\Reader;
use League\Csv\Statement;
use Exception;
use Illuminate\Support\Facades\Validator;

class SurahController extends CrudController
{
    public $model = 'Surah';
    public $route = 'surahs';
    public $view = 'admin.view';
    private $title = 'Surahs';

    function __construct()
    {
        $this->middleware('auth:admin');
        parent::__construct($this->model,$this->route);
    }

    public function index()
    {

//        $languages=Language::select('code','name')->where('status',1)->orderby('id','desc')->get()->toArray();
//
//        $resource = Surah::select('id',   'simple_name',  'arabic_name', 'revelation_place', 'count_verses' )->orderBy('created_at', 'desc')->get();
//
//        $page = new Page($this->title,$this->title);
//        $page->create_button('Add',$this->route.'.create','add');
//        $page->create_filter($languages,$this->route,'filter');
//        $table = $page->table(['ID', 'Name','Arabic Name','Revelation Place','Total Ayats' ],$this->route);
//        $table->add_actions([ 'edit','delete']);
//        //$table->replace_column('language_code','language','name');
//        //$table->replace_column('author_id','author','name');
//        $table->hide_columns(['ID']);
//        $table->render($resource);
//        //$table->add_actions(['view','edit','delete']);
//        $page->add($table);
//        return view($this->view)->with(['page'=>$page]);

        $language='de';
        $languages=Language::select('code','name')->where('status',1)->orderby('id','desc')->get()->toArray();

        $resource = Translation::select('id','source_id' , 'name', 'original_name','language_code')->with(['language' => function ($query ) use ($language) {
            $query->where('code',$language);
        }])->where('language_code',$language)->where('source_type','surah')->get()   ;

        $page = new Page($this->title,$this->title);
        $page->create_button('Add',$this->route.'.create','add');

        $page->create_filter($languages,route($this->route.'.index'),'filter',$language);
        $table = $page->table([ 'Primary_ID','ID', 'Name','English Name','Language' ],$this->route);
        $table->add_actions([ 'edit','delete']);
        $table->replace_column('language','language','name');

        $table->hide_columns(['Primary_ID']);
        $table->render($resource);
        $page->add($table);
        return view($this->view)->with(['page'=>$page]);
    }

    public function create()
    {

        $page = new Page($this->title,$this->title);
        $form = $page->form(route('surahs.store'));

        $languages = Language::orderby('name', 'asc')->where('status',1)->get();
        $languages_list = $languages->mapWithKeys(function ($item) {
            return [$item['code'] => $item['name'] . ' (' . $item['code'] . ')'];
        });

        $form->render([
            ['label' => 'Language', 'type' => 'select', 'name' => 'language_id', 'class' => 'form-control search-select', 'options' => $languages_list],
            ['label' => 'Upload CSV', 'type' => 'file', 'name' => 'file', 'required' => true],
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
                'language_id' => ['required'],
                'file' => ['required', 'mimes:csv,txt'],
            ]);
            if ($validator->fails()) {
                return back()->withErrors([Enum::fail => $validator->errors()->first()]);
            }
            $language_id = $request->language_id;
            $file = $request->file('file');
            $language = Language::where('code', $language_id)->first();

            $csv = Reader::createFromPath($file->getRealPath(), 'r');
            $csv->setHeaderOffset(0); //set the CSV header offset
            $stmt = (new Statement())
                ->offset(0); //start getting data from first row

            $records = $stmt->process($csv);

            $translations = [];

            $sql = "DELETE FROM translations WHERE language_code='$language->code' and source_type='surah'";
            \DB::connection('mysql')->select(\DB::raw($sql));
            $count = count($records);
            foreach ($records as $record) {
                $translation_array = [
                    'language_code' => $language_id,
                    'original_name' => $record['original_name'],
                    'name' => $record['translation'],
                    'source_id' => $record['id'],
                    'source_type'=>'surah',
                ];
                $translations[] = $translation_array;

            }
            $translations_chunks = array_chunk($translations, 1000);

            foreach ($translations_chunks as $translations_chunk) {
                Translation::insert($translations_chunk);
            }

            $time = microtime(true) - $start; //end timer
            return back()->withErrors([Enum::success => number_format((float) $time, 2, '.', '') . 's']);
            return back()->withErrors([Enum::success => [Enum::success_add]]);
        } catch (Exception $e) {
            return back()->withErrors([Enum::fail => $e->getMessage()]);
        }

//        try {
//
//            $validator = Validator::make($request->all(), [
//                'revelation_place' => ['required'],
//                'revelation_order' => ['required'],
//                'simple_name' => ['required'],
//                'count_verses' => ['required'],
//                'pages' => ['required'],
//                'start_page' => ['required'  ],
//                'end_page' => ['required'],
//                'complex_name' => ['required'],
//                'arabic_name' => ['required'],
//                'english_name' => ['required' ],
//            ]);
//            if ($validator->fails()) {
//                return back()->withErrors([Enum::fail => $validator->errors()->first()]);
//            }
//
//            $surah = new Surah();
//
//            $surah->bismillah_pre = $request->bismillah_pre;
//            $surah->revelation_order = $request->revelation_order;
//            $surah->revelation_place = $request->revelation_place;
//            $surah->simple_name = $request->simple_name;
//            $surah->count_verses = $request->count_verses;
//            $surah->pages = $request->pages;
//            $surah->start_page = $request->start_page;
//            $surah->end_page = $request->end_page;
//            $surah->english_name = $request->english_name;
//            $surah->arabic_name = $request->arabic_name;
//            $surah->complex_name = $request->complex_name;
//            $surah->status = $request->status;
//
//            $surah->save();
//
//            return back()->withErrors([Enum::success => [Enum::success_add]]);
//
//        } catch (Exception $e) {
//            return back()->withErrors([Enum::fail => $e->getMessage()]);
//        }
    }

    public function edit($id)
    {
        $page = new Page($this->title,$this->title);
        $data = parent::edit($id);
        $data = Translation::find($id) ;

        $languages=Language::select('code','name')->where('status',1)->orderby('id','desc')->get();

        $languages_list = $languages->mapWithKeys(function ($item) {
            return [$item['code'] => $item['name']  ];
        });

        $status =  array(0=>'No',1=>'Yes');

        $form = $page->form(route($this->route.'.update',[$data->id]),'PATCH');
        $form->render([

            ['label' => 'Language', 'type' => 'select', 'name' => 'language_code', 'class' => 'form-control search-select', 'options' => $languages_list,'val'=>$data->language_code],
            ['label' => 'English Name', 'type' => 'input', 'input-type' => 'text', 'name' => 'original_name', 'class' => 'form-control form-control-alternative', 'placeholder' => 'English Name', 'disabled' => true,'val'=>$data->original_name],
            ['label' => 'Language Name', 'type' => 'input', 'input-type' => 'text', 'name' => 'name', 'class' => 'form-control form-control-alternative', 'placeholder' => 'Language Name', 'required' => true,'val'=>$data->name],
            ['type'=>'input','input-type'=>'submit','class'=>'form-control form-control-alternative btn btn-success','name'=>'submit','val'=>'Update']
        ]);
        $page->add($form);
        return view('admin.view')->with(['page'=>$page]);
    }

    public function update(Request $request,$id,$table=null)
    {
        $this->attributes([
            'language_code',
            'name',
        ]);
        return parent::update($request,$id,'App\Models\\Translation');
    }

    public function show($language){


        $languages=Language::select('code','name')->where('status',1)->orderby('id','desc')->get()->toArray();

        $resource = Translation::select('id','source_id' , 'name', 'original_name','language_code')->with(['language' => function ($query ) use ($language) {
            $query->where('code',$language);
        }])->where('language_code',$language)->where('source_type','surah')->get()   ;

        $page = new Page($this->title,$this->title);
        $page->create_button('Add',$this->route.'.create','add');

        $page->create_filter($languages,route($this->route.'.index'),'filter',$language);
        $table = $page->table([ 'Primary_ID','ID', 'Name','English Name','Language' ],$this->route);
        $table->add_actions([ 'edit','delete']);
        $table->replace_column('language','language','name');

        $table->hide_columns(['Primary_ID']);
        $table->render($resource);
        $page->add($table);
        return view($this->view)->with(['page'=>$page]);

//        $languages=Language::select('code','name')->where('status',1)->orderby('id','desc')->get()->toArray();
//
//        $resource = Surah::select('id' ,  'simple_name', 'english_name',   'revelation_place', 'count_verses'  )->with(['translation' => function ($query ) use ($language) {
//            $query->where('language_code',$language);
//            $query->where('source_type','surah');
//        }])->get();
//
//
//        $page = new Page($this->title,$this->title);
//        $page->create_button('Add',$this->route.'.create','add');
//        $page->create_filter($languages,$this->route,'filter');
//        $table = $page->table([ 'ID', 'Name','English Name', 'Revelation Place','Total Ayats' ],$this->route);
//        $table->add_actions([ 'edit','delete']);
//        //$table->replace_column('simple_name','translation','name');
//        $table->replace_column('id','translation','id');
//        //$table->replace_column('author_id','author','name');
//        $table->hide_columns(['ID']);
//        $table->render($resource);
//        //$table->add_actions(['view','edit','delete']);
//        $page->add($table);
//        return view($this->view)->with(['page'=>$page]);

    }

}
