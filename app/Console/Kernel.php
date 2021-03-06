<?php namespace PandaLove\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

use PandaLove\Console\Commands\updatePandas;
use PandaLove\Console\Commands\alertSender;
use PandaLove\Console\Commands\updateMedals;
use PandaLove\Console\Commands\updateCsrs;
use PandaLove\Console\Commands\updatePlaylists;
use PandaLove\Console\Commands\updateSeasons;
use PandaLove\Console\Commands\updateWeapons;
use PandaLove\Console\Commands\updateHistoricStats;
use PandaLove\Console\Commands\updateH5Pandas;

class Kernel extends ConsoleKernel {

	/**
	 * The Artisan commands provided by your application.
	 *
	 * @var array
	 */
	protected $commands = [
		updatePandas::class,
		alertSender::class,
		updateMedals::class,
		updateCsrs::class,
		updatePlaylists::class,
		updateSeasons::class,
		updateWeapons::class,
		updateHistoricStats::class,
		updateH5Pandas::class
	];

	/**
	 * Define the application's command schedule.
	 *
	 * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
	 * @return void
	 */
	protected function schedule(Schedule $schedule)
	{

	}

}
