<?php
/*
 * Copyright (c) 2013, webvariants GbR, http://www.webvariants.de
 *
 * This file is released under the terms of the MIT license. You can find the
 * complete text in the attached LICENSE file or online at:
 *
 * http://www.opensource.org/licenses/mit-license.php
 */

interface pac_DestinationProvider {
	/**
	 * returns a pac_Destination
	 *
	 * @param  string $destination   the individual id of the destination
	 * @return pac_Destination  destination
	 */
	public function getDestination($destination);
}
