<?php

namespace envoy\passwordprotect\migrations;


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

        $this->addForeignKey($this->db->getForeignKeyName('{{%pw_routes}}', 'siteId'), '{{%pw_routes}}', 'siteId', '{{%sites}}', 'id', 'CASCADE', 'CASCADE');
        $this->createIndex($this->db->getIndexName('{{%pw_routes}}', 'uriPattern', true), '{{%routes}}', 'uriPattern', true);
        $this->createIndex($this->db->getIndexName('{{%pw_routes}}', 'siteId', false, true), '{{%routes}}', 'siteId', false);

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