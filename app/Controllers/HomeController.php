<?php

namespace App\Controllers;

class HomeController extends Controller
{
    public function index()
    {
        $data = [
            'title' => 'Home Page',
            'content' => 'Welcome to the home page!',
        ];

        $this->view('Home', $data);
    }
}
