<?php

namespace App\Console\Commands;


use App\Models\PreventiveMaintenance;
use App\Models\RepairOrder;
use Illuminate\Console\Command;

class TestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tranzit:test {value1}';

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
        //$value1 = $this->input->getArgument('value1');
        //$t1 = bcrypt($value1);
        //$this->info('The test output is: '.$t1);

        $tenantId = $this->input->getArgument('value1');
        $query = RepairOrder::query();
        $query->select('repair_orders.*');
        $query->where('repair_orders.tenant_id', $tenantId);
        $query->whereNotNull('repair_orders.preventive_maintenance_id');
        $ros_with_pms = $query->get();

        foreach ($ros_with_pms as $ro) {
            $pmId = $ro->preventive_maintenance_id;
            $pm = PreventiveMaintenance::findOrFail($pmId);
            if ($pm->repair_order_id == null && $ro->id) {
                $this->info(json_encode($pm));
                $pm->repair_order_id = $ro->id;
                $this->info(json_encode($pm));
                $pm->save();
            }
            $this->info("-------------------------------");
        }

        return Command::SUCCESS;
    }
}
