<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\Page\Page;
use App\Models\Language;
use App\Models\Translations;
use App\Models\WordTranslation;
use App\Models\WordTranslationList;
use App\Models\Enum;
use Illuminate\Support\Facades\Validator;
use League\Csv\Reader;
use League\Csv\Statement;
use Exception;

class WBWController extends CrudController
{
    public $model = 'WordTranslationList';
    public $route = 'wbw';
    public $view = 'admin.view';
    private $title = 'Word By Word';
    function __construct()
    {
        $this->middleware('auth:admin');
        parent::__construct($this->model, $this->route);
    }

    public function index()
    {
        $word_translation_list = WordTranslationList::select('id', 'language_id')->with('language:id,name')->where('language_code', '!=', 'en')->orderby('created_at','DESC')->get();
        $page = new Page($this->title, $this->title);
        $page->create_button('Add', $this->route . '.create', 'add');
        $table = $page->table(['ID', 'Language'], $this->route);
        $table->add_actions(['delete']);
        $table->replace_column('language_id', 'language', 'name');
        $table->hide_columns(['ID']);
        $table->render($word_translation_list);
        $page->add($table);
        return view($this->view)->with(['page' => $page]);
    }

    public function create()
    {
        $page = new Page('Upload WBW', 'Upload Word By Word');
        $form = $page->form(route($this->route.'.store'));
        $languages = Language::orderby('name', 'asc')->get();
        $languages_list = $languages->mapWithKeys(function ($item) {
            return [$item['id'] => $item['name'] . ' (' . $item['iso_code'] . ')'];
        });
        $form->render([
            ['label' => 'Language', 'type' => 'select', 'name' => 'language_id', 'class' => 'form-control search-select', 'options' => $languages_list],
            ['label' => 'Upload CSV', 'type' => 'file', 'name' => 'file', 'required' => true],
            ['type' => 'input', 'input-type' => 'submit', 'class' => 'form-control form-control-alternative btn btn-success', 'name' => 'submit', 'val' => 'Submit']
        ]);
        $page->add($form);
        return view('admin.view')->with(['page' => $page]);
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
            $language = Language::where('id', $language_id)->first();
            $csv = Reader::createFromPath($file->getRealPath(), 'r');
            $csv->setHeaderOffset(0); //set the CSV header offset
            $stmt = (new Statement())
                ->offset(0); //start getting data from first row

            $records = $stmt->process($csv);
            $translations = [];
            $word_translation = [];
            $sql = "DELETE FROM translations WHERE language_id=$language_id";
            \DB::connection('mysql')->select(\DB::raw($sql));
            $sql = "DELETE FROM word_translation WHERE language_code='$language->iso_code'";
            \DB::connection('mysql')->select(\DB::raw($sql));
            $count = count($records);
            foreach ($records as $record) {
                $translation_array = [
                    'language_id' => $language_id,
                    'text' => $record['translation'],
                    'language_name' => lcfirst($language->name)
                ];
                $translations[] = $translation_array;
                $word_translation_array = [
                    'word_id' => $record['id'],
                    'language_code' => $language->iso_code
                ];
                $word_translation[] = $word_translation_array;
            }
            $translations_chunks = array_chunk($translations, 1000);
            foreach ($translations_chunks as $translations_chunk) {
                Translations::insert($translations_chunk);
            }
            $sql = "SELECT id FROM translations WHERE language_id=$language_id ORDER BY id ASC";
            $translations = \DB::connection('mysql')->select(\DB::raw($sql));
            $loop = 0;
            foreach ($translations as $translation) {
                $word_translation[$loop]['translation_id'] = $translation->id;
                $loop++;
            }
            $word_translation_chunks = array_chunk($word_translation, 1000);
            foreach ($word_translation_chunks as $word_translation_chunk) {
                WordTranslation::insert($word_translation_chunk);
            }
            $word_translation_list = WordTranslationList::where('language_id',$language_id)->first();
            if(!$word_translation_list)
            {
                $word_translation_list = new WordTranslationList;
            }
            $word_translation_list->language_id = $language_id;
            $word_translation_list->language_code = $language->iso_code;
            $word_translation_list->is_active =  1;
            $word_translation_list->save();
            $time = microtime(true) - $start; //end timer
            return back()->withErrors([Enum::success => number_format((float) $time, 2, '.', '') . 's']);
            return back()->withErrors([Enum::success => [Enum::success_add]]);
        } catch (Exception $e) {
            return back()->withErrors([Enum::fail => $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        $word_translation_list = WordTranslationList::find($id);
        $sql = "DELETE FROM translations WHERE language_id=$word_translation_list->language_id";
        \DB::connection('mysql')->select(\DB::raw($sql));
        $sql = "DELETE FROM word_translation WHERE language_code='$word_translation_list->language_code'";
        \DB::connection('mysql')->select(\DB::raw($sql));
        return parent::destroy($id);
    }
}
