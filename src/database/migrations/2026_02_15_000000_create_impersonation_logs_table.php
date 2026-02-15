<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateImpersonationLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(config('laravel-impersonate.log_table', 'impersonation_logs'), function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('impersonator_id');
            $table->unsignedBigInteger('impersonated_id');
            $table->timestamp('impersonated_at')->useCurrent();
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
        Schema::dropIfExists(config('laravel-impersonate.log_table', 'impersonation_logs'));
    }
}
