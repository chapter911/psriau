#!/usr/bin/env bash
set -euo pipefail

BASE='http://127.0.0.1:8081'
COOKIE='/tmp/simak_konsultasi_custom_cookie.txt'
LOGIN_HTML='/tmp/simak_konsultasi_custom_login.html'
PAGE_HTML='/tmp/simak_konsultasi_custom_page.html'
IMPORT_HEADERS='/tmp/simak_konsultasi_custom_import_headers.txt'
IMPORT_BODY='/tmp/simak_konsultasi_custom_import_body.html'
CUSTOM_FILE='/tmp/simak_konsultasi_custom_from_contoh.xlsx'
NOMOR="SMOKE/JK/CUSTOM/$(date +%Y%m%d%H%M%S)"

echo "CUSTOM_NOMOR=$NOMOR"

php -r 'require "vendor/autoload.php"; $nomor=$argv[1]; $file=$argv[2]; $ss=new \PhpOffice\PhpSpreadsheet\Spreadsheet(); $sh=$ss->getActiveSheet(); $sh->setTitle("Daftar SIMAK JK (>100juta)"); $headers=["satker","ppk_nip","ppk_nama","jenis_pekerjaan_jasa_konsultansi","nama_paket","masa_pelaksanaan","tahun_anggaran","pagu_anggaran","penyedia","nomor_kontrak","nilai_kontrak","metode_pemilihan","tahapan_pekerjaan","tanggal_pemeriksaan","keterangan"]; $row=["Perencanaan Prasarana Strategis","199012212018021001","Agung Justika Indra Kesuma, ST","Perencanaan","Paket Konsultansi Contoh Custom","SYC","2026 - 2027",350000000,"PT Penyedia Contoh",$nomor,200000000,"Seleksi","Tahap Persiapan",date("Y-m-d"),"Custom dari contoh SIMAK JK"]; $sh->fromArray($headers,null,"A1"); $sh->fromArray($row,null,"A2"); $writer=new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($ss); $writer->save($file);' "$NOMOR" "$CUSTOM_FILE"

file "$CUSTOM_FILE"

curl -sS --max-time 10 -c "$COOKIE" "$BASE/masuk" -o "$LOGIN_HTML"
CSRF_NAME=$(perl -ne 'if(/name="([^"]+)" value="([^"]+)"/){print $1; exit}' "$LOGIN_HTML")
CSRF_VALUE=$(perl -ne 'if(/name="([^"]+)" value="([^"]+)"/){print $2; exit}' "$LOGIN_HTML")

curl -sS --max-time 15 -b "$COOKIE" -c "$COOKIE" -X POST "$BASE/masuk" \
  --data-urlencode 'username=199011092025061005' \
  --data-urlencode 'password=123456' \
  --data-urlencode "$CSRF_NAME=$CSRF_VALUE" \
  -D /tmp/simak_konsultasi_custom_login_headers.txt \
  -o /tmp/simak_konsultasi_custom_login_result.html

curl -sS --max-time 10 -b "$COOKIE" -c "$COOKIE" "$BASE/admin/kontrak/simak/konsultasi" -o "$PAGE_HTML"
CSRF_NAME=$(perl -ne 'if(/name="([^"]+)" value="([^"]+)"/){print $1; exit}' "$PAGE_HTML")
CSRF_VALUE=$(perl -ne 'if(/name="([^"]+)" value="([^"]+)"/){print $2; exit}' "$PAGE_HTML")

curl -sS --max-time 20 -b "$COOKIE" -c "$COOKIE" -D "$IMPORT_HEADERS" \
  -F "$CSRF_NAME=$CSRF_VALUE" \
  -F "file_import=@$CUSTOM_FILE;type=application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" \
  "$BASE/admin/kontrak/simak/konsultasi/import" \
  -o "$IMPORT_BODY"

STATUS=$(head -n 1 "$IMPORT_HEADERS" | awk '{print $2}')
LOCATION=$(grep -i '^Location:' "$IMPORT_HEADERS" | tr -d '\r' | awk '{print $2}')

echo "IMPORT_STATUS=$STATUS"
echo "IMPORT_LOCATION=$LOCATION"

php -r '$nomor=$argv[1]; $db=new mysqli("srv1515.hstgr.io","u763926118_pps_riau_dev","x8R4>OB$","u763926118_pps_riau_dev",3306); if($db->connect_errno){fwrite(STDERR,$db->connect_error.PHP_EOL); exit(1);} $stmt=$db->prepare("SELECT id, nomor_kontrak, nama_paket, jenis_pekerjaan_jasa_konsultansi, masa_pelaksanaan, metode_pemilihan, created_at FROM trn_kontrak_simak_konsultasi WHERE nomor_kontrak=? ORDER BY id DESC LIMIT 1"); $stmt->bind_param("s",$nomor); $stmt->execute(); $res=$stmt->get_result(); $row=$res->fetch_assoc(); if($row){ echo "DB_INSERTED=YES\n"; echo json_encode($row, JSON_UNESCAPED_UNICODE)."\n"; } else { echo "DB_INSERTED=NO\n"; }' "$NOMOR"
