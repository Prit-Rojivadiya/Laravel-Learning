<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\VehicleController;
use App\Imports\VehicleImport;
use App\Models\Vehicle;
use Illuminate\Console\Command;

class ImportData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tranzit:import {file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
    public function handle()
    {
        $filePath = $this->input->getArgument('file');

        (new VehicleImport($filePath))->import();

        return Command::SUCCESS;
    }
}
