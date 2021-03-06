<?php namespace Onyx\Destiny\Objects;

use Illuminate\Database\Eloquent\Model;
use Onyx\Destiny\Constants;
use Onyx\Destiny\Helpers\Assets\Images;
use Onyx\Destiny\Helpers\String\Hashes;
use Onyx\Destiny\Helpers\Utils\Game as GameHelper;

/**
 * Class GamePlayer
 * @package Onyx\Destiny\Objects
 * @property int $id
 * @property int $game_id
 * @property int $membershipId
 * @property int $characterId
 * @property int $level
 * @property Hash $class
 * @property Hash $emblem
 * @property int $assists
 * @property int $deaths
 * @property int $kills
 * @property boolean $completed
 * @property int $secondsPlayed
 * @property float $averageLifespan
 * @property int $score
 * @property int $team
 * @property int $standing
 * @property int $revives_given
 * @property int $revives_taken
 * @property int $account_id
 */
class GamePlayer extends Model {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'destiny_game_players';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var \Onyx\Destiny\Helpers\String\Hashes $translator
     */
    private $translator;

    function __construct()
    {
        parent::__construct();

        $this->translator = new Hashes();
    }

    //---------------------------------------------------------------------------------
    // Accessors & Mutators
    //---------------------------------------------------------------------------------

    public function setEmblemAttribute($value)
    {
        $hash = Hash::where('extra', $value)->first();

        if ($hash instanceof Hash)
        {
            $this->setAttributePullImage('emblem', $hash->hash);
        }
    }

    public function getEmblemAttribute($value)
    {
        return $this->translator->map($value, false);
    }

    //---------------------------------------------------------------------------------
    // Public Methods
    //---------------------------------------------------------------------------------

    public function game()
    {
        return $this->belongsTo('Onyx\Destiny\Objects\Game', 'game_id', 'instanceId');
    }

    public function character()
    {
        return $this->hasOne('Onyx\Destiny\Objects\Character', 'characterId', 'characterId');
    }

    public function gameChar()
    {
        // We use this to only return the fields we need on the game character screen as
        // loading the entire char and all his/her fields is time consuming when we only
        // need like emblem/background
        return $this->hasOne('Onyx\Destiny\Objects\Character', 'characterId', 'characterId')
            ->select('id', 'membershipId', 'characterId', 'emblem');
    }

    public function account()
    {
        return $this->hasOne('Onyx\Account', 'id', 'account_id');
    }

    public function historyAccount()
    {
        // We us this function to only return clanName when counting "PandaLove" members
        // in a game. This prevents a nasty SELECT * FROM accounts, etc
        return $this->hasOne('Onyx\Account', 'id', 'account_id')
            ->select('id');
    }

    public function setTranslatorUrl($url)
    {
        $this->translator->setUrl($url);
    }

    public function kdr()
    {
        return GameHelper::kd($this->kills, $this->deaths);
    }

    public function kadr()
    {
        return GameHelper::kadr($this->kills, $this->assists, $this->deaths);
    }

    //---------------------------------------------------------------------------------
    // Private Methods
    //---------------------------------------------------------------------------------

    /**
     * @param string $index Index for $this->attributes
     * @param string $hash hashCode for item
     * @throws \Onyx\Destiny\Helpers\String\HashNotLocatedException
     */
    private function setAttributePullImage($index, $hash)
    {
        if ($hash == null || $hash == "") return;
        Images::saveImagesLocally($this->translator->map($hash, false));
        $this->attributes[$index] = $hash;
    }
}
