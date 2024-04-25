<?php

namespace bs\framework\database;

/**
 * Class RelationsEnum
 * @package bs\framework\database
 *
 */
enum RelationsEnum
{
	public final const OneToOne = 'one_to_one';
	public final const OneToMany = 'one_to_many';
	public final const ManyToMany = 'many_to_many';
}
