<?php

namespace App\Models\Page;

use Illuminate\Database\Eloquent\Model;

class Table extends Model
{
    public $tHead = [];
    public $rows = [];
    public $types = [
        'image' => 'image',
        'text' => 'text'
    ];
    public $edits = [];
    private $action_head = 'Actions';
    private $all_actions = ['view', 'edit', 'delete'];
    public $actions = [];
    private $hide_columns = [];
    private $replace_keys = [];
    public $hide = [];
    public $no_sort = [];
    public $route = null;
    private $clip = [];

    public function __construct($tHead = null,$route=null)
    {
        $this->tHead = $tHead;
        $this->route = $route;
        $this->no_sort[] = $this->action_head;
    }

    public function add_rows($rows)
    {
        foreach ($rows as $row) {
            $this->rows[] = $row;
        }
        return true;
    }

    public function add_row($row)
    {
        if(!empty($this->clip)){
        foreach($this->clip[0] as $key => $clip){
            if(in_array($key,array_keys($row))){
                if(strlen($row[$key])>$clip){
                $row[$key] = \substr($row[$key],0,$clip);
                $row[$key].='....';
                }
            }
        }
    }
        $this->rows[] = $row;
        return true;
    }

    public function render($tables)
    {
        $tables = $tables->toArray();
        if (empty($this->tHead)) {
            foreach ($tables[0] as $key => $row) {
                if (!in_array($key, $this->replace_keys)) {
                    $this->tHead[] = $key;
                }
            }
        }
        foreach ($tables as  $key => $table) {
            foreach ($this->edits as $edit) {
                foreach ($edit as $edit_key => $edit_value) {
                    $table[$edit_key] = $table[$edit_value[0]][$edit_value[1]];
                    unset($table[$edit_value[0]]);
                }
            }
            $this->add_row($table);
        }
    }


    public function replace_column($column_to_replace, $column_replace_with, $column_key = null)
    {
        $this->edits[] = [$column_to_replace => [$column_replace_with, $column_key]];
        $this->replace_keys[] = $column_to_replace;
    }

    // public function hide_columns($columns)
    // {
    //     if (is_array($columns)) {
    //         foreach ($columns as $column) {
    //             $this->hide[] = $column;
    //         }
    //     } else {
    //         $this->hide[] = $columns;
    //     }
    // }

    public function add_actions($actions)
    {
        $this->tHead[] = $this->action_head;
        if (empty($actions)) {
            foreach ($this->all_actions as $action) {
                $this->actions[] = $action;
            }
            return true;
        }

        foreach ($actions as $action) {
            $this->actions[] = $action;
        }
    }

    public function no_sort($columns)
    {
        if (is_array($columns)) {
            foreach ($columns as $column) {
                $this->no_sort[] = $column;
            }
        } else {
            $this->no_sort[] = $columns;
        }
    }

    public function route($route)
    {
        $this->route = $route;
    }

    public function hide_columns($columns)
    {
        foreach ($columns as $column) {
            $key = array_search($column, $this->tHead);
            if ($key !== false) {
                $this->hide[] = $key;
            }
        }
    }
    public function clip($columns)
    {

            $this->clip[] = $columns;

    }
}
