<?php
namespace Consts;

/**
 * XML version string
 * @var string
 */
const VERSION = '1.0';
/**
 * XML encoding / charset
 * @var string
 */
const ENCODING = 'UTF-8';

/**
 * `xmlns` attribute
 * @var string
 */
const XMLNS = 'http://www.w3.org/2000/svg';

/**
 * Exit status if all tests passed
 * @var integer
 */
const VALID = 0;

/**
 * Exit status if any tests failed
 * @var integer
 */
const INVALID = 1;
/**
 * Attributes that are invalid
 * @var Array
 */
const INVALID_ATTRS = [
	'style',
	['http://www.inkscape.org/namespaces/inkscape', 'version'],
];

/**
 * Attributes that are required on `<svg>` elements
 * @var Array
 */
const REQUIRED_ATTRS = [
	'viewBox',
	'xmlns',
];

/**
 * Invalid `<svg>` child node tagnames
 * @var Array
 */
const INVALID_TAGS = [
	'image',
];

/**
 * Extensions to lint
 * @var Array
 */
const EXTS = [
	'svg',
];
