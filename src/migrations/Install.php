<?php

namespace weareenvoy\passwordroutes\migrations;


use craft\db\Migration;

class Install extends Migration
{
    /**
     *
     */
    public function safeUp()
    {
        $this->dropTableIfExists('{{%pw_routes}}');
        $this->createTable('{{%pw_routes}}', [
            'id' => $this->primaryKey(),
            'siteId' => $this->integer(),
            'uriPattern' => $this->string()->notNull(),
            'dateCreated' => $this->dateTime()->notNull(),
            'username' => $this->string()->notNull(),
            'password' => $this->string()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createIndex($this->db->getIndexName('{{%pw_routes}}', 'uriPattern', true), '{{%pw_routes}}', 'uriPattern', true);
        $this->createIndex($this->db->getIndexName('{{%pw_routes}}', 'siteId', false, true), '{{%pw_routes}}', 'siteId', false);

        $this->addForeignKey($this->db->getForeignKeyName('{{%pw_routes}}', 'siteId'), '{{%pw_routes}}', 'siteId', '{{%sites}}', 'id', 'CASCADE', 'CASCADE');

        return true;
    }

    /**
     *
     */
    public function safeDown()
    {
        $this->dropTableIfExists('{{%pw_routes}}');
        return true;
    }

}