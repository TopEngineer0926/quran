<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Translation;

class Language extends Model
{
    protected $table = 'languages';

    protected $fillable = [
        'name',
        'code',
        'created_at',
        'updated_at'];

    public function translation()
    {
        return $this->hasOne(Translation::class,'language_code','code');
    }

    public function trans_langugae()
    {
        return $this->hasOne(Translation::class,'source_id','id');
    }
}
