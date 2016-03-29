<?php
/*                 S E A R C H _ T R A I T . P H P
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
/** @file materials_database/search_trait.php
 *
 */
class Specialmaterials_database_searcht extends SpecialPage {
    public function __construct()
    {
	parent::__construct('materials_database_searcht');
    }
    public function execute($sub)
    {
	global $wgStylePath;    
	$dbr = wfGetDB(DB_SLAVE);
	$this->getOutput()->setPageTitle('Search by Trait');
	$dbw = wfGetDB( DB_MASTER );
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
    	$this->getOutput()->addHTML("<form action=http://".$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME']."/Special:materials_database_searcht method='post'><table><tr><td>Search by Trait</td><td><select required name='searcht'>");
	$searcht = $dbr->select('trait_table',array('trait_name'),"",__METHOD__);
	foreach ($searcht as $search1) {
	    $this->getOutput()->addHTML("<option value=".$search1->trait_name.">".ucwords(str_ireplace("_", " ", $search1->trait_name))."</option>");
	}
	$this->getOutput()->addHTML("</select></td></tr><tr><td><input type='submit' value='Search' name=searchtr> </td></tr></table></form>");
	if (isset($_POST['searcht'])) {
	    $res = $dbr->select(
		array('material', $_POST['searcht']),
		array('material_name', 'value', "{$dbr->tableName( $_POST['searcht'] )}.timestamp", "{$dbr->tablename($_POST['searcht'])}.status", 'mat_private', 'userID'),
		array('mat_id>0'), __METHOD__,
		array(),
		array($_POST['searcht'] => array('INNER JOIN', array("{$dbr->tableName('material')}.id=mat_id"))));
            /** Fetching trait data */
            $traitdata = $dbr->select('trait_table',array('t_type,u_type'),"trait_name='".$_POST['searcht']."'",__METHOD__);
	    foreach ($traitdata as $data) {
		$tdata[0] = $data->t_type;
		$tdata[1] = $data->u_type;
	    }
            /** Fetching trait type */
            $trait_type = $dbw->query("SELECT type FROM `trait_type` WHERE id='".$tdata[0]."'");
            foreach ($trait_type as $type) {
		$traittype = $type->type;
	    }
            /** Displaying trait type */
            $this->getOutput()->addHTML("<br><b>Trait Type: </b>".ucwords(str_ireplace("_", " ", $traittype))."&emsp;");
            /** Fetching trait unit */
            $trait_unit = $dbw->query("SELECT units FROM `trait_units` WHERE id='".$tdata[1]."'");
            foreach ($trait_unit as $unit) {
		$traitunit = $unit->units;
	    }
            /** Displaying trait unit */
            $this->getOutput()->addHTML("<b>Trait Unit: </b>".$traitunit."<br><br>");
            /** Creating table for displaying material data for trait */
            $this->getOutput()->addHTML("<table border='1' width='550' height='30' cellspacing='1' cellpadding='3'><tr><th>Material Name</th><th>".ucwords(str_ireplace("_", " ", $_POST['searcht']))."</th><th>Timestamp</th><th>Status</th></tr>");
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
