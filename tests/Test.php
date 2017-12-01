<?php
/**
 * Created by PhpStorm.
 * User: Andrey Mistulov
 * Company: Aristos
 * Date: 01.12.2017
 * Time: 8:45
 */

use Prowebcraft\Dot as Dot;

class Test extends \PHPUnit\Framework\TestCase
{
    
    public function testGetValue()
    {
        $array = [
            'a' => 1,
            'b' => [
                'bc' => 2
            ]
        ];
        $this->assertEquals(1, Dot::getValue($array, 'a'));
        $this->assertInternalType('array', Dot::getValue($array, 'b'));
        $this->assertEquals(2, Dot::getValue($array, 'b.bc'));
        $this->assertNull(Dot::getValue($array, 'notexist'));
        $this->assertNull(Dot::getValue($array, 'notexist.deep'));
        $this->assertFalse(Dot::getValue($array, 'test-default-false', false));
    }

    public function testGetValueObject()
    {
        $array = [
            'a' => 1,
            'b' => [
                'bc' => 2
            ]
        ];
        $dot = new Dot($array);
        $this->assertEquals(1, $dot->get('a'));
        $this->assertInternalType('array', $dot->get('b'));
        $this->assertEquals(2, $dot->get('b.bc'));
        $this->assertNull($dot->get('notexist'));
        $this->assertNull($dot->get('notexist.deep'));
        $this->assertFalse($dot->get('test-default-false', false));
    }

    public function testSetValue()
    {
        $array = [
            'a' => 33,
            'b' => [
                'bc' => 33,
                'z' => 1
            ]
        ];
        Dot::setValue($array, 'a', 1);
        Dot::setValue($array, 'b', [
            'bc' => 2
        ]);
        $this->assertEquals(1, Dot::getValue($array, 'a'));
        $this->assertInternalType('array', Dot::getValue($array, 'b'));
        $this->assertNull(Dot::getValue($array, 'b.z'));
        $this->assertEquals(2, Dot::getValue($array, 'b.bc'));
    }

    public function testAddValue()
    {
        $array = [
            'a' => 1
        ];

        Dot::addValue($array, 'b', 'one');
        $this->assertInternalType('array', Dot::getValue($array, 'b'));
        $this->assertCount(1, Dot::getValue($array, 'b'));
        Dot::addValue($array, 'b', 'two');
        $this->assertCount(2, Dot::getValue($array, 'b'));

        //Rewriting
        Dot::addValue($array, 'a', 'one');
        $this->assertInternalType('array', Dot::getValue($array, 'a'));
        $this->assertCount(1, Dot::getValue($array, 'a'));
    }

    public function testAddValueObject()
    {
        $array = [
            'a' => 1
        ];
        $dot = new Dot($array);
        $dot->add('b', 'one');
        $this->assertInternalType('array', $dot->get('b'));
        $this->assertCount(1, $dot->get('b'));
        $dot->add('b', 'two');
        $this->assertCount(2, $dot->get('b'));

        //Rewriting
        $dot->add('a', 'one');
        $this->assertInternalType('array', $dot->get('a'));
        $this->assertCount(1, $dot->get('a'));
    }
}
