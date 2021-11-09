<?php

namespace App\Console\Commands;

use App\Services\GoogleSheet;
use App\Models\EventClick;
use App\Models\Variable;
use Illuminate\Console\Command;

class ClickEvents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:clickevents';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Este comando permite poder sincronizar los datos de los eventos clic';

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
     * Execute the console command.
     *
     * @return int
     */
    public function handle( GoogleSheet $googleSheet )
    {
        $variable = Variable::query()
            ->where('name', 'lastClickEventsIDSync')
            ->first();
        
        $rows = EventClick::query()
            ->where('id', '>', $variable->value)
            ->orderBy('id')
            ->limit(100)
            ->get();       

        if( $rows->count() === 0 ){
            return  true;
        }

        $finalData = collect();
        $lastId = 0;

        foreach ($rows as $row){
            $fullname = $row->fullname ? $row->fullname : 'Indefinido';
            $username = $row->username ? $row->username : 'Indefinido';
            $name_scene = $row->name_scene ? $row->name_scene : 'Indefinido';
            $click_name = $row->click_name ? $row->click_name : 'Indefinido';

            $finalData->push([
                $row->id,
                $row->email,
                $fullname,
                $username,
                $name_scene,
                $click_name,
                $row->date_visit,
                $row->created_at,
            ]);

            $lastId = $row->id;
        }   

        $googleSheet->saveDataToSheet(
            $finalData->toArray(),
            '1x3njrgGgyLsHFXCTuJw1o3FFScEWWMh2c-FnQ4HkXI0',
            'clics',
        );

        $variable->value = $lastId;
        $variable->save();

        return true;
    }
    
}
