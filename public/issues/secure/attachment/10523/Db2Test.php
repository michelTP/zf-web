<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Db
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

require_once 'Zend/Db/Statement/TestCommon.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__);

class Zend_Db_Statement_Db2Test extends Zend_Db_Statement_TestCommon
{

    public function testStatementConstruct()
    {
        $select = $this->_db->select()
            ->from('zfproducts');
        $sql = $select->__toString();
        $stmt = new Zend_Db_Statement_Db2($this->_db, $sql);
        $this->assertType('Zend_Db_Statement_Db2', $stmt);
    }

    public function testStatementConstructWithSelectObject()
    {
        $select = $this->_db->select()
            ->from('zfproducts');
        $stmt = new Zend_Db_Statement_Db2($this->_db, $select);
        $this->assertType('Zend_Db_Statement_Interface', $stmt);
        $stmt->closeCursor();
    }

    public function testStatementErrorCodeKeyViolation()
    {
        $this->markTestIncomplete($this->getDriver() . ' does not return error codes correctly.');
    }

    public function testStatementErrorInfoKeyViolation()
    {
        $this->markTestIncomplete($this->getDriver() . ' does not return error codes correctly.');
    }

    public function testStatementColumnCountForSelect()
    {
        $this->markTestIncomplete($this->getDriver() . ' gets the wrong result in this test.');
    }

    public function testStatementBindParamByPosition()
    {
        $this->markTestIncomplete($this->getDriver() . ' crashes when binding params');
    }

    public function testStatementBindParamByName()
    {
        $products = $this->_db->quoteIdentifier('zfproducts');
        $product_id = $this->_db->quoteIdentifier('product_id');
        $product_name = $this->_db->quoteIdentifier('product_name');

        $productIdValue   = 4;
        $productNameValue = 'AmigaOS';
       
        try {
            $stmt = $this->_db->prepare("INSERT INTO $products ($product_id, $product_name) VALUES (:id, :name)");
            // test with colon prefix
            $this->assertTrue($stmt->bindParam(':id', $productIdValue), 'Expected bindParam(\':id\') to return true');
            // test with no colon prefix
            $this->assertTrue($stmt->bindParam('name', $productNameValue), 'Expected bindParam(\'name\') to return true');
            $this->fail('Expected to catch Zend_Db_Statement_Exception');
        } catch (Zend_Exception $e) {
            $this->assertType('Zend_Db_Statement_Exception', $e,
                'Expecting object of type Zend_Db_Statement_Exception, got '.get_class($e));
            $this->assertEquals("Invalid bind-variable position ':id'", $e->getMessage());
        }
    }

    public function testStatementBindValueByPosition()
    {
        $this->markTestIncomplete($this->getDriver() . ' crashes when binding params');
    }

    public function testStatementBindValueByName()
    {
        $products = $this->_db->quoteIdentifier('zfproducts');
        $product_id = $this->_db->quoteIdentifier('product_id');
        $product_name = $this->_db->quoteIdentifier('product_name');

        $productIdValue   = 4;
        $productNameValue = 'AmigaOS';

        try {
            $stmt = $this->_db->prepare("INSERT INTO $products ($product_id, $product_name) VALUES (:id, :name)");
            // test with colon prefix
            $this->assertTrue($stmt->bindParam(':id', $productIdValue), 'Expected bindParam(\':id\') to return true');
            // test with no colon prefix
            $this->assertTrue($stmt->bindParam('name', $productNameValue), 'Expected bindParam(\'name\') to return true');
            $this->fail('Expected to catch Zend_Db_Statement_Exception');
        } catch (Zend_Exception $e) {
            $this->assertType('Zend_Db_Statement_Exception', $e,
                'Expecting object of type Zend_Db_Statement_Exception, got '.get_class($e));
            $this->assertEquals("Invalid bind-variable position ':id'", $e->getMessage());
        }
    }

    public function getDriver()
    {
        return 'Db2';
    }

}