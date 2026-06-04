<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index()
    {
        $data = [
            'title' => 'MDK Alliance Administration'
        ];
        
        return view('welcome_message', $data);
    }
}
