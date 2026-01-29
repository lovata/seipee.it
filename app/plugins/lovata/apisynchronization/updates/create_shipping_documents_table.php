<?php namespace Lovata\ApiSynchronization\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

/**
 * Create shipping documents table
 */
class CreateShippingDocumentsTable extends Migration
{
    public function up()
    {
        Schema::create('lovata_apisync_shipping_documents', function (Blueprint $table) {
            $table->id();
            $table->string('seipee_document_id')->unique();
            $table->string('document_number', 50);
            $table->dateTime('document_date')->nullable();
            $table->string('document_type_code', 10);
            $table->string('document_type_description', 255)->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('customer_code', 50)->nullable();
            $table->string('payment_type', 50)->nullable();
            $table->integer('rows_count')->default(0);
            $table->decimal('total_excl_vat', 15, 2)->default(0);
            $table->decimal('total_incl_vat', 15, 2)->default(0);
            $table->boolean('is_fully_delivered')->default(false);
            $table->string('pdf_url', 500)->nullable();
            $table->text('property')->nullable();
            $table->timestamps();

            // Indexes with short names
            $table->index('seipee_document_id', 'idx_seipee_doc_id');
            $table->index('document_number', 'idx_doc_number');
            $table->index('document_date', 'idx_doc_date');
            $table->index('customer_code', 'idx_customer_code');
            $table->index('is_fully_delivered', 'idx_delivered');
        });
    }

    public function down()
    {
        Schema::dropIfExists('lovata_apisync_shipping_documents');
    }
}
