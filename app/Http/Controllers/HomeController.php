<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Book;
use App\Models\Bookshelve;
use App\Models\User;
use App\Models\Office;
use App\Models\CabBooking;
use App\Models\FlightBooking;
use App\Models\TrainBooking;
use App\Models\HotelBooking;
use App\Models\CaveLocation;
use App\Models\CaveForm;
use App\Models\CaveDoc;
use Auth;
use DB;
class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $data = (object)[];
        
       
        return view('home',compact('data'));
    }
}
