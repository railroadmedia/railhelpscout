<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHelpscoutCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'helpscout_customers',
            function (Blueprint $table) {

                $table->increments('internal_id');

                $table->bigInteger('external_id')->index();

                $table->timestamp('created_at')->index();
                $table->timestamp('updated_at')->index();
                $table->timestamp('deleted_at')->nullable()->index();

            }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('helpscout_customers');
    }
}
