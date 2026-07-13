<?php

use CodeIgniter\Test\CIUnitTestCase;
use App\Models\MstSekolahModel;

/**
 * @internal
 */
final class SekolahTest extends CIUnitTestCase
{
    public function testSekolahModelAllowedFields(): void
    {
        $model = new MstSekolahModel();
        $allowedFields = self::getPrivateProperty($model, 'allowedFields');
        $this->assertContains('paket_id', $allowedFields);
    }
}
