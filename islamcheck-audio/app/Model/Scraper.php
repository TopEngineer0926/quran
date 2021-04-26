<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Log;

class Scraper extends Model
{

    const surahs_url = "https://quranicaudio.com/api/surahs";
    const section_url = "https://quranicaudio.com/api/sections";
    const qaris_url = "https://quranicaudio.com/api/qaris";
    const audiofiles_url='https://quranicaudio.com/api/audio_files';

    public function curl($URL){

        $ch = curl_init();
        $timeout = 3600;
        curl_setopt($ch, CURLOPT_URL, $URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/39.0.2171.95 Safari/537.36');
        $data = curl_exec($ch);

        if(curl_errno($ch))
        {
            return false;
        }
        else
        {
            $result = $data;
        }

        curl_close($ch);

        return $result;

    }
}
