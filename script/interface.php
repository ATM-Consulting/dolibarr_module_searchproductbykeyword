<?php

require '../config.php';
dol_include_once('/categories/class/categorie.class.php');
dol_include_once('/product/class/product.class.php');
dol_include_once('/comm/propal/class/propal.class.php');
dol_include_once('/commande/class/commande.class.php');
dol_include_once('/compta/facture/class/facture.class.php');
dol_include_once('/fourn/class/fournisseur.commande.class.php');
dol_include_once('/fourn/class/fournisseur.facture.class.php');
dol_include_once('/supplier_proposal/class/supplier_proposal.class.php');

$get=GETPOST('get');
$put=GETPOST('put');

switch ($get)
{
    case 'products':
        $keyword= GETPOST('keyword');
        $fk_soc = GETPOST('fk_soc');
        $is_supplier = GETPOST('is_supplier', 'int');

        $Tab =array(
            "TProduct" => _products($is_supplier, $keyword)
        );

        if (!empty($conf->global->PRODUIT_MULTIPRICES))
        {
            require_once DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php';
            $societe = new Societe($db);
            $societe->fetch($fk_soc);

            $Tab['default_price_level'] = 1;
            if ($societe->id > 0)
            {
                $Tab['default_price_level'] = $societe->price_level;
            }
        }

        __out($Tab,'json');

        break;
}

switch ($put)
{
    case 'addline':

        $object_type=GETPOST('object_type');
        $object_id=(int)GETPOST('object_id');
        $qty=(float)GETPOST('qty');
        $TProduct=GETPOST('TProduct');
        $TProductPrice=GETPOST('TProductPrice');
        $TProductQty=GETPOST('TProductQty');
        $txtva=(float)GETPOST('txtva');
        $fk_soc = GETPOST('fk_soc');

        if(!empty($TProduct)) {
            $o=new $object_type($db);
            $o->fetch($object_id);

            if(empty($o->thirdparty) && method_exists($o, 'fetch_thirdparty')) {
                $o->fetch_thirdparty();
            }

            foreach($TProduct as $k => $fk_product) {
                $p=new Product($db);
                $p->fetch($fk_product);

                $txtva = get_default_tva($mysoc, $o->thirdparty, $p->id);

                $price = 0;
                if (!empty($conf->global->PRODUIT_MULTIPRICES) && !empty($TProductPrice[$k])) {
                    $price = price2num($TProductPrice[$k]);

                    if (isset($p->multiprices_tva_tx[$o->thirdparty->price_level])) $txtva=$p->multiprices_tva_tx[$o->thirdparty->price_level];
                }
                if (empty($price)) $price = $p->price;

                $qty = $TProductQty[$k];

                if($object_type == 'facture'){
                    $res = $o->addline($p->description, $price, $qty, $txtva,0,0,$fk_product, 0, '', '', 0, 0, '', 'HT',0, Facture::TYPE_STANDARD, -1, 0, '',0, 0, null, '', '', 0, 100, '', $p->fk_unit , 0);
                }elseif($object_type == 'propal'){
                    $res = $o->addline($p->description, $price, $qty, $txtva,0,0,$fk_product,0.0, 'HT', 0.0,0, 0, -1, 0,0, 0, '', '','', '',0, $p->fk_unit);
                }elseif($object_type == 'commande'){
                    $res = $o->addline($p->description, $price, $qty, $txtva,0,0,$fk_product, 0, 0, 0, 'HT', 0, '', '', 0, -1, 0, 0, null, '', '',0, $p->fk_unit);
                }
                else
                {
                    /*$result = $p->get_buyprice(0, $qty, $fk_product, 'none', $fk_soc);
                    if ($result > 0) $price = $p->fourn_pu;
                    else $price = 0;*/
                    $fournprice_id = $TProductPrice[$k];
                    $sql = "SELECT pfp.ref_fourn, pfp.unitprice FROM ".MAIN_DB_PREFIX."product_fournisseur_price as pfp WHERE rowid='".$fournprice_id."'";
                    $resql = $db->query($sql);
                    $ref_fourn = '';
                    $price = 0;
                    if ($resql)
                    {
                        if ($db->num_rows($resql))
                        {
                            $obj = $db->fetch_object($resql);
                            $ref_fourn = $obj->ref_fourn;
                            $price = $obj->unitprice;
                        }
                        else
                        {
                            $fournprice_id = 0;
                        }
                    }
                    else
                    {
                        $fournprice_id = 0;
                    }

                    if($object_type == 'FactureFournisseur')
                    {
                        $res = $o->addline($p->description, $price, $txtva,0,0, $qty,$fk_product, 0, '', '', 0, '', 'HT', 0, -1, false, 0, null, 0, 0, $ref_fourn);
                    }
                    elseif ($object_type = 'CommandeFournisseur')
                    {
                        $res = $o->addline($p->description, $price, $qty, $txtva,0,0,$fk_product, $fournprice_id, $ref_fourn);
                    }
                    else // SupplierProposal
                    {
                        $res = $o->addline($p->description, $price, $qty, $txtva,0,0,$fk_product, 0, 'HT', 0, 0, 0, -1, 0, 0, $fournprice_id, $price, '', 0, $ref_fourn);
                    }
                }

            }


        }

        echo 1;

        break;
}

function _products($is_supplier=0, $keyword='')
{
    global $db, $conf, $langs, $fk_soc;

    dol_include_once('/core/class/html.form.class.php');
    $form = new Form($db);

    if (empty($keyword)) return array();

    $Tab = array();

    $sql = "SELECT DISTINCT t.rowid FROM ".MAIN_DB_PREFIX."product as t";
    $sql.= " WHERE t.ref LIKE '%".$keyword."%'";
    $sql.= " OR t.ref_ext LIKE '%".$keyword."%'";
    $sql.= " OR t.label LIKE '%".$keyword."%'";
    $sql.= " OR t.description LIKE '%".$keyword."%'";
    $sql.= " OR t.note LIKE '%".$keyword."%'";
    $sql.= " ORDER BY t.ref ASC";

    $res = $db->query($sql);
    if ($res)
    {
        if ($db->num_rows($res))
        {
            while ($obj = $db->fetch_object($res))
            {
                $prod = new Product($db);
                $prod->fetch($obj->rowid);
                $Tab[] = $prod;
            }
        }
    }

    $TProd = array();
    if (!empty($Tab))
    {
        if (empty($is_supplier))
        {
            foreach($Tab as $prod){
                if(empty($is_supplier) && $prod->status == 1) $TProd[] = $prod;
                elseif(! empty($is_supplier) && $prod->status_buy == 1) $TProd[] = $prod;
            }

            if (!empty($conf->global->SPK_DISPLAY_DESC_OF_PRODUCT))
            {
                require_once DOL_DOCUMENT_ROOT.'/core/lib/functions.lib.php';
                foreach ($TProd as &$o) $o->description = dol_html_entity_decode($o->description, ENT_QUOTES);
            }
            if(!empty($conf->global->PRODUCT_USE_UNITS)){
                foreach ($TProd as &$o){
                    $unit = $o->getLabelOfUnit();
                    $o->unit = $langs->trans($unit);
                }
            }
        }
        else
        {
            $array = $form->select_produits_fournisseurs_list($fk_soc, "", "", "", "", "", "", 1, 50, 1);
            foreach($Tab as $prod){
                foreach ($array as $pfp)
                {
                    $prod->pfp = array();
                    if ($pfp['value'] == $prod->ref)
                    {
                        $prod2 = clone $prod;
                        $prod2->pfp = $pfp;
                        $prod2->label = $pfp['label'];

                        $TProd[] = $prod2;
                    }
                }
            }
        }
    }

    return $TProd;
}
