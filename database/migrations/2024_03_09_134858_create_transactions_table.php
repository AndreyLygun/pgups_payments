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
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id');
            $table->timestamps();
            $table->string('user_id');
            $table->string('user_mail');
            $table->integer('payment_amount');
            $table->string('redirect_url')->comment("Страница, куда перенаправить пользователя после платежа");
            $table->string('payment_id')->nullable()->comment("ID платежа в платёжной системе");
            $table->string('description')->nullable();
            $table->dateTime('pushed2PaperCut')->comment('Время отправки данных о платеже в PaperCut')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
