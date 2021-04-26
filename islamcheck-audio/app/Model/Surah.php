<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Surah extends Model
{
    //use SoftDeletes;

    protected $table = 'surahs';

    protected $fillable = [
        'id',
        'bismillah_pre',
        'simple_name',
        'complex_name',
        'english_name',
        'arabic_name',
        'revelation_place',
        'revelation_order',
        'count_verses',
        'pages',
        'start_page',
        'end_page',
        'status',
        'created_at',
        'updated_at'];

    public function translation()
    {
        return $this->hasOne(Translation::class,'source_id','id');
    }

}
