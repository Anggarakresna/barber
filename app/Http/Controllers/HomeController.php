<?php

namespace App\Http\Controllers;

use App\Models\Barber;
use App\Models\Gallery;
use App\Models\Service;

class HomeController extends Controller
{
    public function index()
    {
        $services = Service::latest()->take(3)->get();
        $barbers = Barber::with('user')->latest()->take(3)->get();
        $galleries = Gallery::latest()->take(4)->get();

        return view('home', compact('services', 'barbers', 'galleries'));
    }
}
