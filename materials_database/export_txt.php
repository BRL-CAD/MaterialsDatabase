<?php
/*                  E X P O R T _ T X T . P H P
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
/** @file materials_database/export_txt.php
 *
 */
/** This page exports material data for traits to txt format */
class Specialmaterials_database_export_txt extends SpecialPage {
    public function __construct()
    {
	parent::__construct('materials_database_export_txt');
    }
    public function execute($sub)
    {
	global $wgStylePath;
	$dbr = wfGetDB(DB_SLAVE);
	$this->getOutput()->setPageTitle('Export to txt');
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
        if ($this->getUser()->isLoggedIn()) {
            /** Include navigation bar */
            include("navigation.php");
            /** Display form to select a trait to export */
            $this->getOutput()->addHTML("<form action=http://".$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME']."/Special:materials_database_export_txt method='post'><table><tr><td>Export by Trait</td><td><select required name='exporttrait'>");
            $exporttrait = $dbr->select('trait_table',array('trait_name'),"",__METHOD__);
            /** Fetch list of traits from traits table and display as a dropdown list*/
            foreach ($exporttrait as $exportindex) {
                $this->getOutput()->addHTML("<option value=".$exportindex->trait_name.">".ucwords(str_ireplace("_", " ", $exportindex->trait_name))."</option>");
            }
            $this->getOutput()->addHTML("</select></td><td><input type='submit' value='Export' name='export_trait'> </td></tr></table></form>");
            $this->getOutput()->addHTML("OR");
            /** Display form to select a material to export */
            $this->getOutput()->addHTML("<form action=http://".$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME']."/Special:materials_database_export_txt method='post'><table><tr><td>Export by Material</td><td><select required name='exportmaterial'>");
            $exportmaterial = $dbr->select('material', array('material_name', 'mat_private', 'userID'), "", __METHOD__);
            /** Fetch list of materials from materials table and display as a dropdown list */
            foreach ($exportmaterial as $matindex) {
                if($matindex->mat_private == 0 || $matindex->mat_private == 1 && $matindex->userID == $curruser || $isadmin == true) {
                    $this->getOutput()->addHTML("<option value=".$matindex->material_name.">".ucwords(str_ireplace("_", " ", $matindex->material_name))."</option>");
                }
            }
            $this->getOutput()->addHTML("</select></td><td><input type='submit' value='Export' name='export_material'> </td></tr></table></form>");     
            /** EXPORT BY TRAIT */
            /** Set the name of exporting trait as file name */
            if (isset($_POST['exporttrait'])) {
                $filename = $_POST['exporttrait'].".txt";
            }
            if (isset($_POST['export_trait'])) {
                /** Join all tables required to access material values for selected trait */
                $res = $dbr->select(
                array('material', $_POST['exporttrait']),
                array('material_name', 'value', "{$dbr->tableName( $_POST['exporttrait'] )}.timestamp", "{$dbr->tablename('material')}.status", 'mat_private', 'userID'),
                array('mat_id>0'),__METHOD__,
                array(),
                array($_POST['exporttrait'] => array('INNER JOIN', array("{$dbr->tableName('material')}.id=mat_id"))));
                /** Write column names and format output for better readability */
                echo str_pad ("Material Name", 20, " ", STR_PAD_RIGHT);
                echo str_pad ("Value", 20, " ", STR_PAD_LEFT)."\n";
                echo str_pad ("", 40, "=", STR_PAD_BOTH)."\n";
                /** Fetch material values for the trait from trait table */
                foreach ($res as $row) {
                    if($row->mat_private == 0 || $row->mat_private == 1 && $row->userID == $curruser || $isadmin == true) {
                        $output_matname = ucwords(str_ireplace("_", " ", $row->material_name));
                        $output_matvalue = $row->value;
                        /** Write each material name and its value and format output */
                        echo str_pad ($output_matname, 20, " ", STR_PAD_RIGHT);
                        echo str_pad ($output_matvalue, 20, " ", STR_PAD_LEFT)."\n";
                    }
                }
                /** Output Handling */
                header("Pragma: public");
                header("Expires: 0");
                header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
                header("Cache-Control: private",false);
                header("Content-Transfer-Encoding: binary;\n");
                header("Content-Disposition: attachment; filename=$filename");
                header("Content-Type: application/force-download");
                header("Content-Type: application/octet-stream");
                header("Content-Type: application/download");
                header("Content-Description: File Transfer");
                /** Prevent any further output */
                die;
            }
            /** EXPORT BY MATERIAL */
            /** Set the name of exporting material as file name */
            if (isset($_POST['exportmaterial'])) {  
                $filename = $_POST['exportmaterial'].".txt";
            }
            if (isset($_POST['export_material'])) {
                $resid = $dbr->select('material',array('id'),"material_name='".$_POST['exportmaterial']."'",__METHOD__);
                foreach ($resid as $d) {
                    $r[0] = $d->id;
                }
                $restype = $dbr->select('trait_table',array('trait_name','u_type'),"",__METHOD__);
                $g = 0;
                foreach ($restype as $samedata) {
                    $traitnames[$g] = $samedata->trait_name;
                    $traitunits[$g] = $samedata->u_type;
                    $g++;
                }
                /** Write column names and format output for better readability */
                echo str_pad ("Trait Name", 20, " ", STR_PAD_RIGHT);
                echo str_pad ("Value", 20, " ", STR_PAD_LEFT);
                echo str_pad ("Unit", 20, " ", STR_PAD_LEFT)."\n";
                echo str_pad ("", 60, "=", STR_PAD_BOTH)."\n";
                for ($i = 0; $i < sizeof($traitnames); $i++ ) {
                    /** Select value of selected material for each trait */
                    $restrait = $dbr->select(
                    array("{$dbr->tableName( $traitnames[$i] )}"),
                    array('value'),                      
                    array("{$dbr->tableName( $traitnames[$i] )}.mat_id='".$r[0]."'"),__METHOD__
                    );
                    /** Select unit of trait for each trait */
                    $resunit = $dbr->select(
                            array("{$dbr->tableName( 'trait_units' )}"),
                            array('units'),
                            array("{$dbr->tableName( 'trait_units' )}.id='".$traitunits[$i]."'"),__METHOD__
                    );
                    /** Fetch trait name */
                    $output_traitname = ucwords(str_ireplace("_", " ", $traitnames[$i]));
                    /** Fetch trait value */
                    foreach ($restrait as $row) {
                        $output_matvalue = $row->value;
                    }
                    /** Fetch trait unit */
                    foreach ($resunit as $row) {
                        $output_traitunit = $row->units;
                    }
                    /** Write each material name and its value and format output */
                    echo str_pad ($output_traitname, 20, " ", STR_PAD_RIGHT);
                    echo str_pad ($output_matvalue, 20, " ", STR_PAD_LEFT);
                    echo str_pad ($output_traitunit, 20, " ", STR_PAD_LEFT)."\n";
                }
                /** Output Handling */
                header("Pragma: public");
                header("Expires: 0");
                header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
                header("Cache-Control: private",false);
                header("Content-Transfer-Encoding: binary;\n");
                header("Content-Disposition: attachment; filename=$filename");
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
