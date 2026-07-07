<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('alert_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category');
            $table->string('priority')->default('normal');
            $table->text('content');
            $table->json('placeholders')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('slack_messages', function (Blueprint $table) {
            $table->foreignId('template_id')->nullable()->constrained('alert_templates')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('slack_messages', function (Blueprint $table) {
            $table->dropForeign(['template_id']);
            $table->dropColumn('template_id');
        });
        
        Schema::dropIfExists('alert_templates');
    }
};