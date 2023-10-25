<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDefaultUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tenant = \App\Models\Tenant::create([
            'name' => 'TranzIT',
            'abbrv' => 'TranzIT',
        ]);

        \App\Models\User::create([
            'name' => 'Local Developer',
            'tenant_id' => $tenant->id,
            'email' => 'developer@tranzitfleet.com',
            'password' => bcrypt('secret'),
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $user = \App\Models\User::where('email', 'developer@local.com')->first()->forceDelete();
        $tenant = \App\Models\Tenant::where('name', 'TranzIT')->first()->forceDelete();
    }
}
