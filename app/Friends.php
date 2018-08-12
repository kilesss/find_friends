<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Friends extends Model
{
    protected $table = 'friends';
    protected $usersTable = 'users';
    public $timestamps = false;


    /**
     *Get records from database where is not alredy taken
     *
     * @param integer $userID Id for the user who search friends
     * @param integer $language Language on the friends
     *
     * @author Kilesss
     * @return object of results
     */

    public function getFriends($userID, $language)
    {
        $categories = DB::table($this->table)
            ->join($this->usersTable, $this->usersTable . '.user_id', '=', $this->table . '.fr2')
            ->where('fr1', '!=', $userID)
            ->where('already_taken', 0)
            ->where($this->usersTable . '.country', $language)
            ->groupBy('fr2')
            ->inRandomOrder()
            ->take(config('friends.countRecords'))
            ->select('fr2', 'real_name')
            ->get();
        $updateArr = [];
        foreach ($categories as $cat) {
            array_push($updateArr, $cat->fr2);
        }
        DB::table($this->table)->whereIn('fr2', $updateArr)->update(['already_taken' => 1]);
        return $categories;

    }

    /**
 *Get all  records from database
 * this is used to load the cache
 *
 * @param integer $userID Id for the user who search friends
 * @param integer $language Language on the friends
 *
 * @author Kilesss
 * @return object of results
 */
    public function getAllFriends($userID, $language){
        return DB::table($this->table)
            ->join($this->usersTable, $this->usersTable . '.user_id', '=', $this->table . '.fr2')
            ->where('fr1', '!=', $userID)
            ->where($this->usersTable . '.country', $language)
            ->groupBy('fr2')
            ->select('fr2', 'real_name')
            ->get();
    }
    /**
     *Reset all flags for displayed users in table
     *
     * @author Kilesss
     * @return bool
     */
    public function resetAlreadyTaken(){
        return DB::table($this->table)
            ->where('already_taken', 1)
            ->update(['already_taken' => 0]);
    }
}
