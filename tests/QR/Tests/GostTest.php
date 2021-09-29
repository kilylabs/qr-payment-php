<?php

namespace Tests\Unit\Kily\Payment\QR;

use Kily\Payment\QR\Gost;
use Kily\Payment\QR\Exception;
use PHPUnit\Framework\TestCase;

/**
 * Class GostTest.
 *
 * @covers \Kily\Payment\QR\Gost
 */
class GostTest extends TestCase
{
    /**
     * @var Gost
     */
    protected $gost;

    /**
     * @var mixed
     */
    protected $name;

    /**
     * @var mixed
     */
    protected $pacc;

    /**
     * @var mixed
     */
    protected $bankname;

    /**
     * @var mixed
     */
    protected $bic;

    /**
     * @var mixed
     */
    protected $cacc;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->gost = new Gost($this->name, $this->pacc, $this->bankname, $this->bic, $this->cacc);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->gost);
        unset($this->name);
        unset($this->pacc);
        unset($this->bankname);
        unset($this->bic);
        unset($this->cacc);
    }

    /**
     * @covers Kily\Payment\QR\Gost::offsetSet
     */

    public function testOffsetSet1(): void
    {
        $this->expectException(Exception::class);
        $this->gost->setThrowExceptions(true);
        $this->gost['Ololo'] = 1;
    }

    /**
     * @covers Kily\Payment\QR\Gost::offsetSet
     */

    public function testOffsetSet2(): void
    {
        $this->gost->setThrowExceptions(false);
        $this->gost['Ololo'] = 1;
        $this->assertNull($this->gost['Ololo']);
        $this->gost['Sum'] = 123;
        $this->assertEquals($this->gost['Sum'], 123);
    }

    /**
     * @covers Kily\Payment\QR\Gost::offsetExists
     */

    public function testOffsetExists(): void
    {
        $this->gost->setThrowExceptions(false);

        $this->gost['Ololo'] = 1;
        $this->assertFalse(isset($this->gost['Ololo']));

        $this->assertFalse(isset($this->gost['Sum']));
        $this->gost['Sum'] = 123;
        $this->assertTrue(isset($this->gost['Sum']));
    }

    /**
     * @covers Kily\Payment\QR\Gost::offsetUnset
     */
    public function testOffsetUnset(): void
    {
        $this->gost->setThrowExceptions(false);

        $this->gost['Sum'] = 123;
        $this->assertTrue(isset($this->gost['Sum']));
        unset($this->gost['Sum']);
        $this->assertFalse(isset($this->gost['Sum']));
    }

    /**
     * @covers Kily\Payment\QR\Gost::offsetGet
     */
    public function testOffsetGet(): void
    {
        $this->gost->setThrowExceptions(false);
        $this->gost['Sum'] = 123;
        $this->assertEquals($this->gost['Sum'], 123);
        $this->gost['Ololo'] = 123;
        $this->assertEquals($this->gost['Ololo'], null);
    }

    /**
     * @covers Kily\Payment\QR\Gost::__get
     */
    public function test__get(): void
    {
        $this->gost->setThrowExceptions(false);
        $this->gost->Sum = 123;
        $this->assertEquals($this->gost->Sum, 123);
        $this->gost->Ololo = 123;
        $this->assertEquals($this->gost->Ololo, null);
    }

    /**
     * @covers Kily\Payment\QR\Gost::__set
     */
    public function test__set1(): void
    {
        $this->expectException(Exception::class);
        $this->gost->setThrowExceptions(true);
        $this->gost->Ololo = 1;
    }

    /**
     * @covers Kily\Payment\QR\Gost::__set
     */
    public function test__set2(): void
    {
        $this->gost->setThrowExceptions(false);
        $this->gost->Ololo = 1;
        $this->assertNull($this->gost->Ololo);
        $this->gost->Sum = 123;
        $this->assertEquals($this->gost->Sum, 123);
    }

    /**
     * @covers Kily\Payment\QR\Gost::render
     */
    public function testRender(): void
    {
        $this->gost->Name = 'OLOLO';
        $this->gost->PersonalAcc = '12312312';
        $this->gost->BankName = 'ОАО КБ АВАНГАРД';
        $this->gost->BIC = 123123;
        $this->gost->CorrespAcc = '12312412';
        $this->assertEquals(md5($this->gost->render()), '20ff40e6b32ed5f63f20065c3b0668de');

        $tmp = tempnam(sys_get_temp_dir(), 'kilygost_');
        $this->gost->render($tmp);
        $contents = file_get_contents($tmp);
        unlink($tmp);
        $this->assertEquals(md5($contents), '20ff40e6b32ed5f63f20065c3b0668de');

        $contents = $this->gost->render(false, ['imageBase64'=>true]);
        $this->assertEquals(md5($contents), '9940ab68a3829ec710446d8fccd7f457');
    }

    /**
     * @covers Kily\Payment\QR\Gost::generate
     */
    public function testGenerate(): void
    {
        $this->gost->Name = 'OLOLO';
        $this->gost->PersonalAcc = '12312312';
        $this->gost->BankName = 'ОАО КБ АВАНГАРД';
        $this->gost->BIC = 123123;
        $this->gost->CorrespAcc = '12312412';
        $this->assertEquals($this->gost->generate(), 'ST00012|Name=OLOLO|PersonalAcc=12312312|BankName=ОАО КБ АВАНГАРД|BIC=123123|CorrespAcc=12312412');
    }

    /**
     * @covers Kily\Payment\QR\Gost::validate
     */
    public function testValidate1(): void
    {
        $this->expectException(Exception::class);
        $this->gost->setThrowExceptions(true);

        //test exception on REQUIRED attrs
        $this->gost->Name = 'OLOLO';
        $this->gost->PersonalAcc = '12312312';
        $this->gost->BankName = 'ОАО КБ АВАНГАРД';
        //$this->gost->BIC = 123123;
        $this->gost->CorrespAcc = '12312412';
        $this->gost->validate();
    }

    /**
     * @covers Kily\Payment\QR\Gost::validate
     */
    public function testValidate2(): void
    {
        $this->expectException(Exception::class);
        $this->gost->setThrowExceptions(true);

        //test exception on RegEx attrs
        $this->gost->Name = 'OLOLO';
        $this->gost->PersonalAcc = '12312312';
        $this->gost->BankName = 'ОАО КБ АВАНГАРД';
        $this->gost->BIC = 123123;
        $this->gost->CorrespAcc = '12312412';
        $this->gost->Sum = 'asdasdasd';
        $this->gost->validate();
    }

    /**
     * @covers Kily\Payment\QR\Gost::validate
     */
    public function testValidate3(): void
    {
        $this->expectException(Exception::class);
        $this->gost->setThrowExceptions(true);

        //test exception on RegEx attrs
        $this->gost->Name = 'OLOLO';
        $this->gost->PersonalAcc = '12312312';
        $this->gost->BankName = 'ОАО КБ АВАНГАРД';
        $this->gost->BIC = 123123;
        $this->gost->CorrespAcc = '12312412';
        $this->gost->TechCode = '100500';
        $this->gost->validate();
    }

    /**
     * @covers Kily\Payment\QR\Gost::validate
     */
    public function testValidate4(): void
    {
        $this->gost->setThrowExceptions(false);

        $this->gost->Name = 'OLOLO';
        $this->gost->PersonalAcc = '12312312';
        $this->gost->BankName = 'ОАО КБ АВАНГАРД';
        $this->gost->BIC = 123123;
        $this->gost->CorrespAcc = '12312412';
        $this->gost->Sum = '100500';
        $this->gost->TechCode = '01';
        $this->assertTrue($this->gost->validate());
    }

    public function testIsValid(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testIsValidKey(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testSetThrowExceptions(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }
}
