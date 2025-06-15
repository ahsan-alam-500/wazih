<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('order_number')->unique();
            $table->decimal('total_amount', 12, 2);
            $table->string('source')->default('web');
            $table->string('payment_status')->default('unpaid');
            $table->enum('status', ['pending', 'processing', 'completed', 'in_courier', 'shipped', 'delivered', 'abandoned', 'cancelled', 'returned'])->default('pending');
            $table->text('shipping_address')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
