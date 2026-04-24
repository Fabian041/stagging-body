<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pis_scan_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('pis_scan_detail_id');
            $table->string('label')->nullable();
            $table->timestamp('scan_time')->useCurrent();
            $table->timestamps();

            $table->foreign('pis_scan_detail_id')
                ->references('id')
                ->on('pis_scan_details')
                ->onDelete('CASCADE')
                ->onUpdate('CASCADE');

            // Performa: query history per detail, urut terbaru
            $table->index(['pis_scan_detail_id', 'scan_time'], 'pis_scan_logs_detail_time_idx');
            // Opsional untuk pencarian label cepat
            $table->index('label', 'pis_scan_logs_label_idx');
        });
    }

    public function down()
    {
        Schema::dropIfExists('pis_scan_logs');
    }
};

