<?php

/*
 * This file is part of the Jasny extension on Symfony.
 *
 * (c) Arnold Daniels <arnold@jasny.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jasny\Bundle\ORMBundle\Helpers;

use Jasny\Bundle\ORMBundle\SluggerInterface;

/**
 * Object Service that generates a slug based on incoming fields.
 * e.g. "My Test" will return "my-test" ("my-test-1" on duplicate)
 */
class Slugger implements AutoReferenceGenerator
{
    private $space;
    private $glue;
    
    /**
     * @param string $space  Replacement character for space
     * @param string $glue   Glue when source is an array
     */
    public function __construct($space = '-', $glue = '-')
    {
        $this->space = $space;
        $this->glue = $glue;
    }


	/**
	 * Return a slug, ensuring it does not appear in exclude (prior collisions).
     * 
	 * @param array|string $values
	 * @param array        $exclude  List of slugs to exclude
	 */
	public function generate($values, array $exclude = array())
	{
		$parts = array();

        foreach ((array)$values as $slug) {
            // Add special treatment for 's i.e. "Sam's" becomes "Sams"
            $slug = preg_replace('~\'s\b~', 's$1', $slug);
		
            $slug = preg_replace('~[^\\pL\d]+~u', $this->space, $slug);
            $slug = trim($slug, $this->space);

            $parts[] = $slug;
        }
        
        $slug = join($this->glue, array_filter($parts));
        $slug = strtolower(preg_replace('~[^-\w]+~', '', self::convertToAscii($slug)));

		// Fall-back to produce something (6 random letters)
		if (!trim($slug)) $slug = base_convert(mt_rand(60466176, min(2176782335, mt_getrandmax())), 10, 36);

		// Append an index to the slug and see if we can generate a unique value
		$loop = 1;
        $test = $slug;
		while(in_array($test, $exclude)) $test = $slug . ($this->glue . $loop++);
		$slug = $test;

		// We have our unique slug suggestion
		return $slug;
	}
    
    /**
     * Convert international characters to Ascii ones.
     * 
     * @param type $string
     * @return type 
     */
    public static function convertToAscii($string)
    {
        $table = array(
            'Š'=>'S', 'š'=>'s', 'Đ'=>'Dj', 'đ'=>'dj', 'Ž'=>'Z', 'ž'=>'z', 'Č'=>'C', 'č'=>'c', 'Ć'=>'C', 'ć'=>'c',
            'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
            'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O',
            'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss',
            'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e',
            'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o',
            'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b',
            'ÿ'=>'y', 'Ŕ'=>'R', 'ŕ'=>'r',
        );

        return strtr($string, $table);
    }    
}
