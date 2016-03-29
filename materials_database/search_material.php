<?php
/*              S E A R C H _ M A T E R I A L . P H P
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
/** @file materials_database/search_material.php
 *
 */
class Specialmaterials_database_searchm extends SpecialPage {
    public function __construct()
    {
	parent::__construct('materials_database_searchm');
    }
    public function execute($sub)
    {    
	global $wgStylePath;
	$dbr = wfGetDB(DB_SLAVE);
	$this->getOutput()->setPageTitle('Search by Material');
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
	$this->getOutput()->addHTML("<form action='http://".$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME']."/Special:materials_database_searchm' method='post'><table>");
	$this->getOutput()->addHTML("<tr><td>Search by Material</td><td><select required name='materialdel'>");
	$matdel = $dbr->select('material', array('material_name', 'mat_private', 'userID'),"",__METHOD__);
	foreach ($matdel as $utype) {
	    if($utype->mat_private == 0 || $utype->mat_private == 1 && $utype->userID == $curruser || $isadmin == true) {
                $this->getOutput()->addHTML("<option  value=".$utype->material_name.">".ucwords(str_ireplace("_", " ", $utype->material_name))."</option>");
            }
	}
	$this->getOutput()->addHTML("</select></td></tr><tr><td><input type='submit' value='Search' name='searchm' ></td></tr></table></form>");
	if (isset($_POST['searchm'])) {
	    $res3 = $dbr->select('material',array('mat_type,id'),"material_name='".$_POST['materialdel']."'",__METHOD__);
	    foreach ($res3 as $d) {
		$r[0] = $d->mat_type;
		$r[1] = $d->id;
	    }
	    $res2 = $dbr->select('trait_table',array('trait_name','t_type','u_type'),"",__METHOD__);
	    $g = 0;
	    foreach ($res2 as $samedata) {
		$array[$g] = $samedata->trait_name;
                $traitunits[$g] = $samedata->u_type;
		$g++;
	    }
            /** Displaying material name as a header */
            $this->getOutput()->addHTML("<br><b>Material Name: </b>".ucwords(str_ireplace("_", " ", $_POST['materialdel']))."&emsp;");
            /** Fetching material type */
            $mat_type = $dbw->query("SELECT mtype FROM `material_type` WHERE id='".$r[0]."'");
            foreach ($mat_type as $type) {
		$mattype = $type->mtype;
	    }
            /** Displaying material type */
            $this->getOutput()->addHTML("<br><b>Material Type: </b>".ucwords(str_ireplace("_", " ", $mattype)));
            /** Fetching material description */
            $mat_description = $dbw->query("SELECT description FROM `material` WHERE material_name='".str_ireplace("_", " ", strtolower($_POST['materialdel']))."'");
            foreach ($mat_description as $description) {
		$matdesc = $description->description;
	    }
            /** Displaying material description */
            $this->getOutput()->addHTML("<br><b>Description: </b>".$matdesc."<br><br>");
            /** Creating a common table to display trait values from different tables */
            $this->getOutput()->addHTML("<table border='1' width='550' height='30' cellspacing='1' cellpadding='3'><tr><th>Trait Name</th><th>Value</th><th>Unit</th><th>Timestamp</th><th>Status</th></tr>");
	    /** Populating the table with trait values from respective tables */
            for ($i = 0; $i < sizeof($array); $i++ ) {
                $res = $dbr->select(
		array('material',$array[$i]),
		array('material_name','value',"{$dbr->tableName( $array[$i] )}.timestamp","{$dbr->tablename($array[$i])}.status" ),
		array("mat_id='".$r[1]."'"),__METHOD__,
		array(),
		array($array[$i] => array('INNER JOIN', array("{$dbr->tableName('material')}.id='".$r[1]."'")))
                );
                /** Displaying trait name in each row */
                $this->getOutput()->addHTML("<tr><td>".ucwords(str_ireplace("_", " ", $array[$i]))."</td>");
                foreach ($res as $row) {
                    $this->getOutput()->addHTML("<td>".$row->value."</td>");
                    /** Fetching trait unit */
                    $trait_unit = $dbw->query("SELECT units FROM `trait_units` WHERE id='".$traitunits[$i]."'");
                    foreach ($trait_unit as $unit) {
                        $traitunit = $unit->units;
                    }
                    $this->getOutput()->addHTML("<td>".$traitunit."</td>");
                    $this->getOutput()->addHTML("<td>".$row->timestamp."</td>");
                    $this->getOutput()->addHTML("<td>".$row->status."</td>");
                }
                $this->getOutput()->addHTML("</tr>");
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
