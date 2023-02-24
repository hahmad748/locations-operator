<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\File;


class CreateLocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('devsfort_locations');
        Schema::create('devsfort_locations', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('country')->nullable();
            $table->string('region')->nullable();
            $table->string('city')->nullable();
            $table->string('urban_area')->nullable();
            $table->string('state_code')->nullable();
            $table->string('state')->nullable();
            $table->string('postcode')->nullable();
            $table->string('type')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->string('elevation')->nullable();
            $table->string('population')->nullable();
            $table->string('median_income')->nullable();
            $table->string('area_sq_km')->nullable();
            $table->string('local_government_area')->nullable();
            $table->string('time_zone')->nullable();
            $table->timestamps();
        });
        $this->migrateData();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('devsfort_locations');
    }

    public function migrateData()
    {
        $data = File::get(__DIR__.'/australia.json');
        $data = json_decode($data,true);
        foreach (array_chunk(array_values($data), 500) as $chunk) {
            array_walk($chunk, function (&$element) {
                $element['city'] = $element['urban_area'];
                $element['created_at'] = date('Y-m-d H:i:s');
                $element['updated_at'] = date('Y-m-d H:i:s');
            });
            DB::table('devsfort_locations')->insert($chunk);
        }
    }
}
