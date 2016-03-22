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
	/** Include navigation bar */
	include("navigation.php");
        /** Display form to select a trait to export */
    	$this->getOutput()->addHTML("<form action=http://".$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME']."/Special:materials_database_export_txt method='post'><table><tr><td>Export by Trait</td><td><select required name='exporttrait'>");
	$exporttrait = $dbr->select('trait_table',array('trait_name'),"",__METHOD__);
	/** Get list of traits from traits table and display as a dropdown list*/
        foreach ($exporttrait as $exportindex) {
	    $this->getOutput()->addHTML("<option value=".$exportindex->trait_name.">".ucwords(str_ireplace("_", " ", $exportindex->trait_name))."</option>");
	}
	$this->getOutput()->addHTML("</select></td></tr><tr><td><input type='submit' value='Export' name=export> </td></tr></table></form>");
        /** Set the name of exporting trait as file name */
        if (isset($_POST['exporttrait'])) {
            $filename = $_POST['exporttrait'].".txt";
        }
        if (isset($_POST['export'])) {
            /** Join all tables required to access material values for selected trait */
            $res = $dbr->select(
            array('material',$_POST['exporttrait']),
            array('material_name','value',"{$dbr->tableName( $_POST['exporttrait'] )}.timestamp","{$dbr->tablename('material')}.status" ),
            array('mat_id>0'),__METHOD__,
            array(),
            array($_POST['exporttrait'] => array('INNER JOIN', array("{$dbr->tableName('material')}.id=mat_id"))));
            /** Write column names and format output for better readability */
            echo str_pad ("Material Name", 20, " ", STR_PAD_RIGHT);
            echo str_pad ("Value", 20, " ", STR_PAD_LEFT)."\n";
            echo str_pad ("", 40, "=", STR_PAD_BOTH)."\n";
            /** Fetch material values for the trait from trait table */
            foreach ($res as $row) {
                $output_matname = ucwords(str_ireplace("_", " ", $row->material_name));
                $output_matvalue = ucwords(str_ireplace("_", " ", $row->value));
                /** Write each material name and its value and format output */
                echo str_pad ($output_matname, 20, " ", STR_PAD_RIGHT);
                echo str_pad ($output_matvalue, 20, " ", STR_PAD_LEFT)."\n";
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
            die; /** Prevent any further output */
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
