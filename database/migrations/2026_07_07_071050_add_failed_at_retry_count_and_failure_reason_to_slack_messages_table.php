<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('slack_messages', function (Blueprint $table) {
            $table->timestamp('failed_at')->nullable()->after('is_sent');
            $table->integer('retry_count')->default(0)->after('failed_at');
            $table->text('failure_reason')->nullable()->after('retry_count');
        });
    }

    public function down()
    {
        Schema::table('slack_messages', function (Blueprint $table) {
            $table->dropColumn(['failed_at', 'retry_count', 'failure_reason']);
        });
    }
};