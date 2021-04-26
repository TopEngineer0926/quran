<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Enum extends Model
{
    //
    const success = "Success";
    const success_add = "Data has successfully been added";
    const success_delete = "Data has successfully been deleted";
    const success_update = "Data has successfully been updated";
    const success_restore = "Data has successfully been restored";
    const fail = "Error";
    const fail_password_mismatch = "Old Password does not match with our records";


    const url_audio = 'https://s3.us-east-2.amazonaws.com/islamcheck.com/verses/';
    const resource_type = [ // resource types for translated_names
        'languages' => 'languages',
        'chapters' => 'chapters',
    ];
    const resource_table_type = [ // resource type for resource table
        'options' => 'options',
    ];
    const resource_table_subtype = [ // resource subtype for resource table
        'translations' => 'translations',
    ];
    const cardinality_type = [ // list of cardinality types
        'unique' => 'unique',
    ];
    const resource_type_media_contents = [ // rource types for media_contents table
        'verse' => 'verse',
    ];
}
