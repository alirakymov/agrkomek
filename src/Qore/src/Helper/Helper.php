<?php

namespace Qore\Helper;

class Helper
{
    /**
     * translit
     *
     * @param string $_string
     */
    public function translit(string $_string) : string
    {

		$_string = preg_replace('/[^a-zа-я0-9\- ]+/iu', '', $_string);

		$translit = array (
			'ый' => 'iy',
			'а'  => 'a',  'б' => 'b', 'в' => 'v',  'г' => 'g',  'д' => 'd',  'е' => 'e',  'ё' => 'e',   'ж' => 'zh', 'з' => 'z', 'и' => 'i',
			'й'  => 'y',  'к' => 'k', 'л' => 'l',  'м' => 'm',  'н' => 'n',  'о' => 'o',  'п' => 'p',   'р' => 'r',  'с' => 's', 'т' => 't',
			'у'  => 'u',  'ф' => 'f', 'х' => 'h',  'ц' => 'ts', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sch', 'ъ' => '',   'ы' => 'y', 'ь' => '',
			'э'  => 'e',  'ю' => 'y', 'я' => 'ya', ' ' => '-',
			//'ә' => 'a', 'ғ' => 'gh', 'һ' => 'h', 'ң' => 'ng', 'ө' => 'o', 'ү' => 'u'
		);

		return strtr(mb_strtolower($_string, 'utf8'), $translit);
    }
}
