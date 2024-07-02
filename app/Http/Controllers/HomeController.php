<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class HomeController extends Controller
{
    public function index()
    {
        $url = "https://books3.audio-books.club/books/9665/14_04.MP3";
        $glava = 15;
        $part = 1;

        while ( $glava <= 43) {
            $url = sprintf("https://books3.audio-books.club/books/9665/%s_%02d.MP3",$glava, $part);
            try {
                $cont = file_get_contents($url);
                $name = sprintf("%s_%02d.MP3",$glava, $part);
                file_put_contents($name, $cont);
            } catch (\Exception $e){
                $part = 1;
                $glava++;
            }
        }
        dd('Done;');
        return view('index');
    }
}
