<?php

use App\Helpers\DatabaseHelper;
use Illuminate\Container\Attributes\Database;
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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->integer('external_id')->unique();
            $table->string('title');
            $table->decimal('price', 10, 2);
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->float('rating')->default(0);
            $table->integer('reviews_count')->default(0);
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['title', 'category_id']);
            $table->index('category_id');
            $table->index('price');
            $table->index('rating');
            $table->index('reviews_count');
            $table->index('external_id');

            if (DatabaseHelper::supportsFullTextSearch()) {
                $table->fullText(['title', 'description']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
