<?php

namespace App\Console\Commands;

use App\Mail\NewRacesAdded;
use App\Race;
use Carbon\Carbon;
use Illuminate\Console\Command;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class UpdateRaces extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'races:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check if there are new races records and update the database.';

    /**
     * The email to which a notification will be sent.
     *
     * @var string
     */
    protected $emailTo = 'test@example.com';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function callApi($limit = 30, $offset = 0, $year){


        $client = new GuzzleClient();
        $response = $client->request('GET', 'http://ergast.com/api/f1/'.$year.'/results.json?limit='.$limit.'&offset='.$offset, [
            'headers' => [
                'Accept' => 'application/json',
                'Content-type' => 'application/json'
            ]
        ]);

        $result = $response->getBody()->getContents();

        return $result;
    }

    public function insertData($races){

        foreach($races as $race){

            $date = $race['date'];
            $time = (isset($race['time'])) ? $race['time'] : '';

            foreach($race['Results'] as $result){

                $raceModel = new \App\Race();
                $raceModel->driver_position = $result['positionText'];
                $raceModel->driver_number = $result['number'];
                $raceModel->driver_name = $result['Driver']['givenName'].' '.$result['Driver']['familyName'];
                $raceModel->car_constructor = $result['Constructor']['name'];
                $raceModel->laps = $result['laps'];
                $raceModel->grid = $result['grid'];
                $raceModel->time = (isset($result['Time'])) ? $result['Time']['time'] : '';
                $raceModel->status = $result['status'];
                $raceModel->points = $result['points'];
                $raceModel->season = $race['season'];
                $raceModel->race_name = $race['raceName'];
                $raceModel->race_datetime = date('Y-m-d H:i:s', strtotime("$date $time"));
                $raceModel->save();

            }

        }
    }

    public function checkNewRaces($races, $latest_race){

        $new_races = [];

        foreach ($races as $race){

            $date = $race['date'];
            $time = (isset($race['time'])) ? $race['time'] : '';

            $datetime = date('Y-m-d H:i:s', strtotime("$date $time"));

            if(Carbon::parse($datetime)->gt($latest_race)){

                $new_races[] = $race;
            }

        }

        return $new_races;
    }


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        try{

            $latest_race_date = Race::select('race_datetime')->orderBy('race_datetime','desc')->first()->race_datetime;

            $latest_date = Carbon::parse($latest_race_date);

            $current_year = intval(date('Y'));

            $result = $this->callApi(30,0, $current_year);

            //$result = Storage::get('results.json');

            $found_new_races = false;

            if($result){

                $json = json_decode($result,JSON_PRETTY_PRINT);

                $total = $json['MRData']['total'];

                $api_calls_number = intval($total)/30;

                $races = $json['MRData']['RaceTable']['Races'];

                $new_races = $this->checkNewRaces($races, $latest_date);

                if(!empty($new_races)){

                    $found_new_races = true;
                    $this->insertData($new_races);

                }

                for($j = 1;$j<=$api_calls_number;$j++){

                    $remaining_results = $this->callApi(30,30*$j, $current_year);

                    if($remaining_results){

                        $json_remaining = json_decode($remaining_results,JSON_PRETTY_PRINT);

                        $races_remaining = $json_remaining['MRData']['RaceTable']['Races'];

                        $new_races_remaining = $this->checkNewRaces($races_remaining, $latest_date);

                        if(!empty($new_races_remaining)){

                            $found_new_races = true;
                            $this->insertData($new_races_remaining);

                        }

                    }

                }

                if($found_new_races){

                    Mail::to($this->emailTo)->send(new NewRacesAdded());

                    $this->info('New races inserted!');

                }else{
                    $this->info('No new races!');
                }

            }else{

                $this->error('Bad request!');

            }

        }catch (RequestException $e){

            $this->error($e);
        }

    }
}
