<?php
/**
 * This file is part of the package moro/history-common
 *
 * @see https://github.com/Moro4125/history-common
 * @license http://opensource.org/licenses/MIT
 * @author Morozkin Andrey <andrey.dmitrievich@gmail.com>
 */

return [
	'update_value.text'     => '%label% is changed from %old_value% to %new_value%',
	'update_value.html'     => '<strong>%label%</strong> is changed from <ins>%old_value%</ins> to <ins>%new_value%</ins>',
	'set_value.text'        => '%label% is set to value %value%',
	'set_value.html'        => '<strong>%label%</strong> is set to value <ins>%value%</ins>',
	'del_value.text'        => '%label% was delete. The value was %value%',
	'del_value.html'        => '<strong>%label%</strong> was delete. The value was <ins>%value%</ins>',
	'list.prefix.text'      => '%label% is changed: ',
	'list.prefix.html'      => '<strong>%label%</strong> is changed: <span>',
	'list.glue.text'        => ', ',
	'list.glue.html'        => '</span>, <span>',
	'list.suffix.text'      => '.',
	'list.suffix.html'      => '</span>.',
	'hash.prefix.text'      => '%label% is changed: ',
	'hash.prefix.html'      => '<strong>%label%</strong> is changed: <span>',
	'hash.glue.text'        => ', ',
	'hash.glue.html'        => '</span>, <span>',
	'hash.suffix.text'      => '.',
	'hash.suffix.html'      => '</span>.',
	'push_item.text'        => 'push %value%',
	'push_item.html'        => 'push <ins>%value%</ins>',
	'pop_item.text'         => 'remove %value%',
	'pop_item.html'         => 'remove <ins>%value%</ins>',
	'order_is_changed.text' => 'The order of %label% has been changed.',
	'order_is_changed.html' => 'The order of <strong>%label%</strong> has been changed.',
];