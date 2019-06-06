<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;

class SeedRaces extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'races:seed {years}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed races from http://ergast.com/api into database.';

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


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        try{

            $years = $this->argument('years');

            $current_year = intval(date('Y'));

            $bar = $this->output->createProgressBar($years);

            $bar->start();

            for($i = $current_year;$i>=$current_year-($years-1);$i--){

                $result = $this->callApi(30,0, $i);

                if($result){

                    $json = json_decode($result,JSON_PRETTY_PRINT);

                    $total = $json['MRData']['total'];

                    $api_calls_number = intval($total)/30;

                    $races = $json['MRData']['RaceTable']['Races'];

                    $this->insertData($races);

                    for($j = 1;$j<=$api_calls_number;$j++){

                        $remaining_results = $this->callApi(30,30*$j, $i);

                        if($remaining_results){

                            $json_remaining = json_decode($remaining_results,JSON_PRETTY_PRINT);

                            $races_remaining = $json_remaining['MRData']['RaceTable']['Races'];

                            $this->insertData($races_remaining);

                        }

                    }

                }else{

                    $this->error('Bad request!');

                }

                $bar->advance();

            }

            $bar->finish();

            $this->info('Seed finished!');

        }catch (RequestException $e){

            $this->error($e);
        }


    }
}
