<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index(): string
    {
        return view('home/index');
    }

    public function pricing(): string
    {
        return view('home/pricing');
    }

    public function features(): string
    {
        return view('home/features');
    }

    public function terms(): string
    {
        return view('home/terms');
    }

    public function privacy(): string
    {
        return view('home/privacy');
    }
}
