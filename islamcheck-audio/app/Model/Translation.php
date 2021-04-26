<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Section;
use App\Models\Surah;
use App\Models\Recitation;
use App\Models\Qari;
use App\Models\Language;
use Eloquent;

class Translation extends Eloquent
{
    protected $table = 'translations';

    protected $fillable = [

        'id',
        'source_id',
        'name',
        'description',
        'language_code',
        'source_type',
         ];


    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function qari()
    {
        return $this->belongsTo(Qari::class);
    }

    public function recitation()
    {
        return $this->belongsTo(Recitation::class);
    }

    public function surah()
    {
        return $this->belongsTo(Surah::class);
    }

    public function language()
    {
        return $this->belongsTo(Language::class);
    }

    public function translated_language()
    {
        return $this->belongsTo(Language::class);
    }


}
