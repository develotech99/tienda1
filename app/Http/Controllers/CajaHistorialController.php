<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CajaHistorialController extends Controller
{
    
    public function index(){
        return view("ventas.CajaReportes");
    }
}
