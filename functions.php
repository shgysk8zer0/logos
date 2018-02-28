<?php
namespace Functions;
use const \Consts\{
	VERSION,
	ENCODING,
	XMLNS,
	REQUIRED_ATTRS,
	INVALID_ATTRS,
	INVALID_TAGS,
	EXTS
};
use \FilesystemIterator;
use \RecursiveDirectoryIterator as Directory;
use \DOMDocument as SVG;
use \DOMElement as Element;
use \DOMNodelist as NodeList;
use \SplFileObject as File;
use \Throwable;
use \Error;
require_once(__DIR__ . DIRECTORY_SEPARATOR . 'consts.php');
/**
 * Lint an SVG that has been loaded into a `DOMDocument`
 * @param  SVG  $svg SVG loaded as a `DomDocument`
 * @return Bool      Whether or not it is valid
 */
function lint_svg(SVG $svg, String $name): Bool
{
	$valid = true;
	try {
		if ($svg->documentElement->tagName !== 'svg') {
			throw new Error('Not a valid SVG');
		} elseif (! has_required_attrs($svg->documentElement)) {
			throw new Error(sprintf('Does not have all required attributes: [%s]', join(', ', REQUIRED_ATTRS)));
		} else {
			lint_nodes($svg->getElementsByTagName('*'));
		}
	} catch (Throwable $e) {
		$valid = false;
		echo "{$e->getMessage()} in '{$name}'" . PHP_EOL;
	} finally {
		return $valid;
	}
}
/**
 * Check that an `<svg>` has all required attributes
 * @param  SVG  $svg The `<svg>` / documentElement
 * @return Bool      Whether or not it is has all required attributes
 */
function has_required_attrs(Element $svg, Array $attrs = REQUIRED_ATTRS): Bool
{
	$valid = true;
	foreach ($attrs as $attr) {
		if (! $svg->hasAttribute($attr)) {
			$valid = false;
			Throw new Error("Missing '{$attr}' attribute");
			break;
		}
	}
	return $valid;
}
/**
 * Check that all elements of SVG are valid
 * @param  NodeList $nodes All elements of an `<svg>`
 * @return Bool            Whether or not they are valid
 */
function lint_nodes(NodeList $nodes): Bool
{
	$valid = true;
	foreach ($nodes as $node) {
		if (! lint_node($node)) {
			$valid = false;
			break;
		}
	}
	return $valid;
}
/**
 * Checks that an element in an SVG is a valid element and does not have invalid attributes
 * @param  Element $node An element from an `<svg>`
 * @return Bool          Whether or not the element is valid
 */
function lint_node(Element $node): Bool
{
	$valid = true;
	if (in_array($node->tagName, INVALID_TAGS)) {
		throw new Error("Invalid element <{$node->tagName}>");
		$valid = false;
	} else {
		foreach (INVALID_ATTRS as $attr) {
			if (is_string($attr) and $node->hasAttribute($attr)) {
				// $node->removeAttribute($attr);
				// echo "Removing attribute {$attr}" . PHP_EOL;
				throw new Error("<{$node->tagName}> has invalid attribute, '{$attr}'");
				$valid = false;
				break;
			} elseif (is_array($attr) and $node->hasAttributeNS($attr[0], $attr[1])) {
				throw new Error("<{$node->tagName}> has invalid attribute, '{$attr[0]}:{$attr[1]}'");
				$valid = false;
				break;
			}
		}
	}
	return $valid;
}
/**
 * Recursively lint a directory
 * @param  String   $dir            Directory to lint
 * @param  Array    $exts           Array of extensions to lint in directory
 * @param  Array    $ignore_dirs    Ignore directories in this array
 * @param  Callable $error_callback Callback to call when linting fails
 * @return Bool                     Whether or not all files linted without errors
 * @see https://secure.php.net/manual/en/class.recursiveiteratoriterator.php
 */
function lint_dir(
	String   $dir            = __DIR__,
	Array    $exts           = EXTS,
	Array    $ignore_dirs    = ['.git', 'node_modules'],
	Callable $error_callback = null
): Bool
{
	$path = new Directory($dir, Directory::SKIP_DOTS);
	$valid = true;
	while ($path->valid()) {
		if ($path->isFile() and in_array($path->getExtension(), $exts)) {
			try {
				$svg = new SVG(VERSION, ENCODING);
				$svg->load($path->getPathname());
				if(! lint_svg($svg, $path->getPathName())) {
					$valid = false;
				}
			} catch (Throwable $e) {
				echo "{$e->getMessage()} in {$path->getPathname()}" . PHP_EOL;
				$valid = false;
			}
		} elseif ($path->isDir() and ! in_array($path, $ignore_dirs)) {
			// So long as $dir is the first argument of the function, this will
			// always work, even if the name of the function changes.
			$args = array_slice(func_get_args(), 1);
			if (! call_user_func(__FUNCTION__, $path->getPathName(), ...$args)) {
				$valid = false;
			}
		}
		$path->next();
	}
	return $valid;
}
