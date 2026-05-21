<?php
// CodeIgniter 4 - Root wrapper for Hostinger LiteSpeed
// This file acts as entry point at document root since .htaccess rewrites don't work on this server
// We need to change to the public directory before including index.php so CodeIgniter's
// path constants (FCPATH, SYSPATH, etc) are set correctly

chdir(__DIR__ . '/public');
require_once __DIR__ . '/public/index.php';
