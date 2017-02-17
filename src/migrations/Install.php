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
            'uri' => $this->string()->notNull(),
            'username' => $this->string()->notNull(),
            'password' => $this->string()->notNull(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        //Create Index
        $this->createIndex($this->db->getIndexName('{{%pw_routes}}', 'uri', true), '{{%pw_routes}}', 'uri', true);

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