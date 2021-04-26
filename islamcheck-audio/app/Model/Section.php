<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

use App\Models\Qari;
use App\Models\Translation;
use Eloquent;

class Section extends Eloquent
{
    protected $table = 'sections';

    protected $fillable = [
        'id',
        'name',
        'status',
        'created_at',
        'updated_at'];



    public function translation()
    {
        return $this->hasMany(Translation::class,'source_id','id');
    }

    public function qari()
    {
        return $this->hasMany(Qari::class);
    }

    public function getSection(){


        //echo 'sdada';die;


        //return $section->all();
    }


}
