<?php

use carbon\carbon;
use App\Models\Enum as enum;

function success()
{
    return Enum::success;
}
function fail()
{
    return Enum::fail;
}

function file_format($fileName){

    $fileName = preg_replace('/\s+/', '_', $fileName);
    return $fileName;
}

function getPath($param, $folder = '')
{
    $returnPath = NULL;
    switch ($param) {
        case 'public-image':
            $returnPath = config('settings.public_image_path', 'default') . $folder;
            break;
        case 'public-video':
            if ($folder == '') {
                $returnPath = config('settings.public_video_path', 'default') . $folder;
            } else {
                $returnPath = config('settings.public_video_path', 'default') . '/' . $folder;
            }
            break;
        default:
            return '';
    }
    return $returnPath;
}

function saveImage($request, $path, $resize = [])
{
    $image = $request->file('image');
    $image_name = time() . rand(1000, 9999) . '.' . $image->getClientOriginalExtension();
    $destinationPath = $path;
    $resize_image = Image::make($image->getRealPath());
    if (!empty($resize)) {
        $resize_image->resize($resize[0], $resize[1], function ($constraint) {
            $constraint->aspectRatio();
        })->save($destinationPath . '/' . $image_name);
    } else {
        $resize_image->save($destinationPath . '/' . $image_name);
    }

    $image_path = $destinationPath . '/' . $image_name;

    return $image_path;
}
function saveVideo($path)
{
    if (Request::hasFile('video')) {

        $file = Request::file('video');
        $filename = $file->getClientOriginalName();
        $filename = time() . '.' . $filename . '.mp4';
        $file->move($path, $filename);
        return $path . '/' . $filename;
    }
}

function saveBase64Image($request, $path)
{
    if ($request->image != NULL) {
        $image = $request->image;  // your base64 encoded
        $image = str_replace('data:image/jpg;base64,', '', $image);
        $image = str_replace(' ', '+', $image);
        $imageName = time() . 'feedback' . '.jpg';
        \File::put($path . '/' . $imageName, base64_decode($image));
        return $path . '/' . $imageName;
    } else {
        return noImage();
    }
}
function getImagePath($image)
{
    if ($image == NULL) {
        return url('/') . "/public/images/noimage.png";
    }
    return url('/') . '/' . $image;
}
function getVideoPath($video)
{
    return url('/') . '/' . $video;
}
function code($code, $message)
{
    return response()->json([
        'code' => $code,
        'message' => $message,
    ], 200);
}

function assets($path)
{
    $version = \Config::get('constants.options.assets_version');
    return app('url')->asset("/assets/" . $path) . "?v=$version";
}

function avatar($avatar)
{
    return assets('avatars/' . $avatar);
}

function no_avatar() // image for default or no avatar
{
    return 'assets/avatars/no-avatar.png';
}
function mapArrayWithKeys($data, $key, $val)
{
    $return = $data->mapWithKeys(function ($item) use ($key, $val) {
        return [$item[$key] => $item[$val]];
    });
    return $return;
}

function noImage()
{
    return ('public/images/noimage.png');
}

function logo()
{
    return getImagePath('assets/images/logo.png');
}
function check_index($data, $index)
{
    if (isset($data[$index])) {
        return $data[$index];
    } else {
        return null;
    }
}
function in_array_r($needle, $haystack, $strict = false) {
    foreach ($haystack as $item) {
        if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && in_array_r($needle, $item, $strict))) {
            return true;
        }
    }

    return false;
}
function get_class_by_action($action)
{
    $class = [];
    switch ($action) {
        case 'delete': {
            $class[] = 'btn btn-bricky tooltips';
            $class[] = 'fa fa-times fa fa-white';
            return $class;
        }
            break;
        case 'edit': {
            $class[] = 'btn btn-blue  tooltips';
            $class[] = 'fa fa-edit';
            return $class;
        }
            break;
        case 'view': {
            $class[] = 'btn btn-blue tooltips';
            $class[] = 'fa fa-arrow-circle-right';
            return $class;
        }
            break;
        default: {
            $class[] = 'btn';
            $class[] = 'fa';
            return $class;
        }
    }
}

function get_action_html($url, $action)
{
    switch ($action) {
        case 'delete': {
            $html = '<form  method="post">
            <?php csrf() ?>
        </form>';
            //$html = '<a class="btn btn-bricky tooltips" data-toggle="modal" onclick="confirm_delete("about/delete?id=4")" href="#static" data-placement="top" data-original-title="Delete"><i class="fa fa-times fa fa-white"></i></a>';
            return $html;
            return sprintf($html, $url);
        }
            break;
    }
}

function xml2array($contents, $get_attributes = 1, $priority = 'attribute')
{
    if (!$contents) return array();

    if (!function_exists('xml_parser_create')) {
        //print "'xml_parser_create()' function not found!";
        return array();
    }

    //Get the XML parser of PHP - PHP must have this module for the parser to work
    $parser = xml_parser_create('');
    xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8"); # http://minutillo.com/steve/weblog/2004/6/17/php-xml-and-character-encodings-a-tale-of-sadness-rage-and-data-loss
    xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
    xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
    xml_parse_into_struct($parser, trim($contents), $xml_values);
    xml_parser_free($parser);

    if (!$xml_values) return; //Hmm...

    //Initializations
    $xml_array = array();
    $parents = array();
    $opened_tags = array();
    $arr = array();

    $current = &$xml_array; //Refference

    //Go through the tags.
    $repeated_tag_index = array(); //Multiple tags with same name will be turned into an array
    foreach ($xml_values as $data) {
        unset($attributes, $value); //Remove existing values, or there will be trouble

        //This command will extract these variables into the foreach scope
        // tag(string), type(string), level(int), attributes(array).
        extract($data); //We could use the array by itself, but this cooler.

        $result = array();
        $attributes_data = array();

        if (isset($value)) {
            if ($priority == 'tag') $result = $value;
            else $result['value'] = $value; //Put the value in a assoc array if we are in the 'Attribute' mode
        }

        //Set the attributes too.
        if (isset($attributes) and $get_attributes) {
            foreach ($attributes as $attr => $val) {
                if ($priority == 'tag') $attributes_data[$attr] = $val;
                else $result['attr'][$attr] = $val; //Set all the attributes in a array called 'attr'
            }
        }

        //See tag status and do the needed.
        if ($type == "open") { //The starting of the tag '<tag>'
            $parent[$level - 1] = &$current;
            if (!is_array($current) or (!in_array($tag, array_keys($current)))) { //Insert New tag
                $current[$tag] = $result;
                if ($attributes_data) $current[$tag . '_attr'] = $attributes_data;
                $repeated_tag_index[$tag . '_' . $level] = 1;

                $current = &$current[$tag];
            } else { //There was another element with the same tag name

                if (isset($current[$tag][0])) { //If there is a 0th element it is already an array
                    $current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
                    $repeated_tag_index[$tag . '_' . $level]++;
                } else { //This section will make the value an array if multiple tags with the same name appear together
                    $current[$tag] = array($current[$tag], $result); //This will combine the existing item and the new item together to make an array
                    $repeated_tag_index[$tag . '_' . $level] = 2;

                    if (isset($current[$tag . '_attr'])) { //The attribute of the last(0th) tag must be moved as well
                        $current[$tag]['0_attr'] = $current[$tag . '_attr'];
                        unset($current[$tag . '_attr']);
                    }
                }
                $last_item_index = $repeated_tag_index[$tag . '_' . $level] - 1;
                $current = &$current[$tag][$last_item_index];
            }
        } elseif ($type == "complete") { //Tags that ends in 1 line '<tag />'
            //See if the key is already taken.
            if (!isset($current[$tag])) { //New Key
                $current[$tag] = $result;
                $repeated_tag_index[$tag . '_' . $level] = 1;
                if ($priority == 'tag' and $attributes_data) $current[$tag . '_attr'] = $attributes_data;
            } else { //If taken, put all things inside a list(array)
                if (isset($current[$tag][0]) and is_array($current[$tag])) { //If it is already an array...

                    // ...push the new element into that array.
                    $current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;

                    if ($priority == 'tag' and $get_attributes and $attributes_data) {
                        $current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
                    }
                    $repeated_tag_index[$tag . '_' . $level]++;
                } else { //If it is not an array...
                    $current[$tag] = array($current[$tag], $result); //...Make it an array using using the existing value and the new value
                    $repeated_tag_index[$tag . '_' . $level] = 1;
                    if ($priority == 'tag' and $get_attributes) {
                        if (isset($current[$tag . '_attr'])) { //The attribute of the last(0th) tag must be moved as well

                            $current[$tag]['0_attr'] = $current[$tag . '_attr'];
                            unset($current[$tag . '_attr']);
                        }

                        if ($attributes_data) {
                            $current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
                        }
                    }
                    $repeated_tag_index[$tag . '_' . $level]++; //0 and 1 index is already taken
                }
            }
        } elseif ($type == 'close') { //End of tag '</tag>'
            $current = &$parent[$level - 1];
        }
    }

    return ($xml_array);
}

function get_url($league, $action)
{
    if (isset(Enum::urls[$league][$action])) {
        $url = str_replace('{key}', config('settings.goalserve_key'), Enum::urls[$league][$action]);
        return $url;
    } else
        return null;
}

function get_name_by_league_id($league_id)
{
    $names = Enum::leagues_ids;

    foreach ($names as $key => $name) {
        if ($name['id'] == $league_id) {
            return $key;
        }
    }
    return null;
}

function get_sports_id_by_league_id($league_id)
{
    $names = Enum::leagues_ids;

    foreach ($names as $name) {
        if ($name['id'] == $league_id) {
            return $name['sports_id'];
        }
    }
    return null;
}

function get_xml_h($level = 0, $league_name, $element = 0, $hr = null, $current_level = 0)
{
    if (!isset($hr)) {
        if (isset(Enum::xml_hierarchy[$league_name])) {
            $hr = Enum::xml_hierarchy[$league_name];
        } else {
            return 'League does not exist';
        }
    }
    foreach ($hr as $key => $value) {
        if ($level == $current_level) {
            $keys = array_keys($hr);
            if (isset($keys[$element])) {
                return $keys[$element];
            } else {
                return 'key does not exist';
            }
        } else {
            return get_xml_h($level, $league_name, $element, $hr[$key], $current_level + 1);
        }
    }
}

function get_element_by_alias($league_name, $alias, $data, $attr = null)
{
    $tagname = null;
    foreach (Enum::xml_alias[$league_name] as $xml_alias => $tag) {
        if ($xml_alias == $alias) {
            $tagname = $tag;
        }
    }
    if (!isset($tagname)) {
        $tagname = $alias; // incase no alias defined....use input alias name
    }
    $path = get_tag_path($tagname, $league_name);
    $return_data = $data;
    foreach ($path as $tag_path) {
        $return_data = $return_data[$tag_path];
    }
    if (isset($attr)) {
        return $return_data['attr'][$attr];
    } else {
        return $return_data;
    }
}

// function get_element_by_tag_name($league_name,$data,$tagname,$xml_hierarchy = null)
// {
//     if(!isset($xml_hierarchy)){
//         $xml_hierarchy = Enum::xml_hierarchy[$league_name];
//     }
//     foreach($xml_hierarchy as $key => $tag)
//     {
//         if($tagname == $key)
//         {
//             return $data[$tagname];
//         }
//         else{
//             foreach($data as $key => $tag_data){
//                 return get_element_by_tag_name($league_name,$data[$key],$tagname,$tag_data);
//             }
//         }
//     }
// }

function get_tag_path($tagname, $league_name, $path = [], $data = null)
{
    if (!isset($data)) {
        $data = Enum::xml_hierarchy[$league_name];
    }
    foreach ($data as $key => $tag_data) {
        if ($key == $tagname) {
            if (empty($path)) {
                return [$tagname];
            }
            array_push($path, $key);
            return $path;
        } else {
            foreach ($data as $data_key => $key_data) {
                array_push($path, $data_key);
                if ($data_key == $tagname) {
                    return $path;
                }
                if (!empty($key_data)) {
                    return get_tag_path($tagname, $league_name, $path, $key_data);
                } else {
                    array_pop($path);
                    continue;
                }
            }
        }
    }
}

function get_tagname_by_alias($alias, $league_id)
{
    $league_name = get_name_by_league_id($league_id);
    foreach (Enum::xml_alias[$league_name] as $key => $xml_alias) {
        if ($alias == $key) {
            return $xml_alias;
        }
    }
}

function get_alias_element($data, $league_name, $alias)
{
    $alias_data = Enum::xml_alias[$league_name][$alias];
    if (isset($alias_data['tag']) && isset($alias_data['index']) && isset($data[$alias_data['tag']][$alias_data['index']])) {
        return $data[$alias_data['tag']][$alias_data['index']];
    } elseif (isset($data[$alias_data])) {
        return $data[$alias_data];
    } else {
        return null;
    }
}

function convert_decimal_to_us($decimal)
{
    $us = 0;
    if($decimal == 1){
        return '0';
    }
    else if($decimal < 2)
    {
        $us = round((-100)/($decimal - 1));
    }
    else
    {
        $us = round(($decimal - 1) * 100);
    }

    if($us < 0)
    {
        $us = (string)$us;
    }

    else
    {
        $us = '+'.$us;
    }

    return $us;
}

function get_odds_alias_by_tagname($tagname,$league_name)
{
    $odds_aliases = Enum::odds_alias[$league_name];
    foreach($odds_aliases as $key => $odds_alias)
    {
        if($tagname == $odds_alias)
        {
            return $key;
        }
    }
}
