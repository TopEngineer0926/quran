<?php
/**
 * Created by PhpStorm.
 * User: Noreen Gul
 * Date: 1/15/2020
 * Time: 12:41 PM
 */

namespace App\Http\Controllers\API;

use App\Models\Language;
use App\Models\Recitation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\Qari;
use App\Models\Surah;
use App\Models\Translation;

use App\Models\Section;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class APIController extends Controller
{
    protected function languages($language='de'){

        $languages=Language::where('status',1)->orderby('id','desc')->get()->toArray();

        foreach($languages as $key => $language){

            $languages_others=[];

            $languages_others = Translation::where('language_code',$language['code'])
                ->Where('source_type','other')
                ->get()->toArray();

            $word_key=array_search('Languages', array_column($languages_others, 'original_name'));
            if(isset($languages_others[$word_key]['name'])){
                $languages[$key]['language_word']=$languages_others[$word_key]['name'];
            }else{
                $languages[$key]['language_word']='Languages';
            }

            $word_reciter_key=array_search('Reciters', array_column($languages_others, 'original_name'));
            if(isset($languages_others[$word_reciter_key]['name'])){
                $languages[$key]['other_qaris']=$languages_others[$word_reciter_key]['name'];
            }else{
                $languages[$key]['other_qaris']= 'Reciters';
            }

            $word_key_suffle_play=array_search('Shuffle Play', array_column($languages_others, 'original_name'));

            if($language['code']=='de'){

                $languages[$key]['shuffle_play']=$languages_others[$word_key_suffle_play]['name'];
            }else{

                $languages[$key]['shuffle_play']= 'Shuffle Play';

            }

            $word_download_key=array_search('Download', array_column($languages_others, 'original_name'));
            if(isset($languages_others[$word_download_key]['name'])){
                $languages[$key]['download']=$languages_others[$word_download_key]['name'];

            }else{
                $languages[$key]['download']= 'Download';
            }

            $word_reading_key=array_search('Read', array_column($languages_others, 'original_name'));
            if(isset($languages_others[$word_reading_key]['name'])){
                $languages[$key]['read']=$languages_others[$word_reading_key]['name'];
            }else{
                $languages[$key]['read']= 'Read';
            }

            $word_home_key=array_search('Home', array_column($languages_others, 'original_name'));
            if(isset($languages_others[$word_home_key]['name'])){
                $languages[$key]['start']=$languages_others[$word_home_key]['name'];
            }else{
                $languages[$key]['start']= 'Start';
            }
        }

        if (count($languages)>0) {

            return [
                'status' => 'success',
                'data' => ($languages),
            ];

        }else{
            return [
                'status' => 'error',
                'data' => 'Not Data Found',
            ];
        }
    }

    protected function surahs($language=null)
    {
        $surahs = Surah::where('status',1)->get()->toArray();

        if (count($surahs)>0) {

            return [
                'status' => 'success',
                'data' => ($surahs),
            ];

        }else{
            return [
                'status' => 'error',
                'data' => 'Not Data Found',
            ];
        }

    }

    protected function sections($language='en')
    {
        $sections = Section::with(['translation' => function ($query ) use ($language) {
            $query->where('language_code',$language);
            $query->where('source_type','section');
        }])->where('status',1)->get()->toArray();

        foreach($sections as $key => $section){

            if( ($section['translation'])){

                $sections[$key]['name']=$section['translation'][0]['name'];
                $sections[$key]['language_code']= $section['translation'][0]['language_code'];

            }else{
                $sections[$key]['language_code']= 'en';
            }

            $sections[$key]['english_name']= $section['name'];

            unset($sections[$key]['translation']);

        }

        if ($sections ) {

            return [
                'status' => 'success',
                'data' => ($sections),
            ];

        }else{

            return [
                'status' => 'error',
                'data' => 'Not Data Found',
            ];
        }

    }

    protected function allQaris($language='en')
    {
        $qaris = Qari::with(['translation' => function ($query ) use ($language) {
            $query->where('language_code',$language);
            $query->where('source_type','qari');
        }])->where('status',1)->orderBy('name','ASC')->get()->toArray();

        foreach($qaris as $key => $qari){

            if( ($qari['translation'])){

                $qaris[$key]['name']=$qari['translation'][0]['name'];
                $qaris[$key]['description']=$qari['translation'][0]['description'];
                $qaris[$key]['language_code']= $qari['translation'][0]['language_code'];

            }else{
                $qaris[$key]['language_code']= 'en';
            }

            $qaris[$key]['english_name']= $qari['name'];

            unset($qaris[$key]['translation']);

        }

        if ($qaris ) {

            return  [
                'status' => 'success',
                'data' => ($qaris),
            ];

        }else{

            return [
                'status' => 'error',
                'data' => 'Not Data Found',
            ];
        }

    }

    protected function qaris($id,$language='en')
    {

        $qaris = Qari::with(['translation' => function ($query ) use ($language) {
            $query->where('language_code',$language);
            $query->where('source_type','qari');
        }])
        ->where('status',1)
        ->where('section_id',$id)
		->orderBy('name','ASC')
        ->get()->toArray();

        foreach($qaris as $key => $qari){

            if( ($qari['translation'])){

                $qaris[$key]['name']=$qari['translation'][0]['name'];
                $qaris[$key]['description']=$qari['translation'][0]['description'];
                $qaris[$key]['language_code']= $qari['translation'][0]['language_code'];

            }else{

                $qaris[$key]['language_code']= 'en';
            }

            $qaris[$key]['english_name']= $qari['name'];

            unset($qaris[$key]['translation']);

        }


        if ($qaris ) {

            return  [
                'status' => 'success',
                'data' => ($qaris),
            ];

        }else{

            return   [
                'status' => 'error',
                'data' => 'Not Data Found',
            ];
        }

    }

    protected function apiList(){

        echo    'api/surahs/{language_code?}'.'<br>';
        echo    'api/sections/{language_code?}'.'<br>';
        echo    'api/allqaris/{language_code?}'.'<br>';
        echo    'api/qaris/{section_id}/{language_code?}'.'<br>';
        echo    'api/surahs_list/{qari_id}'.'<br>';;
        echo    'api/languages'.'<br>';

    }

    protected function surahsList($Qari,$language='en')
    {
        $recitations = Recitation::with(['translation' => function ($query ) use ($language) {
            $query->where('language_code',$language);
            $query->where('source_type','surah');
        }])->where('qari_id',$Qari)->whereNotNull('surah_id')->orderBy('surah_id')->get()->toArray();

        $qari = Qari::with(['translation' => function ($query ) use ($language) {
            $query->where('language_code',$language);
            $query->where('source_type','qari');
        }])

        ->where('id',$Qari)
        ->get()->toArray()[0];

        foreach($recitations as $key => $recitation){

            if( ($recitation['translation'])){

                $recitations[$key]['name']=$recitation['translation'][0]['name'];
                $recitations[$key]['language_code']= $recitation['translation'][0]['language_code'];

            }else{

                $recitations[$key]['language_code']= 'en';
            }

            $recitations[$key]['english_name']= $recitation['name'];

            unset($recitations[$key]['translation']);

        }

        if( ($qari['translation'])){

            $qari['name']=$qari['translation'][0]['name'];
            $qari['language_code']= $qari['translation'][0]['language_code'];

        }else{

            $qari['language_code']= 'en';
        }

        $qari['english_name']= $qari['name'];

        unset($qari['translation']);


        if (count($recitations)>0) {

            return [
                'status' => 'success',
                'data' =>($recitations),
                'qari' =>$qari,
                'path'=>  'https://islamcheck-audio-new.s3.eu-central-1.amazonaws.com/'.$qari['relative_path'],
            ];

        }else{
            return [
                'status' => 'error',
                'data' => 'Not Data Found',
            ];
        }

    }

}