<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Section;
use App\Models\Translation;
use App\Models\Recitation;
use Eloquent;

class Qari extends Model
{
    protected $table = 'qaris';

    protected $fillable = [
        'id',
        'name',
        'arabic_name',
        'relative_path',
        'file_formats',
        'section_id',
        'home',
        'status',
        'created_at',
        'updated_at'];

    public function section()
    {
        return $this->belongsTo(Section::class, 'section_id', 'id');
    }

    public function translation()
    {
        return $this->hasMany(Translation::class,'source_id','id');
    }

    public function recitation()
    {
        return $this->hasMany(Recitation::class,'qari_id','id');
    }

}
