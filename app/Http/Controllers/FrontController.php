<?php

namespace App\Http\Controllers;

use App\Friends;
use Illuminate\Http\Request;
use App\Http\Controllers\Users\UsersController;
use App\Countries;
use Illuminate\Support\Facades\Cache;

class FrontController extends Controller
{

    private $friendsModel;
    public function __construct()
    {
        parent::__construct();
        $this -> friendsModel= new Friends();

    }



    public function indexAction()
    {
        $countries = Countries::all('language_id', 'language_name');
        $this->friendsModel->resetAlreadyTaken();
        return view('home', ['languages' => $countries]);
    }

    /**
     * Get users and display the results.
     * if language is changed and method is dbMarker . Delete all flags for the DB
     * if language is changed and method is cache . Delete old cache records and load new
     * if method is changed to dbMarker delete all markers from db
     * if method is changed to cache delete old cache
     *
     * @author Kilesss
     * @return array of results
     */
    public function getUserAction(Request $request)
    {
        $users = new UsersController();
        $flagChangedLanguage = 0;
        $clearData = 0;
        if($request->session()->get('language') == null || $request->session()->get('language') != $request->language){
            if($request->session()->get('language') != $request->language) {
                $flagChangedLanguage = 1;
                $clearData = 1;
            }
            $request->session()->put('language', $request->language);

        }
        if($request->session()->get('type') == null ) {
            $request->session()->put('type', $request->type);
        }else{
            if ($request->session()->get('type') != $request->type){
                if ($request->session()->get('type') == 'dbMarker'){
                    $this->friendsModel->resetAlreadyTaken();
                    $clearData = 1;
                }elseif ($request->session()->get('type') == 'collection'){
                    Cache::put('friends', null, 1440);
                    $clearData = 1;
                }
                $request->session()->put('type', $request->type);
            }
        }
        if ($request->type == 'dbMarker') {
            $friendsDB = $users->getUsersAction($request->language, $flagChangedLanguage);
            echo json_encode(['records'=>$friendsDB, 'clearData'=>$clearData]);
        } elseif ($request->type == 'collection') {
            $friendsCache = $users->getUsersCacheAction($request->language, $flagChangedLanguage);
            if ($friendsCache['existCache'] == 0){
                $clearData = 1;
            }
            echo json_encode(['records'=>$friendsCache['data'], 'clearData'=>$clearData]);
        } else {
            echo json_encode(['records'=>'', 'clearData'=>2]);
        }


    }

}
