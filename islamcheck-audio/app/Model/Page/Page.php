<?php

namespace App\Models\Page;

use Illuminate\Database\Eloquent\Model;
use App\Models\Page\Table;
use App\Models\Page\Form;

class Page extends Model
{
    public $content = null;
    public $title = null;
    public $heading = null;

    public function __construct($title = null,$heading = null)
    {
        $this->title = $title;
        $this->content = collect();

        if(isset($heading)){
        $this->heading = $heading;
        }
        else{
            $this->heading = $title;
        }
        $this->add('<a class = "btn btn-dark-grey btn-navigate" href="javascript:history.back()"><i class="fa fa-arrow-circle-left"></i> Back</a>');
      }

    public function table($tHead=null,$route = null)
    {
        return new Table($tHead,$route);
    }

    public function form($action='',$method='post')
    {
        return new Form($action,$method);
    }

    public function create_button($value,$route,$type)
    {
        if($type == 'add'){
        $this->add('<a class = "btn btn-teal btn-navigate" href="'.route($route).'"><i class="fa fa-plus"></i>'.$value.'</a>');
        }
    }

    public function create_filter($value,$route,$type,$selected=null)
    {

        if($type == 'filter'){
            $data='<select style="float: right;width: 10% !important;" class="form-control" onchange="location = this.options[this.selectedIndex].value;">';
            $selected_value='';
            foreach($value as $key => $option):
                if(trim($selected)==trim($option['code'])){
                    $selected_value='selected=selected';
                }else{
                    $selected_value='';
                }

            $data.='<option '.$selected_value.' value="'. $route .'/'.$option['code'].'">'.$option['name'].'</option>';
            endforeach;
            $data.='</select>';
            $this->add( $data);
        }
    }

    public function add($content){
        if($content instanceof Table){
            $this->content->push(['type'=>'table','data'=>$content]);
        }
        else if($content instanceof Form){
            $this->content->push(['type'=>'form','data'=>$content]);
        }
        else{
            $this->content->push(['type'=>'html','data'=>$content]);
        }
        return true;
    }
}
