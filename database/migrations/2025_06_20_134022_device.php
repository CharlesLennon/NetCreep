<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->string('mac')->primary(); // MAC address as primary key
            $table->string('last_ip')->nullable();
            $table->string('name')->nullable();
            //type
            $table->string('type')->nullable();// e.g., 'phone', 'laptop', 'printer', etc.
            $table->string('vendor')->nullable(); // e.g., 'Apple', 'Samsung

            //parent device
            $table->string('parent_mac')->nullable(); // MAC address of the parent device,
            $table->foreign('parent_mac')->references('mac')->on('devices')->onDelete('set null');
            $table->string('parent_port')->nullable(); // Port number on the parent device
            $table->string('self_port')->nullable(); // Port number parent mac is connected to on this device

            //first found and last seen timestamps
            $table->timestamp('first_found')->useCurrent();
            $table->timestamp('last_seen')->useCurrent();
        });

        Schema::create('device_presence', function (Blueprint $table) {
            $table->id();
            $table->string('mac');
            $table->foreign('mac')->references('mac')->on('devices')->onDelete('cascade');
            $table->timestamp('seen')->useCurrent();
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_presence');
        Schema::dropIfExists('devices');
    }
};
