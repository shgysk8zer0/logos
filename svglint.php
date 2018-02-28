<?php
namespace SVGLint;
use function \Functions\{lint_dir};
use const \Consts\{VALID, INVALID};
require_once __DIR__ . DIRECTORY_SEPARATOR . 'functions.php';
exit(lint_dir(__DIR__) ? VALID : INVALID);
