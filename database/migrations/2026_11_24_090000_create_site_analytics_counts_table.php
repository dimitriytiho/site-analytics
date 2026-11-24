<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('site_analytics_counts', function (Blueprint $table): void {
            $table->dateTime('hour');
            $table->string('page', 64);
            $table->string('event', 64);
            $table->unsignedBigInteger('hits');
            $table->primary(['hour', 'page', 'event']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_analytics_counts');
    }
};
