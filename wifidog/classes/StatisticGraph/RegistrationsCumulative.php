<?php
/********************************************************************\
 * This program is free software; you can redistribute it and/or    *
 * modify it under the terms of the GNU General Public License as   *
 * published by the Free Software Foundation; either version 2 of   *
 * the License, or (at your option) any later version.              *
 *                                                                  *
 * This program is distributed in the hope that it will be useful,  *
 * but WITHOUT ANY WARRANTY; without even the implied warranty of   *
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the    *
 * GNU General Public License for more details.                     *
 *                                                                  *
 * You should have received a copy of the GNU General Public License*
 * along with this program; if not, contact:                        *
 *                                                                  *
 * Free Software Foundation           Voice:  +1-617-542-5942       *
 * 59 Temple Place - Suite 330        Fax:    +1-617-542-2652       *
 * Boston, MA  02111-1307,  USA       gnu@gnu.org                   *
 *                                                                  *
 \********************************************************************/
/**@file RegistrationsCumulative.php
 * @author Copyright (C) 2005 Philippe April and Technologies Coeus inc. 
 */

require_once BASEPATH.'include/common.php';
require_once BASEPATH.'classes/StatisticGraph.php';

/* An abstract class.  All statistics must inherit from this class */
class RegistrationsCumulative extends StatisticGraph
{
	/** Get the Graph's name.  Must be overriden by the report class 
	 * @return a localised string */
	public static function getGraphName()
	{
		return _("Cumulative number of validated users for the selected network(s)");
	}

	/** Constructor, must be called by subclasses */
	protected function __construct()
	{
		parent :: __construct();
	}

	/** Return the actual Image data  
	 * Classes must override this.
	 * @param $child_html The child method's return value
	 * @return A html fragment 
	 */
	public function showImageData()
	{
		require_once ("Image/Graph.php");
		global $db;
		
		$Graph = & Image_Graph :: factory("Image_Graph", array (600, 200));
		$Plotarea = & $Graph->add(Image_Graph :: factory("Image_Graph_Plotarea"));
		$Dataset = & Image_Graph :: factory("Image_Graph_Dataset_Trivial");
		$Area = & Image_Graph :: factory("Image_Graph_Plot_Area", $Dataset);
		$Area->setFillColor("#9db8d2");
		$Plot = & $Plotarea->add($Area);

		$total = 0;
		
		$network_constraint=self::$stats->getSqlNetworkConstraint('account_origin');
					$date_constraint=self::$stats->getSqlDateConstraint('reg_date');
		$db->ExecSql("SELECT COUNT(users) AS num_users, date_trunc('month', reg_date) AS month FROM users  WHERE account_status = ".ACCOUNT_STATUS_ALLOWED." ${date_constraint} {$network_constraint} GROUP BY date_trunc('month', reg_date) ORDER BY month", $registration_stats, false);

			
		if($registration_stats)
		{
		foreach ($registration_stats as $row)
		{
			$total += $row['num_users'];
			$Dataset->addPoint(substr($row['month'], 0, 7), $total);
		}
		}
		$Graph->done();
	}

} //End class
?>