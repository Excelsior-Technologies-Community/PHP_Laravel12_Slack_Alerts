<?php
// database/migrations/2024_01_01_000000_create_slack_messages_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSlackMessagesTable extends Migration
{
    public function up()
    {
        Schema::create('slack_messages', function (Blueprint $table) {
            $table->id();
            $table->string('message');
            $table->string('sender_name')->nullable();
            $table->string('sender_email')->nullable();
            $table->string('priority')->default('normal'); // low, normal, high, urgent
            $table->string('category')->nullable(); // info, alert, warning, error
            $table->boolean('is_sent')->default(true);
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('slack_messages');
    }
}