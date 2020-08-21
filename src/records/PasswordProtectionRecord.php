<?php
/**
 * Password Protection plugin for Craft CMS 3.x
 *
 * Password protect any page on the CMS.
 *
 * @link      imarc.com
 * @copyright Copyright (c) 2020 Imarc
 */

namespace Imarc\Craft\PasswordProtection\Records;

use Craft;
use craft\db\ActiveRecord;

/**
 * PasswordProtectionRecord Record
 *
 * ActiveRecord is the base class for classes representing relational data in terms of objects.
 *
 * Active Record implements the [Active Record design pattern](http://en.wikipedia.org/wiki/Active_record).
 * The premise behind Active Record is that an individual [[ActiveRecord]] object is associated with a specific
 * row in a database table. The object's attributes are mapped to the columns of the corresponding table.
 * Referencing an Active Record attribute is equivalent to accessing the corresponding table column for that record.
 *
 * http://www.yiiframework.com/doc-2.0/guide-db-active-record.html
 *
 * @author    Imarc
 * @package   PasswordProtection
 * @since     1.0.0
 */
class PasswordProtectionRecord extends ActiveRecord
{
    protected static $modifiable_fields = ['entryId', 'passwordProtectionEnabled', 'password'];

    // Public Static Methods
    // =========================================================================

     /**
     * Declares the name of the database table associated with this AR class.
     * By default this method returns the class name as the table name by calling [[Inflector::camel2id()]]
     * with prefix [[Connection::tablePrefix]]. For example if [[Connection::tablePrefix]] is `tbl_`,
     * `Customer` becomes `tbl_customer`, and `OrderItem` becomes `tbl_order_item`. You may override this method
     * if the table is not named after this convention.
     *
     * By convention, tables created by plugins should be prefixed with the plugin
     * name and an underscore.
     *
     * @return string the table name
     */
    public static function tableName()
    {
        return '{{%imarc_password_protection_record}}';
    }


    /**
     * Create a new record from post data, filtered by $modifiable_fields.
     *
     * @param array $fields
     * @return void
     */
    public static function updateRecord($fields, PasswordProtectionRecord $record = null): ?self
    {
        //Allow creating only by the modifiable fields
        $fields = array_intersect_key($fields, array_flip(self::$modifiable_fields));

        if (!$record) {
            $record = new Self();
        }

        foreach($fields as $fieldName=>$value) {
            $record->$fieldName = $value;
        }

        $record->save();

        return $record;
    }


    public static function findByEntryId($entryId) {
        return self::findOne([
            'entryId' => $entryId
        ]);
    }
}
