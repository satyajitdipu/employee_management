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
        Schema::create('performance_reviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('reviewer_id'); // Manager or HR
            $table->string('review_period'); // e.g., '2024-Q1', '2024-H1', '2024-Annual'
            $table->date('review_date');
            $table->decimal('overall_rating', 3, 1)->nullable(); // 1.0 to 5.0
            $table->json('category_ratings')->nullable(); // JSON object with category scores
            $table->text('strengths')->nullable();
            $table->text('areas_for_improvement')->nullable();
            $table->text('goals')->nullable();
            $table->text('reviewer_comments')->nullable();
            $table->text('employee_comments')->nullable();
            $table->string('status')->default('draft'); // draft, submitted, reviewed, completed
            $table->date('due_date')->nullable();
            $table->json('attachments')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('reviewer_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['employee_id', 'review_period']);
            $table->index(['reviewer_id', 'status']);
            $table->index('review_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('performance_reviews');
    }
};
