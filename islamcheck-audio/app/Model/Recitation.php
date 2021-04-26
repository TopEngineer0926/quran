<?php

namespace App\Models;

use App\Models\Qari;
use App\Models\Translation;


use Illuminate\Database\Eloquent\Model;

class Recitation extends Model
{

    protected $table = 'recitations';

    protected $fillable = [
        'id',
        'name',
        'file_name',
        'extension',
        'size',
        'stream_count',
        'duration',
        'format_long_name',
        'probe_score',
        'start_time',
        'qari_id',
        'surah_id',
        'bit_rate',
        'download_count',
        'created_at',
        'updated_at'];

    public function qari()
    {
        return $this->belongsTo(Qari::class);
    }

    public function surah()
    {
        return $this->hasMany('App\Model\Surah');
    }

    /*public function translation()
    {
        return $this->hasMany(Translation::class,'source_id','id');
    }*/

    public function translation()
    {
        return $this->hasMany(Translation::class,'source_id','surah_id');
    }
}
