<?php namespace Lovata\ApiSynchronization\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

/**
 * Create shipping document positions table
 */
class CreateShippingDocumentPositionsTable extends Migration
{
    public function up()
    {
        Schema::create('lovata_apisync_shipping_document_positions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shipping_document_id');
            $table->bigInteger('seipee_position_id');
            $table->unsignedBigInteger('order_position_id')->nullable();
            $table->unsignedBigInteger('offer_id')->nullable();
            $table->string('product_code', 100);
            $table->string('description', 500)->nullable();
            $table->string('variant', 255)->nullable();
            $table->string('unit_of_measure', 20)->default('NR');
            $table->decimal('quantity', 15, 8)->default(0);
            $table->decimal('deliverable_quantity', 15, 8)->default(0);
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('total_price', 15, 2)->default(0);
            $table->string('discount', 50)->nullable();
            $table->dateTime('delivery_date')->nullable();
            $table->boolean('is_fully_delivered')->default(false);
            $table->text('property')->nullable();
            $table->timestamps();

            // Foreign key
            $table->foreign('shipping_document_id', 'fk_ship_doc_pos')
                ->references('id')
                ->on('lovata_apisync_shipping_documents')
                ->onDelete('cascade');

            // Indexes with short names
            $table->index('shipping_document_id', 'idx_ship_doc_id');
            $table->index('seipee_position_id', 'idx_seipee_pos_id');
            $table->index('order_position_id', 'idx_order_pos_id');
            $table->index('product_code', 'idx_product_code');
            $table->index('is_fully_delivered', 'idx_is_delivered');
        });
    }

    public function down()
    {
        Schema::dropIfExists('lovata_apisync_shipping_document_positions');
    }
}
