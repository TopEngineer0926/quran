<?php
/**
 * Created by PhpStorm.
 * User: Noreen Gul
 * Date: 1/3/2020
 * Time: 4:31 PM
 */

namespace App\Http\Controllers\import;

use App\Models\Surah;
use App\Models\Scraper;
use App\Models\Section;
use App\Models\Language;
use App\Models\Qari;
use App\Models\Recitation;

use Illuminate\Support\Facades\Storage;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImportController
{
    public $surahs_list=array();

    public function insertLanguage(){

        try {

            $file = fopen("D:\languages.csv","r");

            $i=1;
            while(! feof($file))
            {
                if( $i!=1){
                    $data=fgetcsv($file);

                    $result = Language::firstOrCreate(
                        [
                            'name' => $data[1],
                            'code' => $data[2] ,
                            'status' => true,
                            'native_name' => $data[3] ,

                        ]
                    );
                }

                $i++;
            }

            fclose($file);


        } catch (QueryException $e) {

            $error_code = $e->errorInfo[1];
            if ($error_code == 1062) {
                return 'error, you have a duplicate entry problem';
            }
        }




    }

    public function insertSurahs()
    {

        $curl = new Scraper;
        $url = Scraper::surahs_url;
        $data = $curl->curl($url);

        //$data=self::curlFun($url);

        foreach (json_decode($data) as $key => $val) {


            $names = $val->name;
            $page = $val->page;
            $revelation = $val->revelation;

            try {
                $result = Surah::updateOrCreate(
                    [
                        'id' => $val->id,
                        'bismillah_pre' => 1,
                        'simple_name' => $names->{'simple'},
                        'complex_name' => $names->{'complex'},
                        'english_name' => $names->{'english'},
                        'arabic_name' => $names->{'arabic'},
                        'revelation_place' => $revelation->{'place'},
                        'revelation_order' => $revelation->{'order'},
                        'count_verses' => $val->ayat,
                        'pages' => $page[1] - $page[0] == 0 ? 1 : $page[1] - $page[0],
                        'start_page' => $page[0],
                        'end_page' => $page[1],
                        'status' => true,


                    ],
                    ['id' => $val->id]
                );

            } catch (QueryException $e) {

                $error_code = $e->errorInfo[1];
                if ($error_code == 1062) {
                    return 'error, you have a duplicate entry problem';
                }
            }
        }

        echo json_encode($data);

    }

    public function insertSections()
    {

        $curl = new Scraper;
        $url = Scraper::section_url;

        $data = $curl->curl($url);

        foreach (json_decode($data) as $key => $val) {

            try {
                $result = Section::updateOrCreate(
                    [
                        'id' => $val->id,
                        'name' => $val->name,
                        'status' => true,


                    ],
                    ['id' => $val->id]
                );

            } catch (QueryException $e) {

                echo $e;
                die;
                $error_code = $e->errorInfo[1];
                if ($error_code == 1062) {
                    return 'error, you have a duplicate entry problem';
                }
            }


        }
        echo json_encode($data);
    }

    public function insertQaris()
    {

        $PathToFile = public_path() . '/audio_files/';

        if (!file_exists($PathToFile)) {

            mkdir($PathToFile, 0755, true);
        }

        $curl = new Scraper;
        $url = Scraper::qaris_url;

        $data = $curl->curl($url);

        foreach (json_decode($data) as $key => $val) {

            try {
                $result = Qari::updateOrCreate(
                    [
                        'id' => $val->id,
                        'name' => $val->name,
                        'arabic_name' => $val->arabic_name,
                        'relative_path' => $val->relative_path,
                        'file_formats' => $val->file_formats,
                        'description' => $val->description,
                        'section_id' => $val->section_id,
                        'home' => 1,
                        'status' => true,

                    ],
                    ['id' => $val->id]
                );

            } catch (QueryException $e) {

                echo $e;
                die;
                $error_code = $e->errorInfo[1];
                if ($error_code == 1062) {
                    return 'error, you have a duplicate entry problem';
                }
            }

        }

        echo json_encode($data);

    }

    public function insertRecitation($start,$end)
    {

        $PathToFile = public_path() . '/audio_files/';

        if (!file_exists($PathToFile)) {

            mkdir($PathToFile, 0755, true);
        }

        $curl = new Scraper;
        $url = Scraper::qaris_url;

        $data = $curl->curl($url);

        foreach (json_decode($data) as $key => $val) {

            echo $val->id;

            if (  $val->id< $end && $val->id >$start ) {

                try {

                    $PathToFile = public_path("audio_files/" . $val->relative_path);

                    if (!file_exists($PathToFile))
                        mkdir($PathToFile, 0755, true);

                    $urlAudioFiles = $url . '/' . $val->id . '/audio_files/mp3';

                    $audioFiles = $curl->curl($urlAudioFiles);

                    if (isset($audioFiles)) {

                        foreach (json_decode($audioFiles) as $file) {

                            if ($val->relative_path == 'mohamed_al-tablawi/') {
                                $val->relative_path = 'mohammad_altablawi';
                            }

                            $fileSource = 'https://download.quranicaudio.com/quran/' . $val->relative_path . $file->file_name;
                            $fileName = $file->file_name;
                            //$headers = ['Content-Type: application/attachment'];

                            $PathToFileSurah = $PathToFile . '/' . $fileName;

                            if (!file_exists($PathToFileSurah)) {

                                // $getContent = $curl->curl($fileSource);
                                $getContent = file_get_contents($fileSource); // Here cURL can be use.

                                if ($getContent) {

                                    file_put_contents($PathToFileSurah, $getContent);

                                    $format = $file->format;
                                    $metadata = $file->metadata;

                                    $result = Recitation::updateOrCreate(
                                        [
                                            'id' => $file->main_id,
                                            'name' => $metadata->title,
                                            'file_name' => $file->file_name,
                                            'extension' => $file->extension,
                                            'size' => $format->size,
                                            'stream_count' => $file->stream_count,
                                            'duration' => $format->duration,
                                            'format_long_name' => $format->format_long_name,
                                            'probe_score' => $format->probe_score,
                                            'start_time' => $format->start_time,
                                            'qari_id' => $file->qari_id,
                                            'surah_id' => $file->surah_id,
                                            'bit_rate' => $format->bit_rate,
                                            'download_count' => 0,

                                        ],
                                        ['id' => $file->main_id]
                                    );
                                } else {

                                    $fileSource = 'https://download.quranicaudio.com/quran/' . $val->relative_path . '/mistakes/' . $file->file_name;

                                    $getContent = $curl->curl($fileSource);

                                    if ($getContent) {

                                        file_put_contents($PathToFileSurah, $getContent);

                                        $format = $file->format;
                                        $metadata = $file->metadata;

                                        $result = Recitation::updateOrCreate(
                                            [
                                                'id' => $file->main_id,
                                                'name' => $metadata->title,
                                                'file_name' => $file->file_name,
                                                'extension' => $file->extension,
                                                'size' => $format->size,
                                                'stream_count' => $file->stream_count,
                                                'duration' => $format->duration,
                                                'format_long_name' => $format->format_long_name,
                                                'probe_score' => $format->probe_score,
                                                'start_time' => $format->start_time,
                                                'qari_id' => $file->qari_id,
                                                'surah_id' => $file->surah_id,
                                                'bit_rate' => $format->bit_rate,
                                                'download_count' => 0,

                                            ],
                                            ['id' => $file->main_id]
                                        );
                                    }
                                }


                            }
                        }
                    }


                } catch (QueryException $e) {

                    echo $e;
                    die;
                    $error_code = $e->errorInfo[1];
                    if ($error_code == 1062) {
                        return 'error, you have a duplicate entry problem';
                    }
                }
            }
        }

    }

    public function insertRecitationQari($qariPath,$id)
    {

        $PathToFile = public_path() . '/audio_files/';

        try {

            $PathToFile = public_path("audio_files/" . $qariPath);

            if (!file_exists($PathToFile)) {

                mkdir($PathToFile, 0755, true);
            }

            $curl = new Scraper;
            $url = Scraper::qaris_url;
            $urlAudioFiles = $url . '/' .$id . '/audio_files/mp3';

            $audioFiles = $curl->curl($urlAudioFiles);

            if (isset($audioFiles)) {

                foreach (json_decode($audioFiles) as $file) {

                    $fileSource = 'https://download.quranicaudio.com/quran/' . $qariPath .'/'. $file->file_name;
                    $fileName = $file->file_name;

                    $headers = ['Content-Type: application/attachment'];

                    $PathToFileSurah = $PathToFile . '/' . $fileName;

                    if (file_exists($PathToFileSurah) ) {

                        $format = $file->format;
                        $metadata = $file->metadata;

                        $result = Recitation::updateOrCreate(
                            [
                                'id' => $file->main_id,
                                'name' => $metadata->title,
                                'file_name' => $file->file_name,
                                'extension' => $file->extension,
                                'size' => $format->size,
                                'stream_count' => $file->stream_count,
                                'duration' => $format->duration,
                                'format_long_name' => $format->format_long_name,
                                'probe_score' => $format->probe_score,
                                'start_time' => $format->start_time,
                                'qari_id' => $file->qari_id,
                                'surah_id' => $file->surah_id,
                                'bit_rate' => $format->bit_rate,
                                'download_count' => 0,

                            ],
                            ['id' => $file->main_id]
                        );
                    }


                }
            }

        } catch (QueryException $e) {

            echo $e;
            die;
            $error_code = $e->errorInfo[1];
            if ($error_code == 1062) {
                return 'error, you have a duplicate entry problem';
            }
        }

    }

    public function insertDataOnly(){

        $PathToFile =  'https://islamcheck-audio-new.s3.eu-central-1.amazonaws.com/';

        $curl = new Scraper;
        $url = Scraper::qaris_url;

        $data = $curl->curl($url);

        //print_r($data);die;

        foreach (json_decode($data) as $key => $val) {

            try {

                //if( $val->id<21 ):
                //if($val->id>20 && $val->id<41 ):
                //if($val->id>40 && $val->id<61 ):
                //if($val->id>60 && $val->id<81 ):
                //if($val->id>80 && $val->id<101 ):
                if($val->id>100  ):

                    $PathToFile =$PathToFile."/" . $val->relative_path;

                    $urlAudioFiles = $url . '/' . $val->id . '/audio_files/mp3';

                    $audioFiles = $curl->curl($urlAudioFiles);

                    if (isset($audioFiles)) {

                        foreach (json_decode($audioFiles) as $file) {

                            if ($val->relative_path == 'mohamed_al-tablawi/') {
                                $val->relative_path = 'mohammad_altablawi';
                            }

                            $format = $file->format;
                            $metadata = $file->metadata;
                            $fileName = $file->file_name;

                            $data_arr=[
                                'id' => $file->main_id,

                                'file_name' => $file->file_name,
                                'extension' => $file->extension,

                                'stream_count' => $file->stream_count,

                                'download_count' => 0,

                            ];

                            if(isset( $metadata->title)){
                                $data_arr['name'] = $metadata->title;
                            }

                            if(isset( $format->size)){
                                $data_arr['size'] = $format->size;
                            }

                            if(isset( $format->duration)){
                                $data_arr['duration'] = $format->duration;
                            }

                            if(isset( $format->format_long_name)){
                                $data_arr['format_long_name'] = $format->format_long_name;
                            }

                            if(isset( $format->probe_score)){
                                $data_arr['probe_score'] = $format->probe_score;
                            }

                            if(isset( $format->start_time)){
                                $data_arr['start_time'] = $format->start_time;
                            }

                            if(isset( $format->bit_rate)){
                                $data_arr['bit_rate'] = $format->bit_rate;
                            }

                            if(isset( $file->qari_id)){
                                $data_arr['qari_id'] = $file->qari_id;
                            }

                            if(isset( $file->surah_id)){
                                $data_arr['surah_id'] = $file->surah_id;
                            }

                            //$PathToFileSurah = $PathToFile . '/' . $fileName;

                            //if (file_exists($PathToFileSurah)) {


                            $result = Recitation::firstOrNew  (
                                $data_arr
                            //['id' => $file->main_id]
                            );

                            $result->save();
                            // }
                        }
                    }
                endif;

            } catch (QueryException $e) {

                $error_code = $e->errorInfo[1];
                if ($error_code == 1062) {
                    return 'error, you have a duplicate entry problem';
                }
            }

        }

    }

    public function addNewFolder(){

        /* $option_tbl = DB::table('options')
            ->where('meta_key', 'files_cron_count')
            ->update([
                'meta_value' => 0,
            ]);
 */
        //$this->getSurahName();

        $folders_db = DB::table('directories')->get()->toArray();

        $folders= Storage::disk('s3')->allDirectories('');

        $bulk_recitations=array();
        $bulk_files=array();
        $count=0;
        $new_folder=false;

        foreach($folders as $folder){
			 
            $neededObject = array_filter(
                $folders_db,
                function ($e) use ($folder) { 
                    return $e->name == $folder;
                }
            );
			

            if(count($neededObject)==0){
  
                $new_folder=true;

                $qari_id=$this->getQariID($folder);
 
                //$files = Storage::disk('s3')->allFiles($folder);

                /* $bulk_recitations=array();
                $bulk_files=array();
                $count=0; */

                /* foreach ($files as $file) {

                    $filePath = pathinfo($file);

                    if ($filePath['extension'] == 'mp3') {

                        $surah_id_without_00=(int)ltrim($filePath['filename'], '0');

                        if(isset($this->surahs_list[$surah_id_without_00])){
                            $surah_id=$surah_id_without_00;
                            $surah_name=$this->surahs_list[$surah_id];
                        }else{
                            $surah_id=0;
                            $surah_name='no name';
                        }

                        /* $recitation = array(
                            'surah_id' =>$surah_id,
                            'qari_id' => $qari_id,
                            'name'=>$surah_name,
                            'file_name' => $filePath['basename'],
                            'extension' => $filePath['extension'],
                            'format_long_name' => $filePath['extension'],
                            'size' => Storage::disk('s3')->size($file),
                            'stream_count' => Storage::disk('s3')->size($file),
                            'duration' => (Storage::disk('s3')->size($file) / 1000) / 60,
                            'bit_rate' => Storage::disk('s3')->size($file),
                            'probe_score' => 0,
                            'start_time' => '0',
                        );
                        $count++;

                        $bulk_recitations[]=$recitation;

                        $bulk_files[]=array(
                            'name'=> $file,
                            'folder'=> $folder,
                            'last_modified'=>time(),
                            'qari_id'=>$qari_id
                        );
 */
                        /* if($count==10){
                            echo 'cron ended';
							
							
                            //die;
                        } */
                    /*}
                } */

                //if(count($bulk_recitations)>0){
					
					$insert=DB::table('directories')->insert(
                    ['name' => $folder ]
					);

                    echo '<br> folder =>'.$folder.' Completly uploaded <br>';

                    /* $insert_recitations=DB::table('recitations')->insert(
                        $bulk_recitations
                    );

                    $insert_files=DB::table('files')->insert(
                        $bulk_files
                    ); */

                //}

            }

        }

        if($new_folder==false){

            echo 'No New Folder Found!';
        }

    }

    public function updateFiles(){

        Log::info('Cron Stated!'.time());

        $this->getSurahName();

        $option_tbl = DB::table('options')->get()->toArray();

        $files_db = DB::table('files')->get()->toArray();

        $qari_db=Qari::get()->ToArray();

        $files= Storage::disk('s3')->AllFiles('');
		 
        $count=0;
		$count_key=1;

        $bulk_recitations=array();
        $bulk_files=array();
        $new_files='';
		 
        //arsort($files);

        $files = array_slice($files, $option_tbl[0]->{'meta_value'}, 2000);
		 
		if(count($files)==0){
            $option_tbl = DB::table('options')
                ->where('meta_key', 'files_cron_count')
                ->update([
                    'meta_value' =>0,
                ]);
            die;
        }

		  
        foreach($files as $key => $file){

            $neededObject = array_filter(
                $files_db,
                function ($e) use ($file){
                    return $e->name == $file;
                }
            );

            $filePath=pathinfo($file);

            $QariObject=array();

            $QariObject = array_filter(
                $qari_db,
                function ($et) use ($filePath) {

                    return $et['relative_path'] == $filePath['dirname'].'/';
                }
            );

            if(count($neededObject)==0 && count($QariObject) > 0  ){

                $qari_id=(int) array_key_first($QariObject);

                if ($filePath['extension'] == 'mp3') {

                    $surah_id_without_00=(int)ltrim($filePath['filename'], '0');

                    if(isset($this->surahs_list[$surah_id_without_00])){
                        $surah_id=$surah_id_without_00;
                        $surah_name=$this->surahs_list[$surah_id];
                    }else{
                        $surah_id=0;
                        $surah_name='no name';
                    }

                    $recitation = array(
                        'surah_id' =>$surah_id,
                        'qari_id' => $QariObject[$qari_id]['id'],
                        'name'=>$surah_name,
                        'file_name' => $filePath['basename'],
                        'extension' => $filePath['extension'],
                        'format_long_name' => $filePath['extension'],
                        'size' => Storage::disk('s3')->size($file),
                        'stream_count' => Storage::disk('s3')->size($file),
                        'duration' => (Storage::disk('s3')->size($file) / 1000) / 60,
                        'bit_rate' => Storage::disk('s3')->size($file),
                        'probe_score' => 0,
                        'start_time' => '0',
                    );

                    $bulk_recitations[]=$recitation;

                    $bulk_files[]=array(
                        'name'=>$file,
                        'folder'=>$filePath['dirname'],
                        'last_modified'=>time(),
                        'qari_id'=>$QariObject[$qari_id]['id']
                    );

                    $count++;

                    $new_files.=$file.'<br>';

                }

            }

            if($count==5){
                echo 'cron ended';
                break;
            }
			
			$count_key++;
        }

        if(count($bulk_recitations)>0){

            $insert_recitations=DB::table('recitations')->insert(
                $bulk_recitations
            );

            $insert_files=DB::table('files')->insert(
                $bulk_files
            );

            echo '<br> New Files <br>'.$new_files ;

        }else{
            echo 'No New File Found!';
        }
		 
        $option_tbl = DB::table('options')
            ->where('meta_key', 'files_cron_count')
            ->update([
                'meta_value' => $count_key+$option_tbl[0]->{'meta_value'},
            ]);


        Log::info('Cron ended!'.time().'new files ---'.$new_files);
    }
	
	function updateFilesQari(){
		
		$this->getSurahName();
		
		$qari_id = 841;

        $files= Storage::disk('s3')->allFiles('dr_shawqy_Hamed/murattal');
		
		$bulk_recitations=array();
        $bulk_files=array();
        $new_files='';$count=1;
		
		
		foreach($files as $key => $file){
			
			$filePath=pathinfo($file);
			
			
			if ($filePath['extension'] == 'mp3') {

                    $surah_id_without_00=(int)ltrim($filePath['filename'], '0');

                    if(isset($this->surahs_list[$surah_id_without_00])){
                        $surah_id=$surah_id_without_00;
                        $surah_name=$this->surahs_list[$surah_id];
                    }else{
                        $surah_id=0;
                        $surah_name='no name';
                    }

                    $recitation = array(
                        'surah_id' =>$surah_id,
                        'qari_id' => $qari_id,
                        'name'=>$surah_name,
                        'file_name' => $filePath['basename'],
                        'extension' => $filePath['extension'],
                        'format_long_name' => $filePath['extension'],
                        'size' => Storage::disk('s3')->size($file),
                        'stream_count' => Storage::disk('s3')->size($file),
                        'duration' => (Storage::disk('s3')->size($file) / 1000) / 60,
                        'bit_rate' => Storage::disk('s3')->size($file),
                        'probe_score' => 0,
                        'start_time' => '0',
                    );

                    $bulk_recitations[]=$recitation;

                    $bulk_files[]=array(
                        'name'=>$file,
                        'folder'=>$filePath['dirname'],
                        'last_modified'=>time(),
                        'qari_id'=>$qari_id
                    );

                    $count++;

                    $new_files.=$file.'<br>';

                }
			
		}
		
		if(count($bulk_recitations)>0){

            $insert_recitations=DB::table('recitations')->insert(
                $bulk_recitations
            );

            $insert_files=DB::table('files')->insert(
                $bulk_files
            );

            echo '<br> New Files <br>'.$new_files ;

        }else{
            echo 'No New File Found!';
        }
 
        //foreach($qaris_db as $key =>  $qari){
			 
			  //$path=str_replace('/','',$qari->relative_path); 
			
            //$qari=DB::table('recitations')->where('qari_id',$qari->id)->get()->toArray();
			//$directories=DB::table('directories')->where('name',$path)->get()->toArray();
 
			//if(count($qari)==0 && count($directories)!=0){
				 
				  
				//$deletedRows = DB::table('files')->where('folder',  $path)->delete();
				
			//}
  
			 //if($key==50){
				//echo 'data deleted <br> '.$folder;
				 
			  //}
		//}
		
	}

    function getSurahName( ){

        $surahs=Surah::get()->ToArray();

        foreach($surahs as $key => $surah) {

            $this->surahs_list[$key+1]='Surat '.$surah['simple_name'];
        }

    }

    function getQariID($folder){

        $qari_name=ucwords(str_replace('_',' ',str_replace('-',' ',$folder))) ;
        $qari_db = new Qari();

        $qari_db->section_id = 1;
        $qari_db->name = $qari_name;
        $qari_db->arabic_name = $qari_name;
        $qari_db->status = 0;
        $qari_db->home = 1;
        $qari_db->file_formats = 'mp3';
        $qari_db->relative_path = file_format($folder.'/');
        $qari_db->description = ' ';

        $qari_db->save();

        $qari_id = $qari_db->id;

        return $qari_id;
    }

}