<?php

use App\Enums\WordPressSiteStatus;
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
        Schema::create('wordpress_sites', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('domain')->unique();
            $table->string('container_name')->unique();
            $table->string('server_host');
            $table->unsignedSmallInteger('server_port')->default(22);
            $table->string('server_user')->default('root');
            $table->enum('auth_type', ['password', 'key'])->default('key');
            $table->text('server_password')->nullable();
            $table->longText('server_private_key')->nullable();
            $table->string('docker_image')->default('wordpress:latest');
            $table->string('database_name');
            $table->string('database_username');
            $table->text('database_password');
            $table->string('status')->default(WordPressSiteStatus::Deploying->value);
            $table->json('environment')->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('deployed_at')->nullable();
            $table->timestamp('last_health_check_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wordpress_sites');
    }
};
