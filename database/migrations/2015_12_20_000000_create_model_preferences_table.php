<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use KLaude\EloquentPreferences\Preference;

class CreateModelPreferencesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Model::getConnectionResolver()
            ->connection()
            ->getSchemaBuilder()
            ->create((new Preference)->getQualifiedTableName(), function (Blueprint $table) {
                $table->increments('id');
                $table->string('preference');
                $table->string('value');
                $table->morphs('preferable');
                $table->timestamps();
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Model::getConnectionResolver()
            ->connection()
            ->getSchemaBuilder()
            ->drop((new Preference)->getQualifiedTableName());
    }
}
