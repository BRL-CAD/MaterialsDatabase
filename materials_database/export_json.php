<?php
/*                  E X P O R T _ J S O N . P H P
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
/** @file materials_database/export_json.php
 *
 */
class Specialmaterials_database_export_json extends SpecialPage {
    public function __construct()
    {
	parent::__construct('materials_database_export_json');
    }
    public function execute($sub)
    {
	global $wgStylePath;    
	$name = $this->getUser()->getId();
	$dbr = wfGetDB(DB_SLAVE);
	$dbw = wfGetDB(DB_MASTER);
        $isadmin = false;
        $admincheck = $dbw->query("SELECT ug_group FROM `user_groups` WHERE ug_user=".$name."");
        $i = 0;
        foreach ($admincheck as $checker) {
            $admin[$i] = $checker->ug_group;
            $i++;
        }
        if($i != 0) {
            $isadmin = true;
        }
	if ($this->getUser()->isLoggedIn()) {
    
	    /** Include navigation bar */
	    include("navigation.php");

	    $this->getOutput()->setPageTitle('Export to json');
            /** Display form to select a trait to export */
	    $this->getOutput()->addHTML("<form action='http://".$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME']."/Special:materials_database_export_json' method='post'><table><tr><td>Export by Trait</td><td><select required name='exporttrait'>");
	    $searcht = $dbr->select('trait_table',array('trait_name'),"",__METHOD__);
	    /** Fetch list of traits from traits table and display as a dropdown list */
            foreach ($searcht as $traitindex) {
		$this->getOutput()->addHTML("<option value=".$traitindex->trait_name.">".ucwords(str_ireplace("_", " ", $traitindex->trait_name))."</option>");
	    }
	    $this->getOutput()->addHTML("</select></td><td><input type='submit' value='Export' name=export_trait> </td></tr></table></form>");
            $this->getOutput()->addHTML("OR");
            /** Display form to select a material to export */
            $this->getOutput()->addHTML("<form action=http://".$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME']."/Special:materials_database_export_json method='post'><table><tr><td>Export by Material</td><td><select required name='exportmaterial'>");
            $exportmaterial = $dbr->select('material', array('material_name', 'mat_private', 'userID'), "", __METHOD__);
            /** Fetch list of materials from materials table and display as a dropdown list */
            foreach ($exportmaterial as $matindex) {
                if($matindex->mat_private == 0 || $matindex->mat_private == 1 && $matindex->userID == $name || $isadmin == true) {
                     $this->getOutput()->addHTML("<option value=".$matindex->material_name.">".ucwords(str_ireplace("_", " ", $matindex->material_name))."</option>");
                }     
            }
            $this->getOutput()->addHTML("</select></td><td><input type='submit' value='Export' name='export_material'> </td></tr></table></form>");     
	    /** EXPORT BY TRAIT */
            if (isset($_POST['export_trait'])) {
		$this->getOutput()->disable();
                /** Fetch material names for trait */
		$matdel = $dbr->select('material', array('material_name', 'id', 'mat_private', 'userID'), "", __METHOD__);
		$f = 0;
		foreach ($matdel as $samedata) {
                    $matprivacy[$f] = $samedata->mat_private;
                    $matowner[$f] = $samedata->userID;
                    if($samedata->mat_private == 0 || $samedata->mat_private == 1 && $samedata->userID == $name || $isadmin == true) {
                        $arraymaterial[$f] = $samedata->material_name;
                        $arraymaterialid[$f] = $samedata->id;
                        $mat_counter = $f + 1;
                        $f++;
                    }
		}
		/** Fetch material values for trait */
                $valuebp = $dbr->select($_POST['exporttrait'], array('value', 'mat_id'), "", __METHOD__);
		$h = 0;
		foreach ($valuebp as $samedata) {
                    if($arraymaterialid[$h] == $samedata->mat_id) {
                        $arrayexport[$h] = $samedata->value;
                        $h++;
                        if($h == $mat_counter) {
                            break;
                        }
                    }
		}
                /** Combine arrays of material name and material value for trait */
		$combine = array_combine($arraymaterial, $arrayexport);
		$export = array();
		for ($i = 0; $i < $mat_counter; $i++) {
		    $export[] = array('Material' => ucwords(str_ireplace("_", " ", $arraymaterial[$i])), 'Value' => $arrayexport[$i]);
		}
		/** Encode the combined material data in json */
                $json_material = json_encode($export);
		/** Set the name of exporting trait as file name */
                $myFile = $_POST['exporttrait'].".json";
		/** Write json data to file */
                echo $json_material;
		/** Output Handling */
	        header("Pragma: public");
	        header("Expires: 0");
	        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	        header("Cache-Control: private",false);
	        header("Content-Transfer-Encoding: json;\n");
	        header("Content-Disposition: attachment; filename=$myFile");
	        header("Content-Type: application/force-download");
	        header("Content-Type: application/octet-stream");
	        header("Content-Type: application/download");
	        header("Content-Description: File Transfer");
		/** Prevent any further output */
	        die;
	    }
            /** EXPORT BY MATERIAL */
            /** Fetch material ID for identified materials in trait tables */
            if (isset($_POST['export_material'])) {
                $resid = $dbr->select('material',array('id'),"material_name='".$_POST['exportmaterial']."'",__METHOD__);
                foreach ($resid as $d) {
                    $r[0] = $d->id;
                }
            /** Fetch list of trait names and their unit types from trait table */
            $restype = $dbr->select('trait_table',array('trait_name','u_type'),"",__METHOD__);
            $g = 0;
            foreach ($restype as $samedata) {
                $traitnames[$g] = $samedata->trait_name;
                $traitunits[$g] = $samedata->u_type;
                $g++;
            }
            for ($i = 0; $i < sizeof($traitnames); $i++ ) {
                /** Fetch material value for each trait */
                $restrait = $dbr->select(
                        array("{$dbr->tableName( $traitnames[$i] )}"),
                        array('value'),
                        array("{$dbr->tableName( $traitnames[$i] )}.mat_id='".$r[0]."'"),__METHOD__
                );
                foreach ($restrait as $row) {
                    $output_matvalue[$i] = $row->value;
                }
                /** Fetch unit of each trait using unit type (fetched from earlier query) */
                $resunit = $dbr->select(
                    array("{$dbr->tableName( 'trait_units' )}"),
                    array('units'),                      
                    array("{$dbr->tableName( 'trait_units' )}.id='".$traitunits[$i]."'"),__METHOD__
                );
                foreach ($resunit as $row) {
                    $output_traitunit[$i] = $row->units;
                }
            }
            /** Combine arrays of trait name and trait value for material */
            $combine = array_combine($traitnames, $output_matvalue);
            /** Combine previous result (trait name and value) with unit of traits */
            $combines = array_combine($combine, $output_traitunit);
            $export = array();
            for ($i = 0; $i < sizeof($traitnames); $i++) {
                $export[] = array('Trait' => ucwords(str_ireplace("_", " ", $traitnames[$i])),
                    'Value' => $output_matvalue[$i], 'Unit' => $output_traitunit[$i]);
            }
            /** Encode the combined material data in json */
            $json_material = json_encode($export);
            /** Set the name of exporting material as file name */
            $myFile = $_POST['exportmaterial'].".json";
            /** Write json data to file */
            echo $json_material;
            /** Output Handling */
	    header("Pragma: public");
	    header("Expires: 0");
	    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	    header("Cache-Control: private",false);
	    header("Content-Transfer-Encoding: json;\n");
	    header("Content-Disposition: attachment; filename=$myFile");
	    header("Content-Type: application/force-download");
	    header("Content-Type: application/octet-stream");
	    header("Content-Type: application/download");
	    header("Content-Description: File Transfer");
            /** Prevent any further output */
	    die;
            }            
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
