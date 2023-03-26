<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameCommentIdToParentIdOnCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->dropForeign('comments_comment_id_foreign');
            $table->dropIndex('comments_comment_id_foreign');

            $table->renameColumn('comment_id', 'parent_id');

            $table->foreign('parent_id')->references('id')->on('comments');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->dropForeign('comments_parent_id_foreign');
            $table->dropIndex('comments_parent_id_foreign');

            $table->renameColumn('parent_id', 'comment_id');

            $table->foreign('comment_id')->references('id')->on('comments');
        });
    }
}
