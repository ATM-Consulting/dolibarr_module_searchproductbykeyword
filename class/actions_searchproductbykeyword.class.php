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
 * \file    class/actions_searchproductbykeyword.class.php
 * \ingroup searchproductbykeyword
 * \brief   This file is an example hook overload class file
 *          Put some comments here
 */

/**
 * Class Actionssearchproductbykeyword
 */
class Actionssearchproductbykeyword
{
	/**
	 * @var array Hook results. Propagated to $hookmanager->resArray for later reuse
	 */
	public $results = array();

	/**
	 * @var string String displayed by executeHook() immediately after return
	 */
	public $resprints;

	/**
	 * @var array Errors
	 */
	public $errors = array();

	/**
	 * Constructor
	 */
	public function __construct($db)
	{
		$this->db = $db;
	}

	/**
	 * Overloading the doActions function : replacing the parent's function with the one below
	 *
	 * @param   array()         $parameters     Hook metadatas (context, etc...)
	 * @param   CommonObject    &$object        The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param   string          &$action        Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	function doActions($parameters, &$object, &$action, $hookmanager)
	{
		/*$error = 0; // Error counter
		$myvalue = 'test'; // A result value

		print_r($parameters);
		echo "action: " . $action;
		print_r($object);

		if (in_array('somecontext', explode(':', $parameters['context'])))
		{
		  // do something only for the context 'somecontext'
		}

		if (! $error)
		{
			$this->results = array('myreturn' => $myvalue);
			$this->resprints = 'A text to show';
			return 0; // or return 1 to replace standard code
		}
		else
		{
			$this->errors[] = 'Error message';
			return -1;
		}*/
	}

    function formAddObjectLine ($parameters, &$object, &$action, $hookmanager)
    {

        global $db, $langs, $user, $conf, $inputalsopricewithtax;

        $TContext = explode(':',$parameters['context']);

        if (in_array('propalcard',$TContext) || in_array('ordercard',$TContext) || in_array('invoicecard',$TContext)
            || in_array('supplier_proposalcard',$TContext) || in_array('ordersuppliercard',$TContext) || in_array('invoicesuppliercard',$TContext))
        {
            $element = $object->element;
            if ($element == 'order_supplier') $element = 'CommandeFournisseur';
            if ($element == 'invoice_supplier') $element = 'FactureFournisseur';
            if ($element == 'supplier_proposal') $element = 'SupplierProposal';
            ?>
            <script type="text/javascript">
                var spk_object_type = '<?php echo $element ?>';
                var spk_object_id = '<?php echo $object->id ?>';
                var spk_fk_soc = '<?php echo $object->socid; ?>';
            </script>
            <?php

            $is_fourn = (in_array('supplier_proposalcard',$TContext) || in_array('ordersuppliercard',$TContext) || in_array('invoicesuppliercard',$TContext)) ? 1 : 0;

            $colspan1 = 5;
            $colspan2 = 5;
            if (!empty($inputalsopricewithtax)) { $colspan1++; $colspan2++; }
            if (!empty($conf->global->PRODUCT_USE_UNITS)) $colspan1++;
            if (!empty($conf->margin->enabled))
            {
                $colspan1++;
                if ($user->rights->margins->creer && ! empty($conf->global->DISPLAY_MARGIN_RATES)) $colspan1++;
                if ($user->rights->margins->creer && ! empty($conf->global->DISPLAY_MARK_RATES)) $colspan1++;
            }

            $langs->load('searchproductbykeyword@searchproductbykeyword');
            ?>

            <tr class="liste_titre nodrag nodrop">
                <td colspan="<?php echo $colspan1; ?>"><?php echo $langs->trans('SearchByKeyword') ?><input type="hidden" id="fourn" name="fourn" value="<?php echo $is_fourn; ?>"></td>
<!--                <td align="right">--><?php //echo $langs->trans('Qty'); ?><!--</td>-->
                <td align="center" colspan="<?php echo $colspan2; ?>">&nbsp;<?php if (!empty($conf->global->SUBTOTAL_ALLOW_ADD_LINE_UNDER_TITLE)) { echo $langs->trans('subtotal_title_to_add_under_title'); } ?></td>
            </tr>
            <tr class="pair">
                <td colspan="<?php echo $colspan1; ?>">
                    <div id="ProductList">

                    </div>
                </td>
<!--                <td class="nobottom" align="right">-->
<!--                    <input id="qty_spk" type="text" value="1" size="5" class="flat" />-->
<!--                </td>-->
                <td valign="middle" align="center" colspan="<?php echo $colspan2; ?>">
                    <?php if (!empty($conf->global->SUBTOTAL_ALLOW_ADD_LINE_UNDER_TITLE)) {
                        dol_include_once('/subtotal/class/subtotal.class.php');
                        $TTitle = TSubtotal::getAllTitleFromDocument($object);
                        echo getHtmlSelectTitle($object);
                    } ?>
                    <input id="addline_spk" class="button" type="button" name="addline_spk" value="<?php echo $langs->trans('Add') ?>">
                </td>
            </tr>

            <?php

        }

        return 0;
    }
}
