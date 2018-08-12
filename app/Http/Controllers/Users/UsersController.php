<?php

namespace App\Http\Controllers\Users;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Friends;
use Illuminate\Support\Facades\Cache;

class UsersController extends Controller
{

    protected $friendsModel;

    public function __construct()
    {
        $this->friendsModel = new Friends();
    }

    /**
     * Return results from databases
     * If language is changed we delete all flags from DB and return new records
     *

     * @param integer $language The language of the friends
     * @param integer $flagChangedLanguage If language is changed
     *
     * @author Kilesss
     * @return array of results
     */
    public function getUsersAction($language, $flagChangedLanguage)
    {
            $friends = $this->friendsModel->getFriends(Auth::user()->user_id, $language);
            return $friends->toArray();
    }


    /**
     * Return results from Cache
     * If language is changed we delete old Cache load new and  return new records
     *

     * @param integer $language The language of the friends
     * @param integer $flagChangedLanguage If language is changed
     *
     * @author Kilesss
     * @return array of results
     */
    public function getUsersCacheAction($language, $flagChangedLanguage)
    {
        $existCache = 1;
        if (!Cache::get('friends') || Cache::get('friends') == NULL || $flagChangedLanguage == 1) {
            $friends = $this->friendsModel->getAllFriends(Auth::user()->user_id, $language);
            $existCache = 0;
            return ['data' => $this->getFriendsByCache($friends->toArray()), 'existCache' => $existCache];
        } else {
            $friends = Cache::get('friends');
            return ['data' => $this->getFriendsByCache($friends), 'existCache' => $existCache];
        }

    }
    /**
     * Get 20 random records from input array , delete these records from cache and resort the array and return this 20 records
     *
     * @param array $friends The records from cache
     *
     * @author Kilesss
     * @return array of results
     */
    private function getFriendsByCache($friends)
    {
        $displayedFriends = [];
        $keys = $this->UniqueRandomNumbersWithinRange(0, count($friends) - 1, 20);
        foreach ($keys as $k) {
            $displayedFriends[] = $friends[$k];

            unset($friends[$k]);
        }
        Cache::put('friends', $this->sortFriendArray($friends), 1440);
        return $displayedFriends;
    }

    /**
     *resort the array after we take the records
     *
     * @param array $friends The records from cache without taken records
     *
     * @author Kilesss
     * @return array of results
     */
    function sortFriendArray($friends)
    {
        $friendsNew = [];
        foreach ($friends as $fr) {
            $friendsNew[] = $fr;
        }
        return $friendsNew;
    }

    /**
     *Get unique and random ids
     *
     * @param integer $min Minimal range of numbers
     * @param integer $max Maximum range of numbers
     * @param integer $quantity Number ot returned ids
     *
     * @author Kilesss
     * @return array of results
     */
    function UniqueRandomNumbersWithinRange($min, $max, $quantity)
    {
        $numbers = range($min, $max);
        shuffle($numbers);
        return array_slice($numbers, 0, $quantity);
    }
}
