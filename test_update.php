<?php
$ch = curl_init('http://localhost:8080/admin/master/kop-surat/1/ubah');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
// Mock session? We need to bypass auth or login first.
// The controllers have `denyIfNoMenuAccess()` which uses `session()->get('role')`.
// So curl won't work without a valid session.
