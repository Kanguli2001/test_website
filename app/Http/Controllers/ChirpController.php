<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ChirpController extends Controller
{
    public function index()
    {
        $chirps = [
            [
                'author' => 'Alice',
                'message' => 'Hello, world!',
                'timestamp' => '2024-06-01 10:00:00',
            ],
            [
                'author' => 'Bob',
                'message' => 'Laravel is awesome!',
                'timestamp' => '2024-06-01 11:00:00',
            ],
            [
                'author' => 'Charlie',
                'message' => 'Just had a great lunch!',
                'timestamp' => '2024-06-01 12:00:00',
            ]
        ] ;
        
        return view('home', ['chirps' => $chirps]);
    }
}
