<?php

namespace App\Console\Commands;

use App\Services\GoogleSheet;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Variable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RegisteredUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:registeredusers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Este comando permite poder sincronizar los datos de los usuarios registrados';

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
            ->where('name', 'lastRegisteredUsersIDSync')
            ->first();
        
        $rows = User::query()
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
            $created_at = new Carbon($row->created_at, 'America/Bogota');

            $finalData->push([
                $row->id,
                $row->email,
                $fullname,
                $username,
                $row->date_register,
                $created_at
            ]);

            $lastId = $row->id;
        }        

        $googleSheet->saveDataToSheet(
            $finalData->toArray(),
            '1nSaqzUsolEUV-VfmwM2H5_KIBcF7pYycyZkani-4voU',
            'register',
        );

        $variable->value = $lastId;
        $variable->save();

        return true;
    }
}
