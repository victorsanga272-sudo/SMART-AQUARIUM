<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UbuntuServer extends Controller
{
    //
    function index(){
        return view("hello");
    }
    function store(Request $req){
        $req->validate([
'username' => 'required|min:5|max:10'
        ]);
$name= $req->username;
return $name;
    }
}
