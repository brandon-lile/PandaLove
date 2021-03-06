<?php namespace PandaLove\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Intervention\Image\ImageManager;
use Onyx\Halo5\Client;
use Onyx\Halo5\Objects\Season;
use Onyx\Halo5\Objects\Weapon;

class updateWeapons extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'halo5:weapons-update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pulls down weapon information into table';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        $client = new Client();
        $this->info('Getting new Weapon data from 343');
        $weapons = $client->getWeapons();

        if (is_array($weapons))
        {
            $this->info('We found Weapon data. Adding to table after purge.');

            DB::table('halo5_weapons')->truncate();
            foreach($weapons as $weapon)
            {
                $this->info('Adding ' . $weapon['name']);

                $w = new Weapon();
                $w->name = $weapon['name'];
                $w->description = $weapon['description'];
                $w->uuid = $weapon['id'];
                $w->contentId = $weapon['contentId'];
                $w->save();

                $path = 'resources/images/weapons/';

                if (! file_exists($path . $weapon['id'] . '.png'))
                {
                    $icon = file_get_contents($weapon['smallIconImageUrl']);

                    /** @var $image \Intervention\Image\Image */
                    $image = Image::make($icon);
                    $image->save($path . $weapon['id'] . '.png');
                }
            }
        }
    }
}
