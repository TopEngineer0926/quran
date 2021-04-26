<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Enum;
use App\Models\Page\Page;

class ProfileController extends CrudController
{
    public $model = 'Admin';
    public $route = 'profile';
    public $view = 'admin.view';
    function __construct()
    {
        $this->middleware('auth:admin');
        parent::__construct($this->model,$this->route);
    }

    public function index()
    {
        $admin = Admin::where('id',auth('admin')->user()->id)->first();
        $page = new Page('Profile','Profile Settings');
        $form = $page->form(route('profile.update',[$admin->id]),'PATCH');
        $form->render([
            ['label'=>'Name','type'=>'input','input-type'=>'text','name'=>'name','class'=>'form-control form-control-alternative','placeholder'=>'Name','val'=>$admin->name],
            ['label'=>'Email','type'=>'input','input-type'=>'text','name'=>'email','class'=>'form-control form-control-alternative','placeholder'=>'Email','val'=>$admin->email],
            ['label'=>'Old Password','type'=>'input','input-type'=>'text','name'=>'old_password','class'=>'form-control form-control-alternative','placeholder'=>'Current Password'],
            ['label'=>'New Password','type'=>'input','input-type'=>'text','name'=>'password','class'=>'form-control form-control-alternative','placeholder'=>'New Password'],
            ['label'=>'Confirm Password','type'=>'input','input-type'=>'text','name'=>'password_confirmation','class'=>'form-control form-control-alternative','placeholder'=>'Confirm Password'],
            ['type'=>'input','input-type'=>'submit','class'=>'form-control form-control-alternative btn btn-success','name'=>'submit','val'=>'Update']
        ]);
        $page->add($form);
        return view($this->view)->with(['page'=>$page]);
    }
    public function update(Request $request,$id,$table=null)
    {
        $validator = Validator::make($request->all(),[
            'name' => ['required','min:4'],
            'email' => ['required','email','max:191'],
            'password' => ['string','nullable','min:8','confirmed','required_with:old_password','different:old_password'],
            'old_password' => ['string','nullable','required_with:password']
        ]);
        if ($validator->fails()) {
            return back()->withErrors([Enum::fail => $validator->errors()->first()]);
        }
        if(isset($request->old_password))
        {
            if (Hash::check($request->old_password, auth('admin')->user()->password)) {
                $request->merge(['password' => Hash::make($request->input('password'))]);
                $this->attributes(['name','email','password']);
            }
            else
            {
                return back()->withErrors([Enum::fail => Enum::fail_password_mismatch]);
            }
        }
        else
        {
            $this->attributes(['name','email']);
        }
        return parent::update($request,$id);

    }
}
