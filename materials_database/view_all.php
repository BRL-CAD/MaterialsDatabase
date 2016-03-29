<?php
/*                     V I E W _ A L L . P H P
 * BRL-CAD
 *
 * Copyright (c) 1995-2013 United States Government as represented by
 * the U.S. Army Research Laboratory.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public License
 * version 2.1 as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this file; see the file named COPYING for more
 * information.
 */
/** @file materials_database/view_all.php
 *
 */

class Specialmaterials_database_viewall extends SpecialPage {
    public function __construct()
    {
	parent::__construct('materials_database_viewall');
    }
    public function execute($sub)
    {
	global $wgStylePath;    
	$dbr = wfGetDB(DB_SLAVE);
	$this->getOutput()->setPageTitle('View all Materials');
	$dbw = wfGetDB(DB_MASTER);
        $curruser = $this->getUser()->getId();
        $isadmin = false;
        $admincheck = $dbw->query("SELECT ug_group FROM `user_groups` WHERE ug_user=".$curruser."");
        $i = 0;
        foreach ($admincheck as $checker) {
            $admin[$i] = $checker->ug_group;
            $i++;
        }
        if($i != 0) {
            $isadmin = true;
        }
    	/** This code makes the navigation bar at the top */
	include("navigation.php");
	$res2 = $dbr->select('trait_table',array('trait_name','t_type','u_type'),"",__METHOD__);
        $g = 0;
        foreach ($res2 as $samedata) {
            $array[$g] = $samedata->trait_name;
            $traittypes[$g] = $samedata->t_type;
            $traitunits[$g] = $samedata->u_type;
            $count = $g + 1;
            $g++;
        }
        for ($i = 0; $i < $count; $i++) {
            $res = $dbr->select(
		array('material', $array[$i]),
		array('material_name', 'value', "{$dbr->tableName( $array[$i] )}.timestamp", "{$dbr->tableName( $array[$i]) }.status", 'mat_private', 'userID'),
		array('mat_id>0'),__METHOD__,
		array(),
		array($array[$i] => array('INNER JOIN', array("{$dbr->tableName('material')}.id=mat_id")))
            );
            /** Fetching trait type */
            $trait_type = $dbw->query("SELECT type FROM `trait_type` WHERE id='".$traittypes[$i]."'");
            foreach ($trait_type as $type) {
		$traittype = $type->type;
	    }
            /** Displaying trait type */
            $this->getOutput()->addHTML("<br><b>Trait Type: </b>".ucwords(str_ireplace("_", " ", $traittype))."&emsp;");
            /** Fetching trait unit */
            $trait_unit = $dbw->query("SELECT units FROM `trait_units` WHERE id='".$traitunits[$i]."'");
            foreach ($trait_unit as $unit) {
		$traitunit = $unit->units;
	    }
            /** Displaying trait unit */
            $this->getOutput()->addHTML("<b>Trait Unit: </b>".$traitunit."<br><br>");
            $this->getOutput()->addHTML("<table border='1' width='550' height='30' cellspacing='1' cellpadding='3'><tr><th>Material Name</th><th>".ucwords(str_ireplace("_", " ", $array[$i]))."</th><th>Timestamp</th><th>Status</th></tr>");
	    foreach ($res as $row) {
		if($row->mat_private == 0 || $row->mat_private == 1 && $row->userID == $curruser || $isadmin == true) {
                    $this->getOutput()->addHTML("<tr><td>".ucwords(str_ireplace("_", " ", $row->material_name))."</td><td>".$row->value."</td><td>".$row->timestamp."</td><td>".$row->status."</td></tr>");
                }    
	    }
	    $this->getOutput()->addHTML("</table><br>");
	}
    }
}

/*
 * Local Variables:
 * mode: PHP
 * tab-width: 8
 * End:
 * ex: shiftwidth=4 tabstop=8
 */
