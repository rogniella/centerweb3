<?php

namespace Tests\Unit;

use App\clases\ws_afip;
use PHPUnit\Framework\TestCase;

class CondicionIvaReceptorTest extends TestCase
{
    public function test_mapea_condiciones_a_ids_de_arca(): void
    {
        $this->assertSame(1, ws_afip::condicionIvaReceptorId('RI'));
        $this->assertSame(4, ws_afip::condicionIvaReceptorId('EX'));
        $this->assertSame(5, ws_afip::condicionIvaReceptorId('CF'));
        $this->assertSame(6, ws_afip::condicionIvaReceptorId('MO'));
        $this->assertSame(6, ws_afip::condicionIvaReceptorId('MT'));
        $this->assertSame(6, ws_afip::condicionIvaReceptorId('mt'));
    }

    public function test_defaults_a_consumidor_final(): void
    {
        $this->assertSame(5, ws_afip::condicionIvaReceptorId(''));
        $this->assertSame(5, ws_afip::condicionIvaReceptorId(null));
        $this->assertSame(5, ws_afip::condicionIvaReceptorId('DESCONOCIDO'));
    }

    public function test_wsdl_production_declara_condicion_iva_receptor(): void
    {
        $path = dirname(__DIR__, 2).'/vendor/adrianbarabino/afip-php-libre/src/Afip_res/wsfe-production.wsdl';

        if (! file_exists($path)) {
            $this->markTestSkipped('WSDL de production no encontrado');
        }

        $xml = simplexml_load_file($path);
        $this->assertNotFalse($xml);
        $elements = $xml->xpath("//*[local-name()='element'][@name='CondicionIVAReceptorId']");
        $this->assertCount(1, $elements);
    }

    public function test_wsdl_homologacion_declara_condicion_iva_receptor(): void
    {
        $path = dirname(__DIR__, 2).'/vendor/adrianbarabino/afip-php-libre/src/Afip_res/wsfe.wsdl';

        if (! file_exists($path)) {
            $this->markTestSkipped('WSDL de homologación no encontrado');
        }

        $xml = simplexml_load_file($path);
        $this->assertNotFalse($xml);
        $elements = $xml->xpath("//*[local-name()='element'][@name='CondicionIVAReceptorId']");
        $this->assertCount(1, $elements);
    }
}