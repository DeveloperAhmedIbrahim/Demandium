<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSubscriptionSubscriberBookingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subscription_subscriber_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('provider_id')->nullable();
            $table->foreignUuid('booking_id')->nullable();
            $table->foreignUuid('package_subscriber_log_id')->nullable();
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
        Schema::dropIfExists('subscription_subscriber_bookings');
    }
}
