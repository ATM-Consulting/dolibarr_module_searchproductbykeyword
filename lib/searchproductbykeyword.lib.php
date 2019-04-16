<?php
/* <one line to give the program's name and a brief idea of what it does.>
 * Copyright (C) 2015 ATM Consulting <support@atm-consulting.fr>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *	\file		lib/searchproductbykeyword.lib.php
 *	\ingroup	searchproductbykeyword
 *	\brief		This file is an example module library
 *				Put some comments here
 */

function searchproductbykeywordAdminPrepareHead()
{
    global $langs, $conf;

    $langs->load("searchproductbykeyword@searchproductbykeyword");

    $h = 0;
    $head = array();

    $head[$h][0] = dol_buildpath("/searchproductbykeyword/admin/searchproductbykeyword_setup.php", 1);
    $head[$h][1] = $langs->trans("Parameters");
    $head[$h][2] = 'settings';
    $h++;
    $head[$h][0] = dol_buildpath("/searchproductbykeyword/admin/searchproductbykeyword_about.php", 1);
    $head[$h][1] = $langs->trans("About");
    $head[$h][2] = 'about';
    $h++;

    // Show more tabs from modules
    // Entries must be declared in modules descriptor with line
    //$this->tabs = array(
    //	'entity:+tabname:Title:@searchproductbykeyword:/searchproductbykeyword/mypage.php?id=__ID__'
    //); // to add new tab
    //$this->tabs = array(
    //	'entity:-tabname:Title:@searchproductbykeyword:/searchproductbykeyword/mypage.php?id=__ID__'
    //); // to remove a tab
    complete_head_from_modules($conf, $langs, $object, $head, $h, 'searchproductbykeyword');

    return $head;
}

/**
 * Return array of tabs to used on pages for third parties cards.
 *
 * @param 	searchproductbykeyword	$object		Object company shown
 * @return 	array				Array of tabs
 */
function searchproductbykeyword_prepare_head(searchproductbykeyword $object)
{
    global $db, $langs, $conf, $user;
    $h = 0;
    $head = array();
    $head[$h][0] = dol_buildpath('/searchproductbykeyword/card.php', 1).'?id='.$object->id;
    $head[$h][1] = $langs->trans("searchproductbykeywordCard");
    $head[$h][2] = 'card';
    $h++;
	
	// Show more tabs from modules
    // Entries must be declared in modules descriptor with line
    // $this->tabs = array('entity:+tabname:Title:@searchproductbykeyword:/searchproductbykeyword/mypage.php?id=__ID__');   to add new tab
    // $this->tabs = array('entity:-tabname:Title:@searchproductbykeyword:/searchproductbykeyword/mypage.php?id=__ID__');   to remove a tab
    complete_head_from_modules($conf,$langs,$object,$head,$h,'searchproductbykeyword');
	
	return $head;
}

function getFormConfirmsearchproductbykeyword(&$PDOdb, &$form, &$object, $action)
{
    global $langs,$conf,$user;

    $formconfirm = '';

    if ($action == 'validate' && !empty($user->rights->searchproductbykeyword->write))
    {
        $text = $langs->trans('ConfirmValidatesearchproductbykeyword', $object->ref);
        $formconfirm = $form->formconfirm($_SERVER['PHP_SELF'] . '?id=' . $object->id, $langs->trans('Validatesearchproductbykeyword'), $text, 'confirm_validate', '', 0, 1);
    }
    elseif ($action == 'delete' && !empty($user->rights->searchproductbykeyword->write))
    {
        $text = $langs->trans('ConfirmDeletesearchproductbykeyword');
        $formconfirm = $form->formconfirm($_SERVER['PHP_SELF'] . '?id=' . $object->id, $langs->trans('Deletesearchproductbykeyword'), $text, 'confirm_delete', '', 0, 1);
    }
    elseif ($action == 'clone' && !empty($user->rights->searchproductbykeyword->write))
    {
        $text = $langs->trans('ConfirmClonesearchproductbykeyword', $object->ref);
        $formconfirm = $form->formconfirm($_SERVER['PHP_SELF'] . '?id=' . $object->id, $langs->trans('Clonesearchproductbykeyword'), $text, 'confirm_clone', '', 0, 1);
    }

    return $formconfirm;
}